<?php

namespace App\Services\Heritage;

use App\Models\ChildProfile;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class HeritageClientProgressService
{
    private const CACHE_PREFIX = 'heritage_progress:';

    /**
     * @return array{stars: int, done: array<string, bool>, tStars: array<string, int>}
     */
    public function load(User $user, ChildProfile $child): array
    {
        $stored = Cache::get($this->cacheKey($user, $child), []);

        return [
            'stars' => (int) ($stored['stars'] ?? $child->total_stars ?? 0),
            'done' => is_array($stored['done'] ?? null) ? $stored['done'] : [],
            'tStars' => is_array($stored['tStars'] ?? null) ? $stored['tStars'] : [],
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
        ];

        Cache::forever($this->cacheKey($user, $child), $progress);

        $child->forceFill(['total_stars' => $progress['stars']])->save();

        return $progress;
    }

    protected function cacheKey(User $user, ChildProfile $child): string
    {
        return self::CACHE_PREFIX.$user->id.':'.$child->id;
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

        return [
            'stars' => max(0, (int) ($progress['stars'] ?? 0)),
            'activitiesCompleted' => count($completedKeys),
            'activitiesTotal' => $activitiesTotal,
            'tribesStarted' => $tribesStarted,
            'tribesCompleted' => $tribesCompleted,
            'tribesTotal' => count($tribes),
        ];
    }
}
