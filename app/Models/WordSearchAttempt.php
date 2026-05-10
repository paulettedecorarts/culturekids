<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WordSearchAttempt extends Model
{
    protected $fillable = [
        'word_search_id', 'user_id', 'completed',
        'stars_earned', 'words_found', 'time_spent_seconds',
        'attempts_count', 'found_words', 'completed_at',
    ];

    protected $casts = [
        'completed'          => 'boolean',
        'stars_earned'       => 'integer',
        'words_found'        => 'integer',
        'time_spent_seconds' => 'integer',
        'attempts_count'     => 'integer',
        'found_words'        => 'array',
        'completed_at'       => 'datetime',
    ];

    public function wordSearch(): BelongsTo
    {
        return $this->belongsTo(WordSearch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
