<?php

namespace App\Services;

use App\Support\ActivityDrawingTypeFilter;
use App\Services\OfflineBundlePublisher;
use App\Models\Activity;
use App\Models\AuditLog;
use App\Models\Comic;
use App\Models\CultureActivity;
use App\Models\Drawing;
use App\Models\Game;
use App\Models\LanguageActivity;
use App\Models\Maze;
use App\Models\OrganisationComicDecision;
use App\Models\OrganisationContentDecision;
use App\Models\OrganisationSongDecision;
use App\Models\Song;
use App\Models\SpotDifference;
use App\Models\WordSearch;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OrganisationContentReviewService
{
    public function __construct(
        private readonly OrganisationModuleResolver $moduleResolver,
    ) {}
    /** @return Collection<int, array{content_type: string, type_label: string, id: int, title: string, updated_at: mixed, status: ?string}> */
    public function pendingForOrganisation(int $orgId): Collection
    {
        $items = collect();

        foreach ($this->pendingStories($orgId) as $row) {
            $items->push($this->row($row, OrganisationContentDecision::TYPE_STORY, $row->status));
        }

        foreach ($this->pendingSongs($orgId) as $row) {
            $items->push($this->row($row, OrganisationContentDecision::TYPE_SONG, $row->status));
        }

        foreach ($this->pendingActivities($orgId, OrganisationContentDecision::TYPE_FLASHCARD, 'flashcard') as $row) {
            $items->push($this->row($row, OrganisationContentDecision::TYPE_FLASHCARD));
        }

        foreach ($this->pendingActivities($orgId, OrganisationContentDecision::TYPE_PUZZLE, 'puzzle') as $row) {
            $items->push($this->row($row, OrganisationContentDecision::TYPE_PUZZLE));
        }

        foreach ($this->pendingDrawings($orgId, colouring: false) as $row) {
            $items->push($this->row($row, OrganisationContentDecision::TYPE_DRAWING, $row->status));
        }

        foreach ($this->pendingDrawings($orgId, colouring: true) as $row) {
            $items->push($this->row($row, OrganisationContentDecision::TYPE_COLOURING, $row->status));
        }

        foreach ($this->pendingLanguageActivities($orgId) as $row) {
            $items->push($this->row($row, OrganisationContentDecision::TYPE_LANGUAGE, $row->status));
        }

        foreach ($this->pendingStatusModels($orgId, OrganisationContentDecision::TYPE_GAME, Game::class) as $row) {
            $items->push($this->row($row, OrganisationContentDecision::TYPE_GAME, $row->status));
        }

        foreach ($this->pendingStatusModels($orgId, OrganisationContentDecision::TYPE_MAZE, Maze::class) as $row) {
            $items->push($this->row($row, OrganisationContentDecision::TYPE_MAZE, $row->status));
        }

        foreach ($this->pendingStatusModels($orgId, OrganisationContentDecision::TYPE_SPOT_DIFFERENCE, SpotDifference::class) as $row) {
            $items->push($this->row($row, OrganisationContentDecision::TYPE_SPOT_DIFFERENCE, $row->status));
        }

        foreach ($this->pendingStatusModels($orgId, OrganisationContentDecision::TYPE_WORD_SEARCH, WordSearch::class) as $row) {
            $items->push($this->row($row, OrganisationContentDecision::TYPE_WORD_SEARCH, $row->status));
        }

        foreach ($this->pendingStatusModels($orgId, OrganisationContentDecision::TYPE_CULTURE, CultureActivity::class) as $row) {
            $items->push($this->row($row, OrganisationContentDecision::TYPE_CULTURE, $row->status));
        }

        return $this->moduleResolver->filterReviewItemsForOrganisation(
            $this->sortPendingItems($items, 'updated_desc'),
            $orgId
        );
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $items
     * @param  array{search?: string, type?: string, tribe_id?: string|int, status?: string, sort?: string}  $filters
     * @return Collection<int, array<string, mixed>>
     */
    public function filterPendingItems(Collection $items, array $filters): Collection
    {
        $search = mb_strtolower(trim((string) ($filters['search'] ?? '')));
        $type = (string) ($filters['type'] ?? '');
        $tribeId = (string) ($filters['tribe_id'] ?? '');
        $status = (string) ($filters['status'] ?? '');
        $sort = (string) ($filters['sort'] ?? 'updated_desc');

        $filtered = $items
            ->when($search !== '', function (Collection $collection) use ($search) {
                return $collection->filter(function (array $item) use ($search) {
                    $haystack = mb_strtolower(implode(' ', array_filter([
                        $item['title'] ?? '',
                        $item['type_label'] ?? '',
                        $item['tribe_name'] ?? '',
                        $item['content_type'] ?? '',
                        $item['status'] ?? '',
                    ])));

                    return str_contains($haystack, $search);
                });
            })
            ->when($type !== '', fn (Collection $collection) => $collection->where('content_type', $type))
            ->when($tribeId !== '', fn (Collection $collection) => $collection->where('tribe_id', (int) $tribeId))
            ->when($status !== '', function (Collection $collection) use ($status) {
                return $collection->filter(fn (array $item) => $this->displayStatus($item) === $status);
            });

        return $this->sortPendingItems($filtered, $sort)->values();
    }

    /** @param  Collection<int, array<string, mixed>>  $items */
    private function sortPendingItems(Collection $items, string $sort): Collection
    {
        return match ($sort) {
            'updated_asc' => $items->sortBy(fn (array $item) => $item['updated_at']),
            'title_asc' => $items->sortBy(fn (array $item) => mb_strtolower((string) ($item['title'] ?? ''))),
            'title_desc' => $items->sortByDesc(fn (array $item) => mb_strtolower((string) ($item['title'] ?? ''))),
            default => $items->sortByDesc(fn (array $item) => $item['updated_at']),
        };
    }

    /** @param  array<string, mixed>  $item */
    private function displayStatus(array $item): string
    {
        return (string) ($item['status'] ?? 'published');
    }

    /** @return Collection<int, array{content_type: string, type_label: string, id: int, title: string, tribe: ?string, approved_by: string, approved_at: mixed, view_url: ?string}> */
    public function approvedForOrganisation(int $orgId): Collection
    {
        $decisions = OrganisationContentDecision::query()
            ->where('organisation_id', $orgId)
            ->where('decision', OrganisationContentDecision::DECISION_APPROVED)
            ->with('decidedBy:id,name')
            ->latest()
            ->limit(500)
            ->get();

        return $this->moduleResolver->filterReviewItemsForOrganisation(
            $decisions
                ->map(fn (OrganisationContentDecision $decision) => $this->hydrateApprovedRow($decision))
                ->filter()
                ->unique(fn (array $row) => $row['content_type'].':'.$row['id'])
                ->values(),
            $orgId
        );
    }

    public function approve(int $orgId, int $userId, string $contentType, int $contentId): ?string
    {
        if (! in_array($contentType, OrganisationContentDecision::ALL_TYPES, true)) {
            return null;
        }

        if (! $this->moduleResolver->isContentTypeAllowedForOrganisation($orgId, $contentType)) {
            return null;
        }

        $title = match ($contentType) {
            OrganisationContentDecision::TYPE_STORY => $this->approveStory($orgId, $userId, $contentId),
            OrganisationContentDecision::TYPE_SONG => $this->approveSong($orgId, $userId, $contentId),
            default => $this->approvePublishedItem($orgId, $userId, $contentType, $contentId),
        };

        return $title;
    }

    public function reject(int $orgId, int $userId, string $contentType, int $contentId): ?string
    {
        if (! in_array($contentType, OrganisationContentDecision::ALL_TYPES, true)) {
            return null;
        }

        if (! $this->moduleResolver->isContentTypeAllowedForOrganisation($orgId, $contentType)) {
            return null;
        }

        $title = match ($contentType) {
            OrganisationContentDecision::TYPE_STORY => $this->rejectStory($orgId, $userId, $contentId),
            OrganisationContentDecision::TYPE_SONG => $this->rejectSong($orgId, $userId, $contentId),
            default => $this->rejectPublishedItem($orgId, $userId, $contentType, $contentId),
        };

        return $title;
    }

    /** @return Collection<int, object> */
    private function pendingStories(int $orgId): Collection
    {
        return Comic::query()
            ->where(function ($q) use ($orgId) {
                $q->where(function ($q2) use ($orgId) {
                    $q2->where('status', 'review')
                        ->where(function ($h) {
                            $h->whereNull('org_id')->orWhere('org_id', 0);
                        })
                        ->whereNotIn('id', $this->decidedIds($orgId, OrganisationContentDecision::TYPE_STORY));
                })->orWhere(function ($q2) use ($orgId) {
                    $q2->where('status', 'review')
                        ->where('org_id', $orgId)
                        ->whereNotIn('id', $this->decidedIds($orgId, OrganisationContentDecision::TYPE_STORY));
                })->orWhere(function ($q2) use ($orgId) {
                    $q2->where('status', 'published')
                        ->where(function ($h) {
                            $h->whereNull('org_id')->orWhere('org_id', 0);
                        })
                        ->whereNotIn('id', $this->decidedIds($orgId, OrganisationContentDecision::TYPE_STORY));
                });
            })
            ->with('tribe:id,name')
            ->latest()
            ->limit(50)
            ->get(['id', 'title', 'updated_at', 'status', 'org_id', 'tribe_id']);
    }

    /** @return Collection<int, object> */
    private function pendingSongs(int $orgId): Collection
    {
        return Song::query()
            ->where(function ($q) use ($orgId) {
                $q->where(function ($q2) use ($orgId) {
                    $q2->where('status', 'review')
                        ->where(function ($h) {
                            $h->whereNull('org_id')->orWhere('org_id', 0);
                        })
                        ->whereNotIn('id', $this->decidedIds($orgId, OrganisationContentDecision::TYPE_SONG));
                })->orWhere(function ($q2) use ($orgId) {
                    $q2->where('status', 'review')
                        ->where('org_id', $orgId)
                        ->whereNotIn('id', $this->decidedIds($orgId, OrganisationContentDecision::TYPE_SONG));
                })->orWhere(function ($q2) use ($orgId) {
                    $q2->where('status', 'published')
                        ->where(function ($h) {
                            $h->whereNull('org_id')->orWhere('org_id', 0);
                        })
                        ->whereNotIn('id', $this->decidedIds($orgId, OrganisationContentDecision::TYPE_SONG));
                });
            })
            ->with('tribe:id,name')
            ->latest()
            ->limit(50)
            ->get(['id', 'title', 'updated_at', 'status', 'org_id', 'tribe_id']);
    }

    /** @return Collection<int, Activity> */
    private function pendingActivities(int $orgId, string $contentType, string $activityType): Collection
    {
        $exclude = $this->decidedIds($orgId, $contentType);

        return Activity::query()
            ->where('type', $activityType)
            ->where('is_published', true)
            ->when($exclude !== [], fn ($q) => $q->whereNotIn('id', $exclude))
            ->with('tribe:id,name')
            ->latest()
            ->limit(50)
            ->get(['id', 'title', 'updated_at', 'tribe_id']);
    }

    /** @return Collection<int, Drawing> */
    private function pendingDrawings(int $orgId, bool $colouring): Collection
    {
        $contentType = $colouring
            ? OrganisationContentDecision::TYPE_COLOURING
            : OrganisationContentDecision::TYPE_DRAWING;
        $exclude = $this->decidedIds($orgId, $contentType);

        return Drawing::query()
            ->where('status', 'published')
            ->when(
                $colouring,
                fn ($q) => $q->whereIn('drawing_type', ActivityDrawingTypeFilter::COLOURING_TYPES),
                fn ($q) => $q->where(function ($inner) {
                    $inner->whereNull('drawing_type')
                        ->orWhereNotIn('drawing_type', ActivityDrawingTypeFilter::COLOURING_TYPES);
                })
            )
            ->when($exclude !== [], fn ($q) => $q->whereNotIn('id', $exclude))
            ->with('tribe:id,name')
            ->latest()
            ->limit(50)
            ->get(['id', 'title', 'updated_at', 'status', 'tribe_id']);
    }

    /** @return Collection<int, LanguageActivity> */
    private function pendingLanguageActivities(int $orgId): Collection
    {
        $exclude = $this->decidedIds($orgId, OrganisationContentDecision::TYPE_LANGUAGE);

        return LanguageActivity::query()
            ->where('status', 'published')
            ->when($exclude !== [], fn ($q) => $q->whereNotIn('id', $exclude))
            ->with('tribe:id,name')
            ->latest()
            ->limit(50)
            ->get(['id', 'title', 'updated_at', 'status', 'tribe_id']);
    }

    /**
     * @param  class-string  $modelClass
     * @return Collection<int, object>
     */
    private function pendingStatusModels(int $orgId, string $contentType, string $modelClass): Collection
    {
        $exclude = $this->decidedIds($orgId, $contentType);

        return $modelClass::query()
            ->where('status', 'published')
            ->when($exclude !== [], fn ($q) => $q->whereNotIn('id', $exclude))
            ->with('tribe:id,name')
            ->latest()
            ->limit(50)
            ->get(['id', 'title', 'updated_at', 'status', 'tribe_id']);
    }

    /** @return list<int> */
    private function decidedIds(int $orgId, string $contentType): array
    {
        return OrganisationContentDecision::query()
            ->where('organisation_id', $orgId)
            ->where('content_type', $contentType)
            ->pluck('content_id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    /** @param  object  $model */
    private function row(object $model, string $contentType, ?string $status = null): array
    {
        $tribeId = isset($model->tribe_id) && $model->tribe_id ? (int) $model->tribe_id : null;
        $tribeName = null;
        if ($model->relationLoaded('tribe') && $model->tribe) {
            $tribeName = (string) $model->tribe->name;
        }

        return [
            'content_type' => $contentType,
            'type_label' => OrganisationContentDecision::labelFor($contentType),
            'id' => (int) $model->id,
            'title' => (string) $model->title,
            'tribe_id' => $tribeId,
            'tribe_name' => $tribeName,
            'updated_at' => $model->updated_at,
            'status' => $status ?? 'published',
        ];
    }

    private function approveStory(int $orgId, int $userId, int $comicId): ?string
    {
        $comic = Comic::query()->whereKey($comicId)->first();
        if (! $comic || ! $this->storyIsPending($comic, $orgId)) {
            return null;
        }

        DB::transaction(function () use ($comic, $orgId, $userId): void {
            $this->recordDecision($orgId, $userId, OrganisationContentDecision::TYPE_STORY, $comic->id, OrganisationContentDecision::DECISION_APPROVED);
            $this->syncLegacyComicDecision($orgId, $userId, $comic->id, OrganisationComicDecision::DECISION_APPROVED);

            if ($comic->status === 'review') {
                $comic->update(['status' => 'published']);
                OfflineBundlePublisher::queue(OrganisationContentDecision::TYPE_STORY, $comic->id);
                HandlePublishedContentSideEffects::dispatch(null, comicId: $comic->id);
            }
        });

        AuditLog::record('APPROVE_COMIC', "comics/{$comic->id}", ['status' => 'published', 'organisation_id' => $orgId]);

        return $comic->title;
    }

    private function rejectStory(int $orgId, int $userId, int $comicId): ?string
    {
        $comic = Comic::query()->whereKey($comicId)->first();
        if (! $comic || ! $this->storyIsPending($comic, $orgId)) {
            return null;
        }

        DB::transaction(function () use ($comic, $orgId, $userId): void {
            $this->recordDecision($orgId, $userId, OrganisationContentDecision::TYPE_STORY, $comic->id, OrganisationContentDecision::DECISION_REJECTED);
            $this->syncLegacyComicDecision($orgId, $userId, $comic->id, OrganisationComicDecision::DECISION_REJECTED);

            $own = (int) ($comic->org_id ?? 0) === $orgId;
            if ($own && $comic->status === 'review') {
                $comic->update(['status' => 'draft']);
            }
        });

        AuditLog::record('REJECT_COMIC', "comics/{$comic->id}", ['organisation_id' => $orgId]);

        return $comic->title;
    }

    private function approveSong(int $orgId, int $userId, int $songId): ?string
    {
        $song = Song::query()->whereKey($songId)->first();
        if (! $song || ! $this->songIsPending($song, $orgId)) {
            return null;
        }

        DB::transaction(function () use ($song, $orgId, $userId): void {
            $this->recordDecision($orgId, $userId, OrganisationContentDecision::TYPE_SONG, $song->id, OrganisationContentDecision::DECISION_APPROVED);
            $this->syncLegacySongDecision($orgId, $userId, $song->id, OrganisationSongDecision::DECISION_APPROVED);

            if ($song->status === 'review') {
                $song->update(['status' => 'published']);
                OfflineBundlePublisher::queue(OrganisationContentDecision::TYPE_SONG, $song->id);
                HandlePublishedContentSideEffects::dispatch(null, songId: $song->id);
            }
        });

        AuditLog::record('APPROVE_SONG', "songs/{$song->id}", ['organisation_id' => $orgId]);

        return $song->title;
    }

    private function rejectSong(int $orgId, int $userId, int $songId): ?string
    {
        $song = Song::query()->whereKey($songId)->first();
        if (! $song || ! $this->songIsPending($song, $orgId)) {
            return null;
        }

        DB::transaction(function () use ($song, $orgId, $userId): void {
            $this->recordDecision($orgId, $userId, OrganisationContentDecision::TYPE_SONG, $song->id, OrganisationContentDecision::DECISION_REJECTED);
            $this->syncLegacySongDecision($orgId, $userId, $song->id, OrganisationSongDecision::DECISION_REJECTED);

            $own = (int) ($song->org_id ?? 0) === $orgId;
            if ($own && $song->status === 'review') {
                $song->update(['status' => 'draft']);
            }
        });

        AuditLog::record('REJECT_SONG', "songs/{$song->id}", ['organisation_id' => $orgId]);

        return $song->title;
    }

    private function approvePublishedItem(int $orgId, int $userId, string $contentType, int $contentId): ?string
    {
        $item = $this->findPublishedItem($contentType, $contentId);
        if (! $item || in_array($contentId, $this->decidedIds($orgId, $contentType), true)) {
            return null;
        }

        $this->recordDecision($orgId, $userId, $contentType, $contentId, OrganisationContentDecision::DECISION_APPROVED);
        OfflineBundlePublisher::queue($contentType, $contentId);
        AuditLog::record('APPROVE_CONTENT', "{$contentType}/{$contentId}", ['organisation_id' => $orgId]);

        return (string) $item->title;
    }

    private function rejectPublishedItem(int $orgId, int $userId, string $contentType, int $contentId): ?string
    {
        $item = $this->findPublishedItem($contentType, $contentId);
        if (! $item || in_array($contentId, $this->decidedIds($orgId, $contentType), true)) {
            return null;
        }

        $this->recordDecision($orgId, $userId, $contentType, $contentId, OrganisationContentDecision::DECISION_REJECTED);
        AuditLog::record('REJECT_CONTENT', "{$contentType}/{$contentId}", ['organisation_id' => $orgId]);

        return (string) $item->title;
    }

    private function findPublishedItem(string $contentType, int $contentId): ?object
    {
        return match ($contentType) {
            OrganisationContentDecision::TYPE_FLASHCARD,
            OrganisationContentDecision::TYPE_PUZZLE => Activity::query()
                ->whereKey($contentId)
                ->where('type', $contentType === OrganisationContentDecision::TYPE_FLASHCARD ? 'flashcard' : 'puzzle')
                ->where('is_published', true)
                ->first(['id', 'title']),
            OrganisationContentDecision::TYPE_DRAWING => Drawing::query()
                ->whereKey($contentId)
                ->where('status', 'published')
                ->where(function ($q) {
                    $q->whereNull('drawing_type')
                        ->orWhereNotIn('drawing_type', ActivityDrawingTypeFilter::COLOURING_TYPES);
                })
                ->first(['id', 'title']),
            OrganisationContentDecision::TYPE_COLOURING => Drawing::query()
                ->whereKey($contentId)
                ->where('status', 'published')
                ->whereIn('drawing_type', ActivityDrawingTypeFilter::COLOURING_TYPES)
                ->first(['id', 'title']),
            OrganisationContentDecision::TYPE_LANGUAGE => LanguageActivity::query()
                ->whereKey($contentId)
                ->where('status', 'published')
                ->first(['id', 'title']),
            OrganisationContentDecision::TYPE_GAME => Game::query()->whereKey($contentId)->where('status', 'published')->first(['id', 'title']),
            OrganisationContentDecision::TYPE_MAZE => Maze::query()->whereKey($contentId)->where('status', 'published')->first(['id', 'title']),
            OrganisationContentDecision::TYPE_SPOT_DIFFERENCE => SpotDifference::query()->whereKey($contentId)->where('status', 'published')->first(['id', 'title']),
            OrganisationContentDecision::TYPE_WORD_SEARCH => WordSearch::query()->whereKey($contentId)->where('status', 'published')->first(['id', 'title']),
            OrganisationContentDecision::TYPE_CULTURE => CultureActivity::query()->whereKey($contentId)->where('status', 'published')->first(['id', 'title']),
            default => null,
        };
    }

    private function storyIsPending(Comic $comic, int $orgId): bool
    {
        if (in_array((int) $comic->id, $this->decidedIds($orgId, OrganisationContentDecision::TYPE_STORY), true)) {
            return false;
        }

        $shared = $comic->org_id === null || (int) $comic->org_id === 0;
        $own = (int) ($comic->org_id ?? 0) === $orgId;

        return ($comic->status === 'review' && ($shared || $own))
            || ($comic->status === 'published' && $shared);
    }

    private function songIsPending(Song $song, int $orgId): bool
    {
        if (in_array((int) $song->id, $this->decidedIds($orgId, OrganisationContentDecision::TYPE_SONG), true)) {
            return false;
        }

        $shared = $song->org_id === null || (int) $song->org_id === 0;
        $own = (int) ($song->org_id ?? 0) === $orgId;

        return ($song->status === 'review' && ($shared || $own))
            || ($song->status === 'published' && $shared);
    }

    private function recordDecision(int $orgId, int $userId, string $contentType, int $contentId, string $decision): void
    {
        OrganisationContentDecision::updateOrCreate(
            [
                'organisation_id' => $orgId,
                'content_type' => $contentType,
                'content_id' => $contentId,
            ],
            [
                'decision' => $decision,
                'decided_by' => $userId,
            ]
        );
    }

    private function syncLegacyComicDecision(int $orgId, int $userId, int $comicId, string $decision): void
    {
        OrganisationComicDecision::updateOrCreate(
            ['organisation_id' => $orgId, 'comic_id' => $comicId],
            ['decision' => $decision, 'decided_by' => $userId]
        );
    }

    private function syncLegacySongDecision(int $orgId, int $userId, int $songId, string $decision): void
    {
        OrganisationSongDecision::updateOrCreate(
            ['organisation_id' => $orgId, 'song_id' => $songId],
            ['decision' => $decision, 'decided_by' => $userId]
        );
    }

    /** @return ?array{content_type: string, type_label: string, id: int, title: string, tribe: ?string, approved_by: string, approved_at: mixed, view_url: ?string} */
    private function hydrateApprovedRow(OrganisationContentDecision $decision): ?array
    {
        $payload = match ($decision->content_type) {
            OrganisationContentDecision::TYPE_STORY => $this->hydrateStory($decision),
            OrganisationContentDecision::TYPE_SONG => $this->hydrateSong($decision),
            OrganisationContentDecision::TYPE_FLASHCARD,
            OrganisationContentDecision::TYPE_PUZZLE => $this->hydrateActivity($decision),
            OrganisationContentDecision::TYPE_DRAWING,
            OrganisationContentDecision::TYPE_COLOURING => $this->hydrateDrawing($decision),
            OrganisationContentDecision::TYPE_LANGUAGE => $this->hydrateLanguage($decision),
            OrganisationContentDecision::TYPE_GAME => $this->hydrateSimple($decision, Game::class),
            OrganisationContentDecision::TYPE_MAZE => $this->hydrateSimple($decision, Maze::class),
            OrganisationContentDecision::TYPE_SPOT_DIFFERENCE => $this->hydrateSimple($decision, SpotDifference::class),
            OrganisationContentDecision::TYPE_WORD_SEARCH => $this->hydrateSimple($decision, WordSearch::class),
            OrganisationContentDecision::TYPE_CULTURE => $this->hydrateSimple($decision, CultureActivity::class),
            default => null,
        };

        return $payload;
    }

    /** @return ?array<string, mixed> */
    private function hydrateStory(OrganisationContentDecision $decision): ?array
    {
        $comic = Comic::query()->with('tribe:id,name')->find($decision->content_id);
        if (! $comic || $comic->status !== 'published') {
            return null;
        }

        return $this->approvedPayload($decision, $comic->title, $comic->tribe?->name, route('cms.admin.approved-content.stories.show', ['id' => $comic->id]));
    }

    /** @return ?array<string, mixed> */
    private function hydrateSong(OrganisationContentDecision $decision): ?array
    {
        $song = Song::query()->with('tribe:id,name')->find($decision->content_id);
        if (! $song || $song->status !== 'published') {
            return null;
        }

        return $this->approvedPayload($decision, $song->title, $song->tribe?->name, route('cms.admin.approved-content.songs.show', ['id' => $song->id]));
    }

    /** @return ?array<string, mixed> */
    private function hydrateActivity(OrganisationContentDecision $decision): ?array
    {
        $activity = Activity::query()->with('tribe:id,name')->find($decision->content_id);
        if (! $activity || ! $activity->is_published) {
            return null;
        }

        $viewUrl = match ($decision->content_type) {
            OrganisationContentDecision::TYPE_FLASHCARD => route('cms.admin.approved-content.flashcards.show', ['id' => $activity->id]),
            OrganisationContentDecision::TYPE_PUZZLE => route('cms.admin.approved-content.puzzles.show', ['id' => $activity->id]),
            default => null,
        };

        return $this->approvedPayload($decision, $activity->title, $activity->tribe?->name, $viewUrl);
    }

    /** @return ?array<string, mixed> */
    private function hydrateDrawing(OrganisationContentDecision $decision): ?array
    {
        $drawing = Drawing::query()->with('tribe:id,name')->find($decision->content_id);
        if (! $drawing || $drawing->status !== 'published') {
            return null;
        }

        return $this->approvedPayload(
            $decision,
            $drawing->title,
            $drawing->tribe?->name,
            route('cms.admin.approved-content.drawings.show', ['id' => $drawing->id])
        );
    }

    /** @return ?array<string, mixed> */
    private function hydrateLanguage(OrganisationContentDecision $decision): ?array
    {
        $activity = LanguageActivity::query()->with('tribe:id,name')->find($decision->content_id);
        if (! $activity || $activity->status !== 'published') {
            return null;
        }

        return $this->approvedPayload(
            $decision,
            $activity->title,
            $activity->tribe?->name,
            route('cms.admin.approved-content.language-activities.show', ['id' => $activity->id])
        );
    }

    /**
     * @param  class-string  $modelClass
     * @return ?array<string, mixed>
     */
    private function hydrateSimple(OrganisationContentDecision $decision, string $modelClass): ?array
    {
        $item = $modelClass::query()->with('tribe:id,name')->find($decision->content_id);
        if (! $item || $item->status !== 'published') {
            return null;
        }

        $viewUrl = match ($decision->content_type) {
            OrganisationContentDecision::TYPE_GAME => route('cms.admin.approved-content.games.show', ['id' => $item->id]),
            OrganisationContentDecision::TYPE_MAZE => route('cms.admin.approved-content.mazes.show', ['id' => $item->id]),
            OrganisationContentDecision::TYPE_SPOT_DIFFERENCE => route('cms.admin.approved-content.spot-differences.show', ['id' => $item->id]),
            OrganisationContentDecision::TYPE_WORD_SEARCH => route('cms.admin.approved-content.word-searches.show', ['id' => $item->id]),
            OrganisationContentDecision::TYPE_CULTURE => route('cms.admin.approved-content.culture-activities.show', ['id' => $item->id]),
            default => null,
        };

        return $this->approvedPayload($decision, $item->title, $item->tribe?->name, $viewUrl);
    }

    /** @return array<string, mixed> */
    private function approvedPayload(
        OrganisationContentDecision $decision,
        string $title,
        ?string $tribe,
        ?string $viewUrl = null
    ): array {
        return [
            'content_type' => $decision->content_type,
            'type_label' => OrganisationContentDecision::labelFor($decision->content_type),
            'id' => (int) $decision->content_id,
            'title' => $title,
            'tribe' => $tribe,
            'approved_by' => $decision->decidedBy?->name ?? 'Admin',
            'approved_at' => $decision->created_at,
            'view_url' => $viewUrl,
        ];
    }
}
