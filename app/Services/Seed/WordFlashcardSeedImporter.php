<?php

namespace App\Services\Seed;

use App\Models\Activity;
use App\Models\ActivityFlashcardSlide;
use App\Models\Tribe;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Imports Heritage Heroes word flashcards from seed/wordFlashcards.seed.json.
 *
 * Architecture:
 * - One {@see Activity} per tribe (type: flashcard)
 * - One {@see ActivityFlashcardSlide} per vocabulary card (ordered deck)
 *
 * Idempotent via metadata.seed_deck_key (activity) and metadata.seed_card_id (slides).
 */
class WordFlashcardSeedImporter
{
    public const SEED_SOURCE = 'word_flashcards_seed';

    public const JSON_PATH = 'seed/wordFlashcards.seed.json';

    /**
     * @return array{tribes: int, activities: int, slides: int, tribes_skipped: int}
     */
    /**
     * @param  array<string, int>|null  $tribeMap  tribe name => id (from HeritageTribeUpserter)
     * @return array{tribes: int, activities: int, slides: int, tribes_skipped: int}
     */
    public function import(?Command $command = null, ?array $tribeMap = null): array
    {
        $path = base_path(self::JSON_PATH);

        if (! File::exists($path)) {
            throw new \RuntimeException('Word flashcard seed file not found at: '.$path);
        }

        $payload = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);

        $tribeDefinitions = $payload['tribes'] ?? [];
        $cards = $payload['flashcards'] ?? [];

        if ($tribeDefinitions === [] || $cards === []) {
            throw new \RuntimeException('Word flashcard seed file is missing tribes or flashcards.');
        }

        $cardsByTribe = collect($cards)->groupBy('tribe');

        $stats = [
            'tribes' => 0,
            'activities' => 0,
            'slides' => 0,
            'tribes_skipped' => 0,
        ];

        DB::transaction(function () use ($tribeDefinitions, $cardsByTribe, $tribeMap, $command, &$stats): void {
            foreach ($tribeDefinitions as $tribeMeta) {
                $tribeName = (string) ($tribeMeta['name'] ?? '');
                $tribeKey = (string) ($tribeMeta['key'] ?? Str::slug($tribeName));

                if ($tribeName === '') {
                    $stats['tribes_skipped']++;

                    continue;
                }

                $tribeCards = $cardsByTribe->get($tribeName, collect())->values()->all();

                if ($tribeCards === []) {
                    $command?->warn("No flashcards found for tribe: {$tribeName}");
                    $stats['tribes_skipped']++;

                    continue;
                }

                $tribe = $this->resolveTribe($tribeMeta, $tribeCards, $tribeMap);
                $stats['tribes']++;

                $activity = $this->upsertFlashcardDeck($tribe, $tribeMeta, $tribeCards);
                $stats['activities']++;

                $stats['slides'] += $this->syncSlides($activity, $tribeCards);

                $command?->info(sprintf(
                    '  %s: %d slides → activity #%d',
                    $tribeName,
                    count($tribeCards),
                    $activity->id
                ));
            }
        });

        return $stats;
    }

    /**
     * @param  array<string, mixed>  $tribeMeta
     * @param  list<array<string, mixed>>  $tribeCards
     * @param  array<string, int>|null  $tribeMap
     */
    protected function resolveTribe(array $tribeMeta, array $tribeCards, ?array $tribeMap): Tribe
    {
        $name = (string) $tribeMeta['name'];

        if ($tribeMap !== null && isset($tribeMap[$name])) {
            $tribe = Tribe::query()->findOrFail($tribeMap[$name]);
            $sampleMeta = $tribeCards[0]['tribeMetadata'] ?? [];
            if (is_array($sampleMeta) && ! empty($sampleMeta['color']) && ! $tribe->color) {
                $tribe->update(['color' => $sampleMeta['color']]);
            }

            return $tribe->fresh();
        }

        $sampleMeta = $tribeCards[0]['tribeMetadata'] ?? [];

        return Tribe::query()->updateOrCreate(
            ['name' => $name],
            [
                'hero_name' => (string) ($tribeMeta['hero'] ?? 'Heritage Hero'),
                'hero_emoji' => $tribeMeta['emoji'] ?? null,
                'hero_icon' => null,
                'greeting' => $tribeMeta['greeting'] ?? null,
                'region' => $tribeMeta['region'] ?? null,
                'color' => is_array($sampleMeta) ? ($sampleMeta['color'] ?? null) : null,
            ]
        );
    }

    /**
     * @param  array<string, mixed>  $tribeMeta
     * @param  list<array<string, mixed>>  $cards
     */
    protected function upsertFlashcardDeck(Tribe $tribe, array $tribeMeta, array $cards): Activity
    {
        $tribeKey = (string) ($tribeMeta['key'] ?? Str::slug($tribe->name));
        $language = (string) ($tribeMeta['language'] ?? ($cards[0]['language'] ?? ''));

        $activity = Activity::query()
            ->where('type', 'flashcard')
            ->where('tribe_id', $tribe->id)
            ->where('metadata->seed_deck_key', $tribeKey)
            ->first();

        $metadata = array_merge(
            is_array($activity?->metadata) ? Arr::except($activity->metadata, ['flashcard', 'vocab', 'tag']) : [],
            [
                'seed_source' => self::SEED_SOURCE,
                'seed_deck_key' => $tribeKey,
                'heritage_version' => '1.0.0',
                'tag' => 'Heritage Vocabulary',
                'flashcard' => [
                    'count' => count($cards),
                    'deck_type' => 'heritage_word_bank',
                ],
                'vocab' => [
                    'language' => $language,
                    'words_count' => count($cards),
                ],
            ]
        );

        $payload = [
            'tribe_id' => $tribe->id,
            'type' => 'flashcard',
            'title' => $tribe->name.' Heritage Word Flashcards',
            'description' => sprintf(
                '%d vocabulary flashcards for %s heritage learners (ages 2–10). Language: %s.',
                count($cards),
                $tribe->name,
                $language !== '' ? $language : 'local language'
            ),
            'age_range' => '2-10',
            'star_points' => 5,
            'is_published' => true,
            'metadata' => $metadata,
        ];

        if ($activity) {
            $activity->update($payload);

            return $activity->fresh();
        }

        return Activity::query()->create($payload);
    }

    /**
     * @param  list<array<string, mixed>>  $cards
     */
    protected function syncSlides(Activity $activity, array $cards): int
    {
        $idsKept = [];

        foreach (array_values($cards) as $index => $card) {
            $seedCardId = (string) ($card['id'] ?? '');
            $existing = $seedCardId !== ''
                ? ActivityFlashcardSlide::query()
                    ->where('activity_id', $activity->id)
                    ->where('metadata->seed_card_id', $seedCardId)
                    ->first()
                : null;

            $payload = [
                'order_index' => $index,
                'emoji' => filled($card['emoji'] ?? null) ? (string) $card['emoji'] : null,
                'front_label' => (string) ($card['word'] ?? ''),
                'back_label' => (string) ($card['englishMeaning'] ?? ''),
                'phonetic' => filled($card['pronunciationGuide'] ?? null)
                    ? (string) $card['pronunciationGuide']
                    : null,
                'image_path' => $this->resolveImagePath($card),
                'audio_path' => $this->resolveAudioPath($card),
                'metadata' => $this->slideMetadata($card),
            ];

            if ($existing) {
                $existing->update($payload);
                $idsKept[] = $existing->id;
            } else {
                $slide = $activity->flashcardSlides()->create($payload);
                $idsKept[] = $slide->id;
            }
        }

        if ($idsKept !== []) {
            $activity->flashcardSlides()->whereNotIn('id', $idsKept)->delete();
        }

        $activity->update([
            'metadata' => array_merge($activity->metadata ?? [], [
                'flashcard' => [
                    'count' => count($idsKept),
                    'deck_type' => 'heritage_word_bank',
                ],
                'vocab' => array_merge($activity->metadata['vocab'] ?? [], [
                    'words_count' => count($idsKept),
                ]),
            ]),
        ]);

        return count($idsKept);
    }

    /**
     * @param  array<string, mixed>  $card
     * @return array<string, mixed>
     */
    protected function slideMetadata(array $card): array
    {
        $audio = is_array($card['audio'] ?? null) ? $card['audio'] : [];

        return [
            'seed_source' => self::SEED_SOURCE,
            'seed_card_id' => $card['id'] ?? null,
            'seed_slug' => $card['slug'] ?? null,
            'category' => $card['category'] ?? null,
            'language' => $card['language'] ?? null,
            'cultural_note' => $card['culturalNote'] ?? null,
            'example_sentence' => $card['exampleSentence'] ?? null,
            'image_prompt' => $card['imagePrompt'] ?? null,
            'tags' => $card['tags'] ?? [],
            'tribe_metadata' => $card['tribeMetadata'] ?? null,
            'source' => $card['source'] ?? null,
            'status' => $card['status'] ?? 'active',
            'audio' => $audio,
            'audio_file_name' => $audio['audioFileName'] ?? null,
            'recording_version' => $audio['recordingVersion'] ?? 1,
        ];
    }

    /**
     * @param  array<string, mixed>  $card
     */
    protected function resolveAudioPath(array $card): ?string
    {
        $audio = $card['audio'] ?? null;

        if (is_array($audio)) {
            $url = $audio['currentAudioUrl'] ?? $audio['audio'] ?? null;

            if (is_string($url) && $url !== '') {
                return app(HeritageSeedAssetPublisher::class)->publish($url);
            }
        }

        $assets = $card['assets'] ?? null;

        if (is_array($assets) && ! empty($assets['audio'])) {
            return app(HeritageSeedAssetPublisher::class)->publish((string) $assets['audio']);
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $card
     */
    protected function resolveImagePath(array $card): ?string
    {
        $assets = $card['assets'] ?? null;

        if (! is_array($assets) || empty($assets['image'])) {
            return null;
        }

        return app(HeritageSeedAssetPublisher::class)->publish((string) $assets['image']);
    }
}
