<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Comic;
use App\Models\CultureActivity;
use App\Support\CultureApiSerializer;
use App\Support\GameApiSerializer;
use App\Models\Drawing;
use App\Models\Game;
use App\Models\LanguageActivity;
use App\Models\Maze;
use App\Models\OfflineContentBundle;
use App\Models\OrganisationContentDecision;
use App\Models\Song;
use App\Models\SpotDifference;
use App\Models\WordSearch;
use App\Support\OfflineBundle\OfflineBundleAssetCollector;
use App\Support\OfflineBundle\OfflineBundleZipWriter;
use App\Support\PanelVocabTagSerializer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class OfflineBundleBuilder
{
    public const SCHEMA_V2 = 'culturekids.bundle.v2';

    private ?string $tempZipPath = null;

    public function __construct(
        private readonly OfflineBundleAssetCollector $assetCollector = new OfflineBundleAssetCollector,
    ) {}

    /**
     * @return array{bundle_path: string, bundle_hash: string, asset_count: int, bytes: int, schema: string}
     */
    public function build(string $contentType, int $contentId): array
    {
        if (! in_array($contentType, OrganisationContentDecision::ALL_TYPES, true)) {
            throw new \InvalidArgumentException("Unknown offline content type: {$contentType}");
        }

        $result = match ($contentType) {
            OrganisationContentDecision::TYPE_STORY => $this->buildStory($contentId),
            OrganisationContentDecision::TYPE_SONG => $this->buildSong($contentId),
            OrganisationContentDecision::TYPE_FLASHCARD => $this->buildActivity($contentId, 'flashcard'),
            OrganisationContentDecision::TYPE_PUZZLE => $this->buildActivity($contentId, 'puzzle'),
            OrganisationContentDecision::TYPE_DRAWING => $this->buildDrawing($contentId, colouring: false),
            OrganisationContentDecision::TYPE_COLOURING => $this->buildDrawing($contentId, colouring: true),
            OrganisationContentDecision::TYPE_LANGUAGE => $this->buildLanguage($contentId),
            OrganisationContentDecision::TYPE_GAME => $this->buildGame($contentId),
            OrganisationContentDecision::TYPE_MAZE => $this->buildMaze($contentId),
            OrganisationContentDecision::TYPE_SPOT_DIFFERENCE => $this->buildSpotDifference($contentId),
            OrganisationContentDecision::TYPE_WORD_SEARCH => $this->buildWordSearch($contentId),
            OrganisationContentDecision::TYPE_CULTURE => $this->buildCulture($contentId),
            default => throw new \InvalidArgumentException("Unsupported content type: {$contentType}"),
        };

        OfflineContentBundle::upsertFromBuild($contentType, $contentId, $result);

        if ($contentType === OrganisationContentDecision::TYPE_STORY) {
            Comic::query()->whereKey($contentId)->update([
                'bundle_path' => $result['bundle_path'],
                'bundle_hash' => $result['bundle_hash'],
            ]);
        }

        return $result;
    }

    /** @deprecated Use build(OrganisationContentDecision::TYPE_STORY, $comicId) */
    public function buildForComic(Comic $comic): array
    {
        return $this->build(OrganisationContentDecision::TYPE_STORY, (int) $comic->id);
    }

    /**
     * @return array{bundle_path: string, bundle_hash: string, asset_count: int, bytes: int, schema: string}
     */
    private function buildStory(int $comicId): array
    {
        $comic = Comic::query()
            ->whereKey($comicId)
            ->where('status', 'published')
            ->firstOrFail();

        $comic->loadMissing([
            'tribe:id,name',
            'panels:id,comic_id,order_index,image_path,audio_url,caption,metadata',
            'panels.vocabTags:id,panel_id,word,translation,phonetic,x_position,y_position,width,height,metadata',
        ]);

        $explicit = [$comic->cover_image_path];
        foreach ($comic->panels as $panel) {
            $explicit[] = $panel->image_path;
            $explicit[] = $panel->audio_url;
        }

        $paths = $this->assetCollector->collect($comic->toArray(), array_filter($explicit));
        $writer = $this->createWriter(OrganisationContentDecision::TYPE_STORY, $comicId, $comic->org_id);
        $assetMap = $writer->addStorageAssets($paths, 'assets');

        $panels = $comic->panels->sortBy('order_index')->values()->map(function ($panel) use ($assetMap) {
            return [
                'id' => $panel->id,
                'order_index' => $panel->order_index,
                'caption' => $panel->caption,
                'image_path' => $panel->image_path,
                'audio_url' => $panel->audio_url,
                'bundle_image' => $panel->image_path ? ($assetMap[$panel->image_path] ?? null) : null,
                'bundle_audio' => $panel->audio_url ? ($assetMap[$panel->audio_url] ?? null) : null,
                'vocab_tags' => $panel->vocabTags
                    ->map(fn ($tag) => PanelVocabTagSerializer::toArray($tag, includeId: false))
                    ->values()
                    ->all(),
                'metadata' => $panel->metadata,
            ];
        })->all();

        $comicPayload = [
            'id' => $comic->id,
            'org_id' => $comic->org_id,
            'tribe_id' => $comic->tribe_id,
            'tribe' => $comic->tribe?->name,
            'title' => $comic->title,
            'description' => $comic->description,
            'age_min' => $comic->age_min,
            'age_max' => $comic->age_max,
            'status' => $comic->status,
            'star_points' => $comic->star_points,
            'cover_image_path' => $comic->cover_image_path,
            'bundle_cover' => $comic->cover_image_path ? ($assetMap[$comic->cover_image_path] ?? null) : null,
            'metadata' => $comic->metadata,
        ];

        return $this->finalize($writer, [
            'content_type' => OrganisationContentDecision::TYPE_STORY,
            'content_id' => $comic->id,
            'tribe_id' => $comic->tribe_id,
            'org_id' => $comic->org_id,
            'title' => $comic->title,
            'asset_map' => $assetMap,
            'data' => [
                'comic' => $comicPayload,
                'panels' => $panels,
            ],
            'legacy' => [
                'schema' => 'culturekids.bundle.v1',
                'comic' => $comicPayload,
                'panels' => $panels,
            ],
        ], $comic->org_id);
    }

    private function buildSong(int $songId): array
    {
        $song = Song::query()->whereKey($songId)->where('status', 'published')->firstOrFail();
        $song->loadMissing('tribe:id,name');

        $paths = $this->assetCollector->collect($song->toArray(), array_filter([
            $song->audio_path,
            $song->video_path,
            $song->cover_image_path,
        ]));

        $writer = $this->createWriter(OrganisationContentDecision::TYPE_SONG, $songId, $song->org_id ?? null);
        $assetMap = $writer->addStorageAssets($paths);

        return $this->finalize($writer, $this->baseManifest(
            OrganisationContentDecision::TYPE_SONG,
            $song,
            $assetMap,
            ['song' => $this->withBundleRefs($song->toArray(), $assetMap)]
        ), $song->org_id ?? null);
    }

    private function buildActivity(int $activityId, string $type): array
    {
        $activity = Activity::query()
            ->whereKey($activityId)
            ->where('type', $type)
            ->where('is_published', true)
            ->firstOrFail();

        $activity->loadMissing(['tribe:id,name', 'flashcardSlides']);

        $paths = $this->assetCollector->collect($activity->toArray());
        $writer = $this->createWriter(
            $type === 'flashcard' ? OrganisationContentDecision::TYPE_FLASHCARD : OrganisationContentDecision::TYPE_PUZZLE,
            $activityId,
            null
        );
        $assetMap = $writer->addStorageAssets($paths);

        $contentType = $type === 'flashcard'
            ? OrganisationContentDecision::TYPE_FLASHCARD
            : OrganisationContentDecision::TYPE_PUZZLE;

        $slides = $activity->flashcardSlides->map(function ($slide) use ($assetMap) {
            return [
                'id' => $slide->id,
                'activity_id' => $slide->activity_id,
                'order_index' => $slide->order_index,
                'emoji' => $slide->emoji,
                'front_label' => $slide->front_label,
                'back_label' => $slide->back_label,
                'phonetic' => $slide->phonetic,
                'metadata' => $slide->metadata,
                'image_path' => $slide->image_path,
                'audio_path' => $slide->audio_path,
                'bundle_image' => $slide->image_path ? ($assetMap[$slide->image_path] ?? null) : null,
                'bundle_audio' => $slide->audio_path ? ($assetMap[$slide->audio_path] ?? null) : null,
            ];
        })->values()->all();

        $activityPayload = $this->withBundleRefs($activity->toArray(), $assetMap);
        if ($type === 'puzzle') {
            $activityPayload = $this->enrichPuzzleForBundle($activityPayload, $assetMap);
        }

        return $this->finalize($writer, $this->baseManifest(
            $contentType,
            $activity,
            $assetMap,
            [
                'activity' => $activityPayload,
                'slides' => $slides,
            ]
        ), null);
    }

    private function buildDrawing(int $drawingId, bool $colouring): array
    {
        $drawing = $this->findPublishedDrawing($drawingId, $colouring);

        $drawing->loadMissing('tribe:id,name');

        $paths = $this->assetCollector->collect($drawing->toArray(), array_filter([
            $drawing->template_path,
            $drawing->preview_path,
        ]));

        $contentType = $colouring
            ? OrganisationContentDecision::TYPE_COLOURING
            : OrganisationContentDecision::TYPE_DRAWING;

        $writer = $this->createWriter($contentType, $drawingId, null);
        $assetMap = $writer->addStorageAssets($paths);

        return $this->finalize($writer, $this->baseManifest(
            $contentType,
            $drawing,
            $assetMap,
            ['drawing' => $this->withBundleRefs($drawing->toArray(), $assetMap)]
        ), null);
    }

    private function buildLanguage(int $activityId): array
    {
        $activity = LanguageActivity::query()
            ->whereKey($activityId)
            ->where('status', 'published')
            ->firstOrFail();

        $activity->loadMissing(['tribe:id,name', 'words']);

        $explicitWordPaths = $activity->words
            ->flatMap(fn ($word) => array_filter([
                $word->image_path,
                $word->audio_path,
            ]))
            ->values()
            ->all();

        $paths = $this->assetCollector->collect(
            $activity->toArray(),
            array_filter(array_merge([$activity->audio_path], $explicitWordPaths))
        );
        $writer = $this->createWriter(OrganisationContentDecision::TYPE_LANGUAGE, $activityId, null);
        $assetMap = $writer->addStorageAssets($paths);

        $words = $activity->words->map(fn ($word) => $this->withBundleRefs($word->toArray(), $assetMap))->values()->all();

        return $this->finalize($writer, $this->baseManifest(
            OrganisationContentDecision::TYPE_LANGUAGE,
            $activity,
            $assetMap,
            [
                'language_activity' => $this->withBundleRefs($activity->toArray(), $assetMap),
                'words' => $words,
            ]
        ), null);
    }

    private function buildGame(int $gameId): array
    {
        $game = Game::query()->whereKey($gameId)->where('status', 'published')->firstOrFail();
        $game->loadMissing(['tribe:id,name', 'questions']);

        $paths = $this->assetCollector->collect($game->toArray(), array_filter([
            $game->cover_image_path,
            $game->background_music_path,
        ]));

        $writer = $this->createWriter(OrganisationContentDecision::TYPE_GAME, $gameId, null);
        $assetMap = $writer->addStorageAssets($paths);

        $gamePayload = array_merge(
            array_filter([
                'cover_image_path' => $game->cover_image_path,
                'background_music_path' => $game->background_music_path,
            ]),
            GameApiSerializer::toArray($game),
        );

        return $this->finalize($writer, $this->baseManifest(
            OrganisationContentDecision::TYPE_GAME,
            $game,
            $assetMap,
            ['game' => $this->withBundleRefs($gamePayload, $assetMap)]
        ), null);
    }

    private function buildMaze(int $mazeId): array
    {
        $maze = Maze::query()->whereKey($mazeId)->where('status', 'published')->firstOrFail();
        $maze->loadMissing('tribe:id,name');

        $paths = $this->assetCollector->collect($maze->toArray(), array_filter([
            $maze->background_image_path,
            $maze->cover_image_path,
        ]));

        $writer = $this->createWriter(OrganisationContentDecision::TYPE_MAZE, $mazeId, null);
        $assetMap = $writer->addStorageAssets($paths);

        return $this->finalize($writer, $this->baseManifest(
            OrganisationContentDecision::TYPE_MAZE,
            $maze,
            $assetMap,
            ['maze' => $this->withBundleRefs($maze->toArray(), $assetMap)]
        ), null);
    }

    private function buildSpotDifference(int $id): array
    {
        $item = SpotDifference::query()->whereKey($id)->where('status', 'published')->firstOrFail();
        $item->loadMissing(['tribe:id,name', 'zones']);

        $paths = $this->assetCollector->collect($item->toArray(), array_filter([
            $item->image_a_path,
            $item->image_b_path,
        ]));

        $writer = $this->createWriter(OrganisationContentDecision::TYPE_SPOT_DIFFERENCE, $id, null);
        $assetMap = $writer->addStorageAssets($paths);

        return $this->finalize($writer, $this->baseManifest(
            OrganisationContentDecision::TYPE_SPOT_DIFFERENCE,
            $item,
            $assetMap,
            [
                'spot_difference' => $this->withBundleRefs($item->toArray(), $assetMap),
                'zones' => $item->zones->toArray(),
            ]
        ), null);
    }

    private function buildWordSearch(int $id): array
    {
        $item = WordSearch::query()->whereKey($id)->where('status', 'published')->firstOrFail();
        $item->loadMissing('tribe:id,name');

        $paths = $this->assetCollector->collect($item->toArray());
        $writer = $this->createWriter(OrganisationContentDecision::TYPE_WORD_SEARCH, $id, null);
        $assetMap = $writer->addStorageAssets($paths);

        return $this->finalize($writer, $this->baseManifest(
            OrganisationContentDecision::TYPE_WORD_SEARCH,
            $item,
            $assetMap,
            ['word_search' => $this->withBundleRefs($item->toArray(), $assetMap)]
        ), null);
    }

    private function buildCulture(int $id): array
    {
        $item = CultureActivity::query()->whereKey($id)->where('status', 'published')->firstOrFail();
        $item->loadMissing('tribe:id,name');

        $paths = $this->assetCollector->collect($item->toArray(), array_filter([
            $item->cover_image_path,
            $item->map_image_path,
        ]));

        $writer = $this->createWriter(OrganisationContentDecision::TYPE_CULTURE, $id, null);
        $assetMap = $writer->addStorageAssets($paths);

        $culturePayload = array_merge(
            array_filter([
                'cover_image_path' => $item->cover_image_path,
                'map_image_path' => $item->map_image_path,
            ]),
            CultureApiSerializer::toArray($item),
        );

        return $this->finalize($writer, $this->baseManifest(
            OrganisationContentDecision::TYPE_CULTURE,
            $item,
            $assetMap,
            ['culture_activity' => $this->withBundleRefs($culturePayload, $assetMap)]
        ), null);
    }

    private function findPublishedDrawing(int $drawingId, bool $colouring): Drawing
    {
        $query = Drawing::query()
            ->whereKey($drawingId)
            ->where('status', 'published');

        if ($colouring) {
            $query->where('drawing_type', 'coloring');
        } else {
            $query->where(function ($inner) {
                $inner->whereNull('drawing_type')
                    ->orWhere('drawing_type', '!=', 'coloring');
            });
        }

        return $query->firstOrFail();
    }

    private function createWriter(string $contentType, int $contentId, ?int $orgId): OfflineBundleZipWriter
    {
        $buildRoot = storage_path('app/tmp/bundles');
        if (! is_dir($buildRoot)) {
            mkdir($buildRoot, 0755, true);
        }

        $filename = $contentType.'-'.$contentId.'-'.now()->format('YmdHis').'.ckb';
        $this->tempZipPath = $buildRoot.'/'.$filename;

        return new OfflineBundleZipWriter($this->tempZipPath);
    }

    /**
     * @param  array<string, mixed>  $manifestBody
     * @return array{bundle_path: string, bundle_hash: string, asset_count: int, bytes: int, schema: string}
     */
    private function finalize(OfflineBundleZipWriter $writer, array $manifestBody, ?int $orgId): array
    {
        $assetCount = $writer->assetCount();
        $manifest = array_merge([
            'schema' => self::SCHEMA_V2,
            'generated_at' => now()->toIso8601String(),
            'asset_count' => $assetCount,
        ], $manifestBody);

        $writer->addManifest($manifest);
        $writer->close();

        $contentType = (string) $manifestBody['content_type'];
        $contentId = (int) $manifestBody['content_id'];
        $scope = $orgId ? 'org-'.$orgId : 'global';
        $bundlePath = 'bundles/'.$scope.'/'.$contentType.'-'.$contentId.'.ckb';

        $tempZipPath = $this->tempZipPath;
        if (! $tempZipPath || ! is_file($tempZipPath)) {
            throw new \RuntimeException('Offline bundle temp file missing after build.');
        }

        $disk = Storage::disk('public');
        $stream = fopen($tempZipPath, 'r');
        $disk->put($bundlePath, $stream);
        if (is_resource($stream)) {
            fclose($stream);
        }

        $bytes = filesize($tempZipPath) ?: 0;
        $hash = hash_file('sha256', $tempZipPath);
        @unlink($tempZipPath);
        $this->tempZipPath = null;

        return [
            'bundle_path' => $bundlePath,
            'bundle_hash' => $hash,
            'asset_count' => $assetCount,
            'bytes' => (int) $bytes,
            'schema' => self::SCHEMA_V2,
        ];
    }

    /**
     * @param  array<string, string>  $assetMap
     */
    private function baseManifest(string $contentType, Model $model, array $assetMap, array $data): array
    {
        return [
            'content_type' => $contentType,
            'content_id' => (int) $model->getKey(),
            'tribe_id' => $model->tribe_id ?? null,
            'org_id' => $model->org_id ?? null,
            'title' => (string) ($model->title ?? ''),
            'asset_map' => $assetMap,
            'data' => $data,
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @param  array<string, string>  $assetMap
     * @return array<string, mixed>
     */
    private function withBundleRefs(array $row, array $assetMap): array
    {
        foreach ($row as $key => $value) {
            if (! is_string($value) || ! is_string($key)) {
                continue;
            }
            if ((str_ends_with($key, '_path') || str_ends_with($key, '_url')) && isset($assetMap[$value])) {
                $row['bundle_'.str_replace('_path', '', str_replace('_url', '', $key))] = $assetMap[$value];
            }
        }

        return $row;
    }

    /**
     * @param  array<string, mixed>  $activityRow
     * @param  array<string, string>  $assetMap
     * @return array<string, mixed>
     */
    private function enrichPuzzleForBundle(array $activityRow, array $assetMap): array
    {
        $puzzle = data_get($activityRow, 'metadata.puzzle');
        if (! is_array($puzzle)) {
            return $activityRow;
        }

        $source = $puzzle['source_image'] ?? null;
        if (is_string($source) && isset($assetMap[$source])) {
            $puzzle['bundle_source_image'] = $assetMap[$source];
        }

        $piecePaths = $puzzle['piece_paths'] ?? [];
        if (is_array($piecePaths) && $piecePaths !== []) {
            $bundlePiecePaths = [];
            foreach ($piecePaths as $piecePath) {
                if (is_string($piecePath) && isset($assetMap[$piecePath])) {
                    $bundlePiecePaths[] = $assetMap[$piecePath];
                } else {
                    $bundlePiecePaths[] = null;
                }
            }
            if (count(array_filter($bundlePiecePaths)) > 0) {
                $puzzle['bundle_piece_paths'] = $bundlePiecePaths;
            }
        }

        data_set($activityRow, 'metadata.puzzle', $puzzle);

        return $activityRow;
    }
}
