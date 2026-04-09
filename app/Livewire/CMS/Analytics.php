<?php

namespace App\Livewire\CMS;

use App\Models\ProgressEvent;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.cms')]
class Analytics extends Component
{
    public int $rangeDays = 30;

    public function render()
    {
        $user = auth()->user();
        $orgId = $user?->organisation_id;
        $orgName = $user?->organisation?->name ?? 'Your Organization';

        if (! $orgId) {
            return view('livewire.cms.analytics', [
                'organization' => $orgName,
                'kpis' => $this->emptyKpis(),
                'dailyRows' => [],
                'typeRows' => [],
                'rangeDays' => $this->rangeDays,
            ]);
        }

        $now = now();
        $start30 = $now->copy()->subDays(30)->startOfDay();
        $start7 = $now->copy()->subDays(7)->startOfDay();
        $startRange = $now->copy()->subDays(max($this->rangeDays - 1, 0))->startOfDay();

        $baseQuery = ProgressEvent::query()
            ->join('child_profiles', 'child_profiles.id', '=', 'progress_events.child_profile_id')
            ->join('users', 'users.id', '=', 'child_profiles.user_id')
            ->where('users.organisation_id', $orgId);

        $totalEvents = (clone $baseQuery)->count();
        $eventsLast7 = (clone $baseQuery)->where('progress_events.completed_at', '>=', $start7)->count();
        $eventsLast30 = (clone $baseQuery)->where('progress_events.completed_at', '>=', $start30)->count();
        $activeChildren30 = (clone $baseQuery)
            ->where('progress_events.completed_at', '>=', $start30)
            ->distinct('progress_events.child_profile_id')
            ->count('progress_events.child_profile_id');
        $starsLast30 = (int) ((clone $baseQuery)
            ->where('progress_events.completed_at', '>=', $start30)
            ->sum('progress_events.stars_earned'));
        $avgEventsPerChild30 = $activeChildren30 > 0 ? round($eventsLast30 / $activeChildren30, 1) : 0.0;

        $dailySeries = (clone $baseQuery)
            ->where('progress_events.completed_at', '>=', $startRange)
            ->groupBy(DB::raw('DATE(progress_events.completed_at)'))
            ->orderBy(DB::raw('DATE(progress_events.completed_at)'))
            ->get([
                DB::raw('DATE(progress_events.completed_at) as day'),
                DB::raw('COUNT(*) as events_count'),
                DB::raw('SUM(progress_events.stars_earned) as stars_sum'),
            ]);

        $dailyRows = $this->fillDateSeries($dailySeries, $startRange, $now);

        $typeRows = (clone $baseQuery)
            ->leftJoin('activities', 'activities.id', '=', 'progress_events.activity_id')
            ->where('progress_events.completed_at', '>=', $start30)
            ->groupBy('activities.type')
            ->orderByDesc(DB::raw('COUNT(*)'))
            ->limit(8)
            ->get([
                DB::raw('COALESCE(activities.type, "unknown") as activity_type'),
                DB::raw('COUNT(*) as events_count'),
                DB::raw('SUM(progress_events.stars_earned) as stars_sum'),
            ]);

        return view('livewire.cms.analytics', [
            'organization' => $orgName,
            'kpis' => [
                ['label' => 'Total Events', 'value' => number_format($totalEvents), 'hint' => 'All time'],
                ['label' => 'Events (7d)', 'value' => number_format($eventsLast7), 'hint' => 'Last 7 days'],
                ['label' => 'Active Children (30d)', 'value' => number_format($activeChildren30), 'hint' => 'Distinct child profiles'],
                ['label' => 'Stars Earned (30d)', 'value' => number_format($starsLast30), 'hint' => 'Engagement points'],
                ['label' => 'Avg Events / Child (30d)', 'value' => number_format($avgEventsPerChild30, 1), 'hint' => 'Activity density'],
            ],
            'dailyRows' => $dailyRows,
            'typeRows' => $typeRows,
            'rangeDays' => $this->rangeDays,
        ]);
    }

    private function fillDateSeries(Collection $rows, CarbonInterface $startDate, CarbonInterface $endDate): array
    {
        $indexed = $rows->keyBy('day');
        $out = [];
        $cursor = $startDate->copy();

        while ($cursor->lte($endDate)) {
            $key = $cursor->toDateString();
            $row = $indexed->get($key);

            $out[] = [
                'day' => $key,
                'events_count' => (int) ($row->events_count ?? 0),
                'stars_sum' => (int) ($row->stars_sum ?? 0),
            ];

            $cursor = $cursor->addDay();
        }

        return $out;
    }

    private function emptyKpis(): array
    {
        return [
            ['label' => 'Total Events', 'value' => '0', 'hint' => 'All time'],
            ['label' => 'Events (7d)', 'value' => '0', 'hint' => 'Last 7 days'],
            ['label' => 'Active Children (30d)', 'value' => '0', 'hint' => 'Distinct child profiles'],
            ['label' => 'Stars Earned (30d)', 'value' => '0', 'hint' => 'Engagement points'],
            ['label' => 'Avg Events / Child (30d)', 'value' => '0.0', 'hint' => 'Activity density'],
        ];
    }
}
