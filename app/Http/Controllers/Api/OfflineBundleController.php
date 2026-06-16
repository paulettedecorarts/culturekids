<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ChecksOrganisationModules;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Comic;
use App\Models\OfflineContentBundle;
use App\Models\OrganisationContentDecision;
use App\Models\ParentDownloadedPack;
use App\Models\Song;
use App\Models\Tribe;
use App\Services\OrganisationModuleResolver;
use App\Support\ActivityBundleMetadataExtract;
use App\Support\OfflineBundle\ActivityOfflineBundleIdentity;
use App\Support\OrganisationActivityScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OfflineBundleController extends Controller
{
    use ChecksOrganisationModules;
    /**
     * Apply org-owned-or-approved scope for comics.
     */
    private function scopeComicsForUser($query, $user)
    {
        if ($user->organisation_id) {
            $approvedComicIds = $user->organisation?->approvedComicIds() ?? [];
            $query->where(function ($q) use ($approvedComicIds, $user) {
                if ($approvedComicIds !== []) {
                    $q->whereIn('id', $approvedComicIds);
                }
                $q->orWhere('org_id', $user->organisation_id);
            });
        } else {
            $query->whereNull('org_id');
        }

        return $query;
    }

    /**
     * Apply org-owned-or-approved scope for songs.
     */
    private function scopeSongsForUser($query, $user)
    {
        if ($user->organisation_id) {
            $approvedSongIds = $user->organisation?->approvedSongIds() ?? [];
            $query->where(function ($q) use ($approvedSongIds, $user) {
                if ($approvedSongIds !== []) {
                    $q->whereIn('id', $approvedSongIds);
                }
                $q->orWhere('org_id', $user->organisation_id);
            });
        } else {
            $query->whereNull('org_id');
        }

        return $query;
    }

    /**
     * Apply org-owned-or-approved scope for activities (without loading full metadata JSON).
     *
     * @param  \Illuminate\Database\Eloquent\Builder<Activity>  $query
     */
    private function scopeActivitiesForUser($query, $user)
    {
        OrganisationActivityScope::withIdentityExtracts($query, $user);

        $activities = $query->get();
        if (! $user->organisation_id) {
            return $activities->values();
        }

        return OrganisationActivityScope::filterApproved($activities, $user);
    }
    /**
     * Get bundle manifest for a tribe (all content metadata)
     * This returns metadata only - actual assets are downloaded separately
     */
    public function getTribeBundle(Request $request, int $tribeId): JsonResponse
    {
        $this->assertModule($request, 'offline_bundles');
        $tribe = Tribe::findOrFail($tribeId);
        $resolver = app(OrganisationModuleResolver::class);
        $user = $request->user();

        // Get all published comics for this tribe
        $comics = $resolver->filterComicsForUser($this->scopeComicsForUser(
            Comic::where('tribe_id', $tribeId)
            ->where('status', 'published')
            ->with([
                'panels:id,comic_id,order_index,image_path,audio_url,caption',
                'panels.vocabTags:id,panel_id,word,translation,phonetic,x_position,y_position,width,height',
            ]),
            $user
        )->get(), $user);

        // Get all published songs for this tribe
        $songs = $resolver->filterSongsForUser($this->scopeSongsForUser(
            Song::where('tribe_id', $tribeId)
            ->where('status', 'published'),
            $user
        )->get(), $user);

        // Get all published activities for this tribe
        $activities = $resolver->filterActivitiesForUser($this->scopeActivitiesForUser(
            Activity::where('tribe_id', $tribeId)
            ->where('is_published', true)
            ->select([
                'id', 'tribe_id', 'title', 'type', 'description', 'age_range', 'star_points', 'is_published',
            ])
            ->with('flashcardSlides'),
            $user
        ), $user);

        // Calculate total bundle size
        $totalSize = 0;
        
        // Add comic bundle sizes
        foreach ($comics as $comic) {
            $totalSize += $comic->bundle_size_bytes ?? 0;
        }
        
        // Estimate song sizes (if not stored)
        foreach ($songs as $song) {
            if ($song->audio_path && Storage::disk('public')->exists($song->audio_path)) {
                $totalSize += Storage::disk('public')->size($song->audio_path);
            }
            if ($song->cover_image_path && Storage::disk('public')->exists($song->cover_image_path)) {
                $totalSize += Storage::disk('public')->size($song->cover_image_path);
            }
        }
        
        // Estimate activity sizes (metadata + slides)
        foreach ($activities as $activity) {
            // Base metadata size
            $totalSize += 1024; // 1KB for metadata
            
            // Add slide assets
            foreach ($activity->flashcardSlides as $slide) {
                if ($slide->image_path && Storage::disk('public')->exists($slide->image_path)) {
                    $totalSize += Storage::disk('public')->size($slide->image_path);
                }
                if ($slide->audio_path && Storage::disk('public')->exists($slide->audio_path)) {
                    $totalSize += Storage::disk('public')->size($slide->audio_path);
                }
            }
        }

        // Build manifest
        $manifest = [
            'schema' => 'culturekids.tribe-bundle.v1',
            'generated_at' => now()->toIso8601String(),
            'bundle_size_bytes' => $totalSize,
            'tribe' => [
                'id' => $tribe->id,
                'name' => $tribe->name,
                'hero_name' => $tribe->hero_name,
                'hero_emoji' => $tribe->hero_emoji,
                'region' => $tribe->region,
                'color' => $tribe->color,
            ],
            'comics' => $comics->map(function ($comic) {
                $bundle = $this->bundleRecord(OrganisationContentDecision::TYPE_STORY, (int) $comic->id);

                return array_merge([
                    'id' => $comic->id,
                    'content_type' => OrganisationContentDecision::TYPE_STORY,
                    'title' => $comic->title,
                    'description' => $comic->description,
                    'age_min' => $comic->age_min,
                    'age_max' => $comic->age_max,
                    'star_points' => $comic->star_points,
                    'cover_image_path' => $comic->cover_image_path,
                    'cover_image_url' => $comic->cover_image_path 
                        ? Storage::disk('public')->url($comic->cover_image_path) 
                        : null,
                    'bundle_size_bytes' => $comic->bundle_size_bytes,
                ], $this->bundleFields($bundle, $comic->bundle_path, $comic->bundle_hash, OrganisationContentDecision::TYPE_STORY, (int) $comic->id), [
                    'panels' => $comic->panels->map(function ($panel) {
                        return [
                            'id' => $panel->id,
                            'order_index' => $panel->order_index,
                            'caption' => $panel->caption,
                            'image_path' => $panel->image_path,
                            'image_url' => $panel->image_path 
                                ? Storage::disk('public')->url($panel->image_path) 
                                : null,
                            'audio_url' => $panel->audio_url 
                                ? Storage::disk('public')->url($panel->audio_url) 
                                : null,
                            'vocab_tags' => $panel->vocabTags->map(
                                fn ($tag) => \App\Support\PanelVocabTagSerializer::toArray($tag, includeId: false)
                            ),
                        ];
                    }),
                ]);
            }),
            'songs' => $songs->map(function ($song) {
                $bundle = $this->bundleRecord(OrganisationContentDecision::TYPE_SONG, (int) $song->id);

                return array_merge([
                    'id' => $song->id,
                    'content_type' => OrganisationContentDecision::TYPE_SONG,
                    'title' => $song->title,
                    'description' => $song->description,
                    'lyrics' => $song->lyrics,
                    'audio_path' => $song->audio_path,
                    'audio_url' => $song->audio_path 
                        ? Storage::disk('public')->url($song->audio_path) 
                        : null,
                    'cover_image_path' => $song->cover_image_path,
                    'cover_image_url' => $song->cover_image_path 
                        ? Storage::disk('public')->url($song->cover_image_path) 
                        : null,
                    'duration_seconds' => $song->duration_seconds,
                ], $this->bundleFields($bundle, null, null, OrganisationContentDecision::TYPE_SONG, (int) $song->id));
            }),
            'activities' => $activities->map(function ($activity) {
                $identity = ActivityOfflineBundleIdentity::resolve($activity);
                $contentType = $identity['content_type'] ?? null;
                $bundleContentId = $identity['content_id'] ?? null;
                $bundle = $contentType && $bundleContentId
                    ? $this->bundleRecord($contentType, $bundleContentId)
                    : null;

                return array_merge([
                    'id' => $activity->id,
                    'type' => $activity->type,
                    'content_type' => $contentType,
                    'bundle_content_id' => $bundleContentId,
                    'title' => $activity->title,
                    'description' => $activity->description,
                    'age_range' => $activity->age_range,
                    'star_points' => $activity->star_points,
                    'metadata' => ActivityBundleMetadataExtract::slimForOfflineBundle($activity),
                    'slides' => $activity->flashcardSlides->map(function ($slide) {
                        return [
                            'id' => $slide->id,
                            'order_index' => $slide->order_index,
                            'emoji' => $slide->emoji,
                            'front_label' => $slide->front_label,
                            'back_label' => $slide->back_label,
                            'phonetic' => $slide->phonetic,
                            'metadata' => $slide->metadata,
                            'image_path' => $slide->image_path,
                            'image_url' => $slide->image_path
                                ? Storage::disk('public')->url($slide->image_path)
                                : null,
                            'audio_path' => $slide->audio_path,
                            'audio_url' => $slide->audio_path
                                ? Storage::disk('public')->url($slide->audio_path)
                                : null,
                        ];
                    }),
                ], $this->bundleFields($bundle, null, null, $contentType, $bundleContentId));
            }),
            'stats' => [
                'comics_count' => $comics->count(),
                'songs_count' => $songs->count(),
                'activities_count' => $activities->count(),
                'total_items' => $comics->count() + $songs->count() + $activities->count(),
            ],
        ];

        return response()->json($manifest);
    }

    /**
     * Download a specific comic bundle (.ckb file) — legacy route.
     */
    public function downloadComicBundle(Request $request, int $comicId)
    {
        return $this->downloadContentBundle($request, OrganisationContentDecision::TYPE_STORY, $comicId);
    }

    /**
     * Download a .ckb for any published content type (12 activity types).
     */
    public function downloadContentBundle(Request $request, string $contentType, int $contentId)
    {
        $this->assertModule($request, 'offline_bundles');
        $this->assertContentModule($request, $contentType);

        if (! in_array($contentType, OrganisationContentDecision::ALL_TYPES, true)) {
            abort(404);
        }

        $bundle = $this->bundleRecord($contentType, $contentId);
        $path = $bundle?->bundle_path;

        if ($contentType === OrganisationContentDecision::TYPE_STORY && ! $path) {
            $comic = Comic::where('status', 'published')->findOrFail($contentId);
            $path = $comic->bundle_path;
        }

        if (! $path || ! Storage::disk('public')->exists($path)) {
            return response()->json([
                'message' => 'Bundle not available. Ask your school to rebuild offline packs.',
            ], 404);
        }

        $filePath = Storage::disk('public')->path($path);
        $filename = "{$contentType}-{$contentId}.ckb";

        return response()->download($filePath, $filename, [
            'Content-Type' => 'application/zip',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    /**
     * Get download URLs for all assets in a tribe bundle
     * Returns signed URLs that expire in 24 hours
     */
    public function getTribeBundleAssets(Request $request, int $tribeId): JsonResponse
    {
        $this->assertModule($request, 'offline_bundles');
        $tribe = Tribe::findOrFail($tribeId);
        $resolver = app(OrganisationModuleResolver::class);
        $user = $request->user();

        $comics = $resolver->filterComicsForUser($this->scopeComicsForUser(
            Comic::where('tribe_id', $tribeId)
            ->where('status', 'published')
            ->with('panels'),
            $user
        )->get(), $user);

        $songs = $resolver->filterSongsForUser($this->scopeSongsForUser(
            Song::where('tribe_id', $tribeId)
            ->where('status', 'published'),
            $user
        )->get(), $user);

        $assets = [
            'comics' => [],
            'songs' => [],
        ];

        // Collect all comic assets
        foreach ($comics as $comic) {
            $comicAssets = [
                'comic_id' => $comic->id,
                'cover_image' => null,
                'panels' => [],
            ];

            if ($comic->cover_image_path && Storage::disk('public')->exists($comic->cover_image_path)) {
                $comicAssets['cover_image'] = [
                    'path' => $comic->cover_image_path,
                    'url' => Storage::disk('public')->url($comic->cover_image_path),
                    'size' => Storage::disk('public')->size($comic->cover_image_path),
                ];
            }

            foreach ($comic->panels as $panel) {
                $panelAssets = [
                    'panel_id' => $panel->id,
                    'image' => null,
                    'audio' => null,
                ];

                if ($panel->image_path && Storage::disk('public')->exists($panel->image_path)) {
                    $panelAssets['image'] = [
                        'path' => $panel->image_path,
                        'url' => Storage::disk('public')->url($panel->image_path),
                        'size' => Storage::disk('public')->size($panel->image_path),
                    ];
                }

                if ($panel->audio_url && Storage::disk('public')->exists($panel->audio_url)) {
                    $panelAssets['audio'] = [
                        'path' => $panel->audio_url,
                        'url' => Storage::disk('public')->url($panel->audio_url),
                        'size' => Storage::disk('public')->size($panel->audio_url),
                    ];
                }

                $comicAssets['panels'][] = $panelAssets;
            }

            $assets['comics'][] = $comicAssets;
        }

        // Collect all song assets
        foreach ($songs as $song) {
            $songAssets = [
                'song_id' => $song->id,
                'audio' => null,
                'cover_image' => null,
            ];

            if ($song->audio_path && Storage::disk('public')->exists($song->audio_path)) {
                $songAssets['audio'] = [
                    'path' => $song->audio_path,
                    'url' => Storage::disk('public')->url($song->audio_path),
                    'size' => Storage::disk('public')->size($song->audio_path),
                ];
            }

            if ($song->cover_image_path && Storage::disk('public')->exists($song->cover_image_path)) {
                $songAssets['cover_image'] = [
                    'path' => $song->cover_image_path,
                    'url' => Storage::disk('public')->url($song->cover_image_path),
                    'size' => Storage::disk('public')->size($song->cover_image_path),
                ];
            }

            $assets['songs'][] = $songAssets;
        }

        return response()->json($assets);
    }

    /**
     * Mark a tribe pack as downloaded for the authenticated parent
     */
    public function markPackAsDownloaded(Request $request, int $tribeId): JsonResponse
    {
        $this->assertModule($request, 'offline_bundles');
        $request->validate([
            'downloaded_at' => 'nullable|date',
        ]);

        $tribe = Tribe::findOrFail($tribeId);
        $user = $request->user();

        // Create or update the download record
        $pack = ParentDownloadedPack::updateOrCreate(
            [
                'user_id' => $user->id,
                'tribe_id' => $tribeId,
            ],
            [
                'downloaded_at' => $request->input('downloaded_at', now()),
            ]
        );

        return response()->json([
            'message' => 'Pack marked as downloaded',
            'pack' => [
                'id' => $pack->id,
                'tribe_id' => $pack->tribe_id,
                'tribe_name' => $tribe->name,
                'downloaded_at' => $pack->downloaded_at,
            ],
        ]);
    }

    /**
     * Remove a downloaded pack record for the authenticated parent
     */
    public function removeDownloadedPack(Request $request, int $tribeId): JsonResponse
    {
        $this->assertModule($request, 'offline_bundles');
        $user = $request->user();

        $deleted = ParentDownloadedPack::where('user_id', $user->id)
            ->where('tribe_id', $tribeId)
            ->delete();

        if ($deleted) {
            return response()->json([
                'message' => 'Pack removed successfully',
            ]);
        }

        return response()->json([
            'message' => 'Pack not found',
        ], 404);
    }

    /**
     * Get all downloaded packs for the authenticated parent
     */
    public function getDownloadedPacks(Request $request): JsonResponse
    {
        $this->assertModule($request, 'offline_bundles');
        $user = $request->user();

        $packs = ParentDownloadedPack::where('user_id', $user->id)
            ->with('tribe:id,name,hero_name,hero_emoji,region,color')
            ->get()
            ->map(function ($pack) {
                return [
                    'id' => $pack->id,
                    'tribe_id' => $pack->tribe_id,
                    'tribe_name' => $pack->tribe->name,
                    'tribe' => $pack->tribe,
                    'downloaded_at' => $pack->downloaded_at,
                ];
            });

        return response()->json($packs);
    }

    /**
     * Get content accessible to a child (based on parent's downloaded packs)
     */
    public function getChildAccessibleContent(Request $request): JsonResponse
    {
        $this->assertModule($request, 'offline_bundles');
        $user = $request->user();
        $resolver = app(OrganisationModuleResolver::class);

        // Get parent user (if child, get their parent)
        $parentId = $user->parent_id ?? $user->id;

        // Get all tribe IDs that the parent has downloaded
        $downloadedTribeIds = ParentDownloadedPack::where('user_id', $parentId)
            ->pluck('tribe_id')
            ->toArray();

        if (empty($downloadedTribeIds)) {
            return response()->json([
                'comics' => [],
                'songs' => [],
                'tribes' => [],
            ]);
        }

        // Get comics from downloaded tribes
        $comicsQuery = $this->scopeComicsForUser(Comic::whereIn('tribe_id', $downloadedTribeIds)
            ->where('status', 'published')
            ->with('tribe:id,name,icon,color'), $user);
        $comics = $resolver->filterComicsForUser($comicsQuery->get(), $user);

        // Get songs from downloaded tribes
        $songsQuery = $this->scopeSongsForUser(Song::whereIn('tribe_id', $downloadedTribeIds)
            ->where('status', 'published')
            ->with('tribe:id,name,icon,color'), $user);
        $songs = $resolver->filterSongsForUser($songsQuery->get(), $user);

        // Get tribe info
        $tribes = Tribe::whereIn('id', $downloadedTribeIds)->get();

        return response()->json([
            'comics' => $comics,
            'songs' => $songs,
            'tribes' => $tribes,
        ]);
    }

    private function bundleRecord(string $contentType, int $contentId): ?OfflineContentBundle
    {
        return OfflineContentBundle::forContent($contentType, $contentId);
    }

    /**
     * @return array{bundle_path: ?string, bundle_hash: ?string, bundle_ready: bool, bundle_download_url: ?string}
     */
    private function bundleFields(?OfflineContentBundle $bundle, ?string $legacyPath = null, ?string $legacyHash = null, ?string $contentType = null, ?int $contentId = null): array
    {
        $path = $bundle?->bundle_path ?? $legacyPath;
        $hash = $bundle?->bundle_hash ?? $legacyHash;
        $ready = $path !== null && $path !== '';

        return [
            'bundle_path' => $path,
            'bundle_hash' => $hash,
            'bundle_ready' => $ready,
            'bundle_download_url' => ($ready && $contentType && $contentId)
                ? url("/api/v1/offline/content/{$contentType}/{$contentId}/download")
                : null,
        ];
    }

    private function assertContentModule(Request $request, string $contentType): void
    {
        $moduleKey = config('modules.content_types')[$contentType] ?? null;
        if ($moduleKey) {
            $this->assertModule($request, $moduleKey);
        }
    }
}

