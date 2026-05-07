<?php

namespace App\Livewire\CMS\Games;

use App\Livewire\Concerns\UsesPortalContext;
use App\Models\Game;
use Livewire\Component;

class GameShow extends Component
{
    use UsesPortalContext;

    public Game $game;

    public function mount(int $id): void
    {
        $this->game = Game::with(['tribe', 'questions', 'attempts'])->findOrFail($id);
    }

    public function edit(): void
    {
        $this->redirectRoute(
            $this->portalRouteName('games.edit'),
            ['id' => $this->game->id],
            navigate: true
        );
    }

    public function render()
    {
        return view('livewire.cms.games.game-show', [
            'routePrefix' => $this->portalRoutePrefix(),
        ])->layout($this->portalLayout());
    }
}
