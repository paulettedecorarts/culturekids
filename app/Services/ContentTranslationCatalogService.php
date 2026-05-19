<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\ActivityFlashcardSlide;
use App\Models\Comic;
use App\Models\ComicPanel;
use App\Models\ContentTranslation;
use App\Models\CultureActivity;
use App\Models\Drawing;
use App\Models\Game;
use App\Models\LanguageActivity;
use App\Models\LanguageActivityWord;
use App\Models\Maze;
use App\Models\OrganisationContentDecision;
use App\Models\Song;
use App\Models\SpotDifference;
use App\Models\WordSearch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ContentTranslationCatalogService
{
    /** @return array<string, string> */
    public function typeOptions(): array
    {
        $options = [];
        foreach (config('content_translations.types', []) as $key => $def) {
            $options[$key] = $def['label'] ?? OrganisationContentDecision::labelFor($key);
        }

        return $options;
    }

    /** @return Collection<int, object{id: int, label: string, tribe_name: ?string}> */
    public function contentItemsForType(string $contentType, ?int $orgId, bool $isSuperAdmin): Collection
    {
        $def = config('content_translations.types.'.$contentType);
        if (! $def || ! isset($def['model'])) {
            return collect();
        }

        /** @var class-string<\Illuminate\Database\Eloquent\Model> $modelClass */
        $modelClass = $def['model'];
        $titleCol = $def['title_column'] ?? 'title';

        $query = $modelClass::query()->with('tribe:id,name');

        if (isset($def['query'])) {
            $filter = $def['query'];
            if (is_callable($filter)) {
                $filter($query);
            } elseif (is_array($filter)) {
                $query->where($filter);
            }
        }

        $this->applyOrgScope($query, $contentType, $orgId, $isSuperAdmin);
        $this->applyPublishedScope($query, $contentType);

        return $query
            ->orderByDesc('updated_at')
            ->limit(250)
            ->get()
            ->map(fn ($row) => (object) [
                'id' => $row->id,
                'label' => $row->{$titleCol},
                'tribe_name' => $row->tribe?->name,
            ]);
    }

    /**
     * @return array<int, array{key: string, label: string}>
     */
    public function subItemOptions(string $contentType, int $contentId): array
    {
        return match ($contentType) {
            OrganisationContentDecision::TYPE_STORY => ComicPanel::query()
                ->where('comic_id', $contentId)
                ->orderBy('order_index')
                ->get()
                ->map(fn (ComicPanel $panel) => [
                    'key' => 'panel:'.$panel->id,
                    'label' => 'Panel '.((int) $panel->order_index + 1)
                        .($panel->caption ? ' — '.\Illuminate\Support\Str::limit($panel->caption, 50) : ''),
                    'panel_id' => $panel->id,
                ])
                ->all(),
            OrganisationContentDecision::TYPE_FLASHCARD => ActivityFlashcardSlide::query()
                ->where('activity_id', $contentId)
                ->orderBy('order_index')
                ->get()
                ->map(fn (ActivityFlashcardSlide $slide) => [
                    'key' => 'slide:'.$slide->id,
                    'label' => 'Card '.((int) $slide->order_index + 1)
                        .($slide->front_label ? ' — '.$slide->front_label : ''),
                ])
                ->all(),
            OrganisationContentDecision::TYPE_LANGUAGE => LanguageActivityWord::query()
                ->where('language_activity_id', $contentId)
                ->orderBy('order_index')
                ->get()
                ->map(fn (LanguageActivityWord $word) => [
                    'key' => 'word:'.$word->id,
                    'label' => $word->word.($word->translation ? ' → '.$word->translation : ''),
                ])
                ->all(),
            OrganisationContentDecision::TYPE_WORD_SEARCH => $this->wordSearchSubItems($contentId),
            default => [],
        };
    }

    public function contextLabel(ContentTranslation $translation): string
    {
        $typeLabel = $translation->typeLabel();
        $title = $this->resolveContentTitle($translation->content_type, (int) $translation->content_id);

        $parts = array_filter([$typeLabel, $title]);

        if ($translation->content_type === OrganisationContentDecision::TYPE_STORY && $translation->panel) {
            $parts[] = 'Panel '.((int) $translation->panel->order_index + 1);
        } elseif ($translation->sub_item_key) {
            $parts[] = $this->subItemKeyLabel($translation->content_type, $translation->sub_item_key);
        }

        return implode(' · ', $parts) ?: $translation->word;
    }

    public function resolveContentTitle(string $contentType, int $contentId): ?string
    {
        $def = config('content_translations.types.'.$contentType);
        if (! $def || ! isset($def['model'])) {
            return null;
        }

        $modelClass = $def['model'];
        $titleCol = $def['title_column'] ?? 'title';

        return $modelClass::query()->whereKey($contentId)->value($titleCol);
    }

    protected function subItemKeyLabel(string $contentType, string $key): string
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
            return 'Word list #'.(substr($key, 3) + 1);
        }

        return $key;
    }

    /** @return array<int, array{key: string, label: string}> */
    protected function wordSearchSubItems(int $wordSearchId): array
    {
        $words = WordSearch::query()->whereKey($wordSearchId)->value('words');
        if (! is_array($words)) {
            return [];
        }

        $options = [];
        foreach ($words as $index => $entry) {
            $word = is_array($entry) ? ($entry['word'] ?? '') : (string) $entry;
            if ($word === '') {
                continue;
            }
            $options[] = [
                'key' => 'ws:'.$index,
                'label' => $word.(is_array($entry) && ! empty($entry['translation']) ? ' → '.$entry['translation'] : ''),
            ];
        }

        return $options;
    }

    protected function applyOrgScope(Builder $query, string $contentType, ?int $orgId, bool $isSuperAdmin): void
    {
        if ($isSuperAdmin || ! $orgId) {
            return;
        }

        if ($contentType === OrganisationContentDecision::TYPE_STORY) {
            $query->where(fn ($q) => $q->where('org_id', $orgId)->orWhereNull('org_id'));

            return;
        }

        if (method_exists($query->getModel(), 'tribe')) {
            $query->whereHas('tribe', fn ($t) => $t->where('org_id', $orgId)->orWhereNull('org_id'));
        }
    }

    protected function applyPublishedScope(Builder $query, string $contentType): void
    {
        $model = $query->getModel();

        if ($model instanceof Comic) {
            $query->where('status', 'published');
        } elseif ($model instanceof Activity) {
            $query->where('is_published', true);
        } elseif ($model instanceof Song
            || $model instanceof Drawing
            || $model instanceof LanguageActivity
            || $model instanceof Game
            || $model instanceof Maze
            || $model instanceof SpotDifference
            || $model instanceof WordSearch
            || $model instanceof CultureActivity) {
            $query->where('status', 'published');
        }
    }
}
