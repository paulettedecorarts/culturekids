<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameAttempt extends Model
{
    protected $fillable = [
        'game_id',
        'user_id',
        'completed',
        'score',
        'stars_earned',
        'correct_answers',
        'wrong_answers',
        'lives_remaining',
        'time_spent_seconds',
        'attempts_count',
        'question_results',
        'completed_at',
    ];

    protected $casts = [
        'completed'          => 'boolean',
        'score'              => 'integer',
        'stars_earned'       => 'integer',
        'correct_answers'    => 'integer',
        'wrong_answers'      => 'integer',
        'lives_remaining'    => 'integer',
        'time_spent_seconds' => 'integer',
        'attempts_count'     => 'integer',
        'question_results'   => 'array',
        'completed_at'       => 'datetime',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
