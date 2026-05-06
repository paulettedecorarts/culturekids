<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LanguageActivityAttempt extends Model
{
    protected $fillable = [
        'language_activity_id',
        'user_id',
        'completed',
        'stars_earned',
        'score',
        'attempts_count',
        'time_spent_seconds',
        'word_results',
        'recording_path',
        'completed_at',
    ];

    protected $casts = [
        'completed'          => 'boolean',
        'stars_earned'       => 'integer',
        'score'              => 'integer',
        'attempts_count'     => 'integer',
        'time_spent_seconds' => 'integer',
        'word_results'       => 'array',
        'completed_at'       => 'datetime',
    ];

    public function activity(): BelongsTo
    {
        return $this->belongsTo(LanguageActivity::class, 'language_activity_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
