<?php

namespace App\Services\Seed;

use App\Models\Clan;
use App\Models\Tribe;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class HeritageTribeUpserter
{
    public const ACTIVITIES_JSON = 'seed/activities.seed.json';

    /**
     * @return array<string, int> tribe name => tribe id
     */
    public function upsertFromActivitiesJson(): array
    {
        $path = base_path(self::ACTIVITIES_JSON);
        $payload = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);
        $definitions = $payload['tribes'] ?? [];

        $map = [];

        foreach ($definitions as $tribeMeta) {
            $name = (string) ($tribeMeta['name'] ?? '');
            if ($name === '') {
                continue;
            }

            $tribe = Tribe::query()->updateOrCreate(
                ['name' => $name],
                [
                    'hero_name' => (string) ($tribeMeta['hero'] ?? 'Heritage Hero'),
                    'hero_emoji' => $tribeMeta['emoji'] ?? null,
                    'hero_icon' => null,
                    'greeting' => $tribeMeta['greeting'] ?? null,
                    'region' => $tribeMeta['region'] ?? null,
                    'color' => null,
                ]
            );

            $this->syncClans($tribe, $tribeMeta);
            $map[$name] = $tribe->id;
        }

        return $map;
    }

    /**
     * @param  array<string, mixed>  $tribeMeta
     */
    protected function syncClans(Tribe $tribe, array $tribeMeta): void
    {
        $clans = $tribeMeta['clans'] ?? [];
        if (! is_array($clans)) {
            return;
        }

        foreach (array_values($clans) as $index => $clanName) {
            if (! is_string($clanName) || $clanName === '') {
                continue;
            }

            Clan::query()->updateOrCreate(
                [
                    'tribe_id' => $tribe->id,
                    'name' => $clanName,
                ],
                [
                    'sort_order' => $index + 1,
                    'is_active' => true,
                    'metadata' => [
                        'seed_source' => 'heritage_activities_seed',
                    ],
                ]
            );
        }
    }

    public function resolveTribeId(array $tribeMap, array $item): ?int
    {
        $name = (string) ($item['tribe'] ?? '');

        if ($name !== '' && isset($tribeMap[$name])) {
            return $tribeMap[$name];
        }

        return null;
    }
}
