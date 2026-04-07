<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use App\Models\Organisation;
use Illuminate\Support\Str;

#[Layout('layouts.admin')]
class OrganizationsManager extends Component
{
    use WithPagination, WithFileUploads;

    public $search = '';
    public $showModal = false;
    public $editing = false;
    
    // Form fields
    public $selectedId;
    public $name;
    public $code;
    public $description;
    public $address;
    public $status = 'active';
    public $logo; // For upload
    public $logo_url;

    protected $rules = [
        'name' => 'required|min:3|max:100',
        'code' => 'required|alpha_dash|unique:organisations,code',
        'description' => 'nullable|max:500',
        'address' => 'nullable|max:255',
        'status' => 'required|in:active,inactive',
        'logo' => 'nullable|image|max:1024', // 1MB Max
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function create()
    {
        $this->resetForm();
        $this->editing = false;
        $this->showModal = true;
    }

    public function edit($id)
    {
        $organization = Organisation::findOrFail($id);
        $this->selectedId = $id;
        $this->name = $organization->name;
        $this->code = $organization->code;
        $this->description = $organization->description;
        $this->address = $organization->address;
        $this->status = $organization->status;
        $this->logo_url = $organization->logo_url;
        
        $this->editing = true;
        $this->showModal = true;
    }

    public function save()
    {
        $rules = $this->rules;
        if ($this->editing) {
            $rules['code'] = 'required|alpha_dash|unique:organisations,code,' . $this->selectedId;
        }

        $this->validate($rules);

        $data = [
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'address' => $this->address,
            'status' => $this->status,
        ];

        if ($this->logo) {
            $data['logo_url'] = $this->logo->store('logos', 'public');
        }

        if ($this->editing) {
            Organisation::findOrFail($this->selectedId)->update($data);
            session()->flash('message', 'Organization updated successfully.');
        } else {
            Organisation::create($data);
            session()->flash('message', 'Organization created successfully.');
        }

        $this->showModal = false;
        $this->resetForm();
    }

    public function delete($id)
    {
        Organisation::findOrFail($id)->delete();
        session()->flash('message', 'Organization removed successfully.');
    }

    public function toggleStatus($id)
    {
        $org = Organisation::findOrFail($id);
        $org->status = $org->status === 'active' ? 'inactive' : 'active';
        $org->save();
    }

    private function resetForm()
    {
        $this->selectedId = null;
        $this->name = '';
        $this->code = '';
        $this->description = '';
        $this->address = '';
        $this->status = 'active';
        $this->logo = null;
        $this->logo_url = null;
        $this->resetErrorBag();
    }

    public function render()
    {
        $organizations = Organisation::withCount('users')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('code', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(15);

        return view('livewire.admin.organizations-manager', [
            'organizations' => $organizations,
        ]);
    }
}
