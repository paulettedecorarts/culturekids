<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LanguageActivityWord extends Model
{
    protected $fillable = [
        'language_activity_id',
        'word',
        'translation',
        'phonetic',
        'emoji',
        'image_path',
        'audio_path',
        'trace_path',
        'is_correct_answer',
        'order_index',
        'is_fixed',
    ];

    protected $casts = [
        'trace_path'        => 'array',
        'is_correct_answer' => 'boolean',
        'is_fixed'          => 'boolean',
        'order_index'       => 'integer',
    ];

    public function activity(): BelongsTo
    {
        return $this->belongsTo(LanguageActivity::class, 'language_activity_id');
    }
}
