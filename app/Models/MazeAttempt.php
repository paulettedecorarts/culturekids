<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MazeAttempt extends Model
{
    protected $fillable = [
        'maze_id', 'user_id', 'completed', 'stars_earned',
        'time_spent_seconds', 'collectibles_found', 'attempts_count',
        'path_taken', 'completed_at',
    ];

    protected $casts = [
        'completed'          => 'boolean',
        'stars_earned'       => 'integer',
        'time_spent_seconds' => 'integer',
        'collectibles_found' => 'integer',
        'attempts_count'     => 'integer',
        'path_taken'         => 'array',
        'completed_at'       => 'datetime',
    ];

    public function maze(): BelongsTo
    {
        return $this->belongsTo(Maze::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
