<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ChecksOrganisationModules;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Comic;
use App\Models\ParentDownloadedPack;
use App\Models\Song;
use App\Models\Tribe;
use App\Services\OrganisationModuleResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OfflineBundleController extends Controller
{
    use ChecksOrganisationModules;
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
        $comics = $resolver->filterComicsForUser(Comic::where('tribe_id', $tribeId)
            ->where('status', 'published')
            ->with([
                'panels:id,comic_id,order_index,image_path,audio_url,caption',
                'panels.vocabTags:id,panel_id,word,translation,phonetic,x_position,y_position,width,height',
            ])
            ->get(), $user);

        // Get all published songs for this tribe
        $songs = $resolver->filterSongsForUser(Song::where('tribe_id', $tribeId)
            ->where('status', 'published')
            ->get(), $user);

        // Get all published activities for this tribe
        $activities = $resolver->filterActivitiesForUser(Activity::where('tribe_id', $tribeId)
            ->where('is_published', true)
            ->with('flashcardSlides')
            ->get(), $user);

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
                return [
                    'id' => $comic->id,
                    'title' => $comic->title,
                    'description' => $comic->description,
                    'age_min' => $comic->age_min,
                    'age_max' => $comic->age_max,
                    'star_points' => $comic->star_points,
                    'cover_image_path' => $comic->cover_image_path,
                    'cover_image_url' => $comic->cover_image_path 
                        ? Storage::disk('public')->url($comic->cover_image_path) 
                        : null,
                    'bundle_path' => $comic->bundle_path,
                    'bundle_hash' => $comic->bundle_hash,
                    'bundle_size_bytes' => $comic->bundle_size_bytes,
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
                ];
            }),
            'songs' => $songs->map(function ($song) {
                return [
                    'id' => $song->id,
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
                ];
            }),
            'activities' => $activities->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'type' => $activity->type,
                    'title' => $activity->title,
                    'description' => $activity->description,
                    'age_range' => $activity->age_range,
                    'star_points' => $activity->star_points,
                    'metadata' => $activity->metadata,
                    'slides' => $activity->flashcardSlides->map(function ($slide) {
                        return [
                            'id' => $slide->id,
                            'order_index' => $slide->order_index,
                            'title' => $slide->title,
                            'description' => $slide->description,
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
                ];
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
     * Download a specific comic bundle (.ckb file)
     */
    public function downloadComicBundle(Request $request, int $comicId)
    {
        $this->assertModule($request, 'offline_bundles');
        $this->assertModule($request, 'stories');
        $comic = Comic::where('status', 'published')->findOrFail($comicId);

        if (!$comic->bundle_path || !Storage::disk('public')->exists($comic->bundle_path)) {
            return response()->json([
                'message' => 'Bundle not available. Please contact support.',
            ], 404);
        }

        $filePath = Storage::disk('public')->path($comic->bundle_path);
        
        return response()->download($filePath, "comic-{$comic->id}.ckb", [
            'Content-Type' => 'application/zip',
            'Content-Disposition' => 'attachment; filename="comic-'.$comic->id.'.ckb"',
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

        $comics = $resolver->filterComicsForUser(Comic::where('tribe_id', $tribeId)
            ->where('status', 'published')
            ->with('panels')
            ->get(), $user);

        $songs = $resolver->filterSongsForUser(Song::where('tribe_id', $tribeId)
            ->where('status', 'published')
            ->get(), $user);

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
        $comics = $resolver->filterComicsForUser(Comic::whereIn('tribe_id', $downloadedTribeIds)
            ->where('status', 'published')
            ->with('tribe:id,name,icon,color')
            ->get(), $user);

        // Get songs from downloaded tribes
        $songs = $resolver->filterSongsForUser(Song::whereIn('tribe_id', $downloadedTribeIds)
            ->where('status', 'published')
            ->with('tribe:id,name,icon,color')
            ->get(), $user);

        // Get tribe info
        $tribes = Tribe::whereIn('id', $downloadedTribeIds)->get();

        return response()->json([
            'comics' => $comics,
            'songs' => $songs,
            'tribes' => $tribes,
        ]);
    }
}

