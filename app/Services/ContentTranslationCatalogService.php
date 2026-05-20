<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Comic;
use App\Models\ContentTranslation;
use App\Models\CultureActivity;
use App\Models\Drawing;
use App\Models\Game;
use App\Models\LanguageActivity;
use App\Models\Maze;
use App\Models\OrganisationContentDecision;
use App\Models\Song;
use App\Models\SpotDifference;
use App\Models\WordSearch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ContentTranslationCatalogService
{
    public function __construct(
        protected ContentTranslationSubItemResolver $subItems,
    ) {}

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

        if (isset($def['query_scope'])) {
            $this->applyQueryScope($query, (string) $def['query_scope']);
        } elseif (isset($def['query']) && is_array($def['query'])) {
            $query->where($def['query']);
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
        return $this->subItems->options($contentType, $contentId);
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
        return $this->subItems->labelForKey($contentType, $key);
    }

    protected function applyQueryScope(Builder $query, string $scope): void
    {
        match ($scope) {
            'drawing_exclude_coloring' => $query->where(function (Builder $inner): void {
                $inner->whereNull('drawing_type')->orWhere('drawing_type', '!=', 'coloring');
            }),
            'drawing_coloring_only' => $query->where('drawing_type', 'coloring'),
            default => null,
        };
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
