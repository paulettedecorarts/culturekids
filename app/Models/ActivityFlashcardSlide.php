<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityFlashcardSlide extends Model
{
    protected $fillable = [
        'activity_id',
        'order_index',
        'emoji',
        'front_label',
        'back_label',
        'phonetic',
        'image_path',
        'audio_path',
        'metadata',
    ];

    protected $casts = [
        'order_index' => 'integer',
        'metadata' => 'array',
    ];

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }
}
