<?php

namespace App\Livewire\Admin;

use App\Models\AgeProfile;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class AgeCategories extends Component
{
    public function render()
    {
        return view('livewire.admin.age-categories', [
            'categories' => AgeProfile::query()
                ->withCount('childProfiles')
                ->orderBy('sort_order')
                ->orderBy('min_age')
                ->get(),
        ]);
    }
}
