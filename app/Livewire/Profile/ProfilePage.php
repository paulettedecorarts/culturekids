<?php

namespace App\Livewire\Profile;

use App\Support\PortalHome;
use Livewire\Component;

class ProfilePage extends Component
{
    public function render()
    {
        return view('livewire.profile.profile-page')
            ->layout(PortalHome::layoutFor(auth()->user()));
    }
}
