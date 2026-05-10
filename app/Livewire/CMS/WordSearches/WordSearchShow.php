<?php

namespace App\Livewire\CMS\WordSearches;

use App\Livewire\Concerns\UsesPortalContext;
use App\Models\WordSearch;
use Livewire\Component;

class WordSearchShow extends Component
{
    use UsesPortalContext;

    public WordSearch $activity;

    public function mount(int $id): void
    {
        $this->activity = WordSearch::with(['tribe', 'attempts'])->findOrFail($id);
    }

    public function edit(): void
    {
        $this->redirectRoute(
            $this->portalRouteName('word-searches.edit'),
            ['id' => $this->activity->id],
            navigate: true
        );
    }

    public function render()
    {
        return view('livewire.cms.word-searches.word-search-show', [
            'routePrefix' => $this->portalRoutePrefix(),
        ])->layout($this->portalLayout());
    }
}
