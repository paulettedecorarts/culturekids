<?php

namespace App\Services\Seed;

use App\Models\Activity;
use App\Models\Comic;
use App\Models\CultureActivity;
use App\Models\Tribe;
use App\Support\CultureQuizGenerator;
use App\Models\Drawing;
use App\Models\Game;
use App\Models\LanguageActivity;
use App\Models\Maze;
use App\Models\Song;
use App\Models\SpotDifference;
use App\Models\WordSearch;
use App\Services\Seed\Concerns\InteractsWithHeritageSeed;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Imports 1,210 Heritage Heroes activities from seed/activities.seed.json
 * into the correct domain models (each mirrors to activities via model events).
 */
class HeritageActivitiesSeedImporter
{
    use InteractsWithHeritageSeed;

    public const JSON_PATH = 'seed/activities.seed.json';

    /**
     * @param  array<string, int>  $tribeMap
     * @return array<string, int|string>
     */
    public function import(array $tribeMap, ?Command $command = null): array
    {
        $path = base_path(self::JSON_PATH);
        $payload = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);
        $items = $payload['activities'] ?? [];

        $stats = [
            'total' => count($items),
            'language' => 0,
            'puzzle' => 0,
            'story' => 0,
            'culture' => 0,
            'song' => 0,
            'maze' => 0,
            'spot_difference' => 0,
            'word_search' => 0,
            'drawing' => 0,
            'game' => 0,
            'skipped' => 0,
        ];

        foreach ($items as $item) {
            $tribeId = app(HeritageTribeUpserter::class)->resolveTribeId($tribeMap, $item);
            if (! $tribeId) {
                $stats['skipped']++;

                continue;
            }

            $category = (string) ($item['category'] ?? '');

            try {
                match ($category) {
                    'Language' => $this->importLanguage($item, $tribeId),
                    'Puzzles' => $this->importPuzzle($item, $tribeId),
                    'Story' => $this->importStory($item, $tribeId),
                    'Culture' => $this->importCulture($item, $tribeId),
                    'Song' => $this->importSong($item, $tribeId),
                    'Maze' => $this->importMaze($item, $tribeId),
                    'Spot the Difference' => $this->importSpotDifference($item, $tribeId),
                    'Word Search' => $this->importWordSearch($item, $tribeId),
                    'Colouring', 'Drawing' => $this->importDrawing($item, $tribeId),
                    'Game' => $this->importGame($item, $tribeId),
                    default => throw new \InvalidArgumentException("Unknown category: {$category}"),
                };

                $key = match ($category) {
                    'Language' => 'language',
                    'Puzzles' => 'puzzle',
                    'Story' => 'story',
                    'Culture' => 'culture',
                    'Song' => 'song',
                    'Maze' => 'maze',
                    'Spot the Difference' => 'spot_difference',
                    'Word Search' => 'word_search',
                    'Colouring', 'Drawing' => 'drawing',
                    'Game' => 'game',
                    default => 'skipped',
                };

                $stats[$key]++;
            } catch (\Throwable $e) {
                $stats['skipped']++;
                $command?->warn(sprintf(
                    'Skipped %s (%s): %s',
                    $item['id'] ?? '?',
                    $category,
                    $e->getMessage()
                ));
            }
        }

        return $stats;
    }

    /**
     * @param  array<string, mixed>  $item
     */
    protected function importLanguage(array $item, int $tribeId): LanguageActivity
    {
        [$ageMin, $ageMax] = $this->parseAgeGroup($item);
        $seedId = (string) $item['id'];

        $existing = LanguageActivity::query()
            ->where('metadata->seed_activity_id', $seedId)
            ->first();

        $payload = [
            'tribe_id' => $tribeId,
            'language_code' => $this->languageCodeFromItem($item),
            'title' => (string) $item['title'],
            'description' => (string) ($item['instructions'] ?? $item['content'] ?? ''),
            'activity_type' => $this->mapLanguageActivityType((string) ($item['activityType'] ?? '')),
            'difficulty_level' => $this->parseDifficulty($item['difficulty'] ?? null),
            'age_min' => $ageMin,
            'age_max' => $ageMax,
            'star_points' => (int) ($item['points'] ?? 5),
            'status' => 'published',
            'full_sentence' => (string) ($item['content'] ?? ''),
            'sentence_translation' => null,
            'cultural_note' => $item['culturalNote'] ?? null,
            'metadata' => $this->heritageActivityMetadata($item),
        ];

        if ($existing) {
            $existing->update($payload);

            return $existing->fresh();
        }

        return LanguageActivity::query()->create($payload);
    }

    /**
     * @param  array<string, mixed>  $item
     */
    protected function importPuzzle(array $item, int $tribeId): Activity
    {
        $seedId = (string) $item['id'];

        $payload = [
            'tribe_id' => $tribeId,
            'type' => 'puzzle',
            'title' => (string) $item['title'],
            'description' => (string) ($item['instructions'] ?? $item['content'] ?? ''),
            'age_range' => (string) ($item['ageGroup'] ?? '2-10'),
            'star_points' => (int) ($item['points'] ?? 10),
            'is_published' => true,
            'metadata' => array_merge($this->heritageActivityMetadata($item), [
                'puzzle' => [
                    'difficulty' => $this->parseDifficulty($item['difficulty'] ?? null),
                    'pieces' => 12,
                ],
            ]),
        ];

        $existing = Activity::query()
            ->where('type', 'puzzle')
            ->where('metadata->seed_activity_id', $seedId)
            ->first();

        if ($existing) {
            $existing->update($payload);

            return $existing->fresh();
        }

        return Activity::query()->create($payload);
    }

    /**
     * @param  array<string, mixed>  $item
     */
    protected function importStory(array $item, int $tribeId): Comic
    {
        [$ageMin, $ageMax] = $this->parseAgeGroup($item);
        $seedId = (string) $item['id'];

        $existing = Comic::query()
            ->where('metadata->seed_activity_id', $seedId)
            ->first();

        $payload = [
            'tribe_id' => $tribeId,
            'title' => (string) $item['title'],
            'description' => (string) ($item['instructions'] ?? $item['content'] ?? ''),
            'age_min' => $ageMin,
            'age_max' => $ageMax,
            'status' => 'published',
            'star_points' => (int) ($item['points'] ?? 10),
            'metadata' => $this->heritageActivityMetadata($item),
        ];

        if ($existing) {
            $existing->update($payload);

            return $existing->fresh();
        }

        return Comic::query()->create($payload);
    }

    /**
     * @param  array<string, mixed>  $item
     */
    protected function importCulture(array $item, int $tribeId): CultureActivity
    {
        [$ageMin, $ageMax] = $this->parseAgeGroup($item);
        $seedId = (string) $item['id'];

        $existing = CultureActivity::query()
            ->where('metadata->seed_activity_id', $seedId)
            ->first();

        $tribe = Tribe::query()->find($tribeId);
        $cultureType = $this->mapCultureType((string) ($item['activityType'] ?? ''), (string) ($item['tag'] ?? ''));
        $quizQuestions = $this->isHeritageQuizCulture($item)
            ? CultureQuizGenerator::fromHeritageItem($item, $tribe)
            : null;

        $payload = [
            'tribe_id' => $tribeId,
            'title' => (string) $item['title'],
            'description' => (string) ($item['instructions'] ?? $item['content'] ?? ''),
            'culture_type' => $cultureType,
            'difficulty_level' => $this->parseDifficulty($item['difficulty'] ?? null),
            'age_min' => $ageMin,
            'age_max' => $ageMax,
            'star_points' => (int) ($item['points'] ?? 5),
            'status' => 'published',
            'content' => (string) ($item['content'] ?? ''),
            'cultural_note' => $item['culturalNote'] ?? null,
            'quiz_questions' => $quizQuestions ?: null,
            'metadata' => $this->heritageActivityMetadata($item),
        ];

        if ($existing) {
            $existing->update($payload);

            return $existing->fresh();
        }

        return CultureActivity::query()->create($payload);
    }

    /**
     * @param  array<string, mixed>  $item
     */
    protected function importSong(array $item, int $tribeId): Song
    {
        [$ageMin, $ageMax] = $this->parseAgeGroup($item);
        $seedId = (string) $item['id'];
        $activityType = (string) ($item['activityType'] ?? '');

        $existing = Song::query()
            ->where('metadata->seed_activity_id', $seedId)
            ->first();

        $payload = [
            'tribe_id' => $tribeId,
            'title' => (string) $item['title'],
            'description' => (string) ($item['instructions'] ?? $item['content'] ?? ''),
            'language' => (string) ($item['language'] ?? ''),
            'song_type' => 'traditional_song',
            'lyrics' => (string) ($item['content'] ?? $item['instructions'] ?? ''),
            'age_min' => $ageMin,
            'age_max' => $ageMax,
            'star_points' => (int) ($item['points'] ?? 8),
            'status' => 'published',
            'activity_type' => Str::slug($activityType, '_'),
            'difficulty_level' => $this->parseDifficulty($item['difficulty'] ?? null),
            'has_karaoke_timing' => str_contains(strtolower($activityType), 'karaoke'),
            'has_fill_blanks' => str_contains(strtolower($activityType), 'fill'),
            'metadata' => $this->heritageActivityMetadata($item),
        ];

        if ($existing) {
            $existing->update($payload);

            return $existing->fresh();
        }

        return Song::query()->create($payload);
    }

    /**
     * @param  array<string, mixed>  $item
     */
    protected function importMaze(array $item, int $tribeId): Maze
    {
        [$ageMin, $ageMax] = $this->parseAgeGroup($item);
        $seedId = (string) $item['id'];

        $existing = Maze::query()
            ->where('metadata->seed_activity_id', $seedId)
            ->first();

        $payload = [
            'tribe_id' => $tribeId,
            'title' => (string) $item['title'],
            'description' => (string) ($item['instructions'] ?? $item['content'] ?? ''),
            'maze_type' => 'standard',
            'difficulty_level' => $this->parseDifficulty($item['difficulty'] ?? null),
            'age_min' => $ageMin,
            'age_max' => $ageMax,
            'star_points' => (int) ($item['points'] ?? 5),
            'status' => 'published',
            'cultural_note' => $item['culturalNote'] ?? null,
            'hero_character' => $item['hero'] ?? null,
            'metadata' => $this->heritageActivityMetadata($item),
        ];

        if ($existing) {
            $existing->update($payload);

            return $existing->fresh();
        }

        return Maze::query()->create($payload);
    }

    /**
     * @param  array<string, mixed>  $item
     */
    protected function importSpotDifference(array $item, int $tribeId): SpotDifference
    {
        [$ageMin, $ageMax] = $this->parseAgeGroup($item);
        $seedId = (string) $item['id'];

        $existing = SpotDifference::query()
            ->where('metadata->seed_activity_id', $seedId)
            ->first();

        $payload = [
            'tribe_id' => $tribeId,
            'title' => (string) $item['title'],
            'description' => (string) ($item['instructions'] ?? $item['content'] ?? ''),
            'difficulty_level' => $this->parseDifficulty($item['difficulty'] ?? null),
            'age_min' => $ageMin,
            'age_max' => $ageMax,
            'star_points' => (int) ($item['points'] ?? 5),
            'status' => 'published',
            'total_differences' => 0,
            'cultural_note' => $item['culturalNote'] ?? null,
            'scene_name' => $item['tag'] ?? null,
            'metadata' => $this->heritageActivityMetadata($item),
        ];

        if ($existing) {
            $existing->update($payload);

            return $existing->fresh();
        }

        return SpotDifference::query()->create($payload);
    }

    /**
     * @param  array<string, mixed>  $item
     */
    protected function importWordSearch(array $item, int $tribeId): WordSearch
    {
        [$ageMin, $ageMax] = $this->parseAgeGroup($item);
        $seedId = (string) $item['id'];

        $existing = WordSearch::query()
            ->where('metadata->seed_activity_id', $seedId)
            ->first();

        $payload = [
            'tribe_id' => $tribeId,
            'title' => (string) $item['title'],
            'description' => (string) ($item['instructions'] ?? $item['content'] ?? ''),
            'difficulty_level' => $this->parseDifficulty($item['difficulty'] ?? null),
            'age_min' => $ageMin,
            'age_max' => $ageMax,
            'star_points' => (int) ($item['points'] ?? 5),
            'status' => 'published',
            'grid_size' => 10,
            'words' => [],
            'cultural_note' => $item['culturalNote'] ?? null,
            'language_code' => $this->languageCodeFromItem($item),
            'metadata' => $this->heritageActivityMetadata($item),
        ];

        if ($existing) {
            $existing->update($payload);

            return $existing->fresh();
        }

        return WordSearch::query()->create($payload);
    }

    /**
     * @param  array<string, mixed>  $item
     */
    protected function importDrawing(array $item, int $tribeId): Drawing
    {
        [$ageMin, $ageMax] = $this->parseAgeGroup($item);
        $seedId = (string) $item['id'];

        $existing = Drawing::query()
            ->where('metadata->seed_activity_id', $seedId)
            ->first();

        $payload = [
            'tribe_id' => $tribeId,
            'title' => (string) $item['title'],
            'description' => (string) ($item['instructions'] ?? $item['content'] ?? ''),
            'drawing_type' => $this->mapDrawingType((string) ($item['category'] ?? ''), (string) ($item['activityType'] ?? '')),
            'difficulty_level' => $this->parseDifficulty($item['difficulty'] ?? null),
            'age_min' => $ageMin,
            'age_max' => $ageMax,
            'star_points' => (int) ($item['points'] ?? 5),
            'status' => 'published',
            'materials' => $item['materialsNeeded'] ?? null,
            'metadata' => $this->heritageActivityMetadata($item),
        ];

        if ($existing) {
            $existing->update($payload);

            return $existing->fresh();
        }

        return Drawing::query()->create($payload);
    }

    /**
     * @param  array<string, mixed>  $item
     */
    protected function importGame(array $item, int $tribeId): Game
    {
        [$ageMin, $ageMax] = $this->parseAgeGroup($item);
        $seedId = (string) $item['id'];

        $existing = Game::query()
            ->where('metadata->seed_activity_id', $seedId)
            ->first();

        $payload = [
            'tribe_id' => $tribeId,
            'title' => (string) $item['title'],
            'description' => (string) ($item['instructions'] ?? $item['content'] ?? ''),
            'game_type' => $this->mapGameType((string) ($item['activityType'] ?? '')),
            'difficulty_level' => $this->parseDifficulty($item['difficulty'] ?? null),
            'age_min' => $ageMin,
            'age_max' => $ageMax,
            'star_points' => (int) ($item['points'] ?? 5),
            'status' => 'published',
            'cultural_note' => $item['culturalNote'] ?? null,
            'language_code' => $this->languageCodeFromItem($item),
            'metadata' => $this->heritageActivityMetadata($item),
        ];

        if ($existing) {
            $existing->update($payload);

            return $existing->fresh();
        }

        return Game::query()->create($payload);
    }

    protected function mapLanguageActivityType(string $type): string
    {
        return match ($type) {
            'Tracing' => 'word_trace',
            'Audio Matching' => 'audio_match',
            'Speaking' => 'speak_back',
            'Word Jumble' => 'proverb_jumble',
            'Sentence Building' => 'sentence_builder',
            'Language Challenge' => 'sentence_builder',
            default => 'word_trace',
        };
    }

    protected function mapCultureType(string $activityType, string $tag): string
    {
        $haystack = strtolower($activityType.' '.$tag);

        return match (true) {
            str_contains($haystack, 'map') => 'clan_map',
            str_contains($haystack, 'design') || str_contains($haystack, 'crest') => 'clan_design',
            str_contains($haystack, 'profile') || str_contains($haystack, 'meet the') => 'clan_profile',
            str_contains($haystack, 'history') || str_contains($haystack, 'long ago') => 'clan_history',
            str_contains($haystack, 'clan quiz') => 'clan_profile',
            default => 'clan_story',
        };
    }

    /**
     * @param  array<string, mixed>  $item
     */
    protected function isHeritageQuizCulture(array $item): bool
    {
        $type = strtolower((string) ($item['activityType'] ?? ''));
        $tag = strtolower((string) ($item['tag'] ?? ''));

        return $type === 'quiz'
            || str_contains($tag, 'quiz')
            || str_contains($tag, 'graduation');
    }

    protected function mapDrawingType(string $category, string $activityType): string
    {
        if ($category === 'Colouring' || str_contains(strtolower($activityType), 'colour')) {
            return str_contains(strtolower($activityType), 'number') ? 'colour_by_number' : 'coloring';
        }

        return match (true) {
            str_contains(strtolower($activityType), 'design') => 'design_tool',
            str_contains(strtolower($activityType), 'hero') => 'hero_drawing',
            default => 'free_draw',
        };
    }

    protected function mapGameType(string $activityType): string
    {
        return match ($activityType) {
            'Rhythm Game', 'Music Game' => 'rhythm',
            'Sound Matching', 'Instrument Exploration' => 'matching',
            'Pronunciation Game', 'Quiz' => 'quiz',
            'Speaking' => 'quiz',
            default => 'quiz',
        };
    }
}
