<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\Organisation;
use Livewire\Attributes\Layout;

#[Layout('layouts.admin')]
class OrganizationDetail extends Component
{
    public Organisation $organization;
    public $message = '';

    public function mount(Organisation $organization)
    {
        $this->organization = $organization->loadCount('users');
    }

    public function toggleStatus()
    {
        $this->organization->status = $this->organization->status === 'active' ? 'inactive' : 'active';
        $this->organization->save();
        session()->flash('message', 'Organization status updated.');
    }

    public function render()
    {
        $teachers = $this->organization->users()->whereHas('roles', function($q) {
            $q->where('name', 'teacher');
        })->latest()->get();

        return view('livewire.admin.organization-detail', [
            'teachers' => $teachers,
        ]);
    }
}
