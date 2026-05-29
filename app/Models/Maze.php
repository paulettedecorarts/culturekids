<?php

namespace App\Models;

use App\Jobs\SyncMazeLegacyActivity;
use App\Support\MazeApiSerializer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Maze extends Model
{
    protected $fillable = [
        'tribe_id', 'title', 'description', 'maze_type', 'difficulty_level',
        'age_min', 'age_max', 'star_points', 'status',
        'grid', 'grid_rows', 'grid_cols',
        'start_position', 'end_position', 'collectibles',
        'time_limit_seconds', 'visibility_radius',
        'background_image_path', 'cover_image_path',
        'cultural_note', 'hero_character', 'metadata',
    ];

    protected $casts = [
        'age_min'          => 'integer',
        'age_max'          => 'integer',
        'star_points'      => 'integer',
        'grid'             => 'array',
        'grid_rows'        => 'integer',
        'grid_cols'        => 'integer',
        'start_position'   => 'array',
        'end_position'     => 'array',
        'collectibles'     => 'array',
        'time_limit_seconds' => 'integer',
        'visibility_radius'  => 'integer',
        'metadata'         => 'array',
    ];

    public const TYPES = [
        'standard'       => 'Standard Maze',
        'timed'          => 'Timed Maze',
        'collect_items'  => 'Collect Items',
        'visibility'     => 'Torch / Visibility',
        'reverse'        => 'Reverse Maze',
        'circular'       => 'Circular Maze',
    ];

    public const DIFFICULTIES = [
        'easy'   => 'Easy',
        'medium' => 'Medium',
        'hard'   => 'Hard',
        'expert' => 'Expert',
        'master' => 'Master',
    ];

    protected static bool $syncingLegacyActivity = false;

    protected static function booted(): void
    {
        static::saved(function (Maze $maze): void {
            if (self::$syncingLegacyActivity) {
                return;
            }

            SyncMazeLegacyActivity::dispatch((int) $maze->id)->afterResponse();
        });

        static::deleted(function (Maze $maze): void {
            if (self::$syncingLegacyActivity) return;
            self::$syncingLegacyActivity = true;
            try {
                DB::table('activities')
                    ->where('type', 'maze')
                    ->where('metadata->legacy_maze_id', $maze->id)
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
        return $this->hasMany(MazeAttempt::class);
    }

    public function getAgeRangeAttribute(): string
    {
        if ($this->age_min && $this->age_max) {
            return "{$this->age_min}-{$this->age_max}";
        }
        return 'All';
    }

    public function getMazeTypeLabelAttribute(): string
    {
        return self::TYPES[$this->maze_type] ?? ucfirst(str_replace('_', ' ', $this->maze_type));
    }

    public function getMazeTypeIconAttribute(): string
    {
        return match($this->maze_type) {
            'standard'      => '🌀',
            'timed'         => '⏱️',
            'collect_items' => '💎',
            'visibility'    => '🔦',
            'reverse'       => '↩️',
            'circular'      => '🎯',
            default         => '🌀',
        };
    }

    public function getDifficultyLabelAttribute(): string
    {
        return self::DIFFICULTIES[$this->difficulty_level] ?? ucfirst($this->difficulty_level);
    }

    /**
     * @return array<string, mixed>
     */
    public function toPlayableArray(): array
    {
        return MazeApiSerializer::toArray($this);
    }

    public function syncLegacyActivityMirror(): void
    {
        if (self::$syncingLegacyActivity) {
            return;
        }

        self::$syncingLegacyActivity = true;
        try {
            $this->syncLegacyActivity();
        } finally {
            self::$syncingLegacyActivity = false;
        }
    }

    protected function syncLegacyActivity(): void
    {
        // Keep activity metadata small (grid lives on mazes table). A full maze blob here
        // slowed list endpoints and CMS saves enough to trigger gateway timeouts.
        $metadata = array_merge($this->metadata ?? [], [
            'source'          => 'maze_mirror',
            'legacy_maze_id'  => $this->id,
            'maze_type'       => $this->maze_type,
            'grid_rows'       => $this->grid_rows,
            'grid_cols'       => $this->grid_cols,
        ]);
        unset($metadata['maze']);

        $query = DB::table('activities')
            ->where('type', 'maze')
            ->where(function ($q): void {
                $q->where('metadata->legacy_maze_id', $this->id)
                  ->orWhere(function ($f): void {
                      $f->where('tribe_id', $this->tribe_id)
                        ->where('title', $this->title);
                  });
            });

        $payload = [
            'tribe_id'     => $this->tribe_id,
            'type'         => 'maze',
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
