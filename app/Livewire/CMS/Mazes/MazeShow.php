<?php

namespace App\Livewire\CMS\Mazes;

use App\Livewire\Concerns\UsesPortalContext;
use App\Models\Maze;
use Livewire\Component;

class MazeShow extends Component
{
    use UsesPortalContext;

    public Maze $maze;

    public function mount(int $id): void
    {
        $this->maze = Maze::with(['tribe', 'attempts'])->findOrFail($id);
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
