<?php

namespace App\Livewire\CMS\Culture;

use App\Livewire\Concerns\UsesPortalContext;
use App\Models\CultureActivity;
use Livewire\Component;

class CultureActivityShow extends Component
{
    use UsesPortalContext;

    public CultureActivity $activity;

    public function mount(int $id): void
    {
        $this->activity = CultureActivity::with(['tribe', 'attempts'])->findOrFail($id);
    }

    public function edit(): void
    {
        $this->redirectRoute(
            $this->portalRouteName('culture-activities.edit'),
            ['id' => $this->activity->id],
            navigate: true
        );
    }

    public function render()
    {
        return view('livewire.cms.culture.culture-activity-show', [
            'routePrefix' => $this->portalRoutePrefix(),
        ])->layout($this->portalLayout());
    }
}
