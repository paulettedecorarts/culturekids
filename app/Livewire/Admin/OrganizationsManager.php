<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\Organisation;
use App\Livewire\Concerns\LogsFileUploads;
use App\Livewire\Concerns\ValidatesOnlyChangedOnEdit;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class OrganizationsManager extends Component
{
    use LogsFileUploads;
    use ValidatesOnlyChangedOnEdit;
    use WithFileUploads;
    use WithPagination;

    public $search = '';

    public $showModal = false;

    public $selectedId;

    public $name;

    public $code;

    public $description;

    public $address;

    public string $plan = 'free';

    public $status = 'active';

    public $logo;

    public $logo_url;

    protected $rules = [
        'name' => 'required|min:3|max:100',
        'code' => 'required|alpha_dash|unique:organisations,code',
        'description' => 'nullable|max:500',
        'address' => 'nullable|max:255',
        'plan' => 'required|in:free,school,enterprise',
        'status' => 'required|in:active,inactive',
        'logo' => 'nullable|image|max:1024',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function edit($id)
    {
        $organization = Organisation::findOrFail($id);
        $this->selectedId = $id;
        $this->name = $organization->name;
        $this->code = $organization->code;
        $this->description = $organization->description;
        $this->address = $organization->address;
        $this->plan = in_array($organization->plan, ['free', 'school', 'enterprise'], true)
            ? $organization->plan
            : 'free';
        $this->status = $organization->status;
        $this->logo_url = $organization->logo_url;

        $this->showModal = true;
    }

    public function save()
    {
        $rules = $this->rules;
        $rules['code'] = 'required|alpha_dash|unique:organisations,code,'.$this->selectedId;

        $this->validate($rules);

        $data = [
            'name' => $this->name,
            'code' => $this->code,
            'description' => $this->description,
            'address' => $this->address,
            'plan' => $this->plan,
            'status' => $this->status,
        ];

        if ($this->logo) {
            $data['logo_url'] = $this->logo->store('logos', 'public');
        }

        $org = Organisation::findOrFail($this->selectedId);
        $org->update($data);
        AuditLog::record('UPDATE_ORGANISATION', "organisations/{$org->id}", [
            'code' => $org->code,
            'plan' => $org->plan,
            'status' => $org->status,
        ]);
        session()->flash('message', 'Organization updated successfully.');

        $this->showModal = false;
        $this->resetForm();
    }

    public function delete($id)
    {
        $org = Organisation::findOrFail($id);
        AuditLog::record('DELETE_ORGANISATION', "organisations/{$org->id}", [
            'code' => $org->code,
            'name' => $org->name,
        ]);
        $org->delete();
        session()->flash('message', 'Organization removed successfully.');
    }

    public function toggleStatus($id)
    {
        $org = Organisation::findOrFail($id);
        $org->status = $org->status === 'active' ? 'inactive' : 'active';
        $org->save();
        AuditLog::record('TOGGLE_ORGANISATION_STATUS', "organisations/{$org->id}", [
            'status' => $org->status,
        ]);
    }

    private function resetForm()
    {
        $this->selectedId = null;
        $this->name = '';
        $this->code = '';
        $this->description = '';
        $this->address = '';
        $this->plan = 'free';
        $this->status = 'active';
        $this->logo = null;
        $this->logo_url = null;
        $this->resetErrorBag();
    }

    public function render()
    {
        $search = $this->search;

        $organizations = Organisation::withCount(['users', 'childProfiles'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%'.$search.'%')
                        ->orWhere('code', 'like', '%'.$search.'%');
                });
            })
            ->latest()
            ->paginate(15);

        return view('livewire.admin.organizations-manager', [
            'organizations' => $organizations,
        ]);
    }
}
