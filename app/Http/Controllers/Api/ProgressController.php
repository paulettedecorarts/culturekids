<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\ChildProfile;
use App\Models\ProgressEvent;
use App\Models\ReadingProgress;
use App\Models\User;
use App\Services\ChildAchievementService;
use App\Services\ChildContentProgressService;
use App\Services\FamilyLeaderboardService;
use App\Support\ChildProfileAccess;
use App\Support\ContentProgressType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ProgressController extends Controller
{
    public function __construct(
        private readonly ChildContentProgressService $progressService,
        private readonly ChildAchievementService $achievementService,
        private readonly FamilyLeaderboardService $leaderboardService,
    ) {}
    /**
     * Record progress events (mark activities as done)
     */
    public function recordEvents(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'events' => 'required|array',
            'events.*.child_profile_id' => 'required|integer',
            'events.*.activity_id' => 'required|integer',
            'events.*.idempotency_key' => 'required|string',
            'events.*.completed_at' => 'required|date',
            'events.*.activity_type' => 'nullable|string|in:'.implode(',', ContentProgressType::ALL),
            'events.*.performance' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        $recorded = [];
        $skipped = [];

        foreach ($request->events as $event) {
            $child = ChildProfileAccess::queryFor($user)
                ->where('id', $event['child_profile_id'])
                ->first();

            if (! $child) {
                $skipped[] = $event['idempotency_key'];
                continue;
            }

            $activity = Activity::find($event['activity_id']);
            if (! $activity) {
                $skipped[] = $event['idempotency_key'];
                continue;
            }

            $contentType = $event['activity_type'] ?? $activity->type;
            if (! in_array($contentType, ContentProgressType::ALL, true)) {
                $skipped[] = $event['idempotency_key'];
                continue;
            }

            try {
                $result = $this->progressService->complete(
                    $user,
                    $child,
                    $contentType,
                    (int) $event['activity_id'],
                    $event['idempotency_key'],
                    $event['performance'] ?? null,
                );

                if (! ($result['already_recorded'] ?? false)) {
                    $recorded[] = $event['idempotency_key'];
                } else {
                    $skipped[] = $event['idempotency_key'];
                }
            } catch (\Throwable) {
                $skipped[] = $event['idempotency_key'];
            }
        }

        return response()->json([
            'message' => 'Progress recorded',
            'recorded' => count($recorded),
            'skipped' => count($skipped),
        ]);
    }

    /**
     * Get progress for a specific child
     */
    public function getChildProgress(Request $request, $childId)
    {
        $child = ChildProfileAccess::findForUserOrFail($request->user(), (int) $childId);

        $cacheKey = sprintf(
            'child_achievements:%d:%d:%d',
            $child->id,
            (int) $child->total_stars,
            $child->updated_at?->timestamp ?? 0,
        );

        $payload = Cache::remember($cacheKey, 60, fn () => $this->achievementService->build($child));

        return response()->json($payload);
    }

    /**
     * Family-scoped star leaderboard (sibling child profiles on the same account).
     */
    public function getFamilyLeaderboard(Request $request, int $childId)
    {
        $payload = $this->leaderboardService->build($request->user(), $childId);

        return response()->json($payload);
    }

    /**
     * Bulk sync progress events (for offline sync)
     */
    public function sync(Request $request)
    {
        return $this->recordEvents($request);
    }

    /**
     * Get progress for the authenticated user (for stories/reading progress)
     */
    public function getUserProgress(Request $request)
    {
        $user = $request->user();

        $child = ChildProfileAccess::queryFor($user)->orderByDesc('updated_at')->first();
        if ($child) {
            return response()->json(
                array_merge(
                    $this->achievementService->build($child),
                    ['legacy_route' => false],
                ),
            );
        }

        return response()->json(
            array_merge(
                $this->buildLegacyUserProgress($user),
                ['legacy_route' => true, 'deprecated' => true],
            ),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function buildLegacyUserProgress(User $user): array
    {
        $totalCompleted = ReadingProgress::where('user_id', $user->id)
            ->where('status', 'completed')
            ->count();

        // Get in-progress stories count
        $inProgress = ReadingProgress::where('user_id', $user->id)
            ->where('status', 'in_progress')
            ->count();

        // Calculate total stars from completed stories
        $totalStars = ReadingProgress::where('reading_progress.user_id', $user->id)
            ->where('reading_progress.status', 'completed')
            ->join('comics', 'reading_progress.comic_id', '=', 'comics.id')
            ->sum('comics.star_points');

        // Build milestones array for stories
        $milestones = [
            [
                'id' => 'first_steps',
                'title' => 'First Steps',
                'description' => 'Complete your first story',
                'icon' => '👣',
                'target' => 1,
                'current' => $totalCompleted,
                'unlocked' => $totalCompleted >= 1,
                'type' => 'stories',
                'color' => '#10B981',
            ],
            [
                'id' => 'getting_started',
                'title' => 'Getting Started',
                'description' => 'Complete 10 stories',
                'icon' => '🌱',
                'target' => 10,
                'current' => $totalCompleted,
                'unlocked' => $totalCompleted >= 10,
                'type' => 'stories',
                'color' => '#3B82F6',
            ],
            [
                'id' => 'dedicated_learner',
                'title' => 'Dedicated Learner',
                'description' => 'Complete 50 stories',
                'icon' => '🌳',
                'target' => 50,
                'current' => $totalCompleted,
                'unlocked' => $totalCompleted >= 50,
                'type' => 'stories',
                'color' => '#059669',
            ],
            [
                'id' => 'activity_champion',
                'title' => 'Activity Champion',
                'description' => 'Complete 100 stories',
                'icon' => '🎯',
                'target' => 100,
                'current' => $totalCompleted,
                'unlocked' => $totalCompleted >= 100,
                'type' => 'stories',
                'color' => '#DC2626',
            ],
            [
                'id' => 'bronze_explorer',
                'title' => 'Bronze Explorer',
                'description' => 'Earn 100 stars',
                'icon' => '🥉',
                'target' => 100,
                'current' => $totalStars,
                'unlocked' => $totalStars >= 100,
                'type' => 'stars',
                'color' => '#CD7F32',
            ],
            [
                'id' => 'silver_learner',
                'title' => 'Silver Learner',
                'description' => 'Earn 500 stars',
                'icon' => '🥈',
                'target' => 500,
                'current' => $totalStars,
                'unlocked' => $totalStars >= 500,
                'type' => 'stars',
                'color' => '#C0C0C0',
            ],
            [
                'id' => 'gold_hero',
                'title' => 'Gold Hero',
                'description' => 'Earn 1,000 stars',
                'icon' => '🥇',
                'target' => 1000,
                'current' => $totalStars,
                'unlocked' => $totalStars >= 1000,
                'type' => 'stars',
                'color' => '#FFD700',
            ],
            [
                'id' => 'platinum_champion',
                'title' => 'Platinum Champion',
                'description' => 'Earn 2,000 stars',
                'icon' => '💎',
                'target' => 2000,
                'current' => $totalStars,
                'unlocked' => $totalStars >= 2000,
                'type' => 'stars',
                'color' => '#E5E4E2',
            ],
        ];

        // Get tribe-specific progress
        $tribeProgress = ReadingProgress::where('reading_progress.user_id', $user->id)
            ->where('reading_progress.status', 'completed')
            ->join('comics', 'reading_progress.comic_id', '=', 'comics.id')
            ->join('tribes', 'comics.tribe_id', '=', 'tribes.id')
            ->select('tribes.id as tribe_id', 'tribes.name as tribe_name', 'tribes.hero_emoji as tribe_icon', 'tribes.color as tribe_color')
            ->selectRaw('COUNT(DISTINCT reading_progress.comic_id) as completed_stories')
            ->selectRaw('SUM(comics.star_points) as stars_earned')
            ->groupBy('tribes.id', 'tribes.name', 'tribes.hero_emoji', 'tribes.color')
            ->get();

        // Get total stories per tribe
        $totalStoriesPerTribe = DB::table('comics')
            ->where('status', 'published')
            ->select('tribe_id')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('tribe_id')
            ->pluck('total', 'tribe_id');

        // Build tribe badges
        $badges = $tribeProgress->map(function ($tribe) use ($totalStoriesPerTribe) {
            $total = $totalStoriesPerTribe[$tribe->tribe_id] ?? 10;
            return [
                'tribe_id' => $tribe->tribe_id,
                'tribe_name' => $tribe->tribe_name,
                'tribe_icon' => $tribe->tribe_icon,
                'tribe_color' => $tribe->tribe_color,
                'completed_stories' => $tribe->completed_stories,
                'total_stories' => $total,
                'stars_earned' => $tribe->stars_earned ?? 0,
                'unlocked' => $tribe->completed_stories > 0,
            ];
        });

        return response()->json([
            'user' => $user,
            'total_stars' => $totalStars ?? 0,
            'total_stories_completed' => $totalCompleted,
            'in_progress_stories' => $inProgress,
            'badges' => $badges,
            'milestones' => $milestones,
        ]);
    }
}
