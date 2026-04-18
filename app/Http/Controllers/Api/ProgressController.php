<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChildProfile;
use App\Models\ProgressEvent;
use App\Models\ReadingProgress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ProgressController extends Controller
{
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
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        $recorded = [];
        $skipped = [];

        foreach ($request->events as $event) {
            // Verify child belongs to authenticated user
            $child = ChildProfile::where('id', $event['child_profile_id'])
                ->where('user_id', $user->id)
                ->first();

            if (!$child) {
                $skipped[] = $event['idempotency_key'];
                continue;
            }

            // Check if event already exists (idempotency)
            $exists = ProgressEvent::where('idempotency_key', $event['idempotency_key'])->exists();
            
            if ($exists) {
                $skipped[] = $event['idempotency_key'];
                continue;
            }

            // Get the activity to determine stars earned
            $activity = \App\Models\Activity::find($event['activity_id']);
            $starsEarned = $activity?->stars ?? 10;

            // Create progress event
            $progressEvent = ProgressEvent::create([
                'child_profile_id' => $event['child_profile_id'],
                'activity_id' => $event['activity_id'],
                'stars_earned' => $starsEarned,
                'idempotency_key' => $event['idempotency_key'],
                'completed_at' => $event['completed_at'],
                'synced_at' => now(),
            ]);

            // Update child's total stars
            $child->increment('total_stars', $starsEarned);

            $recorded[] = $event['idempotency_key'];
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
        $child = ChildProfile::where('id', $childId)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        // Get completed activity IDs
        $completedActivityIds = ProgressEvent::where('child_profile_id', $childId)
            ->pluck('activity_id')
            ->toArray();

        $totalCompleted = count($completedActivityIds);

        // Get stars per tribe
        $tribeStars = DB::table('progress_events')
            ->join('activities', 'progress_events.activity_id', '=', 'activities.id')
            ->join('tribes', 'activities.tribe_id', '=', 'tribes.id')
            ->where('progress_events.child_profile_id', $childId)
            ->select('tribes.id as tribe_id', 'tribes.name as tribe_name', 'tribes.hero_emoji as tribe_icon', 'tribes.color as tribe_color')
            ->selectRaw('COUNT(DISTINCT progress_events.activity_id) as completed_activities')
            ->selectRaw('SUM(activities.stars) as stars_earned')
            ->groupBy('tribes.id', 'tribes.name', 'tribes.hero_emoji', 'tribes.color')
            ->get();

        // Get total activities per tribe
        $totalActivitiesPerTribe = DB::table('activities')
            ->select('tribe_id')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('tribe_id')
            ->pluck('total', 'tribe_id');

        // Build badges array
        $badges = $tribeStars->map(function ($tribe) use ($totalActivitiesPerTribe) {
            $total = $totalActivitiesPerTribe[$tribe->tribe_id] ?? 20;
            return [
                'tribe_id' => $tribe->tribe_id,
                'tribe_name' => $tribe->tribe_name,
                'tribe_icon' => $tribe->tribe_icon,
                'tribe_color' => $tribe->tribe_color,
                'completed_activities' => $tribe->completed_activities,
                'total_activities' => $total,
                'stars_earned' => $tribe->stars_earned ?? 0,
                'unlocked' => $tribe->completed_activities > 0,
            ];
        });

        // Count completed tribes
        $completedTribes = $tribeStars->filter(function ($tribe) use ($totalActivitiesPerTribe) {
            $total = $totalActivitiesPerTribe[$tribe->tribe_id] ?? 20;
            return $tribe->completed_activities >= $total;
        })->count();

        // Calculate milestones
        $totalStars = $child->total_stars;
        $milestones = [
            [
                'id' => 'bronze_explorer',
                'title' => 'Bronze Explorer',
                'description' => 'Earn 100 stars',
                'icon' => '🥉',
                'target' => 100,
                'current' => $totalStars,
                'unlocked' => $totalStars >= 100,
                'type' => 'stars',
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
            ],
            [
                'id' => 'heritage_master',
                'title' => 'Heritage Master',
                'description' => 'Earn 3,500 stars',
                'icon' => '👑',
                'target' => 3500,
                'current' => $totalStars,
                'unlocked' => $totalStars >= 3500,
                'type' => 'stars',
            ],
            [
                'id' => 'tribe_explorer',
                'title' => 'Tribe Explorer',
                'description' => 'Complete 1 tribe',
                'icon' => '🗺️',
                'target' => 1,
                'current' => $completedTribes,
                'unlocked' => $completedTribes >= 1,
                'type' => 'tribes',
            ],
            [
                'id' => 'cultural_learner',
                'title' => 'Cultural Learner',
                'description' => 'Complete 3 tribes',
                'icon' => '📚',
                'target' => 3,
                'current' => $completedTribes,
                'unlocked' => $completedTribes >= 3,
                'type' => 'tribes',
            ],
            [
                'id' => 'heritage_hero',
                'title' => 'Heritage Hero',
                'description' => 'Complete 5 tribes',
                'icon' => '🦸',
                'target' => 5,
                'current' => $completedTribes,
                'unlocked' => $completedTribes >= 5,
                'type' => 'tribes',
            ],
            [
                'id' => 'uganda_master',
                'title' => 'Uganda Master',
                'description' => 'Complete all 10 tribes',
                'icon' => '🏆',
                'target' => 10,
                'current' => $completedTribes,
                'unlocked' => $completedTribes >= 10,
                'type' => 'tribes',
            ],
            [
                'id' => 'first_steps',
                'title' => 'First Steps',
                'description' => 'Complete your first activity',
                'icon' => '👣',
                'target' => 1,
                'current' => $totalCompleted,
                'unlocked' => $totalCompleted >= 1,
                'type' => 'activities',
            ],
            [
                'id' => 'getting_started',
                'title' => 'Getting Started',
                'description' => 'Complete 10 activities',
                'icon' => '🌱',
                'target' => 10,
                'current' => $totalCompleted,
                'unlocked' => $totalCompleted >= 10,
                'type' => 'activities',
            ],
            [
                'id' => 'dedicated_learner',
                'title' => 'Dedicated Learner',
                'description' => 'Complete 50 activities',
                'icon' => '🌳',
                'target' => 50,
                'current' => $totalCompleted,
                'unlocked' => $totalCompleted >= 50,
                'type' => 'activities',
            ],
            [
                'id' => 'activity_champion',
                'title' => 'Activity Champion',
                'description' => 'Complete 100 activities',
                'icon' => '🎯',
                'target' => 100,
                'current' => $totalCompleted,
                'unlocked' => $totalCompleted >= 100,
                'type' => 'activities',
            ],
            [
                'id' => 'ultimate_explorer',
                'title' => 'Ultimate Explorer',
                'description' => 'Complete all 200 activities',
                'icon' => '🌟',
                'target' => 200,
                'current' => $totalCompleted,
                'unlocked' => $totalCompleted >= 200,
                'type' => 'activities',
            ],
        ];

        return response()->json([
            'child' => $child,
            'completed_activity_ids' => $completedActivityIds,
            'total_stars' => $child->total_stars,
            'total_activities_completed' => $totalCompleted,
            'tribes_completed' => $completedTribes,
            'badges' => $badges,
            'milestones' => $milestones,
        ]);
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

        // Get completed stories count
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
