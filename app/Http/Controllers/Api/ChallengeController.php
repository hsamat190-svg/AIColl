<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChallengeAttempt;
use App\Services\AiPhysicsClient;
use App\Services\LocalCollisionPhysics;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class ChallengeController extends Controller
{
    public function submit(Request $request, AiPhysicsClient $client): JsonResponse
    {
        $data = $request->validate([
            'scenario_input' => ['required', 'array'],
            'user_prediction' => ['required', 'array'],
            'user_prediction.v1' => ['required', 'array'],
            'user_prediction.v1.x' => ['required', 'numeric'],
            'user_prediction.v1.y' => ['required', 'numeric'],
            'user_prediction.v2' => ['required', 'array'],
            'user_prediction.v2.x' => ['required', 'numeric'],
            'user_prediction.v2.y' => ['required', 'numeric'],
            'seed' => ['nullable', 'integer'],
            'tag' => ['nullable', 'string', 'max:64'],
            'duration_ms' => ['nullable', 'integer', 'min:0'],
        ]);

        $payload = $this->normalizeScenarioForPhysics($data['scenario_input']);

        try {
            $truth = $client->simulate($payload);
        } catch (RuntimeException) {
            $truth = LocalCollisionPhysics::simulate($payload);
        }

        $score = $this->scorePrediction($data['user_prediction'], $truth);

        $attempt = ChallengeAttempt::create([
            'user_id' => $request->user()->id,
            'tag' => $data['tag'] ?? 'default',
            'scenario_input' => $data['scenario_input'],
            'user_prediction' => $data['user_prediction'],
            'physics_truth' => $truth,
            'score' => $score,
            'duration_ms' => $data['duration_ms'] ?? null,
        ]);

        return response()->json([
            'attempt' => $attempt,
            'physics_truth' => $truth,
            'score' => $score,
        ], 201);
    }

    public function leaderboard(Request $request): JsonResponse
    {
        $tag = $request->query('tag');
        $q = ChallengeAttempt::query()
            ->with('user:id,name')
            ->orderByDesc('score')
            ->limit(50);

        if ($tag) {
            $q->where('tag', $tag);
        }

        return response()->json($q->get());
    }

    protected function normalizeScenarioForPhysics(array $input): array
    {
        $materials = config('collision.materials');
        $mat = $input['material'] ?? 'steel';
        $e = match ($mat) {
            'steel' => $materials['steel']['restitution'],
            'rubber' => $materials['rubber']['restitution'],
            'clay' => $materials['clay']['restitution'],
            'ice' => $materials['ice']['restitution'],
            default => $materials['steel']['restitution'],
        };

        if (($input['collision_type'] ?? '') === 'elastic') {
            $e = 1.0;
        }

        return [
            'm1' => (float) $input['m1'],
            'm2' => (float) $input['m2'],
            'r1' => (float) $input['r1'],
            'r2' => (float) $input['r2'],
            'p1' => ['x' => (float) $input['p1']['x'], 'y' => (float) $input['p1']['y']],
            'p2' => ['x' => (float) $input['p2']['x'], 'y' => (float) $input['p2']['y']],
            'v1' => ['x' => (float) $input['v1']['x'], 'y' => (float) $input['v1']['y']],
            'v2' => ['x' => (float) $input['v2']['x'], 'y' => (float) $input['v2']['y']],
            'collision_type' => $input['collision_type'] ?? 'elastic',
            'restitution' => $e,
            'material' => $mat,
        ];
    }

    protected function scorePrediction(array $user, array $truth): float
    {
        $tv1 = $truth['v1'];
        $tv2 = $truth['v2'];
        $uv1 = $user['v1'];
        $uv2 = $user['v2'];

        $sse = 0.0;
        $sse += ($tv1['x'] - $uv1['x']) ** 2;
        $sse += ($tv1['y'] - $uv1['y']) ** 2;
        $sse += ($tv2['x'] - $uv2['x']) ** 2;
        $sse += ($tv2['y'] - $uv2['y']) ** 2;

        $rmse = sqrt($sse / 4);

        return round(max(0, 100 * exp(-$rmse)), 4);
    }
}
