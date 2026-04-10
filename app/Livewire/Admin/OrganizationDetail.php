<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\Module;
use App\Models\Organisation;
use App\Models\Tribe;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class OrganizationDetail extends Component
{
    public Organisation $organization;

    /** @var array<int, string> */
    public array $allowedTribeIds = [];

    public function mount(Organisation $organization): void
    {
        $this->organization = $organization->loadCount('users')->load('modules');
        $ids = data_get($this->organization->settings, 'allowed_tribe_ids', []);
        $this->allowedTribeIds = is_array($ids)
            ? array_values(array_map('strval', array_filter($ids)))
            : [];
    }

    public function toggleStatus(): void
    {
        $this->organization->status = $this->organization->status === 'active' ? 'inactive' : 'active';
        $this->organization->save();
        AuditLog::record('TOGGLE_ORGANISATION_STATUS', "organisations/{$this->organization->id}", [
            'status' => $this->organization->status,
        ]);
        session()->flash('message', 'Organization status updated.');
    }

    public function toggleOrgModule(int $moduleId): void
    {
        $module = Module::findOrFail($moduleId);
        if (! $module->is_enabled) {
            session()->flash('message', 'This module is disabled platform-wide. Enable it under Modules first.');

            return;
        }

        $org = $this->organization->fresh();
        $attached = $org->modules()->where('modules.id', $moduleId)->first();

        if ($attached) {
            $next = ! $attached->pivot->is_enabled;
            $org->modules()->updateExistingPivot($moduleId, ['is_enabled' => $next]);
        } else {
            $org->modules()->attach($moduleId, ['is_enabled' => false]);
        }

        $pivotRow = $org->modules()->where('modules.id', $moduleId)->first();
        AuditLog::record('ORG_MODULE_TOGGLE', "organisations/{$org->id}", [
            'module_key' => $module->key,
            'is_enabled' => $pivotRow ? (bool) $pivotRow->pivot->is_enabled : false,
        ]);

        $this->organization = $org->load('modules');
        session()->flash('message', 'Module access updated for this organization.');
    }

    public function saveTribeAccess(): void
    {
        $settings = $this->organization->settings ?? [];
        $settings['allowed_tribe_ids'] = array_values(array_unique(array_map('intval', $this->allowedTribeIds)));

        $this->organization->update(['settings' => $settings]);
        $this->organization->refresh();

        AuditLog::record('ORG_TRIBE_ACCESS', "organisations/{$this->organization->id}", [
            'allowed_tribe_ids' => $settings['allowed_tribe_ids'],
        ]);

        session()->flash('message', 'Tribe access saved. Empty selection means all tribes (full library).');
    }

    public function render()
    {
        $this->organization = $this->organization->fresh(['modules']);

        $org = $this->organization;

        $teachers = $org->users()
            ->whereHas('roles', fn ($q) => $q->where('name', 'teacher'))
            ->latest()
            ->get();

        $modules = Module::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $tribes = Tribe::query()->orderBy('name')->get();

        $activityLogs = AuditLog::query()
            ->where('resource', 'like', 'organisations/'.$org->id.'%')
            ->orderByDesc('created_at')
            ->limit(40)
            ->get();

        $moduleStates = $modules->mapWithKeys(fn (Module $m) => [
            $m->id => $this->moduleEnabledForOrg($org, $m),
        ]);

        return view('livewire.admin.organization-detail', [
            'teachers' => $teachers,
            'modules' => $modules,
            'moduleStates' => $moduleStates,
            'tribes' => $tribes,
            'activityLogs' => $activityLogs,
        ]);
    }

    private function moduleEnabledForOrg(Organisation $org, Module $module): bool
    {
        if (! $module->is_enabled) {
            return false;
        }

        $attached = $org->modules->firstWhere('id', $module->id);

        if ($attached === null) {
            return true;
        }

        return (bool) $attached->pivot->is_enabled;
    }
}
