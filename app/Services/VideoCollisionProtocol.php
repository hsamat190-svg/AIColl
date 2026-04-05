<?php

namespace App\Services;

/**
 * Демо-хаттама: механика + физикалық қадамдар + мәтіндік сараптама (шын ЖИ емес, бірақ біртұтас логика).
 */
class VideoCollisionProtocol
{
    /**
     * @param  array{code: string, label: string, confidence: float}  $scenario
     * @param  array<string, mixed>  $kinematics
     * @param  array<string, mixed>  $physics
     * @param  array<string, mixed>  $inference
     * @return array{title: string, blocks: list<array<string, mixed>>}
     */
    public function build(string $seed, array $scenario, array $kinematics, array $physics, array $inference): array
    {
        $vKmh = (float) $kinematics['relative_speed_kmh']['point'];
        $mKg = (int) $kinematics['vehicle_mass_kg']['point'];
        $model = (string) $kinematics['vehicle_model'];
        $ek = (int) $physics['kinetic_energy_j']['point'];
        $scenarioLabel = (string) $scenario['label'];
        $code = (string) $scenario['code'];

        $cost = $inference['repair_cost_kzt'];
        $costPt = (int) $cost['point'];
        $costLo = (int) $cost['low'];
        $costHi = (int) $cost['high'];

        $tier = $this->energyTier($ek);
        $tierDesc = match ($tier) {
            'light' => __('Video protocol tier light'),
            'moderate' => __('Video protocol tier moderate'),
            default => __('Video protocol tier severe'),
        };
        $expect = match ($tier) {
            'light' => __('Video protocol expect light'),
            'moderate' => __('Video protocol expect moderate'),
            default => __('Video protocol expect severe'),
        };

        $vMs = round($vKmh / 3.6, 2);
        $rolloverNote = $this->rolloverSentence($seed, $tier, $vKmh);

        $mechanism = __(
            'Video protocol mechanism body',
            [
                'scenario' => $scenarioLabel,
                'm' => (string) $mKg,
                'v' => $this->fmtNum($vKmh, 1),
                'tier' => $tierDesc,
            ]
        );

        $steps = $this->physicsSteps($physics, $vKmh, $mKg, $vMs);

        $expert1 = __(
            'Video protocol expert para1',
            [
                'model' => $model,
                'v' => $this->fmtNum($vKmh, 1),
                'm' => (string) $mKg,
                'scenario' => $scenarioLabel,
                'ek' => $this->fmtInt($ek),
                'expect' => $expect,
            ]
        );

        $expert2 = __(
            'Video protocol expert para2',
            [
                'low' => $this->fmtInt($costLo),
                'high' => $this->fmtInt($costHi),
                'point' => $this->fmtInt($costPt),
            ]
        );

        $expert3 = trim($rolloverNote.($code === 'uncertain' ? ' '.__('Video protocol expert uncertain add') : ''));

        $blocks = [
            [
                'type' => 'section',
                'heading' => __('Video protocol section mechanism'),
                'paragraphs' => [$mechanism],
            ],
            [
                'type' => 'physics',
                'heading' => __('Video protocol section physics'),
                'steps' => $steps,
            ],
            [
                'type' => 'section',
                'heading' => __('Video protocol section expert'),
                'paragraphs' => array_values(array_filter([$expert1, $expert2, $expert3])),
            ],
        ];

        return [
            'title' => __('Video protocol document title'),
            'blocks' => $blocks,
        ];
    }

    private function energyTier(int $ekJoules): string
    {
        if ($ekJoules < 25_000) {
            return 'light';
        }
        if ($ekJoules < 120_000) {
            return 'moderate';
        }

        return 'severe';
    }

    private function rolloverSentence(string $seed, string $tier, float $vKmh): string
    {
        if ($tier !== 'severe' || $vKmh < 55) {
            return '';
        }
        $u = $this->seedUnit($seed, 'rollover_hint');
        if ($u < 0.55) {
            return '';
        }

        return __('Video protocol rollover risk');
    }

    /**
     * @return list<array{title: string, formula: string, detail: string}>
     */
    private function physicsSteps(array $physics, float $vKmh, int $mKg, float $vMs): array
    {
        $steps = [];
        $steps[] = [
            'title' => __('Video protocol step si speed'),
            'formula' => __('Video protocol formula v si'),
            'detail' => __('Video protocol detail v si', [
                'v_kmh' => $this->fmtNum($vKmh, 1),
                'v_ms' => $this->fmtNum($vMs, 2),
            ]),
        ];

        if (($physics['model'] ?? '') === 'two_body_reduced_mass') {
            $m1 = (int) ($physics['m1_kg']['point'] ?? $mKg);
            $m2 = (int) ($physics['m2_kg']['point'] ?? $mKg);
            $mu = (float) ($physics['reduced_mass_kg']['point'] ?? (($m1 * $m2) / max(1, $m1 + $m2)));
            $ek = (int) $physics['kinetic_energy_j']['point'];

            $steps[] = [
                'title' => __('Video protocol step reduced mass'),
                'formula' => __('Video protocol formula mu'),
                'detail' => __('Video protocol detail mu', [
                    'm1' => (string) $m1,
                    'm2' => (string) $m2,
                    'mu' => $this->fmtNum($mu, 2),
                ]),
            ];
            $steps[] = [
                'title' => __('Video protocol step ek reduced'),
                'formula' => __('Video protocol formula ek reduced'),
                'detail' => __('Video protocol detail ek reduced', [
                    'mu' => $this->fmtNum($mu, 2),
                    'v' => $this->fmtNum($vMs, 2),
                    'ek' => $this->fmtInt($ek),
                ]),
            ];
        } else {
            $ek = (int) $physics['kinetic_energy_j']['point'];
            $half = (int) round(0.5 * $mKg * $vMs * $vMs);
            $steps[] = [
                'title' => __('Video protocol step ek obstacle'),
                'formula' => __('Video protocol formula ek obstacle'),
                'detail' => __('Video protocol detail ek obstacle', [
                    'm' => (string) $mKg,
                    'v' => $this->fmtNum($vMs, 2),
                    'half' => $this->fmtInt($half),
                    'ek' => $this->fmtInt($ek),
                ]),
            ];
        }

        $steps[] = [
            'title' => __('Video protocol step summary energy'),
            'formula' => 'E_k ≈ '.number_format($physics['kinetic_energy_j']['point'], 0, ',', ' ').' '.__('Video protocol unit j'),
            'detail' => (string) ($physics['summary'] ?? ''),
        ];

        return $steps;
    }

    private function seedUnit(string $seed, string $salt): float
    {
        $hex = substr(hash('sha256', $seed.'|'.$salt), 0, 8);
        $n = hexdec($hex);

        return $n / 0xffffffff;
    }

    private function fmtNum(float $x, int $decimals): string
    {
        return number_format($x, $decimals, '.', '');
    }

    private function fmtInt(int $x): string
    {
        return number_format($x, 0, ',', ' ');
    }
}
