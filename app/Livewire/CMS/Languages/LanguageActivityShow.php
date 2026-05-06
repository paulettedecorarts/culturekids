<?php

namespace App\Livewire\CMS\Languages;

use App\Livewire\Concerns\UsesPortalContext;
use App\Models\LanguageActivity;
use Livewire\Component;

class LanguageActivityShow extends Component
{
    use UsesPortalContext;

    public LanguageActivity $activity;

    public function mount(int $id): void
    {
        $this->activity = LanguageActivity::with(['tribe', 'words', 'attempts'])->findOrFail($id);
    }

    public function edit(): void
    {
        $this->redirectRoute(
            $this->portalRouteName('language-activities.edit'),
            ['id' => $this->activity->id],
            navigate: true
        );
    }

    public function render()
    {
        return view('livewire.cms.languages.language-activity-show', [
            'routePrefix' => $this->portalRoutePrefix(),
        ])->layout($this->portalLayout());
    }
}
