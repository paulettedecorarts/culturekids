<?php

namespace App\Livewire\Profile;

use App\Support\PortalHome;
use Livewire\Component;

class ProfilePage extends Component
{
    public function render()
    {
        $user = auth()->user();
        $user?->load('roles');

        return view('livewire.profile.profile-page', [
            'user' => $user,
        ])->layout(PortalHome::layoutFor($user));
    }
}
