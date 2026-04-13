<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\UsesPortalContext;
use App\Models\ChildProfile;
use App\Models\LessonPlan;
use App\Models\Organisation;
use App\Models\ProgressEvent;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class AnalyticsManager extends Component
{
    use UsesPortalContext;

    public function render()
    {
        $now = now();
        $startOfWeek = $now->copy()->startOfWeek();
        $startOfToday = $now->copy()->startOfDay();
        
        // Active pupils (children with activity in last 30 days)
        $activePupils = ChildProfile::whereHas('progressEvents', function($q) use ($now) {
            $q->where('completed_at', '>=', $now->copy()->subDays(30));
        })->count();
        
        $activePupilsLastWeek = ChildProfile::whereHas('progressEvents', function($q) use ($startOfWeek) {
            $q->where('completed_at', '>=', $startOfWeek);
        })->count();
        
        // Total activity completions
        $totalCompletions = ProgressEvent::count();
        $completionsToday = ProgressEvent::where('completed_at', '>=', $startOfToday)->count();
        
        // Stars earned (engagement metric)
        $totalStars = ProgressEvent::sum('stars_earned');
        $avgStars = $totalCompletions > 0 ? round($totalStars / $totalCompletions, 1) : 0;
        
        // Primary region (most common organization)
        $primaryOrg = Organisation::withCount('users')
            ->orderByDesc('users_count')
            ->first();
        
        // Weekly engagement data (last 7 days)
        $weeklyData = ProgressEvent::where('completed_at', '>=', $now->copy()->subDays(6)->startOfDay())
            ->groupBy(DB::raw('DATE(completed_at)'))
            ->orderBy(DB::raw('DATE(completed_at)'))
            ->get([
                DB::raw('DATE(completed_at) as day'),
                DB::raw('COUNT(*) as count'),
            ]);
        
        // Fill missing days with 0
        $weeklyEngagement = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i)->toDateString();
            $found = $weeklyData->firstWhere('day', $date);
            $weeklyEngagement[] = [
                'day' => $date,
                'count' => $found ? $found->count : 0,
            ];
        }
        
        // Calculate max for percentage heights
        $maxCount = max(array_column($weeklyEngagement, 'count')) ?: 1;
        
        // Top performing content (lesson plans with most usage in last 7 days)
        $topContent = LessonPlan::where('created_at', '>=', $startOfWeek)
            ->where('lessonable_type', 'App\\Models\\Comic')
            ->with('lessonable')
            ->groupBy('lessonable_id', 'lessonable_type')
            ->orderByRaw('COUNT(*) DESC')
            ->limit(3)
            ->get([
                'lessonable_id',
                'lessonable_type',
                DB::raw('COUNT(*) as usage_count'),
            ]);
        
        // Load the actual comic titles
        $topContentWithTitles = $topContent->map(function($item) {
            return (object)[
                'title' => $item->lessonable?->title ?? 'Unknown',
                'usage_count' => $item->usage_count,
            ];
        });
        
        return view('livewire.admin.analytics-manager', [
            'activePupils' => $activePupils,
            'activePupilsLastWeek' => $activePupilsLastWeek,
            'totalCompletions' => $totalCompletions,
            'completionsToday' => $completionsToday,
            'avgStars' => $avgStars,
            'primaryRegion' => $primaryOrg?->name ?? 'No Data',
            'weeklyEngagement' => $weeklyEngagement,
            'maxCount' => $maxCount,
            'topContent' => $topContentWithTitles,
        ])
            ->layout($this->portalLayout());
    }
}
