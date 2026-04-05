<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\LabProblemSolverService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProblemSolveController extends Controller
{
    public function __invoke(Request $request, LabProblemSolverService $solver): JsonResponse
    {
        $validated = $request->validate([
            'problem' => ['required', 'string', 'min:3', 'max:8000'],
        ]);

        return response()->json($solver->solve($validated['problem']));
    }
}
