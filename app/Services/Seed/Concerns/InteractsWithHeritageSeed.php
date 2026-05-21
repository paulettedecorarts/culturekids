<?php

namespace App\Services\Seed\Concerns;

use Illuminate\Support\Str;

trait InteractsWithHeritageSeed
{
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
            'language' => $item['language'] ?? null,
        ];

        if ($legacyKey !== null && $legacyId !== null) {
            $meta[$legacyKey] = $legacyId;
        }

        return array_filter($meta, fn ($value) => $value !== null && $value !== []);
    }

    protected function languageCodeFromItem(array $item): string
    {
        if (! empty($item['tribeId'])) {
            return (string) $item['tribeId'];
        }

        return Str::slug((string) ($item['language'] ?? 'en'), '-');
    }
}
