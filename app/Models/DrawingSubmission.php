<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DrawingSubmission extends Model
{
    protected $fillable = [
        'drawing_id',
        'user_id',
        'artwork_path',
        'thumbnail_path',
        'completed',
        'stars_earned',
        'time_spent_seconds',
        'tools_used',
        'drawing_data',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'completed' => 'boolean',
        'stars_earned' => 'integer',
        'time_spent_seconds' => 'integer',
        'tools_used' => 'array',
        'drawing_data' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function drawing(): BelongsTo
    {
        return $this->belongsTo(Drawing::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getTimeSpentFormattedAttribute(): string
    {
        if ($this->time_spent_seconds < 60) {
            return $this->time_spent_seconds . 's';
        }

        $minutes = intdiv($this->time_spent_seconds, 60);
        $seconds = $this->time_spent_seconds % 60;

        if ($minutes < 60) {
            return $minutes . 'm ' . $seconds . 's';
        }

        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        return $hours . 'h ' . $remainingMinutes . 'm';
    }

    public function getCompletionPercentageAttribute(): int
    {
        if (!$this->completed) {
            return 0;
        }

        // Calculate based on tools used, time spent, etc.
        $baseScore = 50; // Base completion score
        
        // Bonus for time spent (up to 30 points)
        $timeBonus = min(30, intdiv($this->time_spent_seconds, 60)); // 1 point per minute, max 30
        
        // Bonus for tools variety (up to 20 points)
        $toolsBonus = min(20, count($this->tools_used ?? []) * 5);
        
        return min(100, $baseScore + $timeBonus + $toolsBonus);
    }
}