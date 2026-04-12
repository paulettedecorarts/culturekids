<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Song extends Model
{
    protected $fillable = [
        'org_id',
        'tribe_id',
        'title',
        'description',
        'language',
        'song_type',
        'lyrics',
        'audio_path',
        'video_path',
        'cover_image_path',
        'duration_seconds',
        'age_min',
        'age_max',
        'star_points',
        'status',
        'metadata',
    ];

    protected $casts = [
        'duration_seconds' => 'integer',
        'age_min' => 'integer',
        'age_max' => 'integer',
        'star_points' => 'integer',
        'metadata' => 'array',
    ];

    protected static bool $syncingLegacyActivity = false;

    protected static function booted(): void
    {
        static::saved(function (Song $song): void {
            if (self::$syncingLegacyActivity) {
                return;
            }

            self::$syncingLegacyActivity = true;
            try {
                $song->syncLegacyActivity();
            } finally {
                self::$syncingLegacyActivity = false;
            }
        });

        static::deleted(function (Song $song): void {
            if (self::$syncingLegacyActivity) {
                return;
            }

            self::$syncingLegacyActivity = true;
            try {
                DB::table('activities')
                    ->where('type', 'song')
                    ->where('metadata->legacy_song_id', $song->id)
                    ->delete();
            } finally {
                self::$syncingLegacyActivity = false;
            }
        });
    }

    public function tribe(): BelongsTo
    {
        return $this->belongsTo(Tribe::class);
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class, 'org_id');
    }

    public function organisationSongDecisions(): HasMany
    {
        return $this->hasMany(OrganisationSongDecision::class);
    }

    public function getAgeRangeAttribute(): string
    {
        if ($this->age_min && $this->age_max) {
            return "{$this->age_min}-{$this->age_max}";
        }

        return 'All';
    }

    public function getDurationLabelAttribute(): string
    {
        if (! $this->duration_seconds || $this->duration_seconds < 1) {
            return '—';
        }

        $minutes = intdiv($this->duration_seconds, 60);
        $seconds = $this->duration_seconds % 60;

        return sprintf('%d:%02d', $minutes, $seconds);
    }

    protected function syncLegacyActivity(): void
    {
        $metadata = array_merge($this->metadata ?? [], [
            'source' => 'song_compat_mirror',
            'legacy_song_id' => $this->id,
            'language' => $this->language,
            'song_type' => $this->song_type,
            'lyrics' => $this->lyrics,
            'audio_path' => $this->audio_path,
            'video_path' => $this->video_path,
            'cover_image_path' => $this->cover_image_path,
            'duration_seconds' => $this->duration_seconds,
        ]);

        $query = DB::table('activities')
            ->where('type', 'song')
            ->where(function ($inner): void {
                $inner->where('metadata->legacy_song_id', $this->id)
                    ->orWhere(function ($fallback): void {
                        $fallback->where('tribe_id', $this->tribe_id)
                            ->where('title', $this->title);
                    });
            });

        $payload = [
            'tribe_id' => $this->tribe_id,
            'type' => 'song',
            'title' => $this->title,
            'description' => $this->description,
            'age_range' => $this->age_range !== 'All' ? $this->age_range : null,
            'star_points' => $this->star_points,
            'metadata' => json_encode($metadata),
            'is_published' => $this->status === 'published',
            'updated_at' => now(),
        ];

        $existing = $query->orderByDesc('id')->first();
        if ($existing) {
            DB::table('activities')->where('id', $existing->id)->update($payload);

            return;
        }

        $payload['created_at'] = now();
        DB::table('activities')->insert($payload);
    }
}
