<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameQuestion extends Model
{
    protected $fillable = [
        'game_id',
        'order_index',
        'question_text',
        'question_image_path',
        'question_audio_path',
        'question_emoji',
        'options',
        'match_text',
        'match_image_path',
        'match_emoji',
        'correct_answer',
        'difference_zones',
        'beat_pattern',
        'hint',
        'points',
    ];

    protected $casts = [
        'options'          => 'array',
        'difference_zones' => 'array',
        'beat_pattern'     => 'array',
        'order_index'      => 'integer',
        'points'           => 'integer',
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }
}
