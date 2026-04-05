<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AiPhysicsClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class DatasetController extends Controller
{
    public function batch(Request $request, AiPhysicsClient $client): JsonResponse
    {
        $data = $request->validate([
            'count' => ['required', 'integer', 'min:1', 'max:50000'],
            'seed' => ['nullable', 'integer'],
        ]);

        try {
            $result = $client->datasetBatch([
                'count' => $data['count'],
                'seed' => $data['seed'] ?? null,
            ]);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 503);
        }

        return response()->json($result);
    }
}
