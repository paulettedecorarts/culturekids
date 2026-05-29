<?php

namespace App\Livewire\CMS\Mazes;

use App\Livewire\Concerns\UsesPortalContext;
use App\Models\Maze;
use App\Models\MazeAttempt;
use Livewire\Component;

class MazeShow extends Component
{
    use UsesPortalContext;

    public Maze $maze;

    /** @var array{attempts:int,completed:int,avg_time:?string,avg_stars:?float} */
    public array $attemptStats = [
        'attempts' => 0,
        'completed' => 0,
        'avg_time' => null,
        'avg_stars' => null,
    ];

    public function mount(int $id): void
    {
        $this->maze = Maze::with('tribe')->findOrFail($id);

        $stats = MazeAttempt::query()
            ->where('maze_id', $this->maze->id)
            ->selectRaw('COUNT(*) as attempts, SUM(CASE WHEN completed = 1 THEN 1 ELSE 0 END) as completed_count, AVG(time_spent_seconds) as avg_time_seconds, AVG(stars_earned) as avg_stars')
            ->first();

        $avgSeconds = $stats?->avg_time_seconds ? (int) round((float) $stats->avg_time_seconds) : null;

        $this->attemptStats = [
            'attempts' => (int) ($stats?->attempts ?? 0),
            'completed' => (int) ($stats?->completed_count ?? 0),
            'avg_time' => $avgSeconds ? gmdate('i:s', $avgSeconds) : null,
            'avg_stars' => $stats?->avg_stars ? round((float) $stats->avg_stars, 1) : null,
        ];
    }

    public function edit(): void
    {
        $this->redirectRoute(
            $this->portalRouteName('mazes.edit'),
            ['id' => $this->maze->id],
            navigate: true
        );
    }

    public function render()
    {
        return view('livewire.cms.mazes.maze-show', [
            'routePrefix' => $this->portalRoutePrefix(),
        ])->layout($this->portalLayout());
    }
}
