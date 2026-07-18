<?php

namespace App\Livewire\Individual;

use App\Services\Heritage\HeritageClientCatalogService;
use App\Services\Heritage\HeritageClientProgressService;
use App\Support\ChildProfileAccess;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.individual')]
class MainDashboard extends Component
{
    public string $learnerName = '';

    /** @var array<int, array{attainment: string, label: string}> */
    public array $stats = [];

    /** @var list<array{label: string, value: string, pct: int, tone: string}> */
    public array $progressRows = [];

    /** @var list<array{icon: string, name: string, color: string}> */
    public array $badges = [];

    public string $culturalQuote = '';

    public string $culturalNote = '';

    public function mount(
        HeritageClientCatalogService $catalog,
        HeritageClientProgressService $progress,
    ): void {
        $user = auth()->user();
        $this->learnerName = $user?->name ? (string) $user->name : __('Learner');

        $profile = $user ? ChildProfileAccess::ensureForUser($user) : null;
        $bootstrap = $user && $profile
            ? $catalog->bootstrap($user, $profile)
            : ['tribes' => []];
        $stored = $user && $profile
            ? $progress->load($user, $profile)
            : ['stars' => 0, 'done' => [], 'tStars' => []];
        $summary = $progress->summarize($bootstrap['tribes'] ?? [], $stored);

        $this->stats = [
            ['attainment' => (string) ($summary['stars'] ?? 0), 'label' => __('Stars earned')],
            ['attainment' => (string) ($summary['activitiesCompleted'] ?? 0), 'label' => __('Activities done')],
            ['attainment' => (string) ($summary['tribesStarted'] ?? 0), 'label' => __('Tribes started')],
        ];

        $this->progressRows = $this->buildProgressRows($bootstrap['tribes'] ?? [], $stored);
        $this->badges = $this->buildBadges($summary);
        $this->culturalQuote = __('“The hare is clever, not fast.”');
        $this->culturalNote = __('In Buganda folklore, the hare wins through wit, not speed. What clever choice will you make in your next activity?');
    }

    /**
     * @param  list<array<string, mixed>>  $tribes
     * @param  array{stars?: int, done?: array<string, bool>, tStars?: array<string, int>}  $stored
     * @return list<array{label: string, value: string, pct: int, tone: string}>
     */
    protected function buildProgressRows(array $tribes, array $stored): array
    {
        $done = is_array($stored['done'] ?? null) ? $stored['done'] : [];
        $doneKeys = array_keys(array_filter($done));
        $tStars = is_array($stored['tStars'] ?? null) ? $stored['tStars'] : [];
        $tones = ['green', 'sun', 'sky'];

        $rows = [];
        foreach (array_slice($tribes, 0, 3) as $index => $tribe) {
            $tribeId = (string) ($tribe['id'] ?? '');
            $name = (string) ($tribe['name'] ?? __('Tribe'));
            $activities = is_array($tribe['activities'] ?? null) ? $tribe['activities'] : [];
            $total = max(count($activities), 1);
            $completed = count(array_filter(
                $doneKeys,
                static fn (string $key): bool => str_starts_with($key, $tribeId.'_'),
            ));
            $pct = (int) round(($completed / $total) * 100);
            $stars = (int) ($tStars[$tribeId] ?? 0);

            $rows[] = [
                'label' => $name,
                'value' => $completed > 0
                    ? __(':done / :total · :stars stars', ['done' => $completed, 'total' => count($activities), 'stars' => $stars])
                    : __('Not started yet'),
                'pct' => $pct,
                'tone' => $tones[$index % count($tones)],
            ];
        }

        return $rows;
    }

    /**
     * @param  array{stars?: int, activitiesCompleted?: int, tribesStarted?: int}  $summary
     * @return list<array{icon: string, name: string, color: string}>
     */
    protected function buildBadges(array $summary): array
    {
        $badges = [];

        if (($summary['stars'] ?? 0) >= 10) {
            $badges[] = ['icon' => '⭐', 'name' => __('Star Seeker'), 'color' => 'gold'];
        }
        if (($summary['activitiesCompleted'] ?? 0) >= 1) {
            $badges[] = ['icon' => '🔥', 'name' => __('First Steps'), 'color' => 'clay'];
        }
        if (($summary['tribesStarted'] ?? 0) >= 1) {
            $badges[] = ['icon' => '🌿', 'name' => __('Explorer'), 'color' => 'green'];
        }

        if ($badges === []) {
            $badges[] = ['icon' => '🌱', 'name' => __('Ready to learn'), 'color' => 'gold'];
        }

        return $badges;
    }

    public function render()
    {
        return view('livewire.individual.main-dashboard');
    }
}
