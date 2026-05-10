<?php

namespace App\Livewire\CMS\SpotDifferences;

use App\Livewire\Concerns\UsesPortalContext;
use App\Models\SpotDifference;
use Livewire\Component;

class SpotDifferenceShow extends Component
{
    use UsesPortalContext;

    public SpotDifference $activity;

    public function mount(int $id): void
    {
        $this->activity = SpotDifference::with(['tribe', 'zones', 'attempts'])->findOrFail($id);
    }

    public function edit(): void
    {
        $this->redirectRoute(
            $this->portalRouteName('spot-differences.edit'),
            ['id' => $this->activity->id],
            navigate: true
        );
    }

    public function render()
    {
        return view('livewire.cms.spot-differences.spot-difference-show', [
            'routePrefix' => $this->portalRoutePrefix(),
        ])->layout($this->portalLayout());
    }
}
