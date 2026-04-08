<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ComicPanel extends Model
{
    protected $fillable = [
        'comic_id',
        'order_index',
        'image_path',
        'audio_url',
        'caption',
        'vocab_tags',
        'metadata',
    ];

    protected $casts = [
        'vocab_tags' => 'array',
        'metadata' => 'array',
        'order_index' => 'integer',
    ];

    public function comic(): BelongsTo
    {
        return $this->belongsTo(Comic::class);
    }

    public function vocabTags(): HasMany
    {
        return $this->hasMany(PanelVocabTag::class, 'panel_id');
    }

    /**
     * Check if panel is a PDF
     */
    public function isPdf(): bool
    {
        return str_ends_with(strtolower($this->image_path), '.pdf');
    }

    /**
     * Check if panel is an image
     */
    public function isImage(): bool
    {
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg'];
        $extension = strtolower(pathinfo($this->image_path, PATHINFO_EXTENSION));

        return in_array($extension, $imageExtensions);
    }

    /**
     * Get file extension
     */
    public function getFileExtension(): string
    {
        return strtolower(pathinfo($this->image_path, PATHINFO_EXTENSION));
    }
}
