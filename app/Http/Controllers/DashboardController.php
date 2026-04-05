<?php

namespace App\Http\Controllers;

use App\Models\Experiment;
use App\Models\LabRecord;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $uid = (int) $request->user()->id;

        $calculationsCount = Experiment::query()->where('user_id', $uid)->count();
        $videosCount = LabRecord::query()
            ->where('user_id', $uid)
            ->where('source', LabRecord::SOURCE_SIMULATOR_3D)
            ->count();

        $lastActivity = collect([
            Experiment::query()->where('user_id', $uid)->latest()->first()?->created_at,
            LabRecord::query()->where('user_id', $uid)->latest()->first()?->created_at,
        ])->filter()->max();

        $experiments = Experiment::query()
            ->where('user_id', $uid)
            ->latest()
            ->limit(10)
            ->get()
            ->reverse()
            ->values();

        $chartLabels = [];
        $chartBefore = [];
        $chartAfter = [];

        foreach ($experiments as $i => $exp) {
            $chartLabels[] = (string) ($i + 1);
            $pr = $exp->physics_result ?? [];
            $chartBefore[] = round((float) ($pr['ke_initial'] ?? 0), 2);
            $chartAfter[] = round((float) ($pr['ke_final'] ?? 0), 2);
        }

        if ($chartLabels === []) {
            for ($i = 1; $i <= 10; $i++) {
                $chartLabels[] = (string) $i;
                $chartBefore[] = round(320 + 190 * sin($i * 0.72) + $i * 12);
                $chartAfter[] = round(300 + 175 * cos($i * 0.68) + $i * 11);
            }
        }

        $simMinutes = max($calculationsCount * 4, 3);
        $simHours = intdiv($simMinutes, 60);
        $simMinsRem = $simMinutes % 60;

        return view('dashboard', [
            'calculationsCount' => $calculationsCount,
            'videosCount' => $videosCount,
            'lastActivity' => $lastActivity,
            'chartLabels' => $chartLabels,
            'chartBefore' => $chartBefore,
            'chartAfter' => $chartAfter,
            'simHours' => $simHours,
            'simMinsRem' => $simMinsRem,
        ]);
    }
}
