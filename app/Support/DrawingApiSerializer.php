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
            'difficulty_level' => $drawing->difficulty_level,
            'template_url' => self::publicUrl($drawing->template_path),
            'preview_url' => self::publicUrl($drawing->preview_path),
            'tools_config' => $toolsConfig,
            'color_palette' => $palette,
            'materials' => $drawing->materials ?? [],
            'metadata' => $drawing->metadata ?? [],
        ];
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
