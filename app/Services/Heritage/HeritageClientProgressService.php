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
}
