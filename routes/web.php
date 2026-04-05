<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\Api\ChallengeController;
use App\Http\Controllers\Api\DatasetController;
use App\Http\Controllers\Api\ExperimentController;
use App\Http\Controllers\Api\ScenarioController;
use App\Http\Controllers\Api\TrainingController;
use App\Http\Controllers\Api\LabRecordController;
use App\Http\Controllers\Api\ProblemSolveController;
use App\Http\Controllers\Api\VideoController;
use App\Http\Controllers\LabController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/locale/{locale}', [LocaleController::class, 'switch'])
    ->whereIn('locale', ['ru', 'kz'])
    ->name('locale.switch');

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/lab', [LabController::class, 'index'])->name('lab.index');
    Route::get('/lab/history', [LabController::class, 'history'])->name('lab.history');
    Route::get('/lab/history/{labRecord}', [LabController::class, 'showHistoryRecord'])->name('lab.history.show');
    Route::patch('/lab/history/{labRecord}', [LabController::class, 'updateHistoryRecord'])->name('lab.history.update');
    Route::delete('/lab/history/{labRecord}', [LabController::class, 'destroyHistoryRecord'])->name('lab.history.destroy');
    Route::get('/lab/experiments/{experiment}/analysis', [LabController::class, 'analysis'])->name('lab.analysis');
    Route::permanentRedirect('/lab/challenge', '/lab/video');
    Route::get('/lab/leaderboard', [LabController::class, 'leaderboardPage'])->name('lab.leaderboard');
    Route::get('/lab/training', [LabController::class, 'training'])->name('lab.training');
    Route::get('/lab/game', [LabController::class, 'game'])->name('lab.game');
    Route::get('/lab/video', [LabController::class, 'video'])->name('lab.video');
    Route::get('/lab/problems', [LabController::class, 'problems'])->name('lab.problems');

    Route::prefix('api/lab')->group(function () {
        Route::get('experiments', [ExperimentController::class, 'index']);
        Route::post('experiments', [ExperimentController::class, 'store']);
        Route::get('experiments/{experiment}', [ExperimentController::class, 'show']);
        Route::post('scenario/random', [ScenarioController::class, 'random']);
        Route::post('challenges/submit', [ChallengeController::class, 'submit']);
        Route::get('leaderboard', [ChallengeController::class, 'leaderboard']);
        Route::post('dataset/batch', [DatasetController::class, 'batch']);
        Route::post('training/submit', [TrainingController::class, 'submit']);
        Route::post('video/analyze', [VideoController::class, 'analyze']);
        Route::post('records', [LabRecordController::class, 'store']);
        Route::post('problems/solve', ProblemSolveController::class)
            ->middleware('throttle:30,1')
            ->name('api.lab.problems.solve');
    });
});

require __DIR__.'/auth.php';
