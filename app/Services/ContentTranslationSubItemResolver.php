<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\ActivityFlashcardSlide;
use App\Models\ComicPanel;
use App\Models\CultureActivity;
use App\Models\Drawing;
use App\Models\GameQuestion;
use App\Models\LanguageActivity;
use App\Models\LanguageActivityWord;
use App\Models\Maze;
use App\Models\OrganisationContentDecision;
use App\Models\SongLyricSegment;
use App\Models\SpotDifferenceZone;
use App\Models\WordSearch;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ContentTranslationSubItemResolver
{
    /**
     * @return array<int, array{key: string, label: string}>
     */
    public function options(string $contentType, int $contentId): array
    {
        return match ($contentType) {
            OrganisationContentDecision::TYPE_STORY => $this->panelOptions($contentId),
            OrganisationContentDecision::TYPE_FLASHCARD => $this->flashcardOptions($contentId),
            OrganisationContentDecision::TYPE_LANGUAGE => $this->languageOptions($contentId),
            OrganisationContentDecision::TYPE_WORD_SEARCH => $this->wordSearchOptions($contentId),
            OrganisationContentDecision::TYPE_SONG => $this->songOptions($contentId),
            OrganisationContentDecision::TYPE_GAME => $this->gameOptions($contentId),
            OrganisationContentDecision::TYPE_PUZZLE => $this->puzzleOptions($contentId),
            OrganisationContentDecision::TYPE_DRAWING,
            OrganisationContentDecision::TYPE_COLOURING => $this->drawingOptions($contentId),
            OrganisationContentDecision::TYPE_MAZE => $this->mazeOptions($contentId),
            OrganisationContentDecision::TYPE_SPOT_DIFFERENCE => $this->spotZoneOptions($contentId),
            OrganisationContentDecision::TYPE_CULTURE => $this->cultureOptions($contentId),
            default => [],
        };
    }

    public function labelForKey(string $contentType, string $key): string
    {
        if (str_starts_with($key, 'panel:')) {
            return 'Panel';
        }
        if (str_starts_with($key, 'slide:')) {
            return 'Flashcard';
        }
        if (str_starts_with($key, 'word:')) {
            return 'Vocab word';
        }
        if (str_starts_with($key, 'ws:')) {
            return 'Word #'.((int) substr($key, 3) + 1);
        }
        if (str_starts_with($key, 'segment:')) {
            return 'Lyric segment';
        }
        if (str_starts_with($key, 'question:')) {
            return 'Question';
        }
        if (str_starts_with($key, 'zone:')) {
            return 'Difference zone';
        }
        if (str_starts_with($key, 'collectible:')) {
            return 'Collectible';
        }
        if (str_starts_with($key, 'label:')) {
            return 'Colour label #'.substr($key, 6);
        }
        if (str_starts_with($key, 'field:')) {
            return Str::headline(str_replace('.', ' ', substr($key, 6)));
        }

        return $key;
    }

    /**
     * @return array{word: string, translation: ?string, phonetic: ?string, x_position: ?int, y_position: ?int, width: ?int, height: ?int}
     */
    public function nativeValues(string $contentType, int $contentId, ?string $subItemKey): array
    {
        $empty = $this->emptyValues();

        if (! $subItemKey) {
            return $empty;
        }

        return match (true) {
            str_starts_with($subItemKey, 'panel:') => $this->emptyValues(['x_position' => 10, 'y_position' => 20, 'width' => 80, 'height' => 40]),
            str_starts_with($subItemKey, 'slide:') => $this->flashcardValues($contentId, $subItemKey) ?? $empty,
            str_starts_with($subItemKey, 'word:') => $this->languageWordValues($contentId, $subItemKey) ?? $empty,
            str_starts_with($subItemKey, 'ws:') => $this->wordSearchValues($contentId, $subItemKey) ?? $empty,
            str_starts_with($subItemKey, 'segment:') => $this->segmentValues($contentId, $subItemKey) ?? $empty,
            str_starts_with($subItemKey, 'question:') => $this->questionValues($contentId, $subItemKey) ?? $empty,
            str_starts_with($subItemKey, 'zone:') => $this->zoneValues($contentId, $subItemKey) ?? $empty,
            str_starts_with($subItemKey, 'collectible:') => $this->collectibleValues($contentId, $subItemKey) ?? $empty,
            str_starts_with($subItemKey, 'label:') => $this->colourLabelValues($contentId, $subItemKey) ?? $empty,
            str_starts_with($subItemKey, 'field:') => $this->fieldValues($contentType, $contentId, substr($subItemKey, 6)) ?? $empty,
            default => $empty,
        };
    }

    public function applyNative(string $contentType, int $contentId, ?string $subItemKey, string $word, ?string $translation, ?string $phonetic): void
    {
        if (! $subItemKey) {
            return;
        }

        if (str_starts_with($subItemKey, 'slide:')) {
            $slide = ActivityFlashcardSlide::query()->find((int) substr($subItemKey, 6));
            if ($slide && (int) $slide->activity_id === $contentId) {
                $slide->update(['front_label' => $word, 'back_label' => $translation, 'phonetic' => $phonetic]);
            }

            return;
        }

        if (str_starts_with($subItemKey, 'word:')) {
            $w = LanguageActivityWord::query()->find((int) substr($subItemKey, 5));
            if ($w && (int) $w->language_activity_id === $contentId) {
                $w->update(['word' => $word, 'translation' => $translation, 'phonetic' => $phonetic]);
            }

            return;
        }

        if (str_starts_with($subItemKey, 'ws:')) {
            $ws = WordSearch::query()->find($contentId);
            if (! $ws || ! is_array($ws->words)) {
                return;
            }
            $index = (int) substr($subItemKey, 3);
            $words = $ws->words;
            if (! isset($words[$index]) || ! is_array($words[$index])) {
                return;
            }
            $words[$index]['word'] = $word;
            $words[$index]['translation'] = $translation;
            if ($phonetic !== null) {
                $words[$index]['hint'] = $phonetic;
            }
            $ws->update(['words' => $words]);

            return;
        }

        if (str_starts_with($subItemKey, 'segment:')) {
            $seg = SongLyricSegment::query()->find((int) substr($subItemKey, 8));
            if ($seg && (int) $seg->song_id === $contentId) {
                $seg->update(['segment_text' => $word, 'blank_answer' => $translation]);
            }

            return;
        }

        if (str_starts_with($subItemKey, 'question:')) {
            $q = GameQuestion::query()->find((int) substr($subItemKey, 9));
            if ($q && (int) $q->game_id === $contentId) {
                $q->update(['question_text' => $word, 'correct_answer' => $translation, 'hint' => $phonetic]);
            }

            return;
        }

        if (str_starts_with($subItemKey, 'zone:')) {
            $z = SpotDifferenceZone::query()->find((int) substr($subItemKey, 5));
            if ($z && (int) $z->spot_difference_id === $contentId) {
                $z->update(['label' => $word]);
            }

            return;
        }

        if (str_starts_with($subItemKey, 'collectible:')) {
            $maze = Maze::query()->find($contentId);
            if (! $maze || ! is_array($maze->collectibles)) {
                return;
            }
            $index = (int) substr($subItemKey, 12);
            $items = $maze->collectibles;
            if (! isset($items[$index])) {
                return;
            }
            $items[$index]['label'] = $word;
            $items[$index]['translation'] = $translation;
            $maze->update(['collectibles' => $items]);

            return;
        }

        if (str_starts_with($subItemKey, 'label:')) {
            $drawing = Drawing::query()->find($contentId);
            if (! $drawing) {
                return;
            }
            $meta = $drawing->metadata ?? [];
            Arr::set($meta, 'colour_labels.'.substr($subItemKey, 6), $word);
            $drawing->update(['metadata' => $meta]);

            return;
        }

        if (str_starts_with($subItemKey, 'field:')) {
            $this->applyFieldValue($contentType, $contentId, substr($subItemKey, 6), $word, $translation, $phonetic);
        }
    }

    /** @return array{word_label: string, translation_label: string, phonetic_label: string, word_hint: string, translation_hint: string} */
    public function fieldLabels(string $contentType, ?string $subtype, ?string $subItemKey): array
    {
        if ($subItemKey && str_starts_with($subItemKey, 'field:')) {
            $path = substr($subItemKey, 6);

            return match ($path) {
                'tag', 'content_tag' => [
                    'word_label' => 'Content tag',
                    'translation_label' => 'Tag translation',
                    'phonetic_label' => 'Note',
                    'word_hint' => 'Taxonomy label from the puzzle editor.',
                    'translation_hint' => 'English gloss for the tag.',
                ],
                'description' => [
                    'word_label' => 'Description',
                    'translation_label' => 'Description translation',
                    'phonetic_label' => 'Note',
                    'word_hint' => 'Primary description text.',
                    'translation_hint' => 'Translated description.',
                ],
                'lyrics' => [
                    'word_label' => 'Lyrics',
                    'translation_label' => 'Lyrics translation',
                    'phonetic_label' => 'Note',
                    'word_hint' => 'Full song lyrics.',
                    'translation_hint' => 'English translation of lyrics.',
                ],
                'full_sentence' => [
                    'word_label' => 'Full sentence',
                    'translation_label' => 'Sentence translation',
                    'phonetic_label' => 'Phonetic',
                    'word_hint' => 'Source-language sentence.',
                    'translation_hint' => 'English meaning.',
                ],
                'sentence_translation' => [
                    'word_label' => 'Sentence (source)',
                    'translation_label' => 'Sentence translation',
                    'phonetic_label' => 'Phonetic',
                    'word_hint' => 'Use full_sentence row instead when creating new.',
                    'translation_hint' => 'English sentence meaning.',
                ],
                'proverb' => [
                    'word_label' => 'Proverb',
                    'translation_label' => 'Proverb translation',
                    'phonetic_label' => 'Note',
                    'word_hint' => 'Original proverb.',
                    'translation_hint' => 'English explanation.',
                ],
                'hero_character' => [
                    'word_label' => 'Hero character',
                    'translation_label' => 'Hero translation',
                    'phonetic_label' => 'Note',
                    'word_hint' => 'Character name in the maze.',
                    'translation_hint' => 'English name or gloss.',
                ],
                'coloring.scene_description', 'coloring.colour_hint',
                'hero.name', 'hero.title', 'hero.instructions',
                'design.prompt', 'free_draw.prompt', 'free_draw.checklist' => [
                    'word_label' => Str::headline(str_replace('.', ' ', $path)),
                    'translation_label' => 'Translation',
                    'phonetic_label' => 'Note',
                    'word_hint' => 'Source text from the drawing editor.',
                    'translation_hint' => 'English translation or gloss.',
                ],
                default => $this->defaultFieldLabels($contentType, $subtype),
            };
        }

        return match ($contentType) {
            OrganisationContentDecision::TYPE_STORY => [
                'word_label' => 'Vocab word', 'translation_label' => 'English gloss', 'phonetic_label' => 'Phonetic',
                'word_hint' => 'Tribe-language word on the panel hotspot.', 'translation_hint' => 'Tap-to-translate meaning.',
            ],
            OrganisationContentDecision::TYPE_FLASHCARD => [
                'word_label' => 'Front label', 'translation_label' => 'Back label', 'phonetic_label' => 'Phonetic',
                'word_hint' => 'Card front (local language).', 'translation_hint' => 'Card back meaning.',
            ],
            OrganisationContentDecision::TYPE_LANGUAGE => [
                'word_label' => 'Word', 'translation_label' => 'Translation', 'phonetic_label' => 'Phonetic',
                'word_hint' => 'Vocabulary word.', 'translation_hint' => 'English meaning.',
            ],
            OrganisationContentDecision::TYPE_WORD_SEARCH => [
                'word_label' => 'Grid word', 'translation_label' => 'Translation', 'phonetic_label' => 'Hint',
                'word_hint' => 'Word in the puzzle.', 'translation_hint' => 'English meaning.',
            ],
            OrganisationContentDecision::TYPE_SONG => [
                'word_label' => 'Lyric line', 'translation_label' => 'Translation / blank answer', 'phonetic_label' => 'Note',
                'word_hint' => 'Segment text from the song editor.', 'translation_hint' => 'English line or fill-blank answer.',
            ],
            OrganisationContentDecision::TYPE_GAME => [
                'word_label' => 'Question', 'translation_label' => 'Correct answer', 'phonetic_label' => 'Hint',
                'word_hint' => 'Prompt shown to the player.', 'translation_hint' => 'Expected answer or match text.',
            ],
            OrganisationContentDecision::TYPE_SPOT_DIFFERENCE => [
                'word_label' => 'Zone label', 'translation_label' => 'Label translation', 'phonetic_label' => 'Note',
                'word_hint' => 'Label for this difference zone.', 'translation_hint' => 'English label.',
            ],
            OrganisationContentDecision::TYPE_MAZE => [
                'word_label' => 'Collectible label', 'translation_label' => 'Translation', 'phonetic_label' => 'Note',
                'word_hint' => 'Item label on the maze grid.', 'translation_hint' => 'English gloss.',
            ],
            default => $this->defaultFieldLabels($contentType, $subtype),
        };
    }

    protected function defaultFieldLabels(string $contentType, ?string $subtype): array
    {
        return [
            'word_label' => 'Source text',
            'translation_label' => 'Translation',
            'phonetic_label' => 'Note',
            'word_hint' => 'Primary text in the source language.',
            'translation_hint' => 'English gloss or meaning.',
        ];
    }

    /** @return array<int, array{key: string, label: string}> */
    protected function panelOptions(int $comicId): array
    {
        return ComicPanel::query()->where('comic_id', $comicId)->orderBy('order_index')->get()
            ->map(fn ($p) => [
                'key' => 'panel:'.$p->id,
                'label' => 'Panel '.((int) $p->order_index + 1).($p->caption ? ' — '.Str::limit($p->caption, 40) : ''),
            ])->all();
    }

    /** @return array<int, array{key: string, label: string}> */
    protected function flashcardOptions(int $activityId): array
    {
        return ActivityFlashcardSlide::query()->where('activity_id', $activityId)->orderBy('order_index')->get()
            ->map(fn ($s) => [
                'key' => 'slide:'.$s->id,
                'label' => 'Card '.((int) $s->order_index + 1).($s->front_label ? ' — '.$s->front_label : ''),
            ])->all();
    }

    /** @return array<int, array{key: string, label: string}> */
    protected function languageOptions(int $activityId): array
    {
        $activity = LanguageActivity::query()->find($activityId);
        $opts = [];

        if ($activity && in_array($activity->activity_type, ['proverb_jumble', 'sentence_builder'], true)) {
            $opts[] = ['key' => 'field:full_sentence', 'label' => 'Full sentence'];
            $opts[] = ['key' => 'field:sentence_translation', 'label' => 'Sentence translation'];
        }

        foreach (LanguageActivityWord::query()->where('language_activity_id', $activityId)->orderBy('order_index')->get() as $w) {
            $opts[] = [
                'key' => 'word:'.$w->id,
                'label' => $w->word.($w->translation ? ' → '.$w->translation : ''),
            ];
        }

        return $opts;
    }

    /** @return array<int, array{key: string, label: string}> */
    protected function wordSearchOptions(int $wordSearchId): array
    {
        $words = WordSearch::query()->whereKey($wordSearchId)->value('words');
        if (! is_array($words)) {
            return [];
        }
        $opts = [];
        foreach ($words as $i => $entry) {
            $word = is_array($entry) ? ($entry['word'] ?? '') : (string) $entry;
            if ($word === '') {
                continue;
            }
            $opts[] = [
                'key' => 'ws:'.$i,
                'label' => $word.(is_array($entry) && ! empty($entry['translation']) ? ' → '.$entry['translation'] : ''),
            ];
        }

        return $opts;
    }

    /** @return array<int, array{key: string, label: string}> */
    protected function songOptions(int $songId): array
    {
        $opts = [['key' => 'field:lyrics', 'label' => 'Full lyrics']];
        foreach (SongLyricSegment::query()->where('song_id', $songId)->orderBy('order_index')->get() as $seg) {
            $opts[] = [
                'key' => 'segment:'.$seg->id,
                'label' => 'Segment '.((int) $seg->order_index + 1).' — '.Str::limit($seg->segment_text, 40),
            ];
        }

        return $opts;
    }

    /** @return array<int, array{key: string, label: string}> */
    protected function gameOptions(int $gameId): array
    {
        return GameQuestion::query()->where('game_id', $gameId)->orderBy('order_index')->get()
            ->map(fn ($q, $i) => [
                'key' => 'question:'.$q->id,
                'label' => 'Q'.($i + 1).' — '.Str::limit($q->question_text ?: $q->match_text ?: 'Question', 45),
            ])->all();
    }

    /** @return array<int, array{key: string, label: string}> */
    protected function puzzleOptions(int $activityId): array
    {
        return [
            ['key' => 'field:tag', 'label' => 'Content tag'],
            ['key' => 'field:description', 'label' => 'Description'],
        ];
    }

    /** @return array<int, array{key: string, label: string}> */
    protected function drawingOptions(int $drawingId): array
    {
        $drawing = Drawing::query()->find($drawingId);
        if (! $drawing) {
            return [];
        }

        return match ($drawing->drawing_type) {
            'coloring' => [
                ['key' => 'field:coloring.scene_description', 'label' => 'Scene description'],
                ['key' => 'field:coloring.colour_hint', 'label' => 'Colour hint'],
            ],
            'colour_by_number' => collect($drawing->metadata['colour_labels'] ?? [])
                ->keys()
                ->map(fn ($num) => ['key' => 'label:'.$num, 'label' => 'Colour #'.$num])
                ->values()
                ->all(),
            'hero_drawing' => [
                ['key' => 'field:hero.name', 'label' => 'Hero name'],
                ['key' => 'field:hero.title', 'label' => 'Hero title'],
                ['key' => 'field:hero.instructions', 'label' => 'Instructions'],
            ],
            'design_tool' => [
                ['key' => 'field:design.prompt', 'label' => 'Design prompt'],
            ],
            'free_draw' => [
                ['key' => 'field:free_draw.prompt', 'label' => 'Creative prompt'],
                ['key' => 'field:free_draw.checklist', 'label' => 'Checklist'],
            ],
            default => [
                ['key' => 'field:description', 'label' => 'Description'],
            ],
        };
    }

    /** @return array<int, array{key: string, label: string}> */
    protected function mazeOptions(int $mazeId): array
    {
        $opts = [['key' => 'field:hero_character', 'label' => 'Hero character']];
        $maze = Maze::query()->find($mazeId);
        if ($maze && is_array($maze->collectibles)) {
            foreach ($maze->collectibles as $i => $col) {
                $label = is_array($col) ? ($col['label'] ?? 'Item') : 'Item';
                $opts[] = ['key' => 'collectible:'.$i, 'label' => 'Collectible: '.$label];
            }
        }

        return $opts;
    }

    /** @return array<int, array{key: string, label: string}> */
    protected function spotZoneOptions(int $spotId): array
    {
        return SpotDifferenceZone::query()->where('spot_difference_id', $spotId)->orderBy('order_index')->get()
            ->map(fn ($z, $i) => [
                'key' => 'zone:'.$z->id,
                'label' => 'Zone '.($i + 1).($z->label ? ' — '.$z->label : ''),
            ])->all();
    }

    /** @return array<int, array{key: string, label: string}> */
    protected function cultureOptions(int $cultureId): array
    {
        $opts = [['key' => 'field:proverb', 'label' => 'Proverb']];
        $activity = CultureActivity::query()->find($cultureId);
        if ($activity && filled($activity->content)) {
            $opts[] = ['key' => 'field:content', 'label' => 'Main content excerpt'];
        }

        return $opts;
    }

    /** @param  array<string, mixed>  $overrides */
    protected function emptyValues(array $overrides = []): array
    {
        return array_merge([
            'word' => '',
            'translation' => null,
            'phonetic' => null,
            'x_position' => null,
            'y_position' => null,
            'width' => null,
            'height' => null,
        ], $overrides);
    }

    protected function flashcardValues(int $activityId, string $key): ?array
    {
        $slide = ActivityFlashcardSlide::query()->where('activity_id', $activityId)->find((int) substr($key, 6));

        return $slide ? $this->emptyValues([
            'word' => (string) ($slide->front_label ?? ''),
            'translation' => $slide->back_label,
            'phonetic' => $slide->phonetic,
        ]) : null;
    }

    protected function languageWordValues(int $activityId, string $key): ?array
    {
        $w = LanguageActivityWord::query()->where('language_activity_id', $activityId)->find((int) substr($key, 5));

        return $w ? $this->emptyValues(['word' => (string) $w->word, 'translation' => $w->translation, 'phonetic' => $w->phonetic]) : null;
    }

    protected function wordSearchValues(int $wsId, string $key): ?array
    {
        $index = (int) substr($key, 3);
        $words = WordSearch::query()->whereKey($wsId)->value('words');
        if (! is_array($words) || ! isset($words[$index]) || ! is_array($words[$index])) {
            return null;
        }
        $e = $words[$index];

        return $this->emptyValues(['word' => (string) ($e['word'] ?? ''), 'translation' => $e['translation'] ?? null, 'phonetic' => $e['hint'] ?? null]);
    }

    protected function segmentValues(int $songId, string $key): ?array
    {
        $seg = SongLyricSegment::query()->where('song_id', $songId)->find((int) substr($key, 8));

        return $seg ? $this->emptyValues(['word' => (string) $seg->segment_text, 'translation' => $seg->blank_answer]) : null;
    }

    protected function questionValues(int $gameId, string $key): ?array
    {
        $q = GameQuestion::query()->where('game_id', $gameId)->find((int) substr($key, 9));

        return $q ? $this->emptyValues([
            'word' => (string) ($q->question_text ?: $q->match_text ?: ''),
            'translation' => $q->correct_answer,
            'phonetic' => $q->hint,
        ]) : null;
    }

    protected function zoneValues(int $spotId, string $key): ?array
    {
        $z = SpotDifferenceZone::query()->where('spot_difference_id', $spotId)->find((int) substr($key, 5));

        return $z ? $this->emptyValues(['word' => (string) ($z->label ?? '')]) : null;
    }

    protected function collectibleValues(int $mazeId, string $key): ?array
    {
        $maze = Maze::query()->find($mazeId);
        $index = (int) substr($key, 12);
        if (! $maze || ! is_array($maze->collectibles) || ! isset($maze->collectibles[$index])) {
            return null;
        }
        $col = $maze->collectibles[$index];

        return $this->emptyValues([
            'word' => (string) ($col['label'] ?? ''),
            'translation' => $col['translation'] ?? null,
        ]);
    }

    protected function colourLabelValues(int $drawingId, string $key): ?array
    {
        $drawing = Drawing::query()->find($drawingId);
        if (! $drawing) {
            return null;
        }
        $num = substr($key, 6);

        return $this->emptyValues(['word' => (string) data_get($drawing->metadata, 'colour_labels.'.$num, '')]);
    }

    protected function fieldValues(string $contentType, int $contentId, string $path): ?array
    {
        $value = match ($contentType) {
            OrganisationContentDecision::TYPE_PUZZLE => $this->puzzleFieldValue($contentId, $path),
            OrganisationContentDecision::TYPE_DRAWING, OrganisationContentDecision::TYPE_COLOURING => $this->drawingFieldValue($contentId, $path),
            OrganisationContentDecision::TYPE_SONG => $path === 'lyrics' ? Song::query()->whereKey($contentId)->value('lyrics') : null,
            OrganisationContentDecision::TYPE_LANGUAGE => $this->languageFieldValue($contentId, $path),
            OrganisationContentDecision::TYPE_CULTURE => $this->cultureFieldValue($contentId, $path),
            OrganisationContentDecision::TYPE_MAZE => $path === 'hero_character' ? Maze::query()->whereKey($contentId)->value('hero_character') : null,
            default => null,
        };

        if ($value === null && $path !== 'sentence_translation') {
            return $this->emptyValues();
        }

        if ($path === 'full_sentence') {
            $activity = LanguageActivity::query()->find($contentId);

            return $this->emptyValues(['word' => (string) ($activity?->full_sentence ?? ''), 'translation' => $activity?->sentence_translation]);
        }

        if ($path === 'sentence_translation') {
            $activity = LanguageActivity::query()->find($contentId);

            return $this->emptyValues(['word' => (string) ($activity?->full_sentence ?? ''), 'translation' => $activity?->sentence_translation]);
        }

        if ($path === 'proverb') {
            $activity = CultureActivity::query()->find($contentId);

            return $this->emptyValues(['word' => (string) ($activity?->proverb ?? ''), 'translation' => $activity?->proverb_translation]);
        }

        if (is_array($value)) {
            return $this->emptyValues($value);
        }

        return $this->emptyValues(['word' => (string) $value]);
    }

    protected function puzzleFieldValue(int $activityId, string $path): mixed
    {
        $activity = Activity::query()->find($activityId);
        if (! $activity) {
            return null;
        }

        return match ($path) {
            'tag' => data_get($activity->metadata, 'tag'),
            'description' => $activity->description,
            default => null,
        };
    }

    protected function drawingFieldValue(int $drawingId, string $path): mixed
    {
        $drawing = Drawing::query()->find($drawingId);

        return data_get($drawing?->metadata, $path)
            ?? ($path === 'description' ? $drawing?->description : null);
    }

    protected function languageFieldValue(int $activityId, string $path): mixed
    {
        $activity = LanguageActivity::query()->find($activityId);

        return match ($path) {
            'full_sentence' => $activity?->full_sentence,
            'sentence_translation' => $activity?->sentence_translation,
            default => null,
        };
    }

    protected function cultureFieldValue(int $cultureId, string $path): mixed
    {
        $activity = CultureActivity::query()->find($cultureId);

        return match ($path) {
            'proverb' => ['word' => (string) ($activity?->proverb ?? ''), 'translation' => $activity?->proverb_translation],
            'content' => Str::limit(strip_tags((string) $activity?->content), 500),
            default => null,
        };
    }

    protected function applyFieldValue(string $contentType, int $contentId, string $path, string $word, ?string $translation, ?string $phonetic): void
    {
        match ($contentType) {
            OrganisationContentDecision::TYPE_PUZZLE => $this->applyPuzzleField($contentId, $path, $word, $translation),
            OrganisationContentDecision::TYPE_DRAWING, OrganisationContentDecision::TYPE_COLOURING => $this->applyDrawingField($contentId, $path, $word, $translation),
            OrganisationContentDecision::TYPE_SONG => $path === 'lyrics' ? Song::query()->whereKey($contentId)->update(['lyrics' => $word]) : null,
            OrganisationContentDecision::TYPE_LANGUAGE => $this->applyLanguageField($contentId, $path, $word, $translation),
            OrganisationContentDecision::TYPE_CULTURE => $this->applyCultureField($contentId, $path, $word, $translation),
            OrganisationContentDecision::TYPE_MAZE => $path === 'hero_character'
                ? Maze::query()->whereKey($contentId)->update(['hero_character' => $word])
                : null,
            default => null,
        };
    }

    protected function applyPuzzleField(int $activityId, string $path, string $word, ?string $translation): void
    {
        $activity = Activity::query()->find($activityId);
        if (! $activity) {
            return;
        }
        if ($path === 'tag') {
            $meta = $activity->metadata ?? [];
            $meta['tag'] = $word;
            $activity->update(['metadata' => $meta]);

            return;
        }
        if ($path === 'description') {
            $activity->update(['description' => $word]);
        }
    }

    protected function applyDrawingField(int $drawingId, string $path, string $word, ?string $translation): void
    {
        $drawing = Drawing::query()->find($drawingId);
        if (! $drawing) {
            return;
        }
        if ($path === 'description') {
            $drawing->update(['description' => $word]);

            return;
        }
        $meta = $drawing->metadata ?? [];
        Arr::set($meta, $path, $word);
        $drawing->update(['metadata' => $meta]);
    }

    protected function applyLanguageField(int $activityId, string $path, string $word, ?string $translation): void
    {
        $activity = LanguageActivity::query()->find($activityId);
        if (! $activity) {
            return;
        }
        if ($path === 'full_sentence') {
            $activity->update(['full_sentence' => $word, 'sentence_translation' => $translation]);

            return;
        }
        if ($path === 'sentence_translation') {
            $activity->update(['sentence_translation' => $translation]);
        }
    }

    protected function applyCultureField(int $cultureId, string $path, string $word, ?string $translation): void
    {
        $activity = CultureActivity::query()->find($cultureId);
        if (! $activity) {
            return;
        }
        if ($path === 'proverb') {
            $activity->update(['proverb' => $word, 'proverb_translation' => $translation]);

            return;
        }
        if ($path === 'content') {
            $activity->update(['content' => $word]);
        }
    }
}
