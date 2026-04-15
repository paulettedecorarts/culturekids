<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReadingProgress extends Model
{
    protected $table = 'reading_progress';

    protected $fillable = [
        'user_id',
        'comic_id',
        'current_page',
        'total_pages',
        'status',
        'last_read_at',
    ];

    protected $casts = [
        'last_read_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comic(): BelongsTo
    {
        return $this->belongsTo(Comic::class);
    }

    /**
     * Calculate progress percentage
     */
    public function getProgressPercentageAttribute(): int
    {
        if ($this->total_pages === 0 || $this->total_pages === null) {
            return 0;
        }
        
        // Ensure current_page doesn't exceed total_pages
        $currentPage = min($this->current_page, $this->total_pages);
        
        return (int) round(($currentPage / $this->total_pages) * 100);
    }

    /**
     * Update status based on current page
     */
    public function updateStatus(): void
    {
        if ($this->current_page === 0) {
            $this->status = 'not_started';
        } elseif ($this->current_page >= $this->total_pages) {
            $this->status = 'completed';
        } else {
            $this->status = 'in_progress';
        }
    }
}
