<?php

namespace App\Services\Admin;

use App\Models\ChildProfile;
use App\Models\LessonPlan;
use App\Models\Organisation;
use App\Models\ProgressEvent;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PlatformAnalyticsService
{
    /**
     * @return array{
     *     active_pupils: int,
     *     active_pupils_last_week: int,
     *     total_completions: int,
     *     completions_today: int,
     *     avg_stars: float,
     *     primary_organisation: string,
     *     weekly_engagement: list<array{day: string, count: int}>,
     *     max_count: int,
     *     top_content: Collection<int, object{title: string, usage_count: int}>,
     * }
     */
    public function engagementSnapshot(?CarbonInterface $now = null): array
    {
        $now = $now ?? now();
        $startOfWeek = $now->copy()->startOfWeek();
        $startOfToday = $now->copy()->startOfDay();

        $activePupils = ChildProfile::query()
            ->whereHas('progressEvents', function ($q) use ($now) {
                $q->where('completed_at', '>=', $now->copy()->subDays(30));
            })
            ->count();

        $activePupilsLastWeek = ChildProfile::query()
            ->whereHas('progressEvents', function ($q) use ($startOfWeek) {
                $q->where('completed_at', '>=', $startOfWeek);
            })
            ->count();

        $totalCompletions = ProgressEvent::query()->count();
        $completionsToday = ProgressEvent::query()
            ->where('completed_at', '>=', $startOfToday)
            ->count();

        $totalStars = (int) ProgressEvent::query()->sum('stars_earned');
        $avgStars = $totalCompletions > 0 ? round($totalStars / $totalCompletions, 1) : 0.0;

        $primaryOrg = Organisation::query()
            ->withCount('users')
            ->orderByDesc('users_count')
            ->first();

        $weeklyData = ProgressEvent::query()
            ->where('completed_at', '>=', $now->copy()->subDays(6)->startOfDay())
            ->groupBy(DB::raw('DATE(completed_at)'))
            ->orderBy(DB::raw('DATE(completed_at)'))
            ->get([
                DB::raw('DATE(completed_at) as day'),
                DB::raw('COUNT(*) as count'),
            ]);

        $weeklyEngagement = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i)->toDateString();
            $found = $weeklyData->firstWhere('day', $date);
            $weeklyEngagement[] = [
                'day' => $date,
                'count' => $found ? (int) $found->count : 0,
            ];
        }

        $maxCount = max(array_column($weeklyEngagement, 'count')) ?: 1;

        $topContent = LessonPlan::query()
            ->where('created_at', '>=', $startOfWeek)
            ->where('lessonable_type', 'App\\Models\\Comic')
            ->with('lessonable')
            ->groupBy('lessonable_id', 'lessonable_type')
            ->orderByRaw('COUNT(*) DESC')
            ->limit(3)
            ->get([
                'lessonable_id',
                'lessonable_type',
                DB::raw('COUNT(*) as usage_count'),
            ])
            ->map(fn ($item) => (object) [
                'title' => $item->lessonable?->title ?? 'Unknown',
                'usage_count' => (int) $item->usage_count,
            ]);

        return [
            'active_pupils' => $activePupils,
            'active_pupils_last_week' => $activePupilsLastWeek,
            'total_completions' => $totalCompletions,
            'completions_today' => $completionsToday,
            'avg_stars' => $avgStars,
            'primary_organisation' => $primaryOrg?->name ?? 'No Data',
            'weekly_engagement' => $weeklyEngagement,
            'max_count' => $maxCount,
            'top_content' => $topContent,
        ];
    }
}
