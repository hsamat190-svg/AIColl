<?php

namespace App\Http\Controllers;

use App\Models\Experiment;
use App\Models\LabRecord;
use App\Support\LabHistoryHumanReadable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LabController extends Controller
{
    public function index(): View
    {
        return view('lab.index');
    }

    public function history(Request $request): View
    {
        $records = LabRecord::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        return view('lab.history', compact('records'));
    }

    public function showHistoryRecord(Request $request, LabRecord $labRecord): View
    {
        if (! $labRecord->belongsToUser((int) $request->user()->id)) {
            abort(403);
        }

        $experiment = null;
        if ($labRecord->source === LabRecord::SOURCE_SIMULATOR_2D && ! empty($labRecord->payload['experiment_id'])) {
            $experiment = Experiment::query()
                ->where('user_id', $request->user()->id)
                ->find($labRecord->payload['experiment_id']);
        }

        if ($experiment) {
            $historySections = LabHistoryHumanReadable::fromExperiment($experiment);
        } elseif ($labRecord->source === LabRecord::SOURCE_SIMULATOR_3D) {
            $historySections = LabHistoryHumanReadable::fromVideoPayload($labRecord->payload ?? []);
        } else {
            $historySections = LabHistoryHumanReadable::from2dRecordPayload($labRecord->payload ?? []);
        }

        return view('lab.history-detail', [
            'record' => $labRecord,
            'experiment' => $experiment,
            'historySections' => $historySections,
        ]);
    }

    public function updateHistoryRecord(Request $request, LabRecord $labRecord): RedirectResponse
    {
        if (! $labRecord->belongsToUser((int) $request->user()->id)) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $labRecord->update(['name' => $validated['name']]);

        return redirect()
            ->route('lab.history.show', $labRecord)
            ->with('status', 'history-updated');
    }

    public function destroyHistoryRecord(Request $request, LabRecord $labRecord): RedirectResponse
    {
        if (! $labRecord->belongsToUser((int) $request->user()->id)) {
            abort(403);
        }

        $labRecord->delete();

        return redirect()->route('lab.history')->with('status', 'history-deleted');
    }

    public function analysis(Request $request, Experiment $experiment): View
    {
        if ($experiment->user_id !== $request->user()->id) {
            abort(403);
        }

        return view('lab.analysis', compact('experiment'));
    }

    public function leaderboardPage(): View
    {
        return view('lab.leaderboard');
    }

    public function training(): View
    {
        return view('lab.training');
    }

    public function game(): View
    {
        return view('lab.game');
    }

    public function video(): View
    {
        return view('lab.video');
    }

    public function problems(): View
    {
        return view('lab.problems');
    }
}
