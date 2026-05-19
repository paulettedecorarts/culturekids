<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\UsesPortalContext;
use App\Services\Admin\PlatformAnalyticsService;
use Livewire\Component;

class AnalyticsManager extends Component
{
    use UsesPortalContext;

    public function render(PlatformAnalyticsService $analytics)
    {
        $snapshot = $analytics->engagementSnapshot();

        return view('livewire.admin.analytics-manager', [
            'analytics' => $snapshot,
            'activePupils' => $snapshot['active_pupils'],
            'activePupilsLastWeek' => $snapshot['active_pupils_last_week'],
            'totalCompletions' => $snapshot['total_completions'],
            'completionsToday' => $snapshot['completions_today'],
            'avgStars' => $snapshot['avg_stars'],
            'primaryRegion' => $snapshot['primary_organisation'],
            'weeklyEngagement' => $snapshot['weekly_engagement'],
            'maxCount' => $snapshot['max_count'],
            'topContent' => $snapshot['top_content'],
        ])->layout($this->portalLayout());
    }
}
