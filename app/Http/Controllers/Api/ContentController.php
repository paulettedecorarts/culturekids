<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ChecksOrganisationModules;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Comic;
use App\Models\OrganisationContentDecision;
use App\Models\Song;
use App\Services\OrganisationModuleResolver;
use App\Support\OfflineBundle\ActivityOfflineBundleIdentity;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    use ChecksOrganisationModules;

    public function index(Request $request)
    {
        $type = $request->query('type');
        $tribeId = $request->query('tribe_id');
        $search = $request->query('search');
        $user = $request->user();

        if ($type === 'story') {
            $this->assertModule($request, 'stories');

            $query = Comic::query()
                ->with('tribe:id,name,hero_emoji,hero_icon,color')
                ->published()
                ->orderBy('created_at', 'desc');

            if ($tribeId) {
                $query->where('tribe_id', $tribeId);
            }

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }

            if ($user?->organisation_id) {
                $approvedIds = $user->organisation?->approvedComicIds() ?? [];
                $query->where(function ($q) use ($approvedIds, $user) {
                    if ($approvedIds !== []) {
                        $q->whereIn('id', $approvedIds);
                    }
                    $q->orWhere('org_id', $user->organisation_id);
                });
            } else {
                $query->whereNull('org_id');
            }

            return response()->json(
                $query->get()->map(function (Comic $comic) {
                    return [
                        'id' => $comic->id,
                        'title' => $comic->title,
                        'type' => 'story',
                        'age_range' => $comic->age_range,
                        'stars' => $comic->star_points ?? 10,
                        'description' => $comic->description,
                        'cover_image' => $comic->cover_image_path ? asset('storage/'.$comic->cover_image_path) : null,
                        'tribe' => $comic->tribe ? [
                            'id' => $comic->tribe->id,
                            'name' => $comic->tribe->name,
                            'icon' => $comic->tribe->hero_emoji ?? $comic->tribe->hero_icon,
                            'color' => $comic->tribe->color,
                        ] : null,
                    ];
                })->values()
            );
        }

        if ($type === 'song') {
            $this->assertModule($request, 'songs');

            $query = Song::query()
                ->with('tribe:id,name,hero_emoji,hero_icon,color')
                ->where('status', 'published')
                ->orderBy('created_at', 'desc');

            if ($tribeId) {
                $query->where('tribe_id', $tribeId);
            }

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }

            if ($user?->organisation_id) {
                $approvedIds = $user->organisation?->approvedSongIds() ?? [];
                $query->where(function ($q) use ($approvedIds, $user) {
                    if ($approvedIds !== []) {
                        $q->whereIn('id', $approvedIds);
                    }
                    $q->orWhere('org_id', $user->organisation_id);
                });
            } else {
                $query->whereNull('org_id');
            }

            return response()->json(
                $query->get()->map(function (Song $song) {
                    return [
                        'id' => $song->id,
                        'title' => $song->title,
                        'type' => 'song',
                        'age_range' => $song->age_range,
                        'stars' => $song->star_points ?? 10,
                        'description' => $song->description,
                        'cover_image' => $song->cover_image_path ? asset('storage/'.$song->cover_image_path) : null,
                        'audio_path' => $song->audio_path ? asset('storage/'.$song->audio_path) : null,
                        'tribe' => $song->tribe ? [
                            'id' => $song->tribe->id,
                            'name' => $song->tribe->name,
                            'icon' => $song->tribe->hero_emoji ?? $song->tribe->hero_icon,
                            'color' => $song->tribe->color,
                        ] : null,
                    ];
                })->values()
            );
        }

        $query = Activity::query()
            ->with('tribe:id,name,hero_emoji,hero_icon,color')
            ->where('is_published', true)
            ->orderBy('title');

        if ($tribeId) {
            $query->where('tribe_id', $tribeId);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $resolver = app(OrganisationModuleResolver::class);

        if ($type) {
            $resolver->assertActivityTypeAllowedForUser($user, $type);
            $query->where('type', $type);
        }

        $approved = null;
        if ($user?->organisation_id) {
            $approved = OrganisationContentDecision::query()
                ->where('organisation_id', (int) $user->organisation_id)
                ->where('decision', OrganisationContentDecision::DECISION_APPROVED)
                ->get(['content_type', 'content_id'])
                ->mapWithKeys(fn ($row) => [(string) $row->content_type.':'.(int) $row->content_id => true]);
        }

        $activities = $resolver->filterActivitiesForUser($query->get(), $user)
            ->filter(function (Activity $activity) use ($user, $approved) {
                if (! $user?->organisation_id) {
                    return true;
                }
                $identity = ActivityOfflineBundleIdentity::resolve($activity);
                if (! $identity) {
                    return false;
                }
                return $approved?->has($identity['content_type'].':'.$identity['content_id']) ?? false;
            })
            ->map(function (Activity $activity) {
                return [
                    'id' => $activity->id,
                    'title' => $activity->title,
                    'type' => $activity->type,
                    'age_range' => $activity->age_range,
                    'stars' => $activity->star_points ?? 10,
                    'description' => $activity->description,
                    'tribe' => $activity->tribe ? [
                        'id' => $activity->tribe->id,
                        'name' => $activity->tribe->name,
                        'icon' => $activity->tribe->hero_emoji ?? $activity->tribe->hero_icon,
                        'color' => $activity->tribe->color,
                    ] : null,
                ];
            })
            ->values();

        return response()->json($activities);
    }
}
