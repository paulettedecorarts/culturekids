<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Drawing extends Model
{
    protected $fillable = [
        'tribe_id',
        'title',
        'description',
        'drawing_type',
        'difficulty_level',
        'age_min',
        'age_max',
        'star_points',
        'status',
        'template_path',
        'preview_path',
        'tools_config',
        'color_palette',
        'materials',
        'metadata',
    ];

    protected $casts = [
        'age_min' => 'integer',
        'age_max' => 'integer',
        'star_points' => 'integer',
        'tools_config' => 'array',
        'color_palette' => 'array',
        'materials' => 'array',
        'metadata' => 'array',
    ];

    protected static bool $syncingLegacyActivity = false;

    protected static function booted(): void
    {
        static::saved(function (Drawing $drawing): void {
            if (self::$syncingLegacyActivity) {
                return;
            }

            self::$syncingLegacyActivity = true;
            try {
                $drawing->syncLegacyActivity();
            } finally {
                self::$syncingLegacyActivity = false;
            }
        });

        static::deleted(function (Drawing $drawing): void {
            if (self::$syncingLegacyActivity) {
                return;
            }

            self::$syncingLegacyActivity = true;
            try {
                DB::table('activities')
                    ->where('type', 'drawing_kit')
                    ->where('metadata->legacy_drawing_id', $drawing->id)
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

    public function submissions(): HasMany
    {
        return $this->hasMany(DrawingSubmission::class);
    }

    public function getAgeRangeAttribute(): string
    {
        if ($this->age_min && $this->age_max) {
            return "{$this->age_min}-{$this->age_max}";
        }

        return 'All';
    }

    public function getDrawingTypeDisplayAttribute(): string
    {
        return match($this->drawing_type) {
            'coloring' => 'Coloring Page',
            'hero_drawing' => 'Hero Drawing',
            'design_tool' => 'Design Tool',
            'free_draw' => 'Free Drawing',
            default => ucfirst(str_replace('_', ' ', $this->drawing_type))
        };
    }

    public function getDefaultToolsConfigAttribute(): array
    {
        return [
            'brushes' => [
                ['name' => 'Small Brush', 'size' => 2],
                ['name' => 'Medium Brush', 'size' => 5],
                ['name' => 'Large Brush', 'size' => 10],
            ],
            'tools' => ['brush', 'eraser', 'fill'],
            'features' => ['undo', 'redo', 'clear'],
        ];
    }

    public function getDefaultColorPaletteAttribute(): array
    {
        return [
            '#FF0000', '#00FF00', '#0000FF', '#FFFF00', '#FF00FF', '#00FFFF',
            '#FFA500', '#800080', '#FFC0CB', '#A52A2A', '#808080', '#000000',
            '#FFFFFF', '#8B4513', '#90EE90', '#FFB6C1', '#20B2AA', '#DDA0DD'
        ];
    }

    protected function syncLegacyActivity(): void
    {
        $metadata = array_merge($this->metadata ?? [], [
            'source' => 'drawing_compat_mirror',
            'legacy_drawing_id' => $this->id,
            'drawing_type' => $this->drawing_type,
            'template_path' => $this->template_path,
            'preview_path' => $this->preview_path,
            'tools_config' => $this->tools_config,
            'color_palette' => $this->color_palette,
            'materials' => $this->materials,
        ]);

        $query = DB::table('activities')
            ->where('type', 'drawing_kit')
            ->where(function ($inner): void {
                $inner->where('metadata->legacy_drawing_id', $this->id)
                    ->orWhere(function ($fallback): void {
                        $fallback->where('tribe_id', $this->tribe_id)
                            ->where('title', $this->title);
                    });
            });

        $payload = [
            'tribe_id' => $this->tribe_id,
            'type' => 'drawing_kit',
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