<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Comic;
use App\Models\OfflineContentBundle;
use App\Models\OrganisationContentDecision;
use App\Models\Song;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * Detects stale or incomplete offline bundles (especially puzzles missing tiles).
 */
class OfflineBundleFreshness
{
    /**
     * @return array{content_type: string, content_id: int}|null
     */
    public function puzzleBundleTarget(Activity $activity): ?array
    {
        if ($activity->type !== 'puzzle' || ! $activity->is_published) {
            return null;
        }

        return [
            'content_type' => OrganisationContentDecision::TYPE_PUZZLE,
            'content_id' => (int) $activity->id,
        ];
    }

    public function puzzleNeedsRebuild(Activity $activity, ?OfflineContentBundle $bundle = null): bool
    {
        if ($activity->type !== 'puzzle' || ! $activity->is_published) {
            return false;
        }

        $puzzle = data_get($activity->metadata, 'puzzle');
        if (! is_array($puzzle)) {
            return false;
        }

        if ((bool) data_get($puzzle, 'generating', false)) {
            return false;
        }

        $piecePaths = array_values(array_filter(
            $puzzle['piece_paths'] ?? [],
            fn ($path) => is_string($path) && $path !== '',
        ));

        if ($piecePaths === []) {
            return false;
        }

        $expectedAssets = count($piecePaths);
        if (is_string($puzzle['source_image'] ?? null) && $puzzle['source_image'] !== '') {
            $expectedAssets += 1;
        }

        $bundle ??= OfflineContentBundle::forContent(
            OrganisationContentDecision::TYPE_PUZZLE,
            (int) $activity->id,
        );

        if (! $bundle || ! $bundle->bundle_path) {
            return true;
        }

        if (! Storage::disk('public')->exists($bundle->bundle_path)) {
            return true;
        }

        if ((int) $bundle->asset_count < $expectedAssets) {
            return true;
        }

        $generatedAt = data_get($puzzle, 'generated_at');
        if (is_string($generatedAt) && $generatedAt !== '' && $bundle->built_at) {
            try {
                if ($bundle->built_at->lt(Carbon::parse($generatedAt))) {
                    return true;
                }
            } catch (\Throwable) {
                // ignore unparsable timestamps
            }
        }

        return false;
    }

    public function bundleNeedsRebuild(
        string $contentType,
        int $contentId,
        ?OfflineContentBundle $bundle = null,
    ): bool {
        if ($contentType === OrganisationContentDecision::TYPE_PUZZLE) {
            $activity = Activity::query()
                ->whereKey($contentId)
                ->where('type', 'puzzle')
                ->first();

            return $activity ? $this->puzzleNeedsRebuild($activity, $bundle) : false;
        }

        $bundle ??= OfflineContentBundle::forContent($contentType, $contentId);

        if (! $bundle || ! $bundle->bundle_path) {
            return true;
        }

        return ! Storage::disk('public')->exists($bundle->bundle_path);
    }

    /**
     * Rebuild synchronously when stale so download/manifest can serve a complete archive.
     */
    public function rebuildIfStale(string $contentType, int $contentId, bool $force = false): bool
    {
        if (! $this->bundleNeedsRebuild($contentType, $contentId)) {
            return false;
        }

        if (! $force) {
            $cached = OfflineBundleBuildStatus::get($contentType, $contentId);
            if ($cached !== null) {
                $status = (string) ($cached['status'] ?? '');
                if (in_array($status, [OfflineBundleBuildStatus::QUEUED, OfflineBundleBuildStatus::BUILDING], true)) {
                    return false;
                }
            }
        }

        OfflineBundleBuildStatus::markBuilding($contentType, $contentId);

        try {
            app(OfflineBundleBuilder::class)->build($contentType, $contentId);
            OfflineBundleBuildStatus::clear($contentType, $contentId);
        } catch (\Throwable $e) {
            OfflineBundleBuildStatus::markFailed($contentType, $contentId, $e->getMessage());
            throw $e;
        }

        return true;
    }

    /**
     * @return list<array{content_type: string, content_id: int}>
     */
    public function staleTargetsForTribe(int $tribeId): array
    {
        $targets = [];

        $comics = Comic::query()
            ->where('tribe_id', $tribeId)
            ->where('status', 'published')
            ->get(['id']);

        foreach ($comics as $comic) {
            if ($this->bundleNeedsRebuild(OrganisationContentDecision::TYPE_STORY, (int) $comic->id)) {
                $targets[] = [
                    'content_type' => OrganisationContentDecision::TYPE_STORY,
                    'content_id' => (int) $comic->id,
                ];
            }
        }

        $songs = Song::query()
            ->where('tribe_id', $tribeId)
            ->where('status', 'published')
            ->get(['id']);

        foreach ($songs as $song) {
            if ($this->bundleNeedsRebuild(OrganisationContentDecision::TYPE_SONG, (int) $song->id)) {
                $targets[] = [
                    'content_type' => OrganisationContentDecision::TYPE_SONG,
                    'content_id' => (int) $song->id,
                ];
            }
        }

        $puzzles = Activity::query()
            ->where('tribe_id', $tribeId)
            ->where('type', 'puzzle')
            ->where('is_published', true)
            ->get(['id', 'type', 'is_published', 'metadata']);

        foreach ($puzzles as $puzzle) {
            if ($this->puzzleNeedsRebuild($puzzle)) {
                $targets[] = [
                    'content_type' => OrganisationContentDecision::TYPE_PUZZLE,
                    'content_id' => (int) $puzzle->id,
                ];
            }
        }

        $activities = Activity::query()
            ->where('tribe_id', $tribeId)
            ->where('is_published', true)
            ->where('type', 'flashcard')
            ->get(['id', 'type']);

        foreach ($activities as $activity) {
            if ($this->bundleNeedsRebuild(OrganisationContentDecision::TYPE_FLASHCARD, (int) $activity->id)) {
                $targets[] = [
                    'content_type' => OrganisationContentDecision::TYPE_FLASHCARD,
                    'content_id' => (int) $activity->id,
                ];
            }
        }

        return $targets;
    }

    /**
     * Rebuild stale puzzle bundles before returning a tribe manifest (bounded for HTTP time).
     *
     * @return int number of puzzles rebuilt synchronously
     */
    public function refreshStalePuzzleBundlesForTribe(int $tribeId, int $maxSync = 8): int
    {
        $rebuilt = 0;

        $puzzles = Activity::query()
            ->where('tribe_id', $tribeId)
            ->where('type', 'puzzle')
            ->where('is_published', true)
            ->orderBy('id')
            ->get(['id', 'type', 'is_published', 'metadata']);

        foreach ($puzzles as $puzzle) {
            if ($rebuilt >= $maxSync) {
                break;
            }

            if (! $this->puzzleNeedsRebuild($puzzle)) {
                continue;
            }

            try {
                if ($this->rebuildIfStale(OrganisationContentDecision::TYPE_PUZZLE, (int) $puzzle->id, force: true)) {
                    $rebuilt++;
                }
            } catch (\Throwable) {
                OfflineBundleBuildStatus::markFailed(
                    OrganisationContentDecision::TYPE_PUZZLE,
                    (int) $puzzle->id,
                    'Sync rebuild failed',
                );
            }
        }

        return $rebuilt;
    }
}