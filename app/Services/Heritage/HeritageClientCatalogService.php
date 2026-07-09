<?php

namespace App\Services\Heritage;

use App\Models\Activity;
use App\Models\Tribe;
use App\Models\User;
use App\Support\OrganisationActivityScope;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HeritageClientCatalogService
{
    public function __construct(
        private readonly HeritageTribeMetaRepository $tribeMeta,
    ) {}

    /**
     * @return array{tribes: list<array<string, mixed>>, tribeImages: array<string, string>, stats: array<string, int>}
     */
    public function bootstrap(User $user): array
    {
        $tribes = Tribe::query()
            ->with(['clans' => fn ($q) => $q->orderBy('sort_order')])
            ->orderBy('name')
            ->get();

        $activitiesByTribe = $this->publishedActivitiesByTribe($user);

        $clientTribes = [];
        $tribeImages = [];
        $totalActivities = 0;

        foreach ($tribes as $tribe) {
            $meta = $this->tribeMeta->forTribeName($tribe->name) ?? [];
            $slug = (string) ($meta['id'] ?? Str::slug($tribe->name));
            $iconUrl = $tribe->resolvedIcon();
            $color = (string) ($tribe->color ?: ($meta['color'] ?? '#C44B2B'));

            if (is_string($iconUrl) && str_starts_with($iconUrl, 'http')) {
                $tribeImages[$slug] = $iconUrl;
            }

            $activities = $this->mapActivitiesForTribe(
                $activitiesByTribe->get($tribe->id, collect()),
                $slug,
                $color,
            );

            $totalActivities += count($activities);

            $clientTribes[] = [
                'id' => $slug,
                'dbId' => $tribe->id,
                'name' => $tribe->name,
                'hero' => (string) ($tribe->hero_name ?: ($meta['hero'] ?? 'Heritage Hero')),
                'heroTitle' => (string) ($meta['heroTitle'] ?? 'Heritage Hero'),
                'greeting' => (string) ($tribe->greeting ?: ($meta['greeting'] ?? '')),
                'phonetic' => (string) ($meta['greetingPhonetic'] ?? ''),
                'meaning' => (string) ($meta['greetingMeaning'] ?? ''),
                'language' => (string) ($meta['language'] ?? ''),
                'region' => (string) ($tribe->region ?: ($meta['region'] ?? '')),
                'animal' => (string) ($meta['sacredAnimal'] ?? ''),
                'symbol' => $tribe->hero_emoji ?: '🌍',
                'color' => $color,
                'accent' => $this->accentColor($color),
                'clans' => $tribe->clans->pluck('name')->values()->all()
                    ?: (array) ($meta['clans'] ?? []),
                'proverbs' => (array) ($meta['proverbs'] ?? []),
                'words' => $this->defaultWords($meta),
                'clanInfo' => $this->defaultClanInfo($tribe, $meta),
                'activities' => $activities,
            ];
        }

        return [
            'tribes' => $clientTribes,
            'tribeImages' => $tribeImages,
            'stats' => [
                'tribes' => count($clientTribes),
                'activities' => $totalActivities,
                'categories' => 6,
            ],
        ];
    }

    /**
     * @return Collection<int, Collection<int, Activity>>
     */
    protected function publishedActivitiesByTribe(User $user): Collection
    {
        $query = Activity::query()
            ->where('is_published', true)
            ->whereNotIn('type', ['flashcard', 'song'])
            ->orderBy('id');

        $activities = OrganisationActivityScope::filterApproved(
            $query->get(),
            $user,
        );

        return $activities->groupBy('tribe_id');
    }

    /**
     * @return list<array<string, mixed>>
     */
    protected function mapActivitiesForTribe(Collection $activities, string $tribeSlug, string $color): array
    {
        $index = 0;

        return $activities->map(function (Activity $activity) use ($tribeSlug, $color, &$index) {
            $meta = is_array($activity->metadata) ? $activity->metadata : [];
            $index++;
            $heritageId = $this->heritageNumericId($meta, $index);
            $category = $this->mapCategory($meta, $activity->type);

            return [
                'id' => $heritageId,
                'activityId' => $activity->id,
                'cat' => $category,
                'tag' => (string) ($meta['tag'] ?? $activity->title),
                'age' => (string) ($activity->age_range ?? '2-10'),
                'diff' => $this->mapDifficulty($meta),
                'title' => $activity->title,
                'desc' => (string) ($activity->description ?: ($meta['learning_objective'] ?? '')),
                'icon' => $this->iconFor($category, $meta),
                'pts' => (int) ($activity->star_points ?? 10),
                'audioUrl' => $this->assetUrl(data_get($meta, 'seed_assets.audio')),
            ];
        })->values()->all();
    }

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

        return $fallback;
    }

    protected function mapCategory(array $meta, string $type): string
    {
        $seedCategory = strtolower((string) ($meta['seed_category'] ?? ''));
        $tag = strtolower((string) ($meta['tag'] ?? ''));

        return match (true) {
            $seedCategory === 'language' => 'language',
            in_array($seedCategory, ['puzzles', 'maze', 'spot difference', 'word search'], true) => 'puzzle',
            in_array($seedCategory, ['drawing', 'colouring'], true) => 'arts',
            $seedCategory === 'song' => 'music',
            $seedCategory === 'culture' => 'clan',
            $seedCategory === 'story' => 'mission',
            $seedCategory === 'game' => 'mission',
            in_array($type, ['puzzle', 'maze', 'spot_difference', 'word_search'], true) => 'puzzle',
            in_array($type, ['drawing_kit', 'colouring'], true) => 'arts',
            $type === 'culture' => 'clan',
            $type === 'game' => 'mission',
            str_contains($tag, 'mission') || str_contains($tag, 'graduation') => 'mission',
            default => 'language',
        };
    }

    protected function mapDifficulty(array $meta): int
    {
        if (isset($meta['difficulty_level']) && is_numeric($meta['difficulty_level'])) {
            return max(1, min(5, (int) $meta['difficulty_level']));
        }

        return match (strtolower((string) ($meta['difficulty'] ?? 'easy'))) {
            'medium' => 3,
            'hard' => 5,
            default => 1,
        };
    }

    protected function iconFor(string $category, array $meta): string
    {
        $tag = strtolower((string) ($meta['tag'] ?? ''));

        return match (true) {
            str_contains($tag, 'maze') => '🌀',
            str_contains($tag, 'jigsaw') => '🧩',
            str_contains($tag, 'song') || str_contains($tag, 'karaoke') => '🎵',
            str_contains($tag, 'drum') => '🥁',
            str_contains($tag, 'colour') || str_contains($tag, 'color') => '🎨',
            str_contains($tag, 'clan') => '🌳',
            str_contains($tag, 'mission') => '🏆',
            $category === 'music' => '🎵',
            $category === 'arts' => '🎨',
            $category === 'clan' => '🌳',
            $category === 'mission' => '🏆',
            $category === 'puzzle' => '🧩',
            default => '✏️',
        };
    }

    protected function assetUrl(?string $path): ?string
    {
        if (! is_string($path) || $path === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }

    protected function accentColor(string $color): string
    {
        return $color.'CC';
    }

    /**
     * @param  array<string, mixed>  $meta
     * @return list<string>
     */
    protected function defaultWords(array $meta): array
    {
        $greeting = (string) ($meta['greeting'] ?? 'Hello');

        return [
            $greeting,
            (string) ($meta['hero'] ?? 'Hero'),
            (string) ($meta['sacredAnimal'] ?? 'Animal'),
            'Family',
            'River',
            'Sun',
            'Star',
            'Drum',
            'Dance',
            'Home',
        ];
    }

    /**
     * @param  array<string, mixed>  $meta
     * @return list<string>
     */
    protected function defaultClanInfo(Tribe $tribe, array $meta): array
    {
        $clans = $tribe->clans->pluck('name')->values()->all()
            ?: (array) ($meta['clans'] ?? []);

        return array_map(
            fn (string $clan) => "{$clan} clan members share stories, duties, and traditions passed down through generations.",
            array_slice($clans, 0, 5),
        );
    }
}
