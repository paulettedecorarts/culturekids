<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ChecksOrganisationModules;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\LanguageActivity;
use App\Models\Maze;
use App\Models\OrganisationContentDecision;
use App\Models\Tribe;
use App\Services\OrganisationModuleResolver;
use App\Support\OfflineBundle\ActivityOfflineBundleIdentity;
use App\Support\LanguageActivityApiSerializer;
use App\Support\MazeApiSerializer;
use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivityController extends Controller
{
    use ChecksOrganisationModules;
    /**
     * Get all activities (flashcards, puzzles) - no organization scoping needed
     * Activities are already scoped through tribes
     */
    public function index(Request $request)
    {
        $type = $request->query('type'); // flashcard, puzzle, game, etc.
        $tribeId = $request->query('tribe_id');
        $search = $request->query('search');
        $user = $request->user();
        
        $listColumns = [
            'id',
            'tribe_id',
            'title',
            'type',
            'age_range',
            'star_points',
            'description',
            'is_published',
        ];

        $query = Activity::query()
            ->select($user?->organisation_id ? [...$listColumns, 'metadata'] : $listColumns)
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
            $resolver->assertActivityTypeAllowedForUser($request->user(), $type);
            $query->where('type', $type);
        }

        $activities = $this->scopeActivitiesForUser(
            $resolver->filterActivitiesForUser($query->get(), $user),
            $user
        )
            ->map(function ($activity) {
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

    /**
     * Get all activities for a specific tribe
     */
    public function getTribeActivities(Request $request, $tribeId)
    {
        $tribe = Tribe::findOrFail($tribeId);
        $search = $request->query('search');
        $type = $request->query('type');
        
        $query = Activity::where('tribe_id', $tribeId)
            ->with('tribe:id,name,hero_emoji,hero_icon,color')
            ->where('is_published', true)
            ->orderBy('type')
            ->orderBy('title');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($type) {
            $query->where('type', $type);
        }

        $resolver = app(OrganisationModuleResolver::class);

        $activities = $this->scopeActivitiesForUser($resolver->filterActivitiesForUser(
            $query->get([
                'id',
                'tribe_id',
                'title',
                'type',
                'age_range',
                'star_points',
                'description',
            ]),
            $request->user()
        ), $request->user())->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'title' => $activity->title,
                    'type' => $activity->type,
                    'age_range' => $activity->age_range,
                    'stars' => $activity->star_points ?? 10,
                    'description' => $activity->description,
                ];
            })
            ->values();

        return response()->json([
            'tribe' => [
                'id' => $tribe->id,
                'name' => $tribe->name,
                'hero' => $tribe->hero_name,
                'language' => 'Luganda', // Default for now
                'color' => $tribe->color,
                'icon' => $tribe->hero_emoji ?? $tribe->hero_icon,
            ],
            'activities' => $activities,
        ]);
    }

    /**
     * Get a single activity with slides for flashcards
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();

        $type = Activity::query()->whereKey($id)->value('type');
        if ($type === null) {
            abort(404);
        }

        $baseColumns = [
            'id',
            'tribe_id',
            'title',
            'type',
            'age_range',
            'star_points',
            'description',
        ];
        $select = in_array($type, ['puzzle', 'vocab_pack'], true)
            ? [...$baseColumns, 'metadata']
            : $baseColumns;

        $activity = Activity::query()
            ->select($select)
            ->with('tribe:id,name,color,hero_emoji,hero_icon')
            ->findOrFail($id);

        if ($activity->type === 'flashcard') {
            $activity->load([
                'flashcardSlides' => function ($q) {
                    $q->orderBy('order_index');
                },
            ]);
        }

        app(OrganisationModuleResolver::class)->assertActivityTypeAllowedForUser($user, $activity->type);

        $response = [
            'id' => $activity->id,
            'title' => $activity->title,
            'type' => $activity->type,
            'age_range' => $activity->age_range,
            'stars' => $activity->star_points ?? 10,
            'description' => $activity->description,
            'tribe' => $activity->tribe ? [
                'id' => $activity->tribe->id,
                'name' => $activity->tribe->name,
                'color' => $activity->tribe->color,
                'icon' => $activity->tribe->hero_emoji ?? $activity->tribe->hero_icon,
            ] : null,
        ];

        // Add slides for flashcards
        if ($activity->type === 'flashcard') {
            $response['slides'] = $activity->flashcardSlides->map(function ($slide) {
                return [
                    'id' => $slide->id,
                    'order' => $slide->order_index,
                    'emoji' => $slide->emoji,
                    'front_label' => $slide->front_label,
                    'back_label' => $slide->back_label,
                    'phonetic' => $slide->phonetic,
                    'image_path' => $slide->image_path ? asset('storage/' . $slide->image_path) : null,
                    'audio_path' => $slide->audio_path ? asset('storage/' . $slide->audio_path) : null,
                ];
            });
        }

        // Add puzzle data if needed
        if ($activity->type === 'puzzle') {
            $response['puzzle_data'] = $activity->metadata;
            $response['printable_url'] = $activity->printableAssetUrl();
        }

        if ($activity->type === 'maze') {
            $legacyId = (int) DB::table('activities')
                ->where('id', $id)
                ->where('type', 'maze')
                ->value(DB::raw("CAST(JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.legacy_maze_id')) AS UNSIGNED)"));

            $maze = $legacyId > 0 ? Maze::query()->find($legacyId) : null;

            if ($maze && is_array($maze->grid) && $maze->grid !== []) {
                $response['maze_data'] = ['maze' => MazeApiSerializer::toArray($maze)];
            }
        }

        if ($activity->type === 'vocab_pack') {
            $legacyId = data_get($activity->metadata, 'legacy_language_activity_id');
            $languageActivity = $legacyId
                ? LanguageActivity::with('words')->find($legacyId)
                : null;

            if ($languageActivity) {
                $response['language_activity'] = LanguageActivityApiSerializer::toArray($languageActivity);
            }
        }

        return response()->json($response);
    }

    /**
     * @param  Collection<int, Activity>  $activities
     * @return Collection<int, Activity>
     */
    private function scopeActivitiesForUser(Collection $activities, $user): Collection
    {
        if (! $user?->organisation_id) {
            return $activities->values();
        }

        $approved = OrganisationContentDecision::query()
            ->where('organisation_id', (int) $user->organisation_id)
            ->where('decision', OrganisationContentDecision::DECISION_APPROVED)
            ->get(['content_type', 'content_id'])
            ->mapWithKeys(function ($row) {
                return [(string) $row->content_type.':'.(int) $row->content_id => true];
            });

        return $activities->filter(function (Activity $activity) use ($user, $approved) {
            $identity = ActivityOfflineBundleIdentity::resolve($activity);
            if (! $identity) {
                return false;
            }

            $key = $identity['content_type'].':'.$identity['content_id'];

            return $approved->has($key);
        })->values();
    }
}
