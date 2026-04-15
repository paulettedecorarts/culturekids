<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Tribe;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    /**
     * Get all activities for a specific tribe
     */
    public function getTribeActivities(Request $request, $tribeId)
    {
        $tribe = Tribe::findOrFail($tribeId);
        
        $activities = Activity::where('tribe_id', $tribeId)
            ->orderBy('type')
            ->orderBy('title')
            ->get([
                'id',
                'title',
                'type',
                'age_range',
                'stars',
                'icon',
                'description',
            ]);

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
     * Get a single activity
     */
    public function show(Request $request, $id)
    {
        $activity = Activity::with('tribe:id,name,color,hero_emoji,hero_icon')
            ->findOrFail($id);

        return response()->json([
            'id' => $activity->id,
            'title' => $activity->title,
            'type' => $activity->type,
            'age_range' => $activity->age_range,
            'stars' => $activity->stars,
            'icon' => $activity->icon,
            'description' => $activity->description,
            'tribe' => [
                'id' => $activity->tribe->id,
                'name' => $activity->tribe->name,
                'color' => $activity->tribe->color,
            ],
        ]);
    }
}
