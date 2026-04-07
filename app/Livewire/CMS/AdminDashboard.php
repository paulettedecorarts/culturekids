<?php

namespace App\Livewire\CMS;

use Livewire\Component;

class AdminDashboard extends Component
{
    public $organization = 'Naluwooza Creative Space';
    public $metrics = [];
    public $brandingStatus = 'Published';

    public function mount()
    {
        $this->metrics = [
            ['label' => 'Total Children', 'val' => '1,204', 'status' => '↑ 12%'],
            ['label' => 'Active Teachers', 'val' => '18', 'status' => 'Stable'],
            ['label' => 'Curriculum Coverage', 'val' => '64%', 'status' => '↑ 3%'],
            ['label' => 'Media Storage', 'val' => '1.4GB', 'status' => '28% used'],
        ];
    }

    public function render()
    {
        return view('livewire.cms.admin-dashboard')
            ->layout('layouts.cms');
    }
}
