<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class LanguageActivity extends Model
{
    protected $fillable = [
        'tribe_id',
        'language_code',
        'title',
        'description',
        'activity_type',
        'difficulty_level',
        'age_min',
        'age_max',
        'star_points',
        'status',
        'full_sentence',
        'sentence_translation',
        'audio_path',
        'cultural_note',
        'metadata',
    ];

    protected $casts = [
        'age_min'    => 'integer',
        'age_max'    => 'integer',
        'star_points' => 'integer',
        'metadata'   => 'array',
    ];

    // Activity type labels
    public const TYPES = [
        'word_trace'       => 'Word Trace',
        'audio_match'      => 'Audio Match',
        'speak_back'       => 'Speak Back',
        'proverb_jumble'   => 'Proverb Jumble',
        'sentence_builder' => 'Sentence Builder',
    ];

    protected static bool $syncingLegacyActivity = false;

    protected static function booted(): void
    {
        static::saved(function (LanguageActivity $activity): void {
            if (self::$syncingLegacyActivity) return;
            self::$syncingLegacyActivity = true;
            try {
                $activity->syncLegacyActivity();
            } finally {
                self::$syncingLegacyActivity = false;
            }
        });

        static::deleted(function (LanguageActivity $activity): void {
            if (self::$syncingLegacyActivity) return;
            self::$syncingLegacyActivity = true;
            try {
                DB::table('activities')
                    ->where('type', 'vocab_pack')
                    ->where('metadata->legacy_language_activity_id', $activity->id)
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

    public function words(): HasMany
    {
        return $this->hasMany(LanguageActivityWord::class)->orderBy('order_index');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(LanguageActivityAttempt::class);
    }

    public function getAgeRangeAttribute(): string
    {
        if ($this->age_min && $this->age_max) {
            return "{$this->age_min}-{$this->age_max}";
        }
        return 'All';
    }

    public function getActivityTypeLabelAttribute(): string
    {
        return self::TYPES[$this->activity_type] ?? ucfirst(str_replace('_', ' ', $this->activity_type));
    }

    public function getActivityTypeIconAttribute(): string
    {
        return match($this->activity_type) {
            'word_trace'       => '✏️',
            'audio_match'      => '🔊',
            'speak_back'       => '🎤',
            'proverb_jumble'   => '🧩',
            'sentence_builder' => '🔤',
            default            => '📝',
        };
    }

    protected function syncLegacyActivity(): void
    {
        $metadata = array_merge($this->metadata ?? [], [
            'source'                    => 'language_activity_mirror',
            'legacy_language_activity_id' => $this->id,
            'activity_type'             => $this->activity_type,
            'language_code'             => $this->language_code,
        ]);

        $query = DB::table('activities')
            ->where('type', 'vocab_pack')
            ->where(function ($q): void {
                $q->where('metadata->legacy_language_activity_id', $this->id)
                  ->orWhere(function ($f): void {
                      $f->where('tribe_id', $this->tribe_id)
                        ->where('title', $this->title);
                  });
            });

        $payload = [
            'tribe_id'     => $this->tribe_id,
            'type'         => 'vocab_pack',
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
