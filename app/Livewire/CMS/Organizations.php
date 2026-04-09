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
    }

    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $org = Organisation::find($this->organizationId);
        if (! $org) {
            return;
        }

        $org->update([
            'name' => $this->name,
            'address' => $this->address,
            'description' => $this->description,
        ]);

        AuditLog::record('UPDATE_ORGANIZATION_PROFILE', "organisations/{$org->id}", [
            'org_code' => $org->code,
        ]);

        session()->flash('message', 'Organization profile updated.');
    }

    public function render()
    {
        $orgId = auth()->user()?->organisation_id;

        $adminCount = User::query()
            ->where('organisation_id', $orgId)
            ->whereHas('roles', fn ($query) => $query->whereIn('name', ['org_admin', 'super_admin']))
            ->count();

        $teacherCount = User::query()
            ->where('organisation_id', $orgId)
            ->whereHas('roles', fn ($query) => $query->where('name', 'teacher'))
            ->count();

        $editorCount = User::query()
            ->where('organisation_id', $orgId)
            ->whereHas('roles', fn ($query) => $query->where('name', 'cms_editor'))
            ->count();

        return view('livewire.cms.organizations', [
            'adminCount' => $adminCount,
            'teacherCount' => $teacherCount,
            'editorCount' => $editorCount,
            'totalUsers' => $adminCount + $teacherCount + $editorCount,
        ]);
    }
}
