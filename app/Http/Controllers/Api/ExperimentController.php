<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Experiment;
use App\Services\AiPhysicsClient;
use App\Services\LocalCollisionPhysics;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class ExperimentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $experiments = Experiment::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return response()->json($experiments);
    }

    public function store(Request $request, AiPhysicsClient $client): JsonResponse
    {
        $validated = $this->validateInput($request);
        $payload = $this->buildPhysicsPayload($validated);

        $physics = $this->resolvePhysics($client, $payload);
        $ai = $this->resolveAiPrediction($client, $payload, $physics);

        $comparison = $this->buildComparison($physics, $ai);

        $experiment = Experiment::create([
            'user_id' => $request->user()->id,
            'input' => $payload,
            'physics_result' => $physics,
            'ai_prediction' => $ai,
            'comparison' => $comparison,
            'mode' => $validated['mode'] ?? 'manual',
            'scenario_seed' => $validated['scenario_seed'] ?? null,
        ]);

        return response()->json($experiment, 201);
    }

    public function show(Request $request, Experiment $experiment): JsonResponse
    {
        if ($experiment->user_id !== $request->user()->id) {
            abort(403);
        }

        return response()->json($experiment);
    }

    protected function validateInput(Request $request): array
    {
        return $request->validate([
            'm1' => ['required', 'numeric', 'min:0.01', 'max:5000'],
            'm2' => ['required', 'numeric', 'min:0.01', 'max:5000'],
            'v1' => ['required', 'array'],
            'v1.x' => ['required', 'numeric'],
            'v1.y' => ['required', 'numeric'],
            'v2' => ['required', 'array'],
            'v2.x' => ['required', 'numeric'],
            'v2.y' => ['required', 'numeric'],
            'r1' => ['sometimes', 'numeric', 'min:0.05', 'max:10'],
            'r2' => ['sometimes', 'numeric', 'min:0.05', 'max:10'],
            'p1' => ['sometimes', 'array'],
            'p1.x' => ['sometimes', 'numeric'],
            'p1.y' => ['sometimes', 'numeric'],
            'p2' => ['sometimes', 'array'],
            'p2.x' => ['sometimes', 'numeric'],
            'p2.y' => ['sometimes', 'numeric'],
            'collision_type' => ['nullable', 'in:elastic,inelastic'],
            'material' => ['nullable', 'string', 'in:steel,rubber,clay,ice,custom'],
            'restitution' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'mode' => ['nullable', 'string', 'in:manual,random,challenge'],
            'scenario_seed' => ['nullable', 'integer', 'min:1'],
        ]);
    }

    /**
     * Default layout and radii for the simulator (must match resources/js/lab.js).
     */
    protected function physicsDefaults(array $v): array
    {
        $m1 = (float) $v['m1'];
        $m2 = (float) $v['m2'];
        $v1x = (float) $v['v1']['x'];
        $v1y = (float) $v['v1']['y'];
        $v2x = (float) $v['v2']['x'];
        $v2y = (float) $v['v2']['y'];

        return [
            'r1' => $this->defaultBodyRadius($m1, $v1x, $v1y),
            'r2' => $this->defaultBodyRadius($m2, $v2x, $v2y),
            'p1' => ['x' => -1.0, 'y' => 0.0],
            'p2' => ['x' => 0.5, 'y' => 0.0],
            'collision_type' => $v['collision_type'] ?? 'elastic',
            'material' => $v['material'] ?? 'steel',
        ];
    }

    protected function defaultBodyRadius(float $m, float $vx, float $vy): float
    {
        $ref = 2.0;
        $base = 0.2;
        $rm = $base * pow(max(0.01, $m) / $ref, 1.0 / 3.0);
        $speed = hypot($vx, $vy);
        $boost = 1.0 + 0.05 * min($speed / 3.0, 2.5);

        return min(0.45, max(0.06, $rm * $boost));
    }

    protected function buildPhysicsPayload(array $v): array
    {
        $v = array_merge($this->physicsDefaults($v), $v);

        $materials = config('collision.materials');
        $mat = $v['material'] ?? 'steel';
        $e = match ($mat) {
            'steel' => $materials['steel']['restitution'],
            'rubber' => $materials['rubber']['restitution'],
            'clay' => $materials['clay']['restitution'],
            'ice' => $materials['ice']['restitution'],
            'custom' => (float) ($v['restitution'] ?? 0.8),
            default => $materials['steel']['restitution'],
        };

        if ($v['collision_type'] === 'elastic') {
            $e = 1.0;
        } elseif ($v['collision_type'] === 'inelastic') {
            $e = 0.0;
        } elseif (($v['material'] ?? '') === 'custom' && isset($v['restitution'])) {
            $e = (float) $v['restitution'];
        }

        return [
            'm1' => (float) $v['m1'],
            'm2' => (float) $v['m2'],
            'r1' => (float) $v['r1'],
            'r2' => (float) $v['r2'],
            'p1' => ['x' => (float) $v['p1']['x'], 'y' => (float) $v['p1']['y']],
            'p2' => ['x' => (float) $v['p2']['x'], 'y' => (float) $v['p2']['y']],
            'v1' => ['x' => (float) $v['v1']['x'], 'y' => (float) $v['v1']['y']],
            'v2' => ['x' => (float) $v['v2']['x'], 'y' => (float) $v['v2']['y']],
            'collision_type' => $v['collision_type'],
            'restitution' => $e,
            'material' => $mat,
        ];
    }

    /**
     * Сначала внешний /simulate; при ошибке — тот же расчёт в PHP (история и эксперимент не теряются).
     */
    protected function resolvePhysics(AiPhysicsClient $client, array $payload): array
    {
        try {
            return $client->simulate($payload);
        } catch (RuntimeException) {
            return LocalCollisionPhysics::simulate($payload);
        }
    }

    /**
     * Предсказание ИИ; при недоступности сервиса — заглушка (сравнение помечается как unavailable).
     */
    protected function resolveAiPrediction(AiPhysicsClient $client, array $payload, array $physics): array
    {
        try {
            return $client->predict($payload);
        } catch (RuntimeException) {
            return [
                'source' => 'unavailable',
                'note' => 'AI service unreachable; no neural prediction.',
            ];
        }
    }

    protected function buildComparison(array $physics, array $ai): array
    {
        if (($ai['source'] ?? '') === 'unavailable') {
            return [
                'velocity_mae' => null,
                'energy_error_percent' => null,
                'ai_source' => 'unavailable',
            ];
        }

        $pv1 = $physics['v1'] ?? ['x' => 0, 'y' => 0];
        $pv2 = $physics['v2'] ?? ['x' => 0, 'y' => 0];
        $av1 = $ai['v1'] ?? $pv1;
        $av2 = $ai['v2'] ?? $pv2;

        $errs = [
            abs($pv1['x'] - $av1['x']),
            abs($pv1['y'] - $av1['y']),
            abs($pv2['x'] - $av2['x']),
            abs($pv2['y'] - $av2['y']),
        ];
        $mae = array_sum($errs) / max(count($errs), 1);

        $keP = (float) ($physics['ke_final'] ?? 0);
        $keA = (float) ($ai['ke_final'] ?? $keP);
        $keErrPct = $keP > 1e-9 ? abs($keP - $keA) / $keP * 100 : 0.0;

        return [
            'velocity_mae' => round($mae, 6),
            'energy_error_percent' => round($keErrPct, 4),
            'ai_source' => $ai['source'] ?? 'unknown',
        ];
    }
}
