<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TrainingController extends Controller
{
    /**
     * Accept hyperparameters for a future training job (Phase 4 hook).
     */
    public function submit(Request $request): JsonResponse
    {
        $data = $request->validate([
            'epochs' => ['nullable', 'integer', 'min:1', 'max:500'],
            'hidden_size' => ['nullable', 'integer', 'min:8', 'max:512'],
            'lr' => ['nullable', 'numeric', 'min:1e-6', 'max:1'],
        ]);

        return response()->json([
            'status' => 'queued',
            'message' => 'Training jobs run offline: use ai-service/train.py with generated dataset.',
            'received' => $data,
        ], 202);
    }
}
