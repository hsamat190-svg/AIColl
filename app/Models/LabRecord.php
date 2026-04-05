<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabRecord extends Model
{
    public const SOURCE_SIMULATOR_2D = 'simulator_2d';

    public const SOURCE_SIMULATOR_3D = 'simulator_3d';

    protected $fillable = [
        'user_id',
        'name',
        'source',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function belongsToUser(int $userId): bool
    {
        return (int) $this->user_id === $userId;
    }
}
