<?php

namespace App\Livewire\CMS;

use Livewire\Component;

class Dashboard extends Component
{
    public $stats = [];
    public $recentActivity = [];

    public function mount()
    {
        // Mock data for the Org Admin/Editor Dashboard
        $this->stats = [
            ['label' => 'Story Packs', 'val' => '12', 'delta' => '75% published'],
            ['label' => 'Total Assets', 'val' => '248', 'delta' => '+18 this week'],
            ['label' => 'Active Children', 'val' => '342', 'delta' => 'Across 14 classes'],
            ['label' => 'Pending Review', 'val' => '4', 'delta' => 'Needs attention'],
        ];

        $this->recentActivity = [
            ['type' => 'upload', 'title' => 'New Comic: The River Spirit', 'time' => '2 hours ago', 'status' => 'Processing'],
            ['type' => 'edit', 'title' => 'Updated Acholi Translations', 'time' => '5 hours ago', 'status' => 'Review'],
            ['type' => 'approve', 'title' => 'Garden Words Pack Published', 'time' => 'Yesterday', 'status' => 'Live'],
            ['type' => 'upload', 'title' => 'Audio Pack: Basoga Folk Songs', 'time' => '2 days ago', 'status' => 'Draft'],
        ];
    }

    public function render()
    {
        return view('livewire.cms.dashboard')
            ->layout('layouts.cms');
    }
}
