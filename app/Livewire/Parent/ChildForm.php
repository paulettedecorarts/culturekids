<?php

namespace App\Livewire\Parent;

use App\Actions\Family\CreateChildProfile;
use App\Support\ChildProfileAccess;
use App\Support\Heritage\HeritageChildSession;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.parent')]
class ChildForm extends Component
{
    public string $name = '';

    public string $date_of_birth = '';

    public string $avatar = '';

    public string $pin = '';

    public string $pin_confirmation = '';

    public ?string $createdChildEmail = null;

    public function save(CreateChildProfile $createChildProfile): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'avatar' => ['nullable', 'string', 'max:10'],
            'pin' => ['required', 'digits:4', 'confirmed'],
        ]);

        $result = $createChildProfile->create(
            auth()->user(),
            $validated['name'],
            $validated['date_of_birth'],
            $validated['pin'],
            $validated['avatar'] ?: null,
        );

        HeritageChildSession::setActiveProfileId($result['profile']->id);

        session()->flash('status', __('Child profile created. Child login: :email', ['email' => $result['child_email']]));

        $this->redirect(route('parent.children.index', absolute: false), navigate: true);
    }

    public function render()
    {
        $childCount = ChildProfileAccess::queryFor(auth()->user())->count();

        return view('livewire.parent.child-form', [
            'childCount' => $childCount,
        ]);
    }
}
