<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comic extends Model
{
    protected $fillable = [
        'org_id',
        'tribe_id',
        'title',
        'description',
        'age_min',
        'age_max',
        'status',
        'cover_image_path',
        'bundle_path',
        'bundle_hash',
        'star_points',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'age_min' => 'integer',
        'age_max' => 'integer',
        'star_points' => 'integer',
    ];

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class, 'org_id');
    }

    public function tribe(): BelongsTo
    {
        return $this->belongsTo(Tribe::class);
    }

    public function panels(): HasMany
    {
        return $this->hasMany(ComicPanel::class)->orderBy('order_index');
    }

    public function organisationComicDecisions(): HasMany
    {
        return $this->hasMany(OrganisationComicDecision::class);
    }

    /**
     * Get age range string (e.g., "2-3", "3-4")
     */
    public function getAgeRangeAttribute(): string
    {
        return "{$this->age_min}-{$this->age_max}";
    }

    /**
     * Scope for published comics
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope for draft comics
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope for comics in review
     */
    public function scopeInReview($query)
    {
        return $query->where('status', 'review');
    }

    protected static bool $syncingActivity = false;

    protected static function booted(): void
    {
        static::saved(function (Comic $comic): void {
            if (self::$syncingActivity) return;
            self::$syncingActivity = true;
            try { $comic->syncToActivities(); }
            finally { self::$syncingActivity = false; }
        });

        static::deleted(function (Comic $comic): void {
            if (self::$syncingActivity) return;
            self::$syncingActivity = true;
            try {
                \Illuminate\Support\Facades\DB::table('activities')
                    ->where('type', 'story')
                    ->where('metadata->comic_id', $comic->id)
                    ->delete();
            } finally { self::$syncingActivity = false; }
        });
    }

    protected function syncToActivities(): void
    {
        if ($this->star_points === null) {
            $this->refresh();
        }

        $metadata = array_merge($this->metadata ?? [], [
            'source'   => 'comic_mirror',
            'comic_id' => $this->id,
        ]);

        $query = \Illuminate\Support\Facades\DB::table('activities')
            ->where('type', 'story')
            ->where(function ($q): void {
                $q->where('metadata->comic_id', $this->id)
                  ->orWhere(function ($f): void {
                      $f->where('tribe_id', $this->tribe_id)
                        ->where('title', $this->title);
                  });
            });

        $payload = [
            'tribe_id'     => $this->tribe_id,
            'type'         => 'story',
            'title'        => $this->title,
            'description'  => $this->description,
            'age_range'    => $this->age_range,
            'star_points'  => $this->star_points,
            'metadata'     => json_encode($metadata),
            'is_published' => $this->status === 'published',
            'updated_at'   => now(),
        ];

        $existing = $query->orderByDesc('id')->first();
        if ($existing) {
            \Illuminate\Support\Facades\DB::table('activities')->where('id', $existing->id)->update($payload);
            return;
        }

        $payload['created_at'] = now();
        \Illuminate\Support\Facades\DB::table('activities')->insert($payload);
    }
}
