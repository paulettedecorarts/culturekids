<?php

namespace App\Livewire\CMS;

use App\Models\AuditLog;
use App\Models\Organisation;
use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Layout;

#[Layout('layouts.cms')]
class Organizations extends Component
{
    public $organizationId;

    public $name = '';

    public $code = '';

    public $status = 'inactive';

    public $plan = 'free';

    public $address = '';

    public $description = '';

    public $logo_url = '';

    public int $adminCount = 0;

    public int $teacherCount = 0;

    public int $totalUsers = 0;

    public bool $showEditModal = false;

    public string $editName = '';

    public string $editAddress = '';

    public string $editDescription = '';

    public function mount(): void
    {
        $org = auth()->user()?->organisation;
        if (! $org) {
            return;
        }

        $this->organizationId = $org->id;
        $this->name = $org->name;
        $this->code = $org->code;
        $this->status = $org->status ?? 'inactive';
        $this->plan = $org->plan ?? 'free';
        $this->address = $org->address ?? '';
        $this->description = $org->description ?? '';
        $this->logo_url = $org->logo_url ?? '';

        $this->refreshOrgUserCounts();
    }

    public function openEditModal(): void
    {
        $this->editName = $this->name;
        $this->editAddress = $this->address;
        $this->editDescription = $this->description;
        $this->resetValidation();
        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->resetValidation();
    }

    public function save(): void
    {
        $this->validate([
            'editName' => ['required', 'string', 'max:120'],
            'editAddress' => ['nullable', 'string', 'max:255'],
            'editDescription' => ['nullable', 'string', 'max:2000'],
        ]);

        $org = Organisation::find($this->organizationId);
        if (! $org) {
            return;
        }

        $org->update([
            'name' => $this->editName,
            'address' => $this->editAddress,
            'description' => $this->editDescription,
        ]);

        $this->name = $this->editName;
        $this->address = $this->editAddress;
        $this->description = $this->editDescription;

        AuditLog::record('UPDATE_ORGANIZATION_PROFILE', "organisations/{$org->id}", [
            'org_code' => $org->code,
        ]);

        $this->refreshOrgUserCounts();

        $this->showEditModal = false;
        $this->resetValidation();

        session()->flash('message', 'Organization profile updated.');
    }

    protected function refreshOrgUserCounts(): void
    {
        $orgId = auth()->user()?->organisation_id;
        if (! $orgId) {
            $this->adminCount = 0;
            $this->teacherCount = 0;
            $this->totalUsers = 0;

            return;
        }

        $this->adminCount = User::query()
            ->where('organisation_id', $orgId)
            ->whereHas('roles', fn ($query) => $query->whereIn('name', ['org_admin', 'super_admin']))
            ->count();

        $this->teacherCount = User::query()
            ->where('organisation_id', $orgId)
            ->whereHas('roles', fn ($query) => $query->where('name', 'teacher'))
            ->count();

        $this->totalUsers = $this->adminCount + $this->teacherCount;
    }

    public function render()
    {
        return view('livewire.cms.organizations');
    }
}
