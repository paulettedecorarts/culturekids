<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ChecksOrganisationModules;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\LanguageActivity;
use App\Models\Tribe;
use App\Services\OrganisationModuleResolver;
use App\Support\LanguageActivityApiSerializer;
use Illuminate\Http\Request;

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
            $resolver->assertActivityTypeAllowedForUser($request->user(), $type);
            $query->where('type', $type);
        }

        $activities = $resolver->filterActivitiesForUser($query->get(), $request->user())
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

        $activities = $resolver->filterActivitiesForUser(
            $query->get([
                'id',
                'title',
                'type',
                'age_range',
                'star_points',
                'description',
            ]),
            $request->user()
        )->map(function ($activity) {
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
        
        $activity = Activity::with([
            'tribe:id,name,color,hero_emoji,hero_icon',
            'flashcardSlides' => function ($q) {
                $q->orderBy('order_index');
            }
        ])->findOrFail($id);

        app(OrganisationModuleResolver::class)->assertActivityTypeAllowedForUser($user, $activity->type);

        // Check organization access
        if ($user->organisation_id && $activity->tribe) {
            if ($activity->tribe->org_id && $activity->tribe->org_id !== $user->organisation_id) {
                abort(403, 'Unauthorized');
            }
        } elseif (!$user->organisation_id && $activity->tribe && $activity->tribe->org_id) {
            abort(403, 'Unauthorized');
        }

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
}
