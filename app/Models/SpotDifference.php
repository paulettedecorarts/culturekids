<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class SpotDifference extends Model
{
    protected $fillable = [
        'tribe_id', 'title', 'description', 'difficulty_level',
        'age_min', 'age_max', 'star_points', 'status',
        'image_a_path', 'image_b_path',
        'time_limit_seconds', 'total_differences',
        'cultural_note', 'scene_name', 'metadata',
    ];

    protected $casts = [
        'age_min'             => 'integer',
        'age_max'             => 'integer',
        'star_points'         => 'integer',
        'time_limit_seconds'  => 'integer',
        'total_differences'   => 'integer',
        'metadata'            => 'array',
    ];

    protected static bool $syncingLegacyActivity = false;

    protected static function booted(): void
    {
        static::saved(function (SpotDifference $sd): void {
            if (self::$syncingLegacyActivity) return;
            self::$syncingLegacyActivity = true;
            try { $sd->syncLegacyActivity(); }
            finally { self::$syncingLegacyActivity = false; }
        });

        static::deleted(function (SpotDifference $sd): void {
            if (self::$syncingLegacyActivity) return;
            self::$syncingLegacyActivity = true;
            try {
                DB::table('activities')
                    ->where('type', 'spot_difference')
                    ->where('metadata->legacy_spot_difference_id', $sd->id)
                    ->delete();
            } finally { self::$syncingLegacyActivity = false; }
        });
    }

    public function tribe(): BelongsTo
    {
        return $this->belongsTo(Tribe::class);
    }

    public function zones(): HasMany
    {
        return $this->hasMany(SpotDifferenceZone::class)->orderBy('order_index');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(SpotDifferenceAttempt::class);
    }

    public function getAgeRangeAttribute(): string
    {
        if ($this->age_min && $this->age_max) {
            return "{$this->age_min}-{$this->age_max}";
        }
        return 'All';
    }

    protected function syncLegacyActivity(): void
    {
        $metadata = array_merge($this->metadata ?? [], [
            'source'                    => 'spot_difference_mirror',
            'legacy_spot_difference_id' => $this->id,
            'total_differences'         => $this->total_differences,
        ]);

        $query = DB::table('activities')
            ->where('type', 'spot_difference')
            ->where(function ($q): void {
                $q->where('metadata->legacy_spot_difference_id', $this->id)
                  ->orWhere(function ($f): void {
                      $f->where('tribe_id', $this->tribe_id)
                        ->where('title', $this->title);
                  });
            });

        $payload = [
            'tribe_id'     => $this->tribe_id,
            'type'         => 'spot_difference',
            'title'        => $this->title,
            'description'  => $this->description,
            'age_range'    => $this->age_range !== 'All' ? $this->age_range : null,
            'star_points'  => $this->star_points,
            'metadata'     => json_encode($metadata),
            'is_published' => $this->status === 'published',
            'updated_at'   => now(),
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
