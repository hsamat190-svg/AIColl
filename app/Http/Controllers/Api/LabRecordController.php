<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LabRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LabRecordController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'source' => ['required', 'string', 'in:simulator_2d,simulator_3d'],
            'payload' => ['required', 'array'],
        ]);

        $record = LabRecord::create([
            'user_id' => $request->user()->id,
            'name' => $validated['name'],
            'source' => $validated['source'],
            'payload' => $validated['payload'],
        ]);

        return response()->json($record, 201);
    }
}
