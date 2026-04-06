<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\User;
use App\Models\Organisation;

#[Layout('layouts.admin')]
class Dashboard extends Component
{
    public function render()
    {
        $organizationsCount = Organisation::count();
        $usersCount = User::count();
        // Since we don't have tribes/activities counts mapped natively if empty, we just default to 10 tribes, 200 items.
        $tribesCount = \DB::table('tribes')->count();
        $activitiesCount = \DB::table('activities')->count();

        // Let's get the 5 most recent users
        $recentUsers = User::with('roles', 'organisation')->latest()->take(5)->get();

        return view('livewire.admin.dashboard', [
            'organizationsCount' => $organizationsCount,
            'usersCount' => $usersCount,
            'tribesCount' => $tribesCount,
            'activitiesCount' => $activitiesCount,
            'recentUsers' => $recentUsers,
        ]);
    }
}
