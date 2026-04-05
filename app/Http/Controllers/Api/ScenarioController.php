<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Random\Engine\Mt19937;
use Random\Randomizer;

class ScenarioController extends Controller
{
    public function random(Request $request): JsonResponse
    {
        $cfg = config('collision.scenario');
        $seed = $request->integer('seed') ?: random_int(1, 2 ** 30);

        $rand = new Randomizer(new Mt19937($seed));

        $scaleM = 100;
        $scaleV = 100;
        $scaleR = 100;
        $scaleP = 100;

        $m1 = $rand->getInt((int) ($cfg['m_min'] * $scaleM), (int) ($cfg['m_max'] * $scaleM)) / $scaleM;
        $m2 = $rand->getInt((int) ($cfg['m_min'] * $scaleM), (int) ($cfg['m_max'] * $scaleM)) / $scaleM;
        $r1 = $rand->getInt(5, 50) / 100.0;
        $r2 = $rand->getInt(5, 50) / 100.0;

        $gap = $rand->getInt(1, 30) / 100.0;
        $p1x = $rand->getInt(-200, 0) / 100.0;
        $p1y = $rand->getInt(-50, 50) / 100.0;
        $p2x = $p1x + $r1 + $r2 + $gap;
        $p2y = $rand->getInt(-50, 50) / 100.0;

        $vMax = (float) $cfg['v_max'];
        $v1x = $rand->getInt(50, (int) ($vMax * 100)) / 100.0;
        $v1y = $rand->getInt(-80, 80) / 100.0;
        $v2x = -$rand->getInt(20, (int) ($vMax * 100)) / 100.0;
        $v2y = $rand->getInt(-80, 80) / 100.0;

        $inelastic = $rand->getInt(0, 1) === 1;
        $collisionType = $inelastic ? 'inelastic' : 'elastic';
        $materials = ['steel', 'rubber', 'clay', 'ice'];
        $material = $materials[$rand->getInt(0, count($materials) - 1)];

        $input = [
            'm1' => round($m1, 2),
            'm2' => round($m2, 2),
            'r1' => round($r1, 2),
            'r2' => round($r2, 2),
            'p1' => ['x' => round($p1x, 2), 'y' => round($p1y, 2)],
            'p2' => ['x' => round($p2x, 2), 'y' => round($p2y, 2)],
            'v1' => ['x' => round($v1x, 2), 'y' => round($v1y, 2)],
            'v2' => ['x' => round($v2x, 2), 'y' => round($v2y, 2)],
            'collision_type' => $collisionType,
            'material' => $material,
        ];

        $tag = 'daily-'.now()->toDateString();

        return response()->json([
            'seed' => $seed,
            'tag' => $tag,
            'input' => $input,
        ]);
    }
}
