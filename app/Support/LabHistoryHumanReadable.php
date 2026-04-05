<?php

namespace App\Support;

use App\Models\Experiment;

/**
 * Тарих жазбасының толық мәтіндік көрінісі (JSON емес).
 */
final class LabHistoryHumanReadable
{
    /**
     * @return list<array<string, mixed>>
     */
    public static function fromExperiment(Experiment $experiment): array
    {
        $sections = [];

        $mode = (string) ($experiment->mode ?? '');
        if ($mode !== '') {
            $rows = [
                self::row(__('History label simulator mode'), self::translateMode($mode)),
            ];
            if ($experiment->scenario_seed !== null) {
                $rows[] = self::row(__('History label scenario seed'), (string) $experiment->scenario_seed);
            }
            $sections[] = [
                'title' => __('History section experiment meta'),
                'rows' => $rows,
            ];
        }

        $in = is_array($experiment->input) ? $experiment->input : [];
        $sections[] = [
            'title' => __('History section input'),
            'rows' => self::rowsFrom2dInput($in),
        ];

        $phys = is_array($experiment->physics_result) ? $experiment->physics_result : [];
        $sections[] = [
            'title' => __('History section physics'),
            'rows' => self::rowsFrom2dPhysics($phys),
        ];

        $ai = is_array($experiment->ai_prediction) ? $experiment->ai_prediction : [];
        $aiRows = self::rowsFrom2dAi($ai);
        $aiParas = self::paragraphsFrom2dAi($ai);
        $sections[] = array_filter([
            'title' => __('History section ai'),
            'rows' => $aiRows,
            'paragraphs' => $aiParas,
        ], fn ($v) => $v !== null && $v !== []);

        $comp = is_array($experiment->comparison) ? $experiment->comparison : [];
        $sections[] = [
            'title' => __('History section comparison'),
            'rows' => self::rowsFromComparison($comp),
        ];

        return array_values(array_filter(
            $sections,
            static fn (array $s): bool => ! empty($s['rows'] ?? []) || ! empty($s['paragraphs'] ?? []) || ($s['type'] ?? '') === 'protocol'
        ));
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return list<array<string, mixed>>
     */
    public static function fromVideoPayload(array $payload): array
    {
        $sections = [];

        $file = is_array($payload['file'] ?? null) ? $payload['file'] : [];
        $metaRows = array_values(array_filter([
            isset($file['name']) ? self::row(__('History label file name'), (string) $file['name']) : null,
            isset($file['size_bytes']) ? self::row(__('History label file size'), self::formatBytes((int) $file['size_bytes'])) : null,
            isset($file['mime']) ? self::row(__('History label file mime'), (string) $file['mime']) : null,
            isset($payload['trace_id']) ? self::row(__('History label trace id'), (string) $payload['trace_id']) : null,
            isset($payload['mode']) ? self::row(__('History label analysis mode'), (string) $payload['mode']) : null,
            isset($payload['confidence']) ? self::row(__('3D simulator confidence'), (string) $payload['confidence']) : null,
        ]));

        if ($metaRows !== []) {
            $sections[] = [
                'title' => __('History section video meta'),
                'rows' => $metaRows,
            ];
        }

        $scenario = is_array($payload['collision_scenario'] ?? null) ? $payload['collision_scenario'] : [];
        if ($scenario !== []) {
            $scRows = array_values(array_filter([
                isset($scenario['label']) ? self::row(__('3D simulator scenario title'), (string) $scenario['label']) : null,
                isset($scenario['confidence']) ? self::row(__('History label scenario confidence'), (string) $scenario['confidence']) : null,
            ]));
            if ($scRows !== []) {
                $sections[] = [
                    'title' => __('History section video scenario'),
                    'rows' => $scRows,
                ];
            }
        }

        $inf = is_array($payload['inference'] ?? null) ? $payload['inference'] : [];
        if ($inf !== []) {
            $infRows = array_values(array_filter([
                self::maybeIntervalRow(__('3D simulator metric speed'), $inf['relative_speed_kmh'] ?? null, __('3D simulator unit kmh')),
                self::maybeIntervalRow(__('3D simulator metric mass'), $inf['vehicle_mass_kg'] ?? null, __('3D simulator unit kg')),
                isset($inf['vehicle_model']) ? self::row(__('3D simulator metric model'), (string) $inf['vehicle_model']) : null,
                self::maybeIntervalRow(__('3D simulator metric cost'), $inf['repair_cost_kzt'] ?? null, '₸'),
            ]));
            if ($infRows !== []) {
                $sections[] = [
                    'title' => __('History section video inference'),
                    'rows' => $infRows,
                ];
            }
        }

        $phys = is_array($payload['physics_analysis'] ?? null) ? $payload['physics_analysis'] : [];
        if ($phys !== []) {
            $paras = [];
            if (! empty($phys['summary']) && is_string($phys['summary'])) {
                $paras[] = $phys['summary'];
            }
            $pRows = array_values(array_filter([
                isset($phys['model']) ? self::row(__('History label physics model'), (string) $phys['model']) : null,
                self::maybeScalarBlockRow(__('3D simulator physics v_rel'), $phys['v_rel_ms'] ?? null, __('3D simulator unit ms')),
                self::maybeScalarBlockRow(__('3D simulator physics m1'), $phys['m1_kg'] ?? null, __('3D simulator unit kg')),
                self::maybeScalarBlockRow(__('3D simulator physics m2'), $phys['m2_kg'] ?? null, __('3D simulator unit kg')),
                self::maybeScalarBlockRow(__('3D simulator physics mu'), $phys['reduced_mass_kg'] ?? null, __('3D simulator unit kg')),
                self::maybeScalarBlockRow(__('3D simulator physics ek'), $phys['kinetic_energy_j'] ?? null, __('3D simulator unit j')),
            ]));
            if ($paras !== [] || $pRows !== []) {
                $sections[] = [
                    'title' => __('3D simulator physics title'),
                    'paragraphs' => $paras,
                    'rows' => $pRows,
                ];
            }
        }

        $proto = is_array($payload['collision_protocol'] ?? null) ? $payload['collision_protocol'] : [];
        if ($proto !== [] && ! empty($proto['blocks'])) {
            $sections[] = [
                'type' => 'protocol',
                'protocol' => $proto,
            ];
        }

        if (! empty($payload['training_hint']) && is_string($payload['training_hint'])) {
            $sections[] = [
                'title' => __('History section training hint'),
                'paragraphs' => [$payload['training_hint']],
            ];
        }

        $pipe = $payload['pipeline'] ?? null;
        if (is_array($pipe) && $pipe !== []) {
            $lines = [];
            foreach ($pipe as $stage) {
                if (! is_array($stage)) {
                    continue;
                }
                $code = (string) ($stage['stage'] ?? '');
                $ms = isset($stage['ms']) ? (int) $stage['ms'] : null;
                $label = self::translatePipelineStage($code);
                if ($ms !== null) {
                    $lines[] = $label.': '.$ms.' '.__('History label ms');
                } else {
                    $lines[] = $label;
                }
            }
            if ($lines !== []) {
                $sections[] = [
                    'title' => __('3D simulator pipeline'),
                    'paragraphs' => [implode("\n", $lines)],
                ];
            }
        }

        if ($sections === []) {
            $sections[] = [
                'title' => __('History detail payload title'),
                'paragraphs' => [__('History label payload empty')],
            ];
        }

        return $sections;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return list<array<string, mixed>>
     */
    public static function from2dRecordPayload(array $payload): array
    {
        $rows = [];
        if (isset($payload['experiment_id'])) {
            $rows[] = self::row(__('History label experiment id'), (string) $payload['experiment_id']);
        }
        if (isset($payload['mode'])) {
            $rows[] = self::row(__('History label simulator mode'), self::translateMode((string) $payload['mode']));
        }
        foreach ($payload as $key => $value) {
            if (in_array($key, ['experiment_id', 'mode'], true)) {
                continue;
            }
            if (is_scalar($value)) {
                $rows[] = self::row((string) $key, (string) $value);
            }
        }

        if ($rows === []) {
            $rows[] = self::row(__('History detail payload title'), __('History label no extra fields'));
        }

        return [
            [
                'title' => __('History detail payload title'),
                'rows' => $rows,
            ],
        ];
    }

    private static function translatePipelineStage(string $code): string
    {
        return match ($code) {
            'decode_mux' => __('3D simulator stage decode'),
            'scenario_classifier' => __('3D simulator stage scenario'),
            'motion_stabilize' => __('3D simulator stage stabilize'),
            'contact_frame_search' => __('3D simulator stage contact'),
            'optical_flow_features' => __('3D simulator stage flow'),
            'fusion_head_regress' => __('3D simulator stage fusion'),
            'physics_regression' => __('3D simulator stage physics'),
            'calibration_isotonic' => __('3D simulator stage calib'),
            default => $code,
        };
    }

    /**
     * @param  array<string, mixed>|null  $block
     */
    private static function maybeScalarBlockRow(string $label, mixed $block, string $unitSuffix): ?array
    {
        if (! is_array($block)) {
            return null;
        }
        if (array_key_exists('point', $block)) {
            $v = $block['point'];

            return self::row($label, self::fmtScalar($v).' '.$unitSuffix);
        }

        return self::maybeIntervalRow($label, $block, $unitSuffix);
    }

    /**
     * @param  array<string, mixed>|null  $triplet
     */
    private static function maybeIntervalRow(string $label, mixed $triplet, string $unitSuffix): ?array
    {
        if (! is_array($triplet)) {
            return null;
        }
        if (! isset($triplet['low'], $triplet['high'], $triplet['point'])) {
            return null;
        }
        $tpl = __('3D simulator interval');
        $interval = str_replace(
            [':low', ':high'],
            [self::fmtScalar($triplet['low']), self::fmtScalar($triplet['high'])],
            $tpl
        );
        $mid = self::fmtScalar($triplet['point']);

        return self::row($label, $mid.' '.$unitSuffix.' ('.$interval.' '.$unitSuffix.')');
    }

    private static function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes.' B';
        }
        if ($bytes < 1024 * 1024) {
            return round($bytes / 1024, 1).' KB';
        }

        return round($bytes / (1024 * 1024), 2).' MB';
    }

    private static function row(string $label, string $value): array
    {
        return ['label' => $label, 'value' => $value];
    }

    private static function translateMode(string $mode): string
    {
        return match ($mode) {
            'manual' => __('History mode manual'),
            'random' => __('History mode random'),
            'challenge' => __('History mode challenge'),
            default => $mode,
        };
    }

    /**
     * @param  array<string, mixed>  $in
     * @return list<array{label: string, value: string}>
     */
    private static function rowsFrom2dInput(array $in): array
    {
        $rows = [];
        if (isset($in['m1'])) {
            $rows[] = self::row(__('Simulator mass m1'), self::fmtFloat((float) $in['m1']));
        }
        if (isset($in['m2'])) {
            $rows[] = self::row(__('Simulator mass m2'), self::fmtFloat((float) $in['m2']));
        }
        if (isset($in['r1'])) {
            $rows[] = self::row(__('History label radius r1'), self::fmtFloat((float) $in['r1']).' '.__('History unit m'));
        }
        if (isset($in['r2'])) {
            $rows[] = self::row(__('History label radius r2'), self::fmtFloat((float) $in['r2']).' '.__('History unit m'));
        }
        if (isset($in['p1']) && is_array($in['p1'])) {
            $rows[] = self::row(__('History label position body1'), self::fmtVec2($in['p1']).' '.__('History unit m'));
        }
        if (isset($in['p2']) && is_array($in['p2'])) {
            $rows[] = self::row(__('History label position body2'), self::fmtVec2($in['p2']).' '.__('History unit m'));
        }
        if (isset($in['v1']) && is_array($in['v1'])) {
            $rows[] = self::row(__('Simulator velocity v1 legend'), self::fmtVec2($in['v1']).' '.__('History unit ms'));
        }
        if (isset($in['v2']) && is_array($in['v2'])) {
            $rows[] = self::row(__('Simulator velocity v2 legend'), self::fmtVec2($in['v2']).' '.__('History unit ms'));
        }

        $ctype = (string) ($in['collision_type'] ?? '');
        if ($ctype !== '') {
            $label = match ($ctype) {
                'elastic' => __('Elastic collision'),
                'inelastic' => __('Inelastic collision'),
                default => $ctype,
            };
            $rows[] = self::row(__('Collision type'), $label);
        }

        if (isset($in['material'])) {
            $rows[] = self::row(__('Material'), self::translateMaterial((string) $in['material']));
        }
        if (isset($in['restitution'])) {
            $rows[] = self::row(__('History label restitution'), self::fmtFloat((float) $in['restitution']));
        }

        return $rows;
    }

    /**
     * @param  array<string, mixed>  $phys
     * @return list<array{label: string, value: string}>
     */
    private static function rowsFrom2dPhysics(array $phys): array
    {
        $rows = [];
        if (isset($phys['v1']) && is_array($phys['v1'])) {
            $rows[] = self::row(__('Simulator after collision').' · v₁', self::fmtVec2($phys['v1']).' '.__('History unit ms'));
        }
        if (isset($phys['v2']) && is_array($phys['v2'])) {
            $rows[] = self::row(__('Simulator after collision').' · v₂', self::fmtVec2($phys['v2']).' '.__('History unit ms'));
        }
        if (isset($phys['ke_initial'])) {
            $rows[] = self::row(__('History label ke initial'), self::fmtFloat((float) $phys['ke_initial']).' '.__('History unit joule'));
        }
        if (isset($phys['ke_final'])) {
            $rows[] = self::row(__('History label ke final'), self::fmtFloat((float) $phys['ke_final']).' '.__('History unit joule'));
        }
        if (isset($phys['momentum_initial']) && is_array($phys['momentum_initial'])) {
            $rows[] = self::row(__('History label momentum initial'), self::fmtVec2($phys['momentum_initial']).' '.__('History unit momentum'));
        }
        if (isset($phys['momentum_final']) && is_array($phys['momentum_final'])) {
            $rows[] = self::row(__('History label momentum final'), self::fmtVec2($phys['momentum_final']).' '.__('History unit momentum'));
        }
        if (isset($phys['source'])) {
            $rows[] = self::row(__('History label result source'), (string) $phys['source']);
        }

        return $rows;
    }

    /**
     * @param  array<string, mixed>  $ai
     * @return list<array{label: string, value: string}>
     */
    private static function rowsFrom2dAi(array $ai): array
    {
        if (($ai['source'] ?? '') === 'unavailable') {
            return [];
        }
        $rows = [];
        if (isset($ai['v1']) && is_array($ai['v1'])) {
            $rows[] = self::row(__('Simulator after collision').' · v₁ (ИИ)', self::fmtVec2($ai['v1']).' '.__('History unit ms'));
        }
        if (isset($ai['v2']) && is_array($ai['v2'])) {
            $rows[] = self::row(__('Simulator after collision').' · v₂ (ИИ)', self::fmtVec2($ai['v2']).' '.__('History unit ms'));
        }
        if (isset($ai['ke_final'])) {
            $rows[] = self::row(__('History label ke final').' (ИИ)', self::fmtFloat((float) $ai['ke_final']).' '.__('History unit joule'));
        }
        if (isset($ai['source'])) {
            $rows[] = self::row(__('History label ai source'), (string) $ai['source']);
        }

        return $rows;
    }

    /**
     * @param  array<string, mixed>  $ai
     * @return list<string>
     */
    private static function paragraphsFrom2dAi(array $ai): array
    {
        if (($ai['source'] ?? '') === 'unavailable') {
            $note = (string) ($ai['note'] ?? __('History ai unavailable note'));

            return [$note];
        }

        return [];
    }

    /**
     * @param  array<string, mixed>  $comp
     * @return list<array{label: string, value: string}>
     */
    private static function rowsFromComparison(array $comp): array
    {
        $rows = [];
        if (array_key_exists('velocity_mae', $comp)) {
            $v = $comp['velocity_mae'];
            $rows[] = self::row(
                __('History label velocity mae'),
                $v === null ? '—' : self::fmtFloat((float) $v).' '.__('History unit ms')
            );
        }
        if (array_key_exists('energy_error_percent', $comp)) {
            $e = $comp['energy_error_percent'];
            $rows[] = self::row(
                __('History label energy error'),
                $e === null ? '—' : self::fmtFloat((float) $e).' %'
            );
        }
        if (isset($comp['ai_source'])) {
            $rows[] = self::row(__('History label comparison ai source'), (string) $comp['ai_source']);
        }

        return $rows;
    }

    private static function translateMaterial(string $mat): string
    {
        $key = 'History material '.$mat;
        $t = __($key);

        return $t !== $key ? $t : $mat;
    }

    /**
     * @param  array{x?: float|int|string, y?: float|int|string}  $v
     */
    private static function fmtVec2(array $v): string
    {
        $x = isset($v['x']) ? (float) $v['x'] : 0.0;
        $y = isset($v['y']) ? (float) $v['y'] : 0.0;

        return 'x = '.self::fmtFloat($x).', y = '.self::fmtFloat($y);
    }

    private static function fmtFloat(float $n): string
    {
        if (! is_finite($n)) {
            return '—';
        }
        $r = round($n, 6);
        if (abs($r - round($r)) < 1e-9) {
            return (string) (int) round($r);
        }

        return rtrim(rtrim(sprintf('%.6F', $r), '0'), '.');
    }

    private static function fmtScalar(mixed $v): string
    {
        if (is_int($v) || is_float($v)) {
            return self::fmtFloat((float) $v);
        }

        return (string) $v;
    }
}
