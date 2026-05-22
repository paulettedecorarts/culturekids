<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Comic;
use App\Models\CultureActivity;
use App\Models\Drawing;
use App\Models\Game;
use App\Models\LanguageActivity;
use App\Models\Maze;
use App\Models\OrganisationContentDecision;
use App\Models\Song;
use App\Models\SpotDifference;
use App\Models\Tribe;
use App\Models\User;
use App\Models\WordSearch;
use App\Support\TeacherCatalogScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TeacherApprovedCatalogService
{
    /** @var Collection<int, array<string, mixed>>|null */
    private ?Collection $itemsForCache = null;

    private ?int $itemsForCacheUserId = null;

    /** @var array<int, list<array{type: string, label: string, count: int}>>|null */
    private ?array $countsByTribeCache = null;

    private ?int $countsByTribeCacheUserId = null;

    /**
     * @return Collection<int, array{
     *     content_type: string,
     *     type_label: string,
     *     id: int,
     *     title: string,
     *     tribe_id: ?int,
     *     tribe_name: ?string,
     *     tribe_emoji: ?string,
     *     age_min: ?int,
     *     age_max: ?int,
     *     meta: ?string,
     *     view_url: ?string
     * }>
     */
    public function itemsFor(User $user): Collection
    {
        if ($this->itemsForCache !== null && $this->itemsForCacheUserId === (int) $user->id) {
            return $this->itemsForCache;
        }

        $org = $user->organisation;
        if (! $org) {
            return $this->rememberItemsFor($user, collect());
        }

        $items = collect();
        $moduleResolver = app(OrganisationModuleResolver::class);
        $orgId = (int) $org->id;

        if ($moduleResolver->isContentTypeAllowedForOrganisation($orgId, OrganisationContentDecision::TYPE_STORY)) {
            TeacherCatalogScope::comicsQueryFor($user)
                ->withCount('panels')
                ->with('tribe:id,name,hero_emoji')
                ->get(['id', 'title', 'tribe_id', 'age_min', 'age_max', 'cover_image_path'])
                ->each(function (Comic $comic) use ($items): void {
                    $items->push([
                        'content_type' => OrganisationContentDecision::TYPE_STORY,
                        'type_label' => OrganisationContentDecision::labelFor(OrganisationContentDecision::TYPE_STORY),
                        'id' => (int) $comic->id,
                        'title' => $comic->title,
                        'tribe_id' => $comic->tribe_id ? (int) $comic->tribe_id : null,
                        'tribe_name' => $comic->tribe?->name,
                        'tribe_emoji' => $comic->tribe?->hero_emoji,
                        'age_min' => $comic->age_min !== null ? (int) $comic->age_min : null,
                        'age_max' => $comic->age_max !== null ? (int) $comic->age_max : null,
                        'meta' => $comic->panels_count.' panels',
                        'view_url' => route('teacher.stories.show', $comic->id),
                        'cover_image_path' => $comic->cover_image_path,
                    ]);
                });
        }

        if ($moduleResolver->isContentTypeAllowedForOrganisation($orgId, OrganisationContentDecision::TYPE_SONG)) {
            TeacherCatalogScope::songsQueryFor($user)
                ->with('tribe:id,name,hero_emoji')
                ->get(['id', 'title', 'tribe_id', 'age_min', 'age_max'])
                ->each(function (Song $song) use ($items): void {
                    $items->push([
                        'content_type' => OrganisationContentDecision::TYPE_SONG,
                        'type_label' => OrganisationContentDecision::labelFor(OrganisationContentDecision::TYPE_SONG),
                        'id' => (int) $song->id,
                        'title' => $song->title,
                        'tribe_id' => $song->tribe_id ? (int) $song->tribe_id : null,
                        'tribe_name' => $song->tribe?->name,
                        'tribe_emoji' => $song->tribe?->hero_emoji,
                        'age_min' => $song->age_min !== null ? (int) $song->age_min : null,
                        'age_max' => $song->age_max !== null ? (int) $song->age_max : null,
                        'meta' => null,
                        'view_url' => route('teacher.library.songs.show', $song->id),
                        'cover_image_path' => null,
                    ]);
                });
        }

        $this->appendDecisionCatalogItems($items, $orgId, $moduleResolver);

        return $this->rememberItemsFor(
            $user,
            $moduleResolver
                ->filterReviewItemsForOrganisation($items, $orgId)
                ->unique(fn (array $row) => $row['content_type'].':'.$row['id'])
                ->values()
        );
    }

    /**
     * @return array<int, list<array{type: string, label: string, count: int}>>
     */
    public function countsByTribe(User $user): array
    {
        if ($this->countsByTribeCache !== null && $this->countsByTribeCacheUserId === (int) $user->id) {
            return $this->countsByTribeCache;
        }

        $org = $user->organisation;
        if (! $org) {
            return $this->rememberCountsByTribe($user, []);
        }

        $grouped = [];
        $moduleResolver = app(OrganisationModuleResolver::class);
        $orgId = (int) $org->id;

        if ($moduleResolver->isContentTypeAllowedForOrganisation($orgId, OrganisationContentDecision::TYPE_STORY)) {
            $this->mergeTribeTypeCounts(
                $grouped,
                OrganisationContentDecision::TYPE_STORY,
                TeacherCatalogScope::comicsQueryFor($user)
                    ->reorder()
                    ->whereNotNull('tribe_id')
                    ->selectRaw('tribe_id, count(*) as aggregate')
                    ->groupBy('tribe_id')
                    ->pluck('aggregate', 'tribe_id')
            );
        }

        if ($moduleResolver->isContentTypeAllowedForOrganisation($orgId, OrganisationContentDecision::TYPE_SONG)) {
            $this->mergeTribeTypeCounts(
                $grouped,
                OrganisationContentDecision::TYPE_SONG,
                TeacherCatalogScope::songsQueryFor($user)
                    ->reorder()
                    ->whereNotNull('tribe_id')
                    ->selectRaw('tribe_id, count(*) as aggregate')
                    ->groupBy('tribe_id')
                    ->pluck('aggregate', 'tribe_id')
            );
        }

        $decisionsByType = OrganisationContentDecision::query()
            ->where('organisation_id', $orgId)
            ->where('decision', OrganisationContentDecision::DECISION_APPROVED)
            ->whereNotIn('content_type', [
                OrganisationContentDecision::TYPE_STORY,
                OrganisationContentDecision::TYPE_SONG,
            ])
            ->get(['content_type', 'content_id'])
            ->groupBy('content_type');

        foreach ($decisionsByType as $contentType => $decisions) {
            if (! $moduleResolver->isContentTypeAllowedForOrganisation($orgId, (string) $contentType)) {
                continue;
            }

            $ids = $decisions->pluck('content_id')->map(fn ($id) => (int) $id)->all();
            if ($ids === []) {
                continue;
            }

            $this->mergeTribeTypeCounts(
                $grouped,
                (string) $contentType,
                $this->publishedTribeCountsForContentType((string) $contentType, $ids)
            );
        }

        return $this->rememberCountsByTribe($user, $this->formatGroupedCounts($grouped));
    }

    /**
     * @return list<int>
     */
    public function tribeIdsFor(User $user): array
    {
        return array_map('intval', array_keys($this->countsByTribe($user)));
    }

    /**
     * @return Builder<Tribe>
     */
    public function tribesQueryFor(User $user): Builder
    {
        $ids = $this->tribeIdsFor($user);

        if ($ids === []) {
            return Tribe::query()->whereRaw('0 = 1');
        }

        return Tribe::query()
            ->whereIn('id', $ids)
            ->orderBy('name');
    }

    /**
     * @return list<string>
     */
    public function contentTypesPresent(User $user): array
    {
        $present = [];

        foreach ($this->countsByTribe($user) as $rows) {
            foreach ($rows as $row) {
                $present[$row['type']] = true;
            }
        }

        return array_keys($present);
    }

    public function userCanViewItem(User $user, string $contentType, int $contentId): bool
    {
        return $this->itemsFor($user)->contains(
            fn (array $row) => $row['content_type'] === $contentType && $row['id'] === $contentId
        );
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $items
     */
    private function appendDecisionCatalogItems(Collection $items, int $orgId, OrganisationModuleResolver $moduleResolver): void
    {
        $decisionsByType = OrganisationContentDecision::query()
            ->where('organisation_id', $orgId)
            ->where('decision', OrganisationContentDecision::DECISION_APPROVED)
            ->whereNotIn('content_type', [
                OrganisationContentDecision::TYPE_STORY,
                OrganisationContentDecision::TYPE_SONG,
            ])
            ->get()
            ->groupBy('content_type');

        foreach ($decisionsByType as $contentType => $decisions) {
            if (! $moduleResolver->isContentTypeAllowedForOrganisation($orgId, (string) $contentType)) {
                continue;
            }

            $ids = $decisions->pluck('content_id')->map(fn ($id) => (int) $id)->all();
            if ($ids === []) {
                continue;
            }

            match ((string) $contentType) {
                OrganisationContentDecision::TYPE_FLASHCARD => $this->appendActivities($items, $ids, OrganisationContentDecision::TYPE_FLASHCARD, 'flashcard'),
                OrganisationContentDecision::TYPE_PUZZLE => $this->appendActivities($items, $ids, OrganisationContentDecision::TYPE_PUZZLE, 'puzzle'),
                OrganisationContentDecision::TYPE_DRAWING => $this->appendDrawings($items, $ids, colouring: false),
                OrganisationContentDecision::TYPE_COLOURING => $this->appendDrawings($items, $ids, colouring: true),
                OrganisationContentDecision::TYPE_LANGUAGE => $this->appendLanguageActivities($items, $ids),
                OrganisationContentDecision::TYPE_GAME => $this->appendSimpleModels($items, $ids, Game::class, OrganisationContentDecision::TYPE_GAME),
                OrganisationContentDecision::TYPE_MAZE => $this->appendSimpleModels($items, $ids, Maze::class, OrganisationContentDecision::TYPE_MAZE),
                OrganisationContentDecision::TYPE_SPOT_DIFFERENCE => $this->appendSimpleModels($items, $ids, SpotDifference::class, OrganisationContentDecision::TYPE_SPOT_DIFFERENCE),
                OrganisationContentDecision::TYPE_WORD_SEARCH => $this->appendSimpleModels($items, $ids, WordSearch::class, OrganisationContentDecision::TYPE_WORD_SEARCH),
                OrganisationContentDecision::TYPE_CULTURE => $this->appendSimpleModels($items, $ids, CultureActivity::class, OrganisationContentDecision::TYPE_CULTURE),
                default => null,
            };
        }
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $items
     * @param  list<int>  $ids
     */
    private function appendActivities(Collection $items, array $ids, string $contentType, string $activityType): void
    {
        Activity::query()
            ->whereIn('id', $ids)
            ->where('type', $activityType)
            ->where('is_published', true)
            ->with('tribe:id,name,hero_emoji')
            ->get(['id', 'title', 'tribe_id', 'age_range'])
            ->each(function (Activity $activity) use ($items, $contentType): void {
                $viewUrl = match ($contentType) {
                    OrganisationContentDecision::TYPE_FLASHCARD => route('teacher.library.flashcards.show', $activity->id),
                    OrganisationContentDecision::TYPE_PUZZLE => route('teacher.library.puzzles.show', $activity->id),
                    default => null,
                };

                $items->push($this->catalogPayload(
                    $contentType,
                    (int) $activity->id,
                    $activity->title,
                    $activity->tribe_id ? (int) $activity->tribe_id : null,
                    $activity->tribe?->name,
                    $activity->tribe?->hero_emoji,
                    null,
                    null,
                    $activity->age_range,
                    $viewUrl
                ));
            });
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $items
     * @param  list<int>  $ids
     */
    private function appendDrawings(Collection $items, array $ids, bool $colouring): void
    {
        $contentType = $colouring
            ? OrganisationContentDecision::TYPE_COLOURING
            : OrganisationContentDecision::TYPE_DRAWING;

        $query = Drawing::query()
            ->whereIn('id', $ids)
            ->where('status', 'published')
            ->with('tribe:id,name,hero_emoji');

        if ($colouring) {
            $query->where('drawing_type', 'coloring');
        } else {
            $query->where(function ($inner) {
                $inner->whereNull('drawing_type')
                    ->orWhere('drawing_type', '!=', 'coloring');
            });
        }

        $viewRoute = $colouring
            ? 'teacher.library.colouring.show'
            : 'teacher.library.drawings.show';

        $query
            ->get(['id', 'title', 'tribe_id'])
            ->each(function (Drawing $drawing) use ($items, $contentType, $viewRoute): void {
                $items->push($this->catalogPayload(
                    $contentType,
                    (int) $drawing->id,
                    $drawing->title,
                    $drawing->tribe_id ? (int) $drawing->tribe_id : null,
                    $drawing->tribe?->name,
                    $drawing->tribe?->hero_emoji,
                    null,
                    null,
                    null,
                    route($viewRoute, $drawing->id)
                ));
            });
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $items
     * @param  list<int>  $ids
     */
    private function appendLanguageActivities(Collection $items, array $ids): void
    {
        LanguageActivity::query()
            ->whereIn('id', $ids)
            ->where('status', 'published')
            ->with('tribe:id,name,hero_emoji')
            ->get(['id', 'title', 'tribe_id'])
            ->each(function (LanguageActivity $activity) use ($items): void {
                $items->push($this->catalogPayload(
                    OrganisationContentDecision::TYPE_LANGUAGE,
                    (int) $activity->id,
                    $activity->title,
                    $activity->tribe_id ? (int) $activity->tribe_id : null,
                    $activity->tribe?->name,
                    $activity->tribe?->hero_emoji,
                    null,
                    null,
                    null,
                    route('teacher.library.language-activities.show', $activity->id)
                ));
            });
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $items
     * @param  list<int>  $ids
     * @param  class-string  $modelClass
     */
    private function appendSimpleModels(Collection $items, array $ids, string $modelClass, string $contentType): void
    {
        $modelClass::query()
            ->whereIn('id', $ids)
            ->where('status', 'published')
            ->with('tribe:id,name,hero_emoji')
            ->get(['id', 'title', 'tribe_id'])
            ->each(function ($item) use ($items, $contentType): void {
                $viewUrl = match ($contentType) {
                    OrganisationContentDecision::TYPE_GAME => route('teacher.library.games.show', $item->id),
                    OrganisationContentDecision::TYPE_MAZE => route('teacher.library.mazes.show', $item->id),
                    OrganisationContentDecision::TYPE_SPOT_DIFFERENCE => route('teacher.library.spot-differences.show', $item->id),
                    OrganisationContentDecision::TYPE_WORD_SEARCH => route('teacher.library.word-searches.show', $item->id),
                    OrganisationContentDecision::TYPE_CULTURE => route('teacher.library.culture-activities.show', $item->id),
                    default => null,
                };

                $items->push($this->catalogPayload(
                    $contentType,
                    (int) $item->id,
                    $item->title,
                    $item->tribe_id ? (int) $item->tribe_id : null,
                    $item->tribe?->name,
                    $item->tribe?->hero_emoji,
                    null,
                    null,
                    null,
                    $viewUrl
                ));
            });
    }

    /**
     * @param  array<int, array<string, int>>  $grouped
     * @param  \Illuminate\Support\Collection<int|string, int|string>  $counts
     */
    private function mergeTribeTypeCounts(array &$grouped, string $contentType, $counts): void
    {
        foreach ($counts as $tribeId => $count) {
            if (! $tribeId) {
                continue;
            }

            $grouped[(int) $tribeId][$contentType] = ($grouped[(int) $tribeId][$contentType] ?? 0) + (int) $count;
        }
    }

    /**
     * @param  list<int>  $ids
     * @return \Illuminate\Support\Collection<int|string, int|string>
     */
    private function publishedTribeCountsForContentType(string $contentType, array $ids)
    {
        return match ($contentType) {
            OrganisationContentDecision::TYPE_FLASHCARD => Activity::query()
                ->whereIn('id', $ids)
                ->where('type', 'flashcard')
                ->where('is_published', true)
                ->whereNotNull('tribe_id')
                ->selectRaw('tribe_id, count(*) as aggregate')
                ->groupBy('tribe_id')
                ->pluck('aggregate', 'tribe_id'),
            OrganisationContentDecision::TYPE_PUZZLE => Activity::query()
                ->whereIn('id', $ids)
                ->where('type', 'puzzle')
                ->where('is_published', true)
                ->whereNotNull('tribe_id')
                ->selectRaw('tribe_id, count(*) as aggregate')
                ->groupBy('tribe_id')
                ->pluck('aggregate', 'tribe_id'),
            OrganisationContentDecision::TYPE_DRAWING => Drawing::query()
                ->whereIn('id', $ids)
                ->where('status', 'published')
                ->whereNotNull('tribe_id')
                ->where(function ($inner) {
                    $inner->whereNull('drawing_type')
                        ->orWhere('drawing_type', '!=', 'coloring');
                })
                ->selectRaw('tribe_id, count(*) as aggregate')
                ->groupBy('tribe_id')
                ->pluck('aggregate', 'tribe_id'),
            OrganisationContentDecision::TYPE_COLOURING => Drawing::query()
                ->whereIn('id', $ids)
                ->where('status', 'published')
                ->where('drawing_type', 'coloring')
                ->whereNotNull('tribe_id')
                ->selectRaw('tribe_id, count(*) as aggregate')
                ->groupBy('tribe_id')
                ->pluck('aggregate', 'tribe_id'),
            OrganisationContentDecision::TYPE_LANGUAGE => LanguageActivity::query()
                ->whereIn('id', $ids)
                ->where('status', 'published')
                ->whereNotNull('tribe_id')
                ->selectRaw('tribe_id, count(*) as aggregate')
                ->groupBy('tribe_id')
                ->pluck('aggregate', 'tribe_id'),
            OrganisationContentDecision::TYPE_GAME => Game::query()
                ->whereIn('id', $ids)
                ->where('status', 'published')
                ->whereNotNull('tribe_id')
                ->selectRaw('tribe_id, count(*) as aggregate')
                ->groupBy('tribe_id')
                ->pluck('aggregate', 'tribe_id'),
            OrganisationContentDecision::TYPE_MAZE => Maze::query()
                ->whereIn('id', $ids)
                ->where('status', 'published')
                ->whereNotNull('tribe_id')
                ->selectRaw('tribe_id, count(*) as aggregate')
                ->groupBy('tribe_id')
                ->pluck('aggregate', 'tribe_id'),
            OrganisationContentDecision::TYPE_SPOT_DIFFERENCE => SpotDifference::query()
                ->whereIn('id', $ids)
                ->where('status', 'published')
                ->whereNotNull('tribe_id')
                ->selectRaw('tribe_id, count(*) as aggregate')
                ->groupBy('tribe_id')
                ->pluck('aggregate', 'tribe_id'),
            OrganisationContentDecision::TYPE_WORD_SEARCH => WordSearch::query()
                ->whereIn('id', $ids)
                ->where('status', 'published')
                ->whereNotNull('tribe_id')
                ->selectRaw('tribe_id, count(*) as aggregate')
                ->groupBy('tribe_id')
                ->pluck('aggregate', 'tribe_id'),
            OrganisationContentDecision::TYPE_CULTURE => CultureActivity::query()
                ->whereIn('id', $ids)
                ->where('status', 'published')
                ->whereNotNull('tribe_id')
                ->selectRaw('tribe_id, count(*) as aggregate')
                ->groupBy('tribe_id')
                ->pluck('aggregate', 'tribe_id'),
            default => collect(),
        };
    }

    /**
     * @param  array<int, array<string, int>>  $grouped
     * @return array<int, list<array{type: string, label: string, count: int}>>
     */
    private function formatGroupedCounts(array $grouped): array
    {
        $out = [];

        foreach ($grouped as $tribeId => $byType) {
            $rows = [];
            foreach (OrganisationContentDecision::ALL_TYPES as $type) {
                if (! isset($byType[$type])) {
                    continue;
                }
                $rows[] = [
                    'type' => $type,
                    'label' => OrganisationContentDecision::labelFor($type),
                    'count' => $byType[$type],
                ];
            }
            $out[$tribeId] = $rows;
        }

        return $out;
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $items
     * @return Collection<int, array<string, mixed>>
     */
    private function rememberItemsFor(User $user, Collection $items): Collection
    {
        $this->itemsForCacheUserId = (int) $user->id;
        $this->itemsForCache = $items;

        return $items;
    }

    /**
     * @param  array<int, list<array{type: string, label: string, count: int}>>  $counts
     * @return array<int, list<array{type: string, label: string, count: int}>>
     */
    private function rememberCountsByTribe(User $user, array $counts): array
    {
        $this->countsByTribeCacheUserId = (int) $user->id;
        $this->countsByTribeCache = $counts;

        return $counts;
    }

    /** @return array<string, mixed> */
    private function catalogPayload(
        string $contentType,
        int $id,
        string $title,
        ?int $tribeId,
        ?string $tribeName,
        ?string $tribeEmoji,
        ?int $ageMin,
        ?int $ageMax,
        ?string $meta,
        ?string $viewUrl
    ): array {
        return [
            'content_type' => $contentType,
            'type_label' => OrganisationContentDecision::labelFor($contentType),
            'id' => $id,
            'title' => $title,
            'tribe_id' => $tribeId,
            'tribe_name' => $tribeName,
            'tribe_emoji' => $tribeEmoji,
            'age_min' => $ageMin,
            'age_max' => $ageMax,
            'meta' => $meta,
            'view_url' => $viewUrl,
            'cover_image_path' => null,
        ];
    }
}
