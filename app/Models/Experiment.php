<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Experiment extends Model
{
    protected $fillable = [
        'user_id',
        'input',
        'physics_result',
        'ai_prediction',
        'comparison',
        'mode',
        'scenario_seed',
    ];

    protected function casts(): array
    {
        return [
            'input' => 'array',
            'physics_result' => 'array',
            'ai_prediction' => 'array',
            'comparison' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
