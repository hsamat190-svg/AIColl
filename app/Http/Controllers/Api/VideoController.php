<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\VideoCollisionProtocol;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class VideoController extends Controller
{
    public function analyze(Request $request): JsonResponse
    {
        $request->validate([
            'clip' => ['required', 'file', 'max:51200'],
        ]);

        /** @var UploadedFile $file */
        $file = $request->file('clip');
        $path = $file->getRealPath();
        $chunk = '';
        if (is_string($path) && is_readable($path)) {
            $fh = fopen($path, 'rb');
            if ($fh !== false) {
                $chunk = (string) fread($fh, 65536);
                fclose($fh);
            }
        }

        $seed = hash('sha256', $file->getClientOriginalName().'|'.$file->getSize().'|'.$chunk);
        $traceId = substr($seed, 0, 16);

        $collisionScenario = $this->buildCollisionScenario($seed);
        $kinematics = $this->buildDemoKinematics($seed);
        $physicsAnalysis = $this->buildPhysicsAnalysis($seed, $collisionScenario, $kinematics);
        $ekPoint = (int) $physicsAnalysis['kinetic_energy_j']['point'];
        $repair = $this->buildRepairCostFromKineticEnergy($ekPoint, $seed);
        $inference = array_merge($kinematics, $repair);
        $collisionProtocol = app(VideoCollisionProtocol::class)->build(
            $seed,
            $collisionScenario,
            $kinematics,
            $physicsAnalysis,
            $inference,
        );
        $pipeline = $this->buildPipelineStages($seed);

        return response()->json([
            'status' => 'ok',
            'mode' => 'hybrid_cv_physics_demo',
            'trace_id' => $traceId,
            'file' => [
                'name' => $file->getClientOriginalName(),
                'size_bytes' => $file->getSize(),
                'mime' => $file->getMimeType(),
            ],
            'collision_scenario' => $collisionScenario,
            'physics_analysis' => $physicsAnalysis,
            'collision_protocol' => $collisionProtocol,
            'inference' => $inference,
            'pipeline' => $pipeline,
            'confidence' => round($this->floatInRange($seed, 'conf', 0.52, 0.91), 3),
            'training_hint' => __('Video training hint'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function buildCollisionScenario(string $seed): array
    {
        $u = $this->seedUnit($seed, 'scenario_kind');
        if ($u < 0.40) {
            $code = 'vehicle_obstacle';
        } elseif ($u < 0.82) {
            $code = 'vehicle_vehicle';
        } else {
            $code = 'uncertain';
        }

        $labelKey = match ($code) {
            'vehicle_obstacle' => 'Video scenario vehicle obstacle',
            'vehicle_vehicle' => 'Video scenario vehicle vehicle',
            default => 'Video scenario uncertain',
        };

        return [
            'code' => $code,
            'label' => __($labelKey),
            'confidence' => round(0.58 + $this->seedUnit($seed, 'scenario_conf') * 0.37, 3),
        ];
    }

    /**
     * @param  array{code: string, label: string, confidence: float}  $scenario
     * @param  array<string, mixed>  $inf
     * @return array<string, mixed>
     */
    protected function buildPhysicsAnalysis(string $seed, array $scenario, array $inf): array
    {
        $speed = $inf['relative_speed_kmh'];
        $vPt = $speed['point'] / 3.6;
        $vLo = $speed['low'] / 3.6;
        $vHi = $speed['high'] / 3.6;

        $m = (float) $inf['vehicle_mass_kg']['point'];
        $mLo = (float) $inf['vehicle_mass_kg']['low'];
        $mHi = (float) $inf['vehicle_mass_kg']['high'];

        $code = $scenario['code'];

        $vBlock = [
            'point' => round($vPt, 2),
            'low' => round($vLo, 2),
            'high' => round($vHi, 2),
        ];

        if ($code === 'vehicle_vehicle') {
            $m1 = (int) round($m * (0.82 + 0.16 * $this->seedUnit($seed, 'split1')));
            $m2 = (int) round($m * (0.88 + 0.22 * $this->seedUnit($seed, 'split2')));
            $m1 = max(650, min(2900, $m1));
            $m2 = max(650, min(2900, $m2));
            $mu = ($m1 * $m2) / ($m1 + $m2);
            $ekPt = 0.5 * $mu * $vPt * $vPt;
            $ekLo = 0.5 * $mu * $vLo * $vLo;
            $ekHi = 0.5 * $mu * $vHi * $vHi;

            return [
                'model' => 'two_body_reduced_mass',
                'v_rel_ms' => $vBlock,
                'm1_kg' => ['point' => $m1],
                'm2_kg' => ['point' => $m2],
                'reduced_mass_kg' => ['point' => round($mu, 2)],
                'kinetic_energy_j' => [
                    'point' => (int) round($ekPt),
                    'low' => (int) round($ekLo * 0.9),
                    'high' => (int) round($ekHi * 1.1),
                ],
                'summary' => __('Video physics summary two body'),
            ];
        }

        $ekPt = 0.5 * $m * $vPt * $vPt;
        $ekLo = 0.5 * $mLo * $vLo * $vLo;
        $ekHi = 0.5 * $mHi * $vHi * $vHi;

        $summaryKey = $code === 'uncertain'
            ? 'Video physics summary uncertain'
            : 'Video physics summary obstacle';

        return [
            'model' => 'one_body_obstacle',
            'v_rel_ms' => $vBlock,
            'm1_kg' => [
                'point' => (int) round($m),
                'low' => (int) round($mLo),
                'high' => (int) round($mHi),
            ],
            'm2_kg' => null,
            'reduced_mass_kg' => null,
            'kinetic_energy_j' => [
                'point' => (int) round($ekPt),
                'low' => (int) round($ekLo * 0.88),
                'high' => (int) round($ekHi * 1.12),
            ],
            'summary' => __($summaryKey),
        ];
    }

    /**
     * Демо-кинематика: скорость и масса из сида (пока без реального CV).
     * Диапазон скорости расширен вниз, чтобы «лёгкие» клипы давали меньше Eₖ и меньше повреждений.
     *
     * @return array<string, mixed>
     */
    protected function buildDemoKinematics(string $seed): array
    {
        $locale = app()->getLocale();
        $speed = round($this->floatInRange($seed, 'speed', 6, 130), 1);
        $speedLow = max(1, round($speed - $this->floatInRange($seed, 'speed_lo', 3, 18), 1));
        $speedHigh = round($speed + $this->floatInRange($seed, 'speed_hi', 4, 20), 1);

        $mass = (int) round($this->floatInRange($seed, 'mass', 980, 2180));
        $massLow = (int) round($mass - $this->floatInRange($seed, 'mass_lo', 80, 220));
        $massHigh = (int) round($mass + $this->floatInRange($seed, 'mass_hi', 90, 240));

        $models = $this->vehicleLabels($locale);
        $modelIdx = (int) (hexdec(substr(hash('sha256', $seed.'|model'), 0, 4)) % count($models));
        $vehicleLabel = $models[$modelIdx];

        return [
            'relative_speed_kmh' => [
                'point' => $speed,
                'low' => $speedLow,
                'high' => $speedHigh,
            ],
            'vehicle_mass_kg' => [
                'point' => $mass,
                'low' => $massLow,
                'high' => $massHigh,
            ],
            'vehicle_model' => $vehicleLabel,
        ];
    }

    /**
     * Ориентировочная стоимость ремонта только от Eₖ (без поля «% повреждений» в ответе).
     *
     * @return array{repair_cost_kzt: array{point: int, low: int, high: int}}
     */
    protected function buildRepairCostFromKineticEnergy(int $ekJoules, string $seed): array
    {
        $baseCost = $this->floatInRange($seed, 'cost_base', 420_000, 2_200_000);
        $logEk = log10(max(5_000, $ekJoules));
        $factor = 0.18 + (($logEk - 3.7) / 2.45) * 0.98;
        $factor = max(0.16, min(1.28, $factor));
        $cost = (int) round($baseCost * $factor);
        $costLow = (int) round($cost * 0.76);
        $costHigh = (int) round($cost * 1.22);

        return [
            'repair_cost_kzt' => [
                'point' => $cost,
                'low' => $costLow,
                'high' => $costHigh,
            ],
        ];
    }

    /**
     * @return list<string>
     */
    protected function vehicleLabels(string $locale): array
    {
        if ($locale === 'kk') {
            return [
                'Toyota Camry / орта седан',
                'Hyundai Accent / шағын седан',
                'Kia Sportage / қиырлық SUV',
                'Volkswagen Polo / хэтчбек B',
                'Lada Vesta / отандық седан',
                'Mercedes-Benz E / бизнес седан',
                'Ford Focus / шағын отбасылық',
                'Toyota RAV4 / компакт SUV',
            ];
        }

        return [
            'Toyota Camry / седан D',
            'Hyundai Solaris / компакт',
            'Kia Sportage / кроссовер',
            'Volkswagen Polo / хэтчбек B',
            'Lada Vesta / седан',
            'Mercedes-Benz E-класс / бизнес-седан',
            'Ford Focus / компакт',
            'Toyota RAV4 / SUV',
        ];
    }

    /**
     * @return list<array{stage: string, status: string, ms: int}>
     */
    protected function buildPipelineStages(string $seed): array
    {
        $stages = [
            ['stage' => 'decode_mux', 'salt' => 'p0'],
            ['stage' => 'scenario_classifier', 'salt' => 'p0b'],
            ['stage' => 'motion_stabilize', 'salt' => 'p1'],
            ['stage' => 'contact_frame_search', 'salt' => 'p2'],
            ['stage' => 'optical_flow_features', 'salt' => 'p3'],
            ['stage' => 'fusion_head_regress', 'salt' => 'p4'],
            ['stage' => 'physics_regression', 'salt' => 'p4b'],
            ['stage' => 'calibration_isotonic', 'salt' => 'p5'],
        ];
        $out = [];
        foreach ($stages as $row) {
            $ms = (int) round(6 + $this->floatInRange($seed, $row['salt'], 0, 120));
            $out[] = [
                'stage' => $row['stage'],
                'status' => 'ok',
                'ms' => $ms,
            ];
        }

        return $out;
    }

    protected function floatInRange(string $seed, string $salt, float $a, float $b): float
    {
        $u = $this->seedUnit($seed, $salt);

        return $a + $u * ($b - $a);
    }

    protected function seedUnit(string $seed, string $salt): float
    {
        $hex = substr(hash('sha256', $seed.'|'.$salt), 0, 8);
        $n = hexdec($hex);

        return $n / 0xffffffff;
    }
}
