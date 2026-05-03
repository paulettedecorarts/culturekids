<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SongActivity extends Model
{
    protected $fillable = [
        'song_id',
        'user_id',
        'activity_type',
        'completion_data',
        'stars_earned',
        'completed',
        'completed_at',
    ];

    protected $casts = [
        'completion_data' => 'array',
        'stars_earned' => 'integer',
        'completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function song(): BelongsTo
    {
        return $this->belongsTo(Song::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calculate completion percentage based on activity type
     */
    public function getCompletionPercentageAttribute(): int
    {
        $data = $this->completion_data ?? [];
        
        return match($this->activity_type) {
            'karaoke' => $data['sing_along_percentage'] ?? 0,
            'fill_blanks' => $data['correct_answers'] ?? 0,
            'lullaby' => $data['listening_time_percentage'] ?? 0,
            default => $this->completed ? 100 : 0,
        };
    }

    /**
     * Get performance rating (1-5 stars)
     */
    public function getPerformanceRatingAttribute(): int
    {
        $percentage = $this->completion_percentage;
        
        return match(true) {
            $percentage >= 90 => 5,
            $percentage >= 75 => 4,
            $percentage >= 60 => 3,
            $percentage >= 40 => 2,
            $percentage >= 20 => 1,
            default => 0,
        };
    }
}