<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class Activity extends Model
{
    protected $fillable = [
        'tribe_id',
        'type',
        'title',
        'description',
        'age_range',
        'star_points',
        'metadata',
        'is_published',
    ];

    protected $casts = [
        'metadata' => 'json',
        'is_published' => 'boolean',
    ];

    /**
     * Accessor for stars (maps to star_points for backward compatibility)
     */
    public function getStarsAttribute()
    {
        return $this->star_points ?? 10;
    }

    protected static function booted(): void
    {
        static::saving(function (Activity $activity): void {
            if ($activity->type !== 'song') {
                return;
            }

            $source = data_get($activity->metadata, 'source');
            if ($source === 'song_compat_mirror') {
                return;
            }

            Log::warning('Deprecated write path: activities.type=song. Use songs table instead.', [
                'activity_id' => $activity->id,
                'title' => $activity->title,
            ]);
        });
    }

    public function tribe(): BelongsTo
    {
        return $this->belongsTo(Tribe::class);
    }

    /**
     * Ordered slides for flashcard-type activities (deck of cards, like comic panels).
     */
    public function flashcardSlides(): HasMany
    {
        return $this->hasMany(ActivityFlashcardSlide::class)->orderBy('order_index');
    }

    /**
     * Resolved URL for a printable file stored in metadata (relative to the public disk or absolute http(s)).
     */
    public function printableAssetUrl(): ?string
    {
        $metadata = $this->metadata ?? [];
        foreach (['print_path', 'file_path', 'pdf_path', 'asset_path', 'download_url', 'url'] as $key) {
            $value = data_get($metadata, $key);
            if (! is_string($value) || $value === '') {
                continue;
            }
            if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
                return $value;
            }

            return Storage::disk('public')->url(ltrim($value, '/'));
        }

        return null;
    }
}
