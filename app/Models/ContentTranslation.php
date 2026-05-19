<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentTranslation extends Model
{
    protected $fillable = [
        'content_type',
        'content_id',
        'panel_id',
        'sub_item_key',
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

    public function scopeForType(Builder $query, string $contentType): Builder
    {
        return $query->where('content_type', $contentType);
    }

    public function typeLabel(): string
    {
        return OrganisationContentDecision::labelFor($this->content_type);
    }

    public function requiresHotspot(): bool
    {
        return (bool) data_get(config('content_translations.types.'.$this->content_type), 'has_hotspot');
    }
}
