<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\UsesPortalContext;
use App\Models\Activity;
use Livewire\Component;

class ActivityTypeSelector extends Component
{
    use UsesPortalContext;

    public function selectType(string $type)
    {
        if ($type === 'puzzle') {
            return $this->redirectRoute($this->portalRouteName('puzzles.create'), navigate: true);
        }
        
        if ($type === 'song') {
            return $this->redirectRoute($this->portalRouteName('songs.activities.create'), navigate: true);
        }
        
        if ($type === 'drawing') {
            return $this->redirectRoute($this->portalRouteName('drawings.create'), navigate: true);
        }
        
        if ($type === 'flashcard') {
            return $this->redirectRoute($this->portalRouteName('activities.create'), ['type' => 'flashcard'], navigate: true);
        }
        
        // For future activity types, add routing here
        return $this->redirectRoute($this->portalRouteName('activities.create'), ['type' => $type], navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.activity-type-selector', [
            'routePrefix' => $this->portalRoutePrefix(),
        ])->layout($this->portalLayout());
    }
}