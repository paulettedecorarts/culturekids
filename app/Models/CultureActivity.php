<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class CultureActivity extends Model
{
    protected $fillable = [
        'tribe_id', 'title', 'description', 'culture_type',
        'difficulty_level', 'age_min', 'age_max', 'star_points', 'status',
        'clan_name', 'clan_totem', 'clan_role', 'clan_emoji',
        'content', 'content_sections', 'quiz_questions', 'map_data', 'design_elements',
        'cover_image_path', 'map_image_path',
        'cultural_note', 'proverb', 'proverb_translation', 'metadata',
    ];

    protected $casts = [
        'age_min'           => 'integer',
        'age_max'           => 'integer',
        'star_points'       => 'integer',
        'content_sections'  => 'array',
        'quiz_questions'    => 'array',
        'map_data'          => 'array',
        'design_elements'   => 'array',
        'metadata'          => 'array',
    ];

    public const TYPES = [
        'clan_story'   => 'Clan Story',
        'clan_history' => 'Clan History',
        'clan_profile' => 'Clan Profile',
        'clan_map'     => 'Clan Map',
        'clan_design'  => 'Clan Crest Design',
    ];

    protected static bool $syncingLegacyActivity = false;

    protected static function booted(): void
    {
        static::saved(function (CultureActivity $ca): void {
            if (self::$syncingLegacyActivity) return;
            self::$syncingLegacyActivity = true;
            try { $ca->syncLegacyActivity(); }
            finally { self::$syncingLegacyActivity = false; }
        });

        static::deleted(function (CultureActivity $ca): void {
            if (self::$syncingLegacyActivity) return;
            self::$syncingLegacyActivity = true;
            try {
                DB::table('activities')
                    ->where('type', 'culture')
                    ->where('metadata->legacy_culture_activity_id', $ca->id)
                    ->delete();
            } finally { self::$syncingLegacyActivity = false; }
        });
    }

    public function tribe(): BelongsTo
    {
        return $this->belongsTo(Tribe::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(CultureActivityAttempt::class);
    }

    public function getAgeRangeAttribute(): string
    {
        if ($this->age_min && $this->age_max) {
            return "{$this->age_min}-{$this->age_max}";
        }
        return 'All';
    }

    public function getCultureTypeLabelAttribute(): string
    {
        return self::TYPES[$this->culture_type] ?? ucfirst(str_replace('_', ' ', $this->culture_type));
    }

    public function getCultureTypeIconAttribute(): string
    {
        return match($this->culture_type) {
            'clan_story'   => '📖',
            'clan_history' => '📜',
            'clan_profile' => '🌳',
            'clan_map'     => '🗺️',
            'clan_design'  => '🎨',
            default        => '🏛️',
        };
    }

    protected function syncLegacyActivity(): void
    {
        $metadata = array_merge($this->metadata ?? [], [
            'source'                    => 'culture_activity_mirror',
            'legacy_culture_activity_id' => $this->id,
            'culture_type'              => $this->culture_type,
            'clan_name'                 => $this->clan_name,
        ]);

        $query = DB::table('activities')
            ->where('type', 'culture')
            ->where(function ($q): void {
                $q->where('metadata->legacy_culture_activity_id', $this->id)
                  ->orWhere(function ($f): void {
                      $f->where('tribe_id', $this->tribe_id)
                        ->where('title', $this->title);
                  });
            });

        $payload = [
            'tribe_id'     => $this->tribe_id,
            'type'         => 'culture',
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
