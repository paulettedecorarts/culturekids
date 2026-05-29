<?php

namespace App\Support;

use App\Models\Maze;

final class MazeApiSerializer
{
    /**
     * Playable maze payload for the child app and offline bundles.
     *
     * @return array<string, mixed>
     */
    public static function toArray(Maze $maze): array
    {
        return [
            'id' => $maze->id,
            'title' => $maze->title,
            'maze_type' => $maze->maze_type,
            'difficulty_level' => $maze->difficulty_level,
            'grid' => MazePlayableGrid::normalize(
                $maze->grid ?? [],
                $maze->start_position,
                $maze->end_position
            ),
            'grid_rows' => $maze->grid_rows,
            'grid_cols' => $maze->grid_cols,
            'start_position' => $maze->start_position,
            'end_position' => $maze->end_position,
            'collectibles' => $maze->collectibles ?? [],
            'time_limit_seconds' => $maze->time_limit_seconds,
            'visibility_radius' => $maze->visibility_radius,
            'hero_character' => $maze->hero_character,
            'cultural_note' => $maze->cultural_note,
            'background_image_url' => self::publicUrl($maze->background_image_path),
            'cover_image_url' => self::publicUrl($maze->cover_image_path),
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
