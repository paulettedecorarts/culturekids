<?php

namespace App\Support;

use App\Models\Activity;
use App\Models\ActivityFlashcardSlide;
use App\Models\CultureActivity;
use App\Models\Drawing;
use App\Models\Game;
use App\Models\Maze;
use App\Models\SpotDifference;
use Illuminate\Support\Collection;

final class ActivityListCoverResolver
{
    /**
     * @param  Collection<int, Activity>  $activities
     * @return array<int, string|null> activity id => public cover URL
     */
    public static function resolve(Collection $activities): array
    {
        if ($activities->isEmpty()) {
            return [];
        }

        $covers = [];
        $grouped = $activities->groupBy('type');

        self::resolveCulture($grouped->get('culture', collect()), $covers);
        self::resolveMaze($grouped->get('maze', collect()), $covers);
        self::resolveSpotDifference($grouped->get('spot_difference', collect()), $covers);
        self::resolveGame($grouped->get('game', collect()), $covers);
        self::resolveDrawing($grouped->get('drawing_kit', collect()), $covers);
        self::resolvePuzzle($grouped->get('puzzle', collect()), $covers);
        self::resolveFlashcard($grouped->get('flashcard', collect()), $covers);

        return $covers;
    }

    /**
     * @param  Collection<int, Activity>  $activities
     * @param  array<int, string|null>  $covers
     */
    private static function resolveCulture(Collection $activities, array &$covers): void
    {
        $legacyByActivity = self::legacyIds($activities, 'legacy_culture_activity_id');
        if ($legacyByActivity === []) {
            return;
        }

        $paths = CultureActivity::query()
            ->whereIn('id', array_values($legacyByActivity))
            ->pluck('cover_image_path', 'id');

        foreach ($legacyByActivity as $activityId => $legacyId) {
            $covers[$activityId] = self::publicUrl($paths[$legacyId] ?? null);
        }
    }

    /**
     * @param  Collection<int, Activity>  $activities
     * @param  array<int, string|null>  $covers
     */
    private static function resolveMaze(Collection $activities, array &$covers): void
    {
        $legacyByActivity = self::legacyIds($activities, 'legacy_maze_id');
        if ($legacyByActivity === []) {
            return;
        }

        $rows = Maze::query()
            ->whereIn('id', array_values($legacyByActivity))
            ->get(['id', 'cover_image_path', 'background_image_path']);

        $paths = $rows->mapWithKeys(fn (Maze $maze) => [
            $maze->id => $maze->cover_image_path ?: $maze->background_image_path,
        ]);

        foreach ($legacyByActivity as $activityId => $legacyId) {
            $covers[$activityId] = self::publicUrl($paths[$legacyId] ?? null);
        }
    }

    /**
     * @param  Collection<int, Activity>  $activities
     * @param  array<int, string|null>  $covers
     */
    private static function resolveSpotDifference(Collection $activities, array &$covers): void
    {
        $legacyByActivity = self::legacyIds($activities, 'legacy_spot_difference_id');
        if ($legacyByActivity === []) {
            return;
        }

        $paths = SpotDifference::query()
            ->whereIn('id', array_values($legacyByActivity))
            ->pluck('image_a_path', 'id');

        foreach ($legacyByActivity as $activityId => $legacyId) {
            $covers[$activityId] = self::publicUrl($paths[$legacyId] ?? null);
        }
    }

    /**
     * @param  Collection<int, Activity>  $activities
     * @param  array<int, string|null>  $covers
     */
    private static function resolveGame(Collection $activities, array &$covers): void
    {
        $legacyByActivity = self::legacyIds($activities, 'legacy_game_id');
        if ($legacyByActivity === []) {
            return;
        }

        $paths = Game::query()
            ->whereIn('id', array_values($legacyByActivity))
            ->pluck('cover_image_path', 'id');

        foreach ($legacyByActivity as $activityId => $legacyId) {
            $covers[$activityId] = self::publicUrl($paths[$legacyId] ?? null);
        }
    }

    /**
     * @param  Collection<int, Activity>  $activities
     * @param  array<int, string|null>  $covers
     */
    private static function resolveDrawing(Collection $activities, array &$covers): void
    {
        $legacyByActivity = self::legacyIds($activities, 'legacy_drawing_id');
        if ($legacyByActivity === []) {
            return;
        }

        $rows = Drawing::query()
            ->whereIn('id', array_values($legacyByActivity))
            ->get(['id', 'preview_path', 'template_path']);

        $paths = $rows->mapWithKeys(fn (Drawing $drawing) => [
            $drawing->id => $drawing->preview_path ?: $drawing->template_path,
        ]);

        foreach ($legacyByActivity as $activityId => $legacyId) {
            $covers[$activityId] = self::publicUrl($paths[$legacyId] ?? null);
        }
    }

    /**
     * @param  Collection<int, Activity>  $activities
     * @param  array<int, string|null>  $covers
     */
    private static function resolvePuzzle(Collection $activities, array &$covers): void
    {
        foreach ($activities as $activity) {
            $attrs = $activity->getAttributes();
            $path = self::stringOrNull($attrs['_puzzle_source_image'] ?? null);
            if ($path !== null) {
                $covers[$activity->id] = self::publicUrl($path);
            }
        }
    }

    /**
     * @param  Collection<int, Activity>  $activities
     * @param  array<int, string|null>  $covers
     */
    private static function resolveFlashcard(Collection $activities, array &$covers): void
    {
        if ($activities->isEmpty()) {
            return;
        }

        $slides = ActivityFlashcardSlide::query()
            ->whereIn('activity_id', $activities->pluck('id'))
            ->orderBy('order_index')
            ->get(['activity_id', 'image_path', 'order_index']);

        $firstByActivity = $slides
            ->groupBy('activity_id')
            ->map(fn (Collection $group) => $group->first());

        foreach ($firstByActivity as $activityId => $slide) {
            $covers[(int) $activityId] = self::publicUrl($slide->image_path);
        }
    }

    /**
     * @param  Collection<int, Activity>  $activities
     * @return array<int, int> activity id => legacy model id
     */
    private static function legacyIds(Collection $activities, string $key): array
    {
        $map = [];

        foreach ($activities as $activity) {
            $legacyId = (int) data_get(ActivityBundleMetadataExtract::toMetadataArray($activity), $key, 0);
            if ($legacyId > 0) {
                $map[$activity->id] = $legacyId;
            }
        }

        return $map;
    }

    private static function stringOrNull(mixed $value): ?string
    {
        if (! is_string($value) || $value === '' || $value === 'null') {
            return null;
        }

        return $value;
    }

    private static function publicUrl(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset('storage/'.ltrim($path, '/'));
    }
}
