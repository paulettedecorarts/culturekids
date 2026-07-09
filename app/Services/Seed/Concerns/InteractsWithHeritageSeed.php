<?php

namespace App\Services\Seed\Concerns;

use App\Services\Seed\HeritageSeedAssetPublisher;
use Illuminate\Support\Str;

trait InteractsWithHeritageSeed
{
    /**
     * Standard activity asset keys in seed JSON → model column names.
     *
     * @var array<string, string>
     */
    protected array $heritageActivityAssetMap = [
        'coverImage' => 'cover_image_path',
        'audio' => 'audio_path',
        'video' => 'video_path',
        'template' => 'template_path',
        'preview' => 'preview_path',
        'backgroundImage' => 'background_image_path',
        'imageA' => 'image_a_path',
        'imageB' => 'image_b_path',
        'mapImage' => 'map_image_path',
    ];

    public const SEED_SOURCE_ACTIVITIES = 'heritage_activities_seed';

    public const SEED_SOURCE_WORD_FLASHCARDS = 'word_flashcards_seed';

    /**
     * @param  array<string, mixed>  $item
     * @return array{0: ?int, 1: ?int}
     */
    protected function parseAgeGroup(array $item, string $default = '2-10'): array
    {
        $raw = (string) ($item['ageGroup'] ?? $default);

        if (preg_match('/(\d+)\s*-\s*(\d+)/', $raw, $matches)) {
            return [(int) $matches[1], (int) $matches[2]];
        }

        if (preg_match('/(\d+)/', $raw, $matches)) {
            $age = (int) $matches[1];

            return [$age, $age];
        }

        return [2, 10];
    }

    protected function parseDifficulty(?string $difficulty): string
    {
        return match (strtolower((string) $difficulty)) {
            'beginner', 'easy' => 'easy',
            'intermediate', 'medium' => 'medium',
            'advanced', 'hard', 'expert', 'master' => 'hard',
            default => 'easy',
        };
    }

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    protected function heritageActivityMetadata(array $item, ?string $legacyKey = null, mixed $legacyId = null): array
    {
        $meta = [
            'seed_source' => self::SEED_SOURCE_ACTIVITIES,
            'seed_activity_id' => $item['id'] ?? null,
            'seed_slug' => $item['slug'] ?? null,
            'seed_category' => $item['category'] ?? null,
            'seed_activity_type' => $item['activityType'] ?? null,
            'tag' => $item['tag'] ?? null,
            'difficulty' => $this->parseDifficulty($item['difficulty'] ?? null),
            'difficulty_level' => $item['difficultyLevel'] ?? null,
            'learning_objective' => $item['learningObjective'] ?? null,
            'cultural_note' => $item['culturalNote'] ?? null,
            'materials_needed' => $item['materialsNeeded'] ?? null,
            'instructions' => $item['instructions'] ?? null,
            'content' => $item['content'] ?? null,
            'answer' => $item['answer'] ?? null,
            'illustration_prompt' => $item['illustrationPrompt'] ?? null,
            'tags' => $item['tags'] ?? [],
            'source' => $item['source'] ?? null,
            'hero' => $item['hero'] ?? null,
            'hero_title' => $item['heroTitle'] ?? null,
            'greeting' => $item['greeting'] ?? null,
            'greeting_meaning' => $item['greetingMeaning'] ?? null,
            'region' => $item['region'] ?? null,
            'sacred_animal' => $item['sacredAnimal'] ?? null,
            'language' => $item['language'] ?? null,
        ];

        if ($legacyKey !== null && $legacyId !== null) {
            $meta[$legacyKey] = $legacyId;
        }

        return array_filter($meta, fn ($value) => $value !== null && $value !== []);
    }

    /**
     * @param  array<string, mixed>  $item
     * @param  array<string, mixed>  $payload
     * @param  array<string, string>|null  $fieldMap
     * @return array<string, string> published asset key => storage path
     */
    protected function applyHeritageSeedAssets(array $item, array &$payload, ?array $fieldMap = null): array
    {
        $assets = $item['assets'] ?? null;

        if (! is_array($assets) || $assets === []) {
            return [];
        }

        $fieldMap ??= $this->heritageActivityAssetMap;
        $publisher = app(HeritageSeedAssetPublisher::class);
        $published = [];

        foreach ($assets as $assetKey => $relativePath) {
            if (! is_string($relativePath) || $relativePath === '') {
                continue;
            }

            $storagePath = $publisher->publish($relativePath);

            if ($storagePath === null) {
                continue;
            }

            $published[$assetKey] = $storagePath;

            $modelField = $fieldMap[$assetKey] ?? null;

            if (is_string($modelField) && $modelField !== '') {
                $payload[$modelField] = $storagePath;
            }
        }

        if ($published !== []) {
            $metadata = is_array($payload['metadata'] ?? null) ? $payload['metadata'] : [];
            $metadata['seed_assets'] = array_merge($metadata['seed_assets'] ?? [], $published);
            $payload['metadata'] = $metadata;
        }

        return $published;
    }

    protected function languageCodeFromItem(array $item): string
    {
        if (! empty($item['tribeId'])) {
            return (string) $item['tribeId'];
        }

        return Str::slug((string) ($item['language'] ?? 'en'), '-');
    }
}
