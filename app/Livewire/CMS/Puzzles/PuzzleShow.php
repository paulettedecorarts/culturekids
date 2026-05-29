<?php

namespace App\Livewire\CMS\Puzzles;

use App\Livewire\Concerns\RegeneratesPuzzleTiles;
use App\Livewire\Concerns\UsesPortalContext;
use App\Models\Activity;
use App\Models\AgeProfile;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Component;

class PuzzleShow extends Component
{
    use RegeneratesPuzzleTiles;
    use UsesPortalContext;

    public Activity $activity;

    public function mount(int $id): void
    {
        $this->activity = Activity::query()
            ->with('tribe')
            ->where('type', 'puzzle')
            ->findOrFail($id);

        $this->mountRegenerateDefaults($this->activity);
    }

    #[Computed]
    public function ageProfiles()
    {
        return AgeProfile::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('min_age')
            ->get();
    }

    public function deletePuzzle()
    {
        Storage::disk('public')->deleteDirectory('jigsaw-puzzles/'.$this->activity->id);
        $this->activity->delete();
        session()->flash('message', 'Puzzle deleted.');

        return $this->redirectRoute($this->portalRouteName('puzzles'), navigate: true);
    }

    public function render()
    {
        return view('livewire.cms.puzzles.puzzle-show', [
            'routePrefix' => $this->portalRoutePrefix(),
        ])->layout($this->portalLayout());
    }
}
