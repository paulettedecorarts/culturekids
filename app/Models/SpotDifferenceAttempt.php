<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpotDifferenceAttempt extends Model
{
    protected $fillable = [
        'spot_difference_id', 'user_id', 'completed',
        'stars_earned', 'differences_found', 'wrong_taps',
        'time_spent_seconds', 'attempts_count',
        'found_zone_ids', 'completed_at',
    ];

    protected $casts = [
        'completed'          => 'boolean',
        'stars_earned'       => 'integer',
        'differences_found'  => 'integer',
        'wrong_taps'         => 'integer',
        'time_spent_seconds' => 'integer',
        'attempts_count'     => 'integer',
        'found_zone_ids'     => 'array',
        'completed_at'       => 'datetime',
    ];

    public function spotDifference(): BelongsTo
    {
        return $this->belongsTo(SpotDifference::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
