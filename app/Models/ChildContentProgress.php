<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChildContentProgress extends Model
{
    protected $fillable = [
        'child_profile_id',
        'content_type',
        'content_id',
        'status',
        'current_position',
        'total_positions',
        'stars_earned',
        'metadata',
        'started_at',
        'completed_at',
        'last_activity_at',
        'completion_idempotency_key',
    ];

    protected $casts = [
        'metadata' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'last_activity_at' => 'datetime',
    ];

    public function childProfile(): BelongsTo
    {
        return $this->belongsTo(ChildProfile::class);
    }

    public function getPercentageAttribute(): int
    {
        if ($this->total_positions <= 0) {
            return $this->status === 'completed' ? 100 : 0;
        }

        $position = min($this->current_position, $this->total_positions);

        return (int) round(($position / $this->total_positions) * 100);
    }

    public function refreshStatusFromPosition(): void
    {
        if ($this->status === 'completed') {
            return;
        }

        if ($this->current_position <= 0) {
            $this->status = 'not_started';
        } elseif ($this->total_positions > 0 && $this->current_position >= $this->total_positions) {
            // Session ticks may reach the final position before graded completion runs.
            $this->status = 'in_progress';
        } else {
            $this->status = 'in_progress';
        }
    }
}
