<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\ActivityFlashcardSlide;
use App\Models\Comic;
use App\Models\ComicPanel;
use App\Models\CultureActivity;
use App\Models\Drawing;
use App\Models\Game;
use App\Models\GameQuestion;
use App\Models\LanguageActivity;
use App\Models\LanguageActivityWord;
use App\Models\Maze;
use App\Models\OrganisationContentDecision;
use App\Models\PanelVocabTag;
use App\Models\Song;
use App\Models\SongLyricSegment;
use App\Models\SpotDifference;
use App\Models\SpotDifferenceZone;
use App\Models\WordSearch;
use Illuminate\Support\Str;

class ContentTranslationFormPresenter
{
    public function __construct(
        protected ContentTranslationCatalogService $catalog,
        protected ContentTranslationSubItemResolver $subItems,
    ) {}

    /**
     * @return array{subtype: ?string, subtype_label: ?string, fields: list<array{key: string, label: string, hint: ?string, placeholder: ?string}>}
     */
    public function fieldSchema(string $contentType, ?string $subtype = null, ?string $subItemKey = null): array
    {
        $labels = $this->subItems->fieldLabels($contentType, $subtype, $subItemKey);
        $subtypeLabel = match ($contentType) {
            OrganisationContentDecision::TYPE_LANGUAGE => $subtype ? (LanguageActivity::TYPES[$subtype] ?? $subtype) : null,
            OrganisationContentDecision::TYPE_CULTURE => $subtype ? (CultureActivity::TYPES[$subtype] ?? $subtype) : null,
            OrganisationContentDecision::TYPE_GAME => $subtype ? (Game::TYPES[$subtype] ?? $subtype) : null,
            OrganisationContentDecision::TYPE_DRAWING, OrganisationContentDecision::TYPE_COLOURING => $subtype,
            OrganisationContentDecision::TYPE_SONG => $subtype,
            default => OrganisationContentDecision::labelFor($contentType),
        };

        $fields = [
            ['key' => 'word', 'label' => $labels['word_label'], 'hint' => $labels['word_hint'], 'placeholder' => ''],
            ['key' => 'translation', 'label' => $labels['translation_label'], 'hint' => $labels['translation_hint'], 'placeholder' => ''],
            ['key' => 'phonetic', 'label' => $labels['phonetic_label'], 'hint' => 'Optional pronunciation or extra note.', 'placeholder' => ''],
        ];

        if ($contentType === OrganisationContentDecision::TYPE_STORY) {
            $fields = array_merge($fields, [
                ['key' => 'x_position', 'label' => 'Hotspot X %', 'hint' => 'Horizontal position on panel.', 'placeholder' => '10'],
                ['key' => 'y_position', 'label' => 'Hotspot Y %', 'hint' => 'Vertical position on panel.', 'placeholder' => '20'],
                ['key' => 'width', 'label' => 'Hotspot width', 'hint' => 'Tap area width (px).', 'placeholder' => '80'],
                ['key' => 'height', 'label' => 'Hotspot height', 'hint' => 'Tap area height (px).', 'placeholder' => '40'],
            ]);
        }

        if ($contentType === OrganisationContentDecision::TYPE_LANGUAGE
            && in_array($subtype, ['proverb_jumble', 'sentence_builder'], true)
            && $subItemKey && str_starts_with($subItemKey, 'word:')) {
            $fields[] = ['key' => '_note', 'label' => 'Note', 'hint' => 'Sentence-level text uses the Full sentence / Sentence translation parts above.', 'placeholder' => null];
        }

        return [
            'subtype' => $subtype,
            'subtype_label' => $subtypeLabel,
            'fields' => $fields,
        ];
    }

    /**
     * @return array{template: string, title: string, subtitle: ?string, data: array<string, mixed>}|null
     */
    public function sourcePreview(string $contentType, int $contentId, ?string $subItemKey = null): ?array
    {
        return match ($contentType) {
            OrganisationContentDecision::TYPE_STORY => $this->storyPanelPreview($contentId, $subItemKey),
            OrganisationContentDecision::TYPE_FLASHCARD => $this->flashcardSlidePreview($contentId, $subItemKey),
            OrganisationContentDecision::TYPE_LANGUAGE => $this->languagePreview($contentId, $subItemKey),
            OrganisationContentDecision::TYPE_WORD_SEARCH => $this->wordSearchPreview($contentId, $subItemKey),
            OrganisationContentDecision::TYPE_CULTURE => $this->culturePreview($contentId, $subItemKey),
            OrganisationContentDecision::TYPE_SONG => $this->songPreview($contentId, $subItemKey),
            OrganisationContentDecision::TYPE_GAME => $this->gamePreview($contentId, $subItemKey),
            OrganisationContentDecision::TYPE_PUZZLE => $this->puzzlePreview($contentId, $subItemKey),
            OrganisationContentDecision::TYPE_DRAWING, OrganisationContentDecision::TYPE_COLOURING => $this->drawingPreview($contentId, $subItemKey),
            OrganisationContentDecision::TYPE_MAZE => $this->mazePreview($contentId, $subItemKey),
            OrganisationContentDecision::TYPE_SPOT_DIFFERENCE => $this->spotDifferencePreview($contentId, $subItemKey),
            default => null,
        };
    }

    public function resolveSubtype(string $contentType, int $contentId): ?string
    {
        return match ($contentType) {
            OrganisationContentDecision::TYPE_LANGUAGE => LanguageActivity::query()->whereKey($contentId)->value('activity_type'),
            OrganisationContentDecision::TYPE_CULTURE => CultureActivity::query()->whereKey($contentId)->value('culture_type'),
            OrganisationContentDecision::TYPE_GAME => Game::query()->whereKey($contentId)->value('game_type'),
            OrganisationContentDecision::TYPE_DRAWING, OrganisationContentDecision::TYPE_COLOURING => Drawing::query()->whereKey($contentId)->value('drawing_type'),
            OrganisationContentDecision::TYPE_SONG => Song::query()->whereKey($contentId)->value('song_type'),
            OrganisationContentDecision::TYPE_MAZE => Maze::query()->whereKey($contentId)->value('maze_type'),
            default => null,
        };
    }

    /** @return array{word: string, translation: ?string, phonetic: ?string, x_position: ?int, y_position: ?int, width: ?int, height: ?int} */
    public function valuesFromNative(string $contentType, int $contentId, ?string $subItemKey): array
    {
        return $this->subItems->nativeValues($contentType, $contentId, $subItemKey);
    }

    /** @return list<array{key: string, label: string, active: bool}> */
    public function subItemNav(string $contentType, int $contentId, ?string $activeKey): array
    {
        return collect($this->catalog->subItemOptions($contentType, $contentId))
            ->map(fn ($opt) => [
                'key' => $opt['key'],
                'label' => $opt['label'],
                'active' => $activeKey === $opt['key'],
            ])
            ->all();
    }

    protected function storyPanelPreview(int $comicId, ?string $subItemKey): ?array
    {
        $comic = Comic::with('tribe:id,name')->find($comicId);
        if (! $comic) {
            return null;
        }

        $panels = ComicPanel::query()->where('comic_id', $comicId)->orderBy('order_index')->get();
        $panelId = $subItemKey && str_starts_with($subItemKey, 'panel:') ? (int) substr($subItemKey, 6) : null;
        $panel = $panelId ? $panels->firstWhere('id', $panelId) : $panels->first();

        return [
            'template' => 'story_panel',
            'title' => $comic->title,
            'subtitle' => $comic->tribe?->name,
            'data' => [
                'panel' => $panel ? [
                    'caption' => $panel->caption,
                    'image_url' => $panel->image_path ? asset('storage/'.$panel->image_path) : null,
                    'is_pdf' => $panel->isPdf(),
                    'order' => (int) $panel->order_index,
                ] : null,
                'existing_tags' => $panel
                    ? PanelVocabTag::query()->where('panel_id', $panel->id)->get(['word', 'translation', 'x_position', 'y_position'])->all()
                    : [],
            ],
        ];
    }

    protected function flashcardSlidePreview(int $activityId, ?string $subItemKey): ?array
    {
        $activity = Activity::with('tribe:id,name')->find($activityId);
        $slideId = $subItemKey && str_starts_with($subItemKey, 'slide:') ? (int) substr($subItemKey, 6) : null;
        $slide = $slideId
            ? ActivityFlashcardSlide::query()->where('activity_id', $activityId)->find($slideId)
            : ActivityFlashcardSlide::query()->where('activity_id', $activityId)->orderBy('order_index')->first();

        return [
            'template' => 'flashcard_slide',
            'title' => $activity?->title,
            'subtitle' => $activity?->tribe?->name,
            'data' => [
                'slide' => $slide ? [
                    'emoji' => $slide->emoji,
                    'front_label' => $slide->front_label,
                    'back_label' => $slide->back_label,
                    'phonetic' => $slide->phonetic,
                    'image_url' => $slide->image_path ? asset('storage/'.$slide->image_path) : null,
                ] : null,
            ],
        ];
    }

    protected function languagePreview(int $activityId, ?string $subItemKey): ?array
    {
        $activity = LanguageActivity::with('tribe:id,name')->find($activityId);
        if (! $activity) {
            return null;
        }

        $word = null;
        if ($subItemKey && str_starts_with($subItemKey, 'word:')) {
            $word = LanguageActivityWord::query()->find((int) substr($subItemKey, 5));
        }

        return [
            'template' => 'language_word',
            'title' => $activity->title,
            'subtitle' => ($activity->tribe?->name).' · '.(LanguageActivity::TYPES[$activity->activity_type] ?? $activity->activity_type),
            'data' => [
                'activity_type' => $activity->activity_type,
                'full_sentence' => $activity->full_sentence,
                'sentence_translation' => $activity->sentence_translation,
                'word' => $word ? [
                    'word' => $word->word,
                    'translation' => $word->translation,
                    'phonetic' => $word->phonetic,
                    'emoji' => $word->emoji,
                ] : null,
                'field_preview' => $subItemKey && str_starts_with($subItemKey, 'field:')
                    ? $this->subItems->nativeValues(OrganisationContentDecision::TYPE_LANGUAGE, $activityId, $subItemKey)
                    : null,
            ],
        ];
    }

    protected function wordSearchPreview(int $wordSearchId, ?string $subItemKey): ?array
    {
        $ws = WordSearch::with('tribe:id,name')->find($wordSearchId);
        if (! $ws) {
            return null;
        }

        $index = $subItemKey && str_starts_with($subItemKey, 'ws:') ? (int) substr($subItemKey, 3) : 0;
        $words = is_array($ws->words) ? $ws->words : [];
        $entry = $words[$index] ?? [];

        return [
            'template' => 'word_search',
            'title' => $ws->title,
            'subtitle' => $ws->tribe?->name,
            'data' => [
                'entry' => is_array($entry) ? $entry : ['word' => (string) $entry],
                'grid_size' => $ws->grid_size,
            ],
        ];
    }

    protected function culturePreview(int $cultureId, ?string $subItemKey): ?array
    {
        $activity = CultureActivity::with('tribe:id,name')->find($cultureId);
        if (! $activity) {
            return null;
        }

        $native = $subItemKey
            ? $this->subItems->nativeValues(OrganisationContentDecision::TYPE_CULTURE, $cultureId, $subItemKey)
            : null;

        return [
            'template' => 'culture',
            'title' => $activity->title,
            'subtitle' => ($activity->tribe?->name).' · '.(CultureActivity::TYPES[$activity->culture_type] ?? $activity->culture_type),
            'data' => [
                'proverb' => $activity->proverb,
                'proverb_translation' => $activity->proverb_translation,
                'clan_name' => $activity->clan_name,
                'content_excerpt' => Str::limit(strip_tags((string) $activity->content), 200),
                'selected' => $native,
                'sub_key' => $subItemKey,
            ],
        ];
    }

    protected function songPreview(int $songId, ?string $subItemKey): ?array
    {
        $song = Song::with('tribe:id,name')->find($songId);
        if (! $song) {
            return null;
        }

        $segment = ($subItemKey && str_starts_with($subItemKey, 'segment:'))
            ? SongLyricSegment::query()->where('song_id', $songId)->find((int) substr($subItemKey, 8))
            : null;

        return [
            'template' => 'song',
            'title' => $song->title,
            'subtitle' => $song->tribe?->name,
            'data' => [
                'lyrics_excerpt' => Str::limit((string) $song->lyrics, 400),
                'language' => $song->language,
                'segment' => $segment ? [
                    'text' => $segment->segment_text,
                    'blank_answer' => $segment->blank_answer,
                    'start_time' => $segment->start_time,
                    'end_time' => $segment->end_time,
                ] : null,
            ],
        ];
    }

    protected function gamePreview(int $gameId, ?string $subItemKey): ?array
    {
        $game = Game::with('tribe:id,name')->find($gameId);
        if (! $game) {
            return null;
        }

        $question = ($subItemKey && str_starts_with($subItemKey, 'question:'))
            ? GameQuestion::query()->where('game_id', $gameId)->find((int) substr($subItemKey, 9))
            : GameQuestion::query()->where('game_id', $gameId)->orderBy('order_index')->first();

        return [
            'template' => 'game_question',
            'title' => $game->title,
            'subtitle' => ($game->tribe?->name).' · '.(Game::TYPES[$game->game_type] ?? $game->game_type),
            'data' => [
                'question' => $question ? [
                    'question_text' => $question->question_text,
                    'match_text' => $question->match_text,
                    'correct_answer' => $question->correct_answer,
                    'hint' => $question->hint,
                    'emoji' => $question->question_emoji,
                ] : null,
            ],
        ];
    }

    protected function puzzlePreview(int $activityId, ?string $subItemKey): ?array
    {
        $activity = Activity::with('tribe:id,name')->find($activityId);
        if (! $activity) {
            return null;
        }

        $image = data_get($activity->metadata, 'puzzle.source_image');

        return [
            'template' => 'puzzle',
            'title' => $activity->title,
            'subtitle' => $activity->tribe?->name,
            'data' => [
                'description' => $activity->description,
                'content_tag' => data_get($activity->metadata, 'tag'),
                'image_url' => $image ? asset('storage/'.$image) : null,
                'selected' => $subItemKey ? $this->subItems->nativeValues(OrganisationContentDecision::TYPE_PUZZLE, $activityId, $subItemKey) : null,
            ],
        ];
    }

    protected function drawingPreview(int $drawingId, ?string $subItemKey): ?array
    {
        $drawing = Drawing::with('tribe:id,name')->find($drawingId);
        if (! $drawing) {
            return null;
        }

        return [
            'template' => 'drawing',
            'title' => $drawing->title,
            'subtitle' => ($drawing->tribe?->name).' · '.($drawing->drawing_type ?? 'drawing'),
            'data' => [
                'drawing_type' => $drawing->drawing_type,
                'template_url' => $drawing->template_path ? asset('storage/'.$drawing->template_path) : null,
                'preview_url' => $drawing->preview_path ? asset('storage/'.$drawing->preview_path) : null,
                'selected' => $subItemKey ? $this->subItems->nativeValues(OrganisationContentDecision::TYPE_DRAWING, $drawingId, $subItemKey) : null,
                'metadata_excerpt' => Str::limit(json_encode($drawing->metadata ?? [], JSON_UNESCAPED_UNICODE), 300),
            ],
        ];
    }

    protected function mazePreview(int $mazeId, ?string $subItemKey): ?array
    {
        $maze = Maze::with('tribe:id,name')->find($mazeId);
        if (! $maze) {
            return null;
        }

        $collectible = null;
        if ($subItemKey && str_starts_with($subItemKey, 'collectible:')) {
            $i = (int) substr($subItemKey, 12);
            $collectible = is_array($maze->collectibles) ? ($maze->collectibles[$i] ?? null) : null;
        }

        return [
            'template' => 'maze',
            'title' => $maze->title,
            'subtitle' => $maze->tribe?->name,
            'data' => [
                'hero_character' => $maze->hero_character,
                'description' => Str::limit((string) $maze->description, 200),
                'cover_url' => $maze->cover_image_path ? asset('storage/'.$maze->cover_image_path) : null,
                'collectible' => $collectible,
            ],
        ];
    }

    protected function spotDifferencePreview(int $id, ?string $subItemKey): ?array
    {
        $item = SpotDifference::with('tribe:id,name')->find($id);
        if (! $item) {
            return null;
        }

        $zone = ($subItemKey && str_starts_with($subItemKey, 'zone:'))
            ? SpotDifferenceZone::query()->where('spot_difference_id', $id)->find((int) substr($subItemKey, 5))
            : null;

        return [
            'template' => 'spot_difference',
            'title' => $item->title ?? $item->scene_name,
            'subtitle' => $item->tribe?->name,
            'data' => [
                'scene_name' => $item->scene_name,
                'image_url' => $item->image_a_path ? asset('storage/'.$item->image_a_path) : null,
                'zone' => $zone ? [
                    'label' => $zone->label,
                    'x_percent' => $zone->x_percent,
                    'y_percent' => $zone->y_percent,
                ] : null,
            ],
        ];
    }
}
