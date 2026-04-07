<?php

namespace App\Livewire\CMS;

use Livewire\Component;

class Dashboard extends Component
{
    public $stats = [];
    public $recentActivity = [];

    public function mount()
    {
        // Stats will be populated from actual database queries
        $this->stats = [
            ['label' => 'Story Packs', 'val' => '0', 'delta' => 'No data yet'],
            ['label' => 'Total Assets', 'val' => '0', 'delta' => 'No data yet'],
            ['label' => 'Active Children', 'val' => '0', 'delta' => 'No data yet'],
            ['label' => 'Pending Review', 'val' => '0', 'delta' => 'No data yet'],
        ];

        $this->recentActivity = [];
    }

    public function render()
    {
        return view('livewire.cms.dashboard')
            ->layout('layouts.cms');
    }
}
