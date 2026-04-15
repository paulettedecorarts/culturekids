<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChildProfile;
use App\Models\ProgressEvent;
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
            'events.*.child_profile_id' => 'required|exists:child_profiles,id',
            'events.*.activity_id' => 'required|exists:activities,id',
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

            // Create progress event
            $progressEvent = ProgressEvent::create([
                'child_profile_id' => $event['child_profile_id'],
                'activity_id' => $event['activity_id'],
                'idempotency_key' => $event['idempotency_key'],
                'completed_at' => $event['completed_at'],
                'synced_at' => now(),
            ]);

            // Update child's total stars
            $activity = $progressEvent->activity;
            $child->increment('total_stars', $activity->stars ?? 10);

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

        return response()->json([
            'child' => $child,
            'completed_activity_ids' => $completedActivityIds,
            'total_stars' => $child->total_stars,
            'badges' => $badges,
        ]);
    }

    /**
     * Bulk sync progress events (for offline sync)
     */
    public function sync(Request $request)
    {
        return $this->recordEvents($request);
    }
}
