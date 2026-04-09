<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

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
}
