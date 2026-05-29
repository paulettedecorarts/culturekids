<?php

namespace App\Support;

use App\Models\SpotDifference;

final class SpotDifferenceApiSerializer
{
    /**
     * Playable spot-the-difference payload for the child app.
     *
     * @return array<string, mixed>
     */
    public static function toArray(SpotDifference $item): array
    {
        $item->loadMissing('zones');

        return [
            'id' => $item->id,
            'title' => $item->title,
            'description' => $item->description,
            'scene_name' => $item->scene_name,
            'difficulty_level' => $item->difficulty_level,
            'time_limit_seconds' => $item->time_limit_seconds,
            'total_differences' => $item->total_differences,
            'cultural_note' => $item->cultural_note,
            'image_a_url' => self::publicUrl($item->image_a_path),
            'image_b_url' => self::publicUrl($item->image_b_path),
            'zones' => $item->zones->map(fn ($zone) => [
                'id' => $zone->id,
                'x_percent' => (float) $zone->x_percent,
                'y_percent' => (float) $zone->y_percent,
                'radius_percent' => (float) $zone->radius_percent,
                'label' => $zone->label,
                'order_index' => (int) $zone->order_index,
            ])->values()->all(),
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
