<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Game extends Model
{
    protected $fillable = [
        'tribe_id',
        'title',
        'description',
        'game_type',
        'difficulty_level',
        'age_min',
        'age_max',
        'star_points',
        'status',
        'time_limit_seconds',
        'lives',
        'shuffle_questions',
        'questions_per_round',
        'cover_image_path',
        'background_music_path',
        'cultural_note',
        'language_code',
        'metadata',
    ];

    protected $casts = [
        'age_min'             => 'integer',
        'age_max'             => 'integer',
        'star_points'         => 'integer',
        'time_limit_seconds'  => 'integer',
        'lives'               => 'integer',
        'shuffle_questions'   => 'boolean',
        'questions_per_round' => 'integer',
        'metadata'            => 'array',
    ];

    public const TYPES = [
        'matching'        => 'Matching Game',
        'quiz'            => 'Quiz / Multiple Choice',
        'fill_lyric'      => 'Fill the Lyric',
        'rhythm'          => 'Rhythm Tap',
        'spot_difference' => 'Spot the Difference',
        'memory'          => 'Memory Flip',
        'sorting'         => 'Sorting / Categorisation',
    ];

    protected static bool $syncingLegacyActivity = false;

    protected static function booted(): void
    {
        static::saved(function (Game $game): void {
            if (self::$syncingLegacyActivity) return;
            self::$syncingLegacyActivity = true;
            try { $game->syncLegacyActivity(); }
            finally { self::$syncingLegacyActivity = false; }
        });

        static::deleted(function (Game $game): void {
            if (self::$syncingLegacyActivity) return;
            self::$syncingLegacyActivity = true;
            try {
                DB::table('activities')
                    ->where('type', 'game')
                    ->where('metadata->legacy_game_id', $game->id)
                    ->delete();
            } finally { self::$syncingLegacyActivity = false; }
        });
    }

    public function tribe(): BelongsTo
    {
        return $this->belongsTo(Tribe::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(GameQuestion::class)->orderBy('order_index');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(GameAttempt::class);
    }

    public function getAgeRangeAttribute(): string
    {
        if ($this->age_min && $this->age_max) {
            return "{$this->age_min}-{$this->age_max}";
        }
        return 'All';
    }

    public function getGameTypeLabelAttribute(): string
    {
        return self::TYPES[$this->game_type] ?? ucfirst(str_replace('_', ' ', $this->game_type));
    }

    public function getGameTypeIconAttribute(): string
    {
        return match($this->game_type) {
            'matching'        => '🔗',
            'quiz'            => '❓',
            'fill_lyric'      => '🎵',
            'rhythm'          => '🥁',
            'spot_difference' => '🔍',
            'memory'          => '🧠',
            'sorting'         => '📦',
            default           => '🎯',
        };
    }

    protected function syncLegacyActivity(): void
    {
        $metadata = array_merge($this->metadata ?? [], [
            'source'         => 'game_mirror',
            'legacy_game_id' => $this->id,
            'game_type'      => $this->game_type,
        ]);

        $query = DB::table('activities')
            ->where('type', 'game')
            ->where(function ($q): void {
                $q->where('metadata->legacy_game_id', $this->id)
                  ->orWhere(function ($f): void {
                      $f->where('tribe_id', $this->tribe_id)
                        ->where('title', $this->title);
                  });
            });

        $payload = [
            'tribe_id'     => $this->tribe_id,
            'type'         => 'game',
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
