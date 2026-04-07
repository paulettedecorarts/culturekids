<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\User;

#[Layout('layouts.admin')]
class UserDetail extends Component
{
    public User $user;

    public function mount(User $user)
    {
        $this->user = $user->load(['roles', 'organisation', 'childProfiles.progressEvents.activity']);
    }

    public function toggleRole($roleName)
    {
        if ($this->user->hasRole($roleName)) {
            $this->user->removeRole($roleName);
        } else {
            $this->user->assignRole($roleName);
        }
        $this->user->refresh();
        session()->flash('message', 'Platform privileges updated.');
    }

    public function render()
    {
        return view('livewire.admin.user-detail');
    }
}
