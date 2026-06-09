<?php

namespace App\Support;

use App\Models\Drawing;

final class DrawingApiSerializer
{
    /**
     * Playable drawing payload for the child app.
     *
     * @return array<string, mixed>
     */
    public static function toArray(Drawing $drawing): array
    {
        $drawing->loadMissing('tribe:id,name,color,hero_emoji,hero_icon');

        $toolsConfig = $drawing->tools_config;
        if (! is_array($toolsConfig) || $toolsConfig === []) {
            $toolsConfig = $drawing->default_tools_config;
        }

        $palette = $drawing->color_palette;
        if (! is_array($palette) || $palette === []) {
            $palette = $drawing->default_color_palette;
        }

        return [
            'id' => $drawing->id,
            'title' => $drawing->title,
            'description' => $drawing->description,
            'drawing_type' => $drawing->drawing_type,
            'drawing_type_label' => self::typeLabel($drawing->drawing_type),
            'drawing_type_icon' => self::typeIcon($drawing->drawing_type),
            'difficulty_level' => $drawing->difficulty_level,
            'age_min' => $drawing->age_min,
            'age_max' => $drawing->age_max,
            'age_range' => $drawing->age_range,
            'star_points' => $drawing->star_points,
            'template_url' => self::publicUrl($drawing->template_path),
            'preview_url' => self::publicUrl($drawing->preview_path),
            'cover_image_url' => self::publicUrl($drawing->preview_path ?: $drawing->template_path),
            'tools_config' => $toolsConfig,
            'color_palette' => $palette,
            'materials' => $drawing->materials ?? [],
            'metadata' => $drawing->metadata ?? [],
            'tribe' => $drawing->tribe ? [
                'id' => $drawing->tribe->id,
                'name' => $drawing->tribe->name,
                'color' => $drawing->tribe->color,
                'icon' => $drawing->tribe->hero_emoji ?? $drawing->tribe->hero_icon,
            ] : null,
        ];
    }

    private static function typeLabel(?string $drawingType): string
    {
        return match ($drawingType) {
            'coloring' => 'Colouring Page',
            'colour_by_number' => 'Colour by Number',
            'hero_drawing' => 'Hero Drawing',
            'design_tool' => 'Design Tool',
            'free_draw' => 'Free Drawing',
            default => $drawingType
                ? ucfirst(str_replace('_', ' ', $drawingType))
                : 'Drawing',
        };
    }

    private static function typeIcon(?string $drawingType): string
    {
        return match ($drawingType) {
            'coloring', 'colour_by_number' => '🖍️',
            'hero_drawing' => '🦸',
            'design_tool' => '✏️',
            'free_draw' => '🎨',
            default => '🎨',
        };
    }

    private static function publicUrl(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset('storage/'.$path);
    }
}
