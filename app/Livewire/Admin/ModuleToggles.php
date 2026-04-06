<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.admin')]
class ModuleToggles extends Component
{
    // Mocked state for presentation since 'modules' table may not exist yet
    public $modules = [
        [
            'id' => 'mod_tribe',
            'name' => 'Tribe Directory',
            'description' => 'All 65+ tribe profiles and cultural content',
            'status' => true,
            'icon' => '📚'
        ],
        [
            'id' => 'mod_comics',
            'name' => 'Comics & Stories',
            'description' => 'Story packs, panels, and comic reader',
            'status' => true,
            'icon' => '📖'
        ],
        [
            'id' => 'mod_audio',
            'name' => 'Songs & Audio',
            'description' => 'Traditional song library with read-aloud',
            'status' => true,
            'icon' => '🎵'
        ],
        [
            'id' => 'mod_vocab',
            'name' => 'Flashcards / Vocab',
            'description' => 'Tribe-linked vocabulary card sessions',
            'status' => true,
            'icon' => '🗣'
        ],
        [
            'id' => 'mod_kiosk',
            'name' => 'Kiosk Mode',
            'description' => 'Museum and cultural center installations',
            'status' => false,
            'icon' => '🖥️'
        ],
        [
            'id' => 'mod_worksheets',
            'name' => 'Worksheets',
            'description' => 'Teacher-facing printable activities',
            'status' => true,
            'icon' => '🖨️'
        ],
        [
            'id' => 'mod_monetization',
            'name' => 'Monetization',
            'description' => 'Premium pack purchases and IAP',
            'status' => false,
            'icon' => '💰'
        ],
        [
            'id' => 'mod_tourism',
            'name' => 'Tourism Mode',
            'description' => 'Visitor-facing cultural center mode',
            'status' => false,
            'icon' => '🗺️'
        ],
    ];

    public function toggle($index)
    {
        // In a real app, update DB. Here we flip the boolean for interactive UI feel.
        $this->modules[$index]['status'] = !$this->modules[$index]['status'];
    }

    public function render()
    {
        return view('livewire.admin.module-toggles');
    }
}
