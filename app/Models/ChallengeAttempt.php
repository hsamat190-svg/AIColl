<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChallengeAttempt extends Model
{
    protected $fillable = [
        'user_id',
        'tag',
        'scenario_input',
        'user_prediction',
        'physics_truth',
        'score',
        'duration_ms',
    ];

    protected function casts(): array
    {
        return [
            'scenario_input' => 'array',
            'user_prediction' => 'array',
            'physics_truth' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
