<?php

namespace App\Services\Heritage;

use App\Models\Activity;
use App\Models\ChildProfile;
use App\Models\ProgressEvent;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class HeritageClientProgressService
{
    private const CACHE_PREFIX = 'heritage_progress:';

    public function __construct(
        private readonly HeritageTribeMetaRepository $tribeMeta,
    ) {}

    /**
     * @return array{stars: int, done: array<string, bool>, tStars: array<string, int>}
     */
    public function load(User $user, ChildProfile $child): array
    {
        $stored = Cache::get($this->cacheKey($user, $child), []);

        $cached = [
            'stars' => (int) ($stored['stars'] ?? 0),
            'done' => is_array($stored['done'] ?? null) ? $stored['done'] : [],
            'tStars' => is_array($stored['tStars'] ?? null) ? $stored['tStars'] : [],
            'authoritative_synced' => (bool) ($stored['authoritative_synced'] ?? false),
        ];

        if ($this->needsAuthoritativeSync($child, $cached)) {
            $merged = $this->mergeAuthoritativeProgress($child, $cached);
            Cache::forever($this->cacheKey($user, $child), array_merge($merged, [
                'authoritative_synced' => true,
            ]));

            return $merged;
        }

        return [
            'stars' => max($cached['stars'], (int) $child->total_stars),
            'done' => $cached['done'],
            'tStars' => $cached['tStars'],
        ];
    }

    /**
     * @param  array{stars?: int, done?: array<string, bool>, tStars?: array<string, int>}  $payload
     * @return array{stars: int, done: array<string, bool>, tStars: array<string, int>}
     */
    public function save(User $user, ChildProfile $child, array $payload): array
    {
        $progress = [
            'stars' => max(0, (int) ($payload['stars'] ?? 0)),
            'done' => is_array($payload['done'] ?? null) ? $payload['done'] : [],
            'tStars' => is_array($payload['tStars'] ?? null) ? $payload['tStars'] : [],
            'authoritative_synced' => true,
        ];

        Cache::forever($this->cacheKey($user, $child), $progress);

        $child->forceFill(['total_stars' => $progress['stars']])->save();

        return [
            'stars' => $progress['stars'],
            'done' => $progress['done'],
            'tStars' => $progress['tStars'],
        ];
    }

    protected function cacheKey(User $user, ChildProfile $child): string
    {
        return self::CACHE_PREFIX.$user->id.':'.$child->id;
    }

    /**
     * @param  array{stars?: int, done?: array<string, bool>, tStars?: array<string, int>, authoritative_synced?: bool}  $cached
     */
    protected function needsAuthoritativeSync(ChildProfile $child, array $cached): bool
    {
        if ($cached['authoritative_synced'] ?? false) {
            return false;
        }

        $tribeStarTotal = array_sum($cached['tStars'] ?? []);
        $doneCount = count(array_filter($cached['done'] ?? [], static fn ($value): bool => (bool) $value));

        if ($tribeStarTotal > 0 && $doneCount > 0) {
            return false;
        }

        if ((int) $child->total_stars <= 0) {
            return false;
        }

        return $tribeStarTotal === 0 || $doneCount === 0;
    }

    /**
     * @param  array{stars?: int, done?: array<string, bool>, tStars?: array<string, int>}  $cached
     * @return array{stars: int, done: array<string, bool>, tStars: array<string, int>}
     */
    protected function mergeAuthoritativeProgress(ChildProfile $child, array $cached): array
    {
        $done = array_filter($cached['done'] ?? [], static fn ($value): bool => (bool) $value);
        $tStars = is_array($cached['tStars'] ?? null) ? $cached['tStars'] : [];
        $cachedDoneKeys = array_flip(array_keys($done));

        foreach ($this->completedActivityProgress($child) as $completion) {
            $key = $completion['key'];
            $done[$key] = true;

            if (! isset($cachedDoneKeys[$key])) {
                $tribeSlug = explode('_', $key, 2)[0];
                $tStars[$tribeSlug] = ($tStars[$tribeSlug] ?? 0) + $completion['stars'];
            }
        }

        $stars = max(
            (int) ($cached['stars'] ?? 0),
            (int) $child->total_stars,
            array_sum($tStars),
        );

        return [
            'stars' => $stars,
            'done' => $done,
            'tStars' => $tStars,
        ];
    }

    /**
     * @return list<array{key: string, stars: int}>
     */
    protected function completedActivityProgress(ChildProfile $child): array
    {
        $rows = DB::table('progress_events')
            ->join('activities', 'activities.id', '=', 'progress_events.activity_id')
            ->join('tribes', 'tribes.id', '=', 'activities.tribe_id')
            ->where('progress_events.child_profile_id', $child->id)
            ->select([
                'progress_events.stars_earned',
                'activities.id as activity_id',
                'activities.metadata',
                'tribes.name as tribe_name',
            ])
            ->get();

        if ($rows->isEmpty()) {
            return [];
        }

        $entries = [];
        $tribeSlugCache = [];

        foreach ($rows as $row) {
            $tribeName = (string) $row->tribe_name;

            if (! isset($tribeSlugCache[$tribeName])) {
                $meta = $this->tribeMeta->forTribeName($tribeName) ?? [];
                $tribeSlugCache[$tribeName] = (string) ($meta['id'] ?? Str::slug($tribeName));
            }

            $activityMeta = json_decode((string) ($row->metadata ?? ''), true);
            $activityMeta = is_array($activityMeta) ? $activityMeta : [];
            $heritageId = $this->heritageNumericId($activityMeta, (int) $row->activity_id);
            $key = $tribeSlugCache[$tribeName].'_'.$heritageId;
            $stars = max(0, (int) $row->stars_earned);

            $entries[$key] = max($entries[$key] ?? 0, $stars);
        }

        return collect($entries)
            ->map(static fn (int $stars, string $key): array => ['key' => $key, 'stars' => $stars])
            ->values()
            ->all();
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    protected function heritageNumericId(array $meta, int $fallback): int
    {
        $slug = (string) ($meta['seed_slug'] ?? '');

        if (preg_match('/-(\d+)-/', $slug, $matches)) {
            return (int) $matches[1];
        }

        $seedId = (string) ($meta['seed_activity_id'] ?? '');

        if (preg_match('/(\d+)/', $seedId, $matches)) {
            return max(1, (int) $matches[1]);
        }

        return max(1, $fallback);
    }

    /**
     * @param  list<array<string, mixed>>  $tribes
     * @param  array{stars?: int, done?: array<string, bool>, tStars?: array<string, int>}  $progress
     * @return array{
     *     stars: int,
     *     activitiesCompleted: int,
     *     activitiesTotal: int,
     *     tribesStarted: int,
     *     tribesCompleted: int,
     *     tribesTotal: int
     * }
     */
    public function summarize(array $tribes, array $progress): array
    {
        $done = is_array($progress['done'] ?? null) ? $progress['done'] : [];
        $completedKeys = array_keys(array_filter($done));
        $activitiesTotal = 0;
        $tribesStarted = 0;
        $tribesCompleted = 0;

        foreach ($tribes as $tribe) {
            $tribeId = (string) ($tribe['id'] ?? '');
            $tribeActivities = is_array($tribe['activities'] ?? null) ? $tribe['activities'] : [];
            $tribeTotal = count($tribeActivities);
            $activitiesTotal += $tribeTotal;

            $tribeDone = count(array_filter(
                $completedKeys,
                static fn (string $key): bool => str_starts_with($key, $tribeId.'_'),
            ));

            if ($tribeDone > 0) {
                $tribesStarted++;
            }

            if ($tribeTotal > 0 && $tribeDone >= $tribeTotal) {
                $tribesCompleted++;
            }
        }

        $tribeStars = is_array($progress['tStars'] ?? null) ? $progress['tStars'] : [];
        $starsFromTribes = array_sum($tribeStars);

        return [
            'stars' => max(
                (int) ($progress['stars'] ?? 0),
                $starsFromTribes,
            ),
            'activitiesCompleted' => count($completedKeys),
            'activitiesTotal' => $activitiesTotal,
            'tribesStarted' => $tribesStarted,
            'tribesCompleted' => $tribesCompleted,
            'tribesTotal' => count($tribes),
        ];
    }
}
