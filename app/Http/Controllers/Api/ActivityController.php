<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ChecksOrganisationModules;
use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\CultureActivity;
use App\Models\Drawing;
use App\Models\LanguageActivity;
use App\Models\Maze;
use App\Models\SpotDifference;
use App\Models\Tribe;
use App\Models\WordSearch;
use App\Services\OrganisationModuleResolver;
use App\Support\CultureApiSerializer;
use App\Support\DrawingApiSerializer;
use App\Support\LanguageActivityApiSerializer;
use App\Support\MazeApiSerializer;
use App\Support\OrganisationActivityScope;
use App\Support\SpotDifferenceApiSerializer;
use App\Support\WordSearchApiSerializer;
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
            ->select($listColumns)
            ->with('tribe:id,name,hero_emoji,hero_icon,color')
            ->where('is_published', true)
            ->orderBy('title');

        OrganisationActivityScope::withIdentityExtracts($query, $user);

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

        $activities = OrganisationActivityScope::filterApproved(
            $resolver->filterActivitiesForUser($query->get(), $user),
            $user,
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

        $listColumns = [
            'id',
            'tribe_id',
            'title',
            'type',
            'age_range',
            'star_points',
            'description',
        ];

        $query->select($listColumns);
        OrganisationActivityScope::withIdentityExtracts($query, $request->user());

        $activities = OrganisationActivityScope::filterApproved(
            $resolver->filterActivitiesForUser($query->get(), $request->user()),
            $request->user(),
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
        $select = in_array($type, ['puzzle', 'vocab_pack', 'culture'], true)
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

            $maze = $legacyId > 0
                ? Maze::query()->select([
                    'id', 'title', 'maze_type', 'difficulty_level',
                    'grid', 'grid_rows', 'grid_cols',
                    'start_position', 'end_position', 'collectibles',
                    'time_limit_seconds', 'visibility_radius',
                    'hero_character', 'cultural_note',
                    'background_image_path', 'cover_image_path',
                ])->find($legacyId)
                : null;

            if ($maze && is_array($maze->grid) && $maze->grid !== []) {
                $response['maze_data'] = ['maze' => MazeApiSerializer::toArray($maze)];
            }
        }

        if ($activity->type === 'spot_difference') {
            $legacyId = (int) DB::table('activities')
                ->where('id', $id)
                ->where('type', 'spot_difference')
                ->value(DB::raw("CAST(JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.legacy_spot_difference_id')) AS UNSIGNED)"));

            $spotDifference = $legacyId > 0
                ? SpotDifference::query()
                    ->with('zones')
                    ->find($legacyId)
                : null;

            if ($spotDifference && $spotDifference->image_a_path && $spotDifference->image_b_path) {
                $response['spot_difference_data'] = [
                    'spot_difference' => SpotDifferenceApiSerializer::toArray($spotDifference),
                ];
            }
        }

        if ($activity->type === 'word_search') {
            $legacyId = (int) DB::table('activities')
                ->where('id', $id)
                ->where('type', 'word_search')
                ->value(DB::raw("CAST(JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.legacy_word_search_id')) AS UNSIGNED)"));

            $wordSearch = $legacyId > 0
                ? WordSearch::query()->find($legacyId)
                : null;

            if ($wordSearch && is_array($wordSearch->grid) && $wordSearch->grid !== []) {
                $response['word_search_data'] = [
                    'word_search' => WordSearchApiSerializer::toArray($wordSearch),
                ];
            }
        }

        if ($activity->type === 'drawing_kit') {
            $legacyId = (int) DB::table('activities')
                ->where('id', $id)
                ->where('type', 'drawing_kit')
                ->value(DB::raw("CAST(JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.legacy_drawing_id')) AS UNSIGNED)"));

            $drawing = $legacyId > 0
                ? Drawing::query()->find($legacyId)
                : null;

            if ($drawing) {
                $response['drawing_data'] = [
                    'drawing' => DrawingApiSerializer::toArray($drawing),
                ];
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

        if ($activity->type === 'culture') {
            $legacyId = (int) data_get($activity->metadata, 'legacy_culture_activity_id');
            $cultureActivity = $legacyId > 0
                ? CultureActivity::query()->find($legacyId)
                : null;

            if ($cultureActivity && $cultureActivity->status === 'published') {
                $response['culture_activity'] = CultureApiSerializer::toArray($cultureActivity);
            }
        }

        return response()->json($response);
    }
}
