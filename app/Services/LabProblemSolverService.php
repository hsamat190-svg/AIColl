<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * Импульс пен кинетикалық энергия есептері: OpenAI (қолжетімді болса) немесе локалды эвристика.
 */
final class LabProblemSolverService
{
    public function solve(string $problem): array
    {
        $problem = trim($problem);
        if ($problem === '') {
            return $this->failure(__('Problem solver empty'));
        }

        $key = config('services.openai.key');
        if (is_string($key) && $key !== '') {
            try {
                $text = $this->callOpenAi($problem);
                if ($text !== null && $text !== '') {
                    return [
                        'ok' => true,
                        'source' => 'openai',
                        'topic' => 'llm',
                        'sections' => [['heading' => null, 'content' => $text]],
                    ];
                }
            } catch (\Throwable) {
                // локалды шешімге өту
            }
        }

        return $this->solveLocally($problem);
    }

    private function callOpenAi(string $problem): ?string
    {
        $lang = app()->getLocale() === 'kk'
            ? 'Казак тілінде жауап бер. Рус тіліндегі есеп болса, соны қазақша түсіндір.'
            : 'Отвечай на русском языке.';

        $system = $lang.' Ты — преподаватель физики. Решай только задачи про импульс и закон сохранения импульса (p = mv, Σp = const), а также про кинетическую энергию (Eк = mv²/2) и её изменение. Дай пошаговое решение: краткая постановка, формулы, подстановка чисел, численный ответ с единицами СИ. Если задача не про импульс/энергию в механике — коротко откажи и предложи сформулировать задачу про импульс или кинетическую энергию.';

        $model = (string) config('services.openai.model', 'gpt-4o-mini');
        $timeout = (int) config('services.openai.timeout', 90);

        $response = Http::withToken((string) config('services.openai.key'))
            ->timeout($timeout)
            ->acceptJson()
            ->post('https://api.openai.com/v1/chat/completions', [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $system],
                    ['role' => 'user', 'content' => $problem],
                ],
                'temperature' => 0.35,
                'max_tokens' => 2500,
            ]);

        if (! $response->successful()) {
            return null;
        }

        $content = data_get($response->json(), 'choices.0.message.content');

        return is_string($content) ? trim($content) : null;
    }

    /**
     * @return array{ok: bool, source: string, topic: string, sections: list<array{heading: ?string, content: string}>}
     */
    private function solveLocally(string $raw): array
    {
        $t = mb_strtolower($raw, 'UTF-8');

        $masses = $this->findUnitValues($raw, '/(\d+(?:[.,]\d+)?)\s*(?:кг|kg)\b/iu');
        $speeds = $this->findUnitValues($raw, '/(\d+(?:[.,]\d+)?)\s*(?:м\/\s*с|м\/с|m\/s)\b/iu');

        $collision = $this->hasCollisionCue($t);
        $inelastic = $this->hasInelasticCue($t);
        $wantE = $this->hasEnergyCue($t);
        $wantP = $this->hasImpulseCue($t);

        if (($inelastic || $collision) && count($masses) >= 2 && count($speeds) >= 2) {
            return $this->solutionInelastic($masses[0], $speeds[0], $masses[1], $speeds[1]);
        }

        if (count($masses) >= 1 && count($speeds) >= 1) {
            return $this->solutionSingleBody($masses[0], $speeds[0], $wantP, $wantE);
        }

        $plain = $this->extractPlainNumbers($raw);
        if (count($plain) === 2 && ! $collision) {
            $m = $plain[0];
            $v = $plain[1];
            if ($m > 0 && $v >= 0 && $m <= 1e6 && $v <= 1e6) {
                return $this->solutionSingleBody($m, $v, $wantP || ! $wantE, $wantE || ! $wantP);
            }
        }

        if (($inelastic || $collision) && count($plain) >= 4) {
            return $this->solutionInelastic($plain[0], $plain[1], $plain[2], $plain[3]);
        }

        return $this->failure(__('Problem solver unknown'));
    }

    /**
     * @return list<float>
     */
    private function findUnitValues(string $raw, string $pattern): array
    {
        if (preg_match_all($pattern, $raw, $m)) {
            $out = [];
            foreach ($m[1] as $g) {
                $out[] = $this->parseFloat((string) $g);
            }

            return $out;
        }

        return [];
    }

    /**
     * @return list<float>
     */
    private function extractPlainNumbers(string $raw): array
    {
        $s = preg_replace('/(\d)[\s\u{00A0}]*([.,])(?=\d)/u', '$1.', $raw) ?? $raw;
        if (preg_match_all('/\b(\d+(?:\.\d+)?)\b/', $s, $m)) {
            return array_map(fn (string $x) => $this->parseFloat($x), $m[1]);
        }

        return [];
    }

    private function parseFloat(string $s): float
    {
        return (float) str_replace(',', '.', $s);
    }

    private function hasCollisionCue(string $t): bool
    {
        return (bool) preg_match(
            '/соқтығыс|столкнов|удар|collision|impact|бір-біріне|друг с другом/u',
            $t
        );
    }

    private function hasInelasticCue(string $t): bool
    {
        return (bool) preg_match(
            '/неупруг|синіп|бірігіп|липн|скле|perfectly\s*inelastic|inelastic|жабыс/u',
            $t
        );
    }

    private function hasEnergyCue(string $t): bool
    {
        return (bool) preg_match(
            '/энерг|энергия|кинетик|кинетикалық|e_?k|ек\b|mv\^2|mv²|½|1\/2/u',
            $t
        );
    }

    private function hasImpulseCue(string $t): bool
    {
        return (bool) preg_match(
            '/импульс|импульс|momentum|закон сохранения импульса|импульсті сақтау|секіру/u',
            $t
        );
    }

    /**
     * @return array{ok: true, source: 'local', topic: string, sections: list<array{heading: ?string, content: string}>}
     */
    private function solutionSingleBody(float $m, float $v, bool $wantP, bool $wantE): array
    {
        $p = $m * $v;
        $ek = 0.5 * $m * $v * $v;
        $sections = [];

        $doP = $wantP || ! $wantE;
        $doE = $wantE || ! $wantP;

        if ($doP) {
            $sections[] = [
                'heading' => __('Problem solver section impulse'),
                'content' => __('Problem solver explain impulse', [
                    'm' => $this->fmt($m),
                    'v' => $this->fmt($v),
                    'p' => $this->fmt($p),
                ]),
            ];
        }
        if ($doE) {
            $sections[] = [
                'heading' => __('Problem solver section energy'),
                'content' => __('Problem solver explain energy', [
                    'm' => $this->fmt($m),
                    'v' => $this->fmt($v),
                    'ek' => $this->fmt($ek),
                ]),
            ];
        }

        return [
            'ok' => true,
            'source' => 'local',
            'topic' => $doP && $doE ? 'mixed' : ($doP ? 'impulse' : 'energy'),
            'sections' => $sections,
        ];
    }

    /**
     * @return array{ok: true, source: 'local', topic: 'collision', sections: list<array{heading: ?string, content: string}>}
     */
    private function solutionInelastic(float $m1, float $v1, float $m2, float $v2): array
    {
        $p0 = $m1 * $v1 + $m2 * $v2;
        $mSum = $m1 + $m2;
        $vFin = $mSum > 0 ? $p0 / $mSum : 0.0;
        $ekI = 0.5 * $m1 * $v1 * $v1 + 0.5 * $m2 * $v2 * $v2;
        $ekF = 0.5 * $mSum * $vFin * $vFin;

        $content = __('Problem solver explain inelastic', [
            'm1' => $this->fmt($m1),
            'v1' => $this->fmt($v1),
            'm2' => $this->fmt($m2),
            'v2' => $this->fmt($v2),
            'p0' => $this->fmt($p0),
            'vfin' => $this->fmt($vFin),
            'eki' => $this->fmt($ekI),
            'ekf' => $this->fmt($ekF),
        ]);

        return [
            'ok' => true,
            'source' => 'local',
            'topic' => 'collision',
            'sections' => [
                ['heading' => __('Problem solver section collision'), 'content' => $content],
            ],
        ];
    }

    /**
     * @return array{ok: false, source: 'local', topic: 'unknown', sections: list<array{heading: ?string, content: string}>}
     */
    private function failure(string $message): array
    {
        return [
            'ok' => false,
            'source' => 'local',
            'topic' => 'unknown',
            'sections' => [['heading' => null, 'content' => $message]],
        ];
    }

    private function fmt(float $x): string
    {
        if (! is_finite($x)) {
            return '—';
        }
        if (abs($x - round($x)) < 1e-5) {
            return (string) (int) round($x);
        }

        return rtrim(rtrim(sprintf('%.5F', $x), '0'), '.');
    }
}
