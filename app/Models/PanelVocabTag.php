<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PanelVocabTag extends Model
{
    protected $fillable = [
        'panel_id',
        'word',
        'translation',
        'phonetic',
        'x_position',
        'y_position',
        'width',
        'height',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'x_position' => 'integer',
        'y_position' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
    ];

    public function panel(): BelongsTo
    {
        return $this->belongsTo(ComicPanel::class, 'panel_id');
    }
}
