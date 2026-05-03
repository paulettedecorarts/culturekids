<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SongLyricSegment extends Model
{
    protected $fillable = [
        'song_id',
        'segment_text',
        'start_time',
        'end_time',
        'order_index',
        'segment_type',
        'is_fill_blank',
        'blank_answer',
        'metadata',
    ];

    protected $casts = [
        'start_time' => 'decimal:3',
        'end_time' => 'decimal:3',
        'order_index' => 'integer',
        'is_fill_blank' => 'boolean',
        'metadata' => 'array',
    ];

    public function song(): BelongsTo
    {
        return $this->belongsTo(Song::class);
    }

    /**
     * Get the duration of this segment in seconds
     */
    public function getDurationAttribute(): float
    {
        return (float) ($this->end_time - $this->start_time);
    }

    /**
     * Check if this segment overlaps with given time
     */
    public function isActiveAt(float $time): bool
    {
        return $time >= $this->start_time && $time <= $this->end_time;
    }

    /**
     * Get segment text with blanks for fill-the-lyric games
     */
    public function getDisplayTextAttribute(): string
    {
        if ($this->is_fill_blank && $this->blank_answer) {
            return str_replace($this->blank_answer, '____', $this->segment_text);
        }
        
        return $this->segment_text;
    }
}