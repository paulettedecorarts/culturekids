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
        $org = $user->organisation;
        if (! $org) {
            return collect();
        }

        $items = collect();

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

        OrganisationContentDecision::query()
            ->where('organisation_id', $org->id)
            ->where('decision', OrganisationContentDecision::DECISION_APPROVED)
            ->whereNotIn('content_type', [
                OrganisationContentDecision::TYPE_STORY,
                OrganisationContentDecision::TYPE_SONG,
            ])
            ->get()
            ->map(fn (OrganisationContentDecision $decision) => $this->hydrateDecisionItem($decision))
            ->filter()
            ->each(fn (array $row) => $items->push($row));

        return $items
            ->unique(fn (array $row) => $row['content_type'].':'.$row['id'])
            ->values();
    }

    /**
     * @return array<int, list<array{type: string, label: string, count: int}>>
     */
    public function countsByTribe(User $user): array
    {
        $grouped = [];

        foreach ($this->itemsFor($user) as $item) {
            $tribeId = $item['tribe_id'] ?? null;
            if (! $tribeId) {
                continue;
            }

            $type = $item['content_type'];
            $grouped[$tribeId][$type] = ($grouped[$tribeId][$type] ?? 0) + 1;
        }

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
     * @return list<int>
     */
    public function tribeIdsFor(User $user): array
    {
        return $this->itemsFor($user)
            ->pluck('tribe_id')
            ->filter()
            ->unique()
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
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
        return $this->itemsFor($user)
            ->pluck('content_type')
            ->unique()
            ->values()
            ->all();
    }

    public function userCanViewItem(User $user, string $contentType, int $contentId): bool
    {
        return $this->itemsFor($user)->contains(
            fn (array $row) => $row['content_type'] === $contentType && $row['id'] === $contentId
        );
    }

    /** @return ?array<string, mixed> */
    private function hydrateDecisionItem(OrganisationContentDecision $decision): ?array
    {
        return match ($decision->content_type) {
            OrganisationContentDecision::TYPE_FLASHCARD,
            OrganisationContentDecision::TYPE_PUZZLE => $this->hydrateActivity($decision),
            OrganisationContentDecision::TYPE_DRAWING,
            OrganisationContentDecision::TYPE_COLOURING => $this->hydrateDrawing($decision),
            OrganisationContentDecision::TYPE_LANGUAGE => $this->hydrateLanguage($decision),
            OrganisationContentDecision::TYPE_GAME => $this->hydrateSimple($decision, Game::class, OrganisationContentDecision::TYPE_GAME),
            OrganisationContentDecision::TYPE_MAZE => $this->hydrateSimple($decision, Maze::class, OrganisationContentDecision::TYPE_MAZE),
            OrganisationContentDecision::TYPE_SPOT_DIFFERENCE => $this->hydrateSimple($decision, SpotDifference::class, OrganisationContentDecision::TYPE_SPOT_DIFFERENCE),
            OrganisationContentDecision::TYPE_WORD_SEARCH => $this->hydrateSimple($decision, WordSearch::class, OrganisationContentDecision::TYPE_WORD_SEARCH),
            OrganisationContentDecision::TYPE_CULTURE => $this->hydrateSimple($decision, CultureActivity::class, OrganisationContentDecision::TYPE_CULTURE),
            default => null,
        };
    }

    /** @return ?array<string, mixed> */
    private function hydrateActivity(OrganisationContentDecision $decision): ?array
    {
        $activity = Activity::query()
            ->with('tribe:id,name,hero_emoji')
            ->find($decision->content_id);

        if (! $activity || ! $activity->is_published) {
            return null;
        }

        $viewUrl = match ($decision->content_type) {
            OrganisationContentDecision::TYPE_FLASHCARD => route('teacher.library.flashcards.show', $activity->id),
            OrganisationContentDecision::TYPE_PUZZLE => route('teacher.library.puzzles.show', $activity->id),
            default => null,
        };

        return $this->catalogPayload(
            $decision->content_type,
            (int) $activity->id,
            $activity->title,
            $activity->tribe_id ? (int) $activity->tribe_id : null,
            $activity->tribe?->name,
            $activity->tribe?->hero_emoji,
            null,
            null,
            $activity->age_range,
            $viewUrl
        );
    }

    /** @return ?array<string, mixed> */
    private function hydrateDrawing(OrganisationContentDecision $decision): ?array
    {
        $drawing = Drawing::query()
            ->with('tribe:id,name,hero_emoji')
            ->find($decision->content_id);

        if (! $drawing || $drawing->status !== 'published') {
            return null;
        }

        if ($decision->content_type === OrganisationContentDecision::TYPE_COLOURING
            && $drawing->drawing_type !== 'coloring') {
            return null;
        }

        if ($decision->content_type === OrganisationContentDecision::TYPE_DRAWING
            && $drawing->drawing_type === 'coloring') {
            return null;
        }

        $viewRoute = $decision->content_type === OrganisationContentDecision::TYPE_COLOURING
            ? 'teacher.library.colouring.show'
            : 'teacher.library.drawings.show';

        return $this->catalogPayload(
            $decision->content_type,
            (int) $drawing->id,
            $drawing->title,
            $drawing->tribe_id ? (int) $drawing->tribe_id : null,
            $drawing->tribe?->name,
            $drawing->tribe?->hero_emoji,
            null,
            null,
            null,
            route($viewRoute, $drawing->id)
        );
    }

    /** @return ?array<string, mixed> */
    private function hydrateLanguage(OrganisationContentDecision $decision): ?array
    {
        $activity = LanguageActivity::query()
            ->with('tribe:id,name,hero_emoji')
            ->find($decision->content_id);

        if (! $activity || $activity->status !== 'published') {
            return null;
        }

        return $this->catalogPayload(
            $decision->content_type,
            (int) $activity->id,
            $activity->title,
            $activity->tribe_id ? (int) $activity->tribe_id : null,
            $activity->tribe?->name,
            $activity->tribe?->hero_emoji,
            null,
            null,
            null,
            route('teacher.library.language-activities.show', $activity->id)
        );
    }

    /**
     * @param  class-string  $modelClass
     * @return ?array<string, mixed>
     */
    private function hydrateSimple(
        OrganisationContentDecision $decision,
        string $modelClass,
        string $contentType
    ): ?array {
        $item = $modelClass::query()
            ->with('tribe:id,name,hero_emoji')
            ->find($decision->content_id);

        if (! $item || $item->status !== 'published') {
            return null;
        }

        $viewUrl = match ($contentType) {
            OrganisationContentDecision::TYPE_GAME => route('teacher.library.games.show', $item->id),
            OrganisationContentDecision::TYPE_MAZE => route('teacher.library.mazes.show', $item->id),
            OrganisationContentDecision::TYPE_SPOT_DIFFERENCE => route('teacher.library.spot-differences.show', $item->id),
            OrganisationContentDecision::TYPE_WORD_SEARCH => route('teacher.library.word-searches.show', $item->id),
            OrganisationContentDecision::TYPE_CULTURE => route('teacher.library.culture-activities.show', $item->id),
            default => null,
        };

        return $this->catalogPayload(
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
        );
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
