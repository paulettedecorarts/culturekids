<?php

namespace App\Livewire\Parent;

use App\Actions\Family\SyncParentApprovedTribes;
use App\Models\Tribe;
use App\Support\Heritage\HeritageChildSession;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.parent')]
class TribeAccessIndex extends Component
{
    /** @var array<int, string> */
    public array $approvedTribeIds = [];

    public function mount(): void
    {
        $this->approvedTribeIds = auth()->user()
            ->approvedTribes()
            ->pluck('tribes.id')
            ->map(fn ($id) => (string) $id)
            ->values()
            ->all();
    }

    public function save(SyncParentApprovedTribes $syncParentApprovedTribes): void
    {
        $this->validate([
            'approvedTribeIds' => ['required', 'array', 'min:1'],
            'approvedTribeIds.*' => ['integer', 'exists:tribes,id'],
        ], [
            'approvedTribeIds.required' => __('Select at least one tribe for your family.'),
            'approvedTribeIds.min' => __('Select at least one tribe for your family.'),
        ]);

        $syncParentApprovedTribes->sync(auth()->user(), $this->approvedTribeIds);

        session()->flash('status', __('Tribe access saved for your family.'));
    }

    public function saveAndPlay(SyncParentApprovedTribes $syncParentApprovedTribes): void
    {
        $this->validate([
            'approvedTribeIds' => ['required', 'array', 'min:1'],
            'approvedTribeIds.*' => ['integer', 'exists:tribes,id'],
        ], [
            'approvedTribeIds.required' => __('Select at least one tribe before playing.'),
            'approvedTribeIds.min' => __('Select at least one tribe before playing.'),
        ]);

        $syncParentApprovedTribes->sync(auth()->user(), $this->approvedTribeIds);

        $child = auth()->user()->childProfiles()->orderBy('name')->first();
        if ($child) {
            HeritageChildSession::setActiveProfileId($child->id);
        }

        $this->redirect(route('heritage.app', absolute: false), navigate: true);
    }

    public function render()
    {
        $parent = auth()->user()->loadCount('childProfiles');

        return view('livewire.parent.tribe-access-index', [
            'tribes' => Tribe::query()->orderBy('name')->get(),
            'childCount' => $parent->child_profiles_count,
        ]);
    }
}
