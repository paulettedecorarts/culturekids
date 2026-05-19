<?php

namespace App\Services\Admin;

use App\Models\ChildProfile;
use App\Models\Comic;
use App\Models\Organisation;
use App\Models\ProgressEvent;
use App\Models\ReadingProgress;
use App\Models\Tribe;
use Carbon\CarbonInterface;

class PlatformStatsService
{
    /**
     * @return array{
     *     active_children: int,
     *     active_children_this_week: int,
     *     organisations_active: int,
     *     organisations_new_this_month: int,
     *     published_stories: int,
     *     tribes_with_published_stories: int,
     *     learning_completions_7d: int,
     * }
     */
    public function snapshot(?CarbonInterface $now = null): array
    {
        $now = $now ?? now();
        $startOfWeek = $now->copy()->startOfWeek();
        $startOfMonth = $now->copy()->startOfMonth();
        $sevenDaysAgo = $now->copy()->subDays(7);

        $activeChildrenThisWeek = ChildProfile::query()
            ->where(function ($query) use ($startOfWeek) {
                $query->where('created_at', '>=', $startOfWeek)
                    ->orWhereHas('progressEvents', function ($q) use ($startOfWeek) {
                        $q->where('completed_at', '>=', $startOfWeek);
                    });
            })
            ->count();

        return [
            'active_children' => ChildProfile::count(),
            'active_children_this_week' => $activeChildrenThisWeek,
            'organisations_active' => Organisation::where('status', 'active')->count(),
            'organisations_new_this_month' => Organisation::where('created_at', '>=', $startOfMonth)->count(),
            'published_stories' => Comic::published()->count(),
            'tribes_with_published_stories' => Tribe::whereHas('comics', fn ($q) => $q->published())->count(),
            'learning_completions_7d' => $this->learningCompletionsSince($sevenDaysAgo),
        ];
    }

    public function learningCompletionsSince(CarbonInterface $since): int
    {
        $activityCompletions = ProgressEvent::query()
            ->where('completed_at', '>=', $since)
            ->count();

        $storyCompletions = ReadingProgress::query()
            ->where('status', 'completed')
            ->where(function ($query) use ($since) {
                $query->where('updated_at', '>=', $since)
                    ->orWhere('last_read_at', '>=', $since);
            })
            ->count();

        return $activityCompletions + $storyCompletions;
    }
}
