<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CultureActivityAttempt extends Model
{
    protected $fillable = [
        'culture_activity_id', 'user_id', 'completed',
        'stars_earned', 'score', 'time_spent_seconds',
        'attempts_count', 'quiz_results', 'completed_at',
    ];

    protected $casts = [
        'completed'          => 'boolean',
        'stars_earned'       => 'integer',
        'score'              => 'integer',
        'time_spent_seconds' => 'integer',
        'attempts_count'     => 'integer',
        'quiz_results'       => 'array',
        'completed_at'       => 'datetime',
    ];

    public function cultureActivity(): BelongsTo
    {
        return $this->belongsTo(CultureActivity::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
