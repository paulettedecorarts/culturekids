<?php

namespace App\Support;

use App\Models\Activity;
use Illuminate\Support\Collection;

final class ActivityApiListSerializer
{
    /**
     * @param  Collection<int, Activity>  $activities
     * @return Collection<int, array<string, mixed>>
     */
    public static function mapCollection(Collection $activities): Collection
    {
        $covers = ActivityListCoverResolver::resolve($activities);

        return $activities->map(
            fn (Activity $activity) => self::mapOne($activity, $covers[$activity->id] ?? null)
        );
    }

    /**
     * @return array<string, mixed>
     */
    public static function mapOne(Activity $activity, ?string $coverImage = null): array
    {
        return [
            'id' => $activity->id,
            'title' => $activity->title,
            'type' => ActivityDrawingTypeFilter::listTypeForActivity($activity),
            'age_range' => $activity->age_range,
            'stars' => $activity->star_points ?? 10,
            'description' => $activity->description,
            'cover_image' => $coverImage,
            'tribe' => $activity->tribe ? [
                'id' => $activity->tribe->id,
                'name' => $activity->tribe->name,
                'icon' => $activity->tribe->hero_emoji ?? $activity->tribe->hero_icon,
                'color' => $activity->tribe->color,
            ] : null,
        ];
    }
}
