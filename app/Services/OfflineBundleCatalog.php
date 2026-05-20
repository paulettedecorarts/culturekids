<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Comic;
use App\Models\CultureActivity;
use App\Models\Drawing;
use App\Models\Game;
use App\Models\LanguageActivity;
use App\Models\Maze;
use App\Models\OfflineContentBundle;
use App\Models\OrganisationContentDecision;
use App\Services\OfflineBundleBuildStatus;
use App\Models\Song;
use App\Models\SpotDifference;
use App\Models\WordSearch;
use Illuminate\Support\Collection;

class OfflineBundleCatalog
{
    /**
     * @return Collection<int, array{
     *   content_type: string,
     *   type_label: string,
     *   content_id: int,
     *   title: string,
     *   tribe_name: ?string,
     *   bundle_path: ?string,
     *   bundle_hash: ?string,
     *   built_at: ?string,
     *   ready: bool
     * }>
     */
    public function publishedItems(?string $typeFilter = null, ?string $search = null): Collection
    {
        $items = collect();

        foreach ($this->sources() as $contentType => $source) {
            if ($typeFilter !== null && $typeFilter !== '' && $contentType !== $typeFilter) {
                continue;
            }

            $rows = $source();
            foreach ($rows as $row) {
                if ($search !== null && $search !== '') {
                    $hay = strtolower($row['title'].' '.($row['tribe_name'] ?? ''));
                    if (! str_contains($hay, strtolower($search))) {
                        continue;
                    }
                }
                $items->push($row);
            }
        }

        return $items->sortBy([
            ['type_label', 'asc'],
            ['title', 'asc'],
        ])->values();
    }

    /**
     * @return array<string, callable(): list<array<string, mixed>>>
     */
    private function sources(): array
    {
        return [
            OrganisationContentDecision::TYPE_STORY => fn () => $this->mapComics(),
            OrganisationContentDecision::TYPE_SONG => fn () => $this->mapSongs(),
            OrganisationContentDecision::TYPE_FLASHCARD => fn () => $this->mapActivities('flashcard', OrganisationContentDecision::TYPE_FLASHCARD),
            OrganisationContentDecision::TYPE_PUZZLE => fn () => $this->mapActivities('puzzle', OrganisationContentDecision::TYPE_PUZZLE),
            OrganisationContentDecision::TYPE_DRAWING => fn () => $this->mapDrawings('drawing', OrganisationContentDecision::TYPE_DRAWING),
            OrganisationContentDecision::TYPE_COLOURING => fn () => $this->mapDrawings('coloring', OrganisationContentDecision::TYPE_COLOURING),
            OrganisationContentDecision::TYPE_LANGUAGE => fn () => $this->mapLanguages(),
            OrganisationContentDecision::TYPE_GAME => fn () => $this->mapSimple(Game::class, OrganisationContentDecision::TYPE_GAME),
            OrganisationContentDecision::TYPE_MAZE => fn () => $this->mapSimple(Maze::class, OrganisationContentDecision::TYPE_MAZE),
            OrganisationContentDecision::TYPE_SPOT_DIFFERENCE => fn () => $this->mapSimple(SpotDifference::class, OrganisationContentDecision::TYPE_SPOT_DIFFERENCE),
            OrganisationContentDecision::TYPE_WORD_SEARCH => fn () => $this->mapSimple(WordSearch::class, OrganisationContentDecision::TYPE_WORD_SEARCH),
            OrganisationContentDecision::TYPE_CULTURE => fn () => $this->mapSimple(CultureActivity::class, OrganisationContentDecision::TYPE_CULTURE),
        ];
    }

    private function mapComics(): array
    {
        return Comic::query()
            ->with('tribe:id,name')
            ->where('status', 'published')
            ->orderBy('title')
            ->get(['id', 'title', 'tribe_id', 'bundle_path', 'bundle_hash'])
            ->map(function (Comic $c) {
                $bundle = OfflineContentBundle::forContent(OrganisationContentDecision::TYPE_STORY, (int) $c->id);

                return $this->row(
                    OrganisationContentDecision::TYPE_STORY,
                    (int) $c->id,
                    $c->title,
                    $c->tribe?->name,
                    $bundle?->bundle_path ?? $c->bundle_path,
                    $bundle?->bundle_hash ?? $c->bundle_hash,
                    $bundle
                );
            })
            ->all();
    }

    private function mapSongs(): array
    {
        return Song::query()
            ->with('tribe:id,name')
            ->where('status', 'published')
            ->orderBy('title')
            ->get(['id', 'title', 'tribe_id'])
            ->map(fn (Song $s) => $this->rowFromBundle(
                OrganisationContentDecision::TYPE_SONG,
                (int) $s->id,
                $s->title,
                $s->tribe?->name
            ))
            ->all();
    }

    private function mapActivities(string $type, string $contentType): array
    {
        return Activity::query()
            ->with('tribe:id,name')
            ->where('type', $type)
            ->where('is_published', true)
            ->orderBy('title')
            ->get(['id', 'title', 'tribe_id'])
            ->map(fn (Activity $a) => $this->rowFromBundle($contentType, (int) $a->id, $a->title, $a->tribe?->name))
            ->all();
    }

    private function mapDrawings(string $drawingType, string $contentType): array
    {
        return Drawing::query()
            ->with('tribe:id,name')
            ->where('drawing_type', $drawingType)
            ->where('status', 'published')
            ->orderBy('title')
            ->get(['id', 'title', 'tribe_id'])
            ->map(fn (Drawing $d) => $this->rowFromBundle($contentType, (int) $d->id, $d->title, $d->tribe?->name))
            ->all();
    }

    private function mapLanguages(): array
    {
        return LanguageActivity::query()
            ->with('tribe:id,name')
            ->where('status', 'published')
            ->orderBy('title')
            ->get(['id', 'title', 'tribe_id'])
            ->map(fn (LanguageActivity $a) => $this->rowFromBundle(
                OrganisationContentDecision::TYPE_LANGUAGE,
                (int) $a->id,
                $a->title,
                $a->tribe?->name
            ))
            ->all();
    }

    /**
     * @param  class-string  $modelClass
     */
    private function mapSimple(string $modelClass, string $contentType): array
    {
        return $modelClass::query()
            ->with('tribe:id,name')
            ->where('status', 'published')
            ->orderBy('title')
            ->get(['id', 'title', 'tribe_id'])
            ->map(fn ($m) => $this->rowFromBundle($contentType, (int) $m->id, $m->title, $m->tribe?->name))
            ->all();
    }

    private function rowFromBundle(string $contentType, int $contentId, string $title, ?string $tribeName): array
    {
        $bundle = OfflineContentBundle::forContent($contentType, $contentId);

        return $this->row(
            $contentType,
            $contentId,
            $title,
            $tribeName,
            $bundle?->bundle_path,
            $bundle?->bundle_hash,
            $bundle
        );
    }

    private function row(
        string $contentType,
        int $contentId,
        string $title,
        ?string $tribeName,
        ?string $bundlePath,
        ?string $bundleHash,
        ?OfflineContentBundle $bundle,
    ): array {
        $resolved = OfflineBundleBuildStatus::resolve(
            $contentType,
            $contentId,
            $bundlePath,
            $bundleHash,
            $bundle
        );

        return [
            'content_type' => $contentType,
            'type_label' => OrganisationContentDecision::labelFor($contentType),
            'content_id' => $contentId,
            'title' => $title,
            'tribe_name' => $tribeName,
            'bundle_path' => $bundlePath,
            'bundle_hash' => $bundleHash,
            'built_at' => $resolved['built_at'],
            'ready' => $resolved['ready'],
            'status' => $resolved['status'],
            'status_label' => $resolved['label'],
            'status_message' => $resolved['message'],
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $items
     * @return array{total: int, ready: int, queued: int, building: int, failed: int, not_built: int, in_progress: int}
     */
    public function summarize(Collection $items): array
    {
        $counts = [
            'total' => $items->count(),
            'ready' => 0,
            'queued' => 0,
            'building' => 0,
            'failed' => 0,
            'not_built' => 0,
        ];

        foreach ($items as $item) {
            $status = $item['status'] ?? OfflineBundleBuildStatus::NOT_BUILT;
            if ($status === OfflineBundleBuildStatus::READY) {
                $counts['ready']++;
            } elseif ($status === OfflineBundleBuildStatus::QUEUED) {
                $counts['queued']++;
            } elseif ($status === OfflineBundleBuildStatus::BUILDING) {
                $counts['building']++;
            } elseif ($status === OfflineBundleBuildStatus::FAILED) {
                $counts['failed']++;
            } else {
                $counts['not_built']++;
            }
        }

        $counts['in_progress'] = $counts['queued'] + $counts['building'];

        return $counts;
    }
}
