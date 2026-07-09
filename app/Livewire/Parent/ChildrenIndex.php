<?php

namespace App\Livewire\Parent;

use App\Models\ChildProfile;
use App\Support\ChildProfileAccess;
use App\Support\FamilyTribeAccess;
use App\Support\Heritage\HeritageChildSession;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.parent')]
class ChildrenIndex extends Component
{
    public function playAs(ChildProfile $childProfile): void
    {
        $profile = ChildProfileAccess::findForUserOrFail(auth()->user(), $childProfile->id);

        if (! FamilyTribeAccess::hasApprovedTribes(auth()->user())) {
            session()->flash('status', __('Approve tribes for your family before playing Heritage Heroes.'));

            $this->redirect(route('parent.tribe-access', absolute: false), navigate: true);

            return;
        }

        HeritageChildSession::setActiveProfileId($profile->id);

        $this->redirect(route('heritage.app', absolute: false), navigate: true);
    }

    public function render()
    {
        $children = ChildProfileAccess::queryFor(auth()->user())
            ->with('childUser')
            ->orderBy('name')
            ->get();

        return view('livewire.parent.children-index', [
            'children' => $children,
        ]);
    }
}
