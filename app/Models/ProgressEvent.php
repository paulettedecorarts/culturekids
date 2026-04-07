<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgressEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'child_profile_id',
        'activity_id',
        'stars_earned',
        'completed_at',
        'synced_at',
        'idempotency_key',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'synced_at' => 'datetime',
    ];

    /**
     * Get the child this progress event belongs to
     */
    public function childProfile(): BelongsTo
    {
        return $this->belongsTo(ChildProfile::class);
    }

    /**
     * Get the associated activity
     */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }
}
