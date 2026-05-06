<?php

namespace App\Livewire\CMS\Drawings;

use App\Livewire\Concerns\UsesPortalContext;
use App\Models\Drawing;
use Livewire\Component;

class DrawingShow extends Component
{
    use UsesPortalContext;

    public Drawing $drawing;

    public function mount(int $id): void
    {
        $this->drawing = Drawing::with(['tribe', 'submissions.user'])->findOrFail($id);
    }

    public function edit(): void
    {
        $this->redirectRoute($this->portalRouteName('drawings.edit'), ['id' => $this->drawing->id], navigate: true);
    }

    public function render()
    {
        return view('livewire.cms.drawings.drawing-show', [
            'routePrefix' => $this->portalRoutePrefix(),
        ])->layout($this->portalLayout());
    }
}