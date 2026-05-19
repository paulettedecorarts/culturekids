<?php

namespace App\Livewire\Admin;

use App\Models\AuditLog;
use App\Models\Module;
use App\Models\Organisation;
use App\Models\Tribe;
use App\Services\OrganisationModuleToggleService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class OrganizationDetail extends Component
{
    public Organisation $organization;

    /** @var array<int, string> */
    public array $allowedTribeIds = [];

    public string $plan = 'free';

    public function mount(Organisation $organization): void
    {
        $this->organization = $organization->loadCount('users')->load('modules');
        $this->syncPlanFromOrganization();
        $ids = data_get($this->organization->settings, 'allowed_tribe_ids', []);
        $this->allowedTribeIds = is_array($ids)
            ? array_values(array_map('strval', array_filter($ids)))
            : [];
    }

    public function saveSubscriptionPlan(): void
    {
        $this->validate([
            'plan' => 'required|in:free,school,enterprise',
        ]);

        $this->organization->update(['plan' => $this->plan]);
        $this->organization->refresh();
        $this->syncPlanFromOrganization();

        AuditLog::record('UPDATE_ORGANISATION_PLAN', "organisations/{$this->organization->id}", [
            'plan' => $this->plan,
        ]);

        session()->flash('message', 'Subscription plan updated.');
    }

    private function syncPlanFromOrganization(): void
    {
        $p = $this->organization->plan;
        $this->plan = in_array($p, ['free', 'school', 'enterprise'], true) ? $p : 'free';
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

    public function toggleOrgModule(int $moduleId, OrganisationModuleToggleService $toggleService): void
    {
        $module = Module::findOrFail($moduleId);
        $result = $toggleService->toggle($this->organization, $module);

        if (! $result['ok']) {
            session()->flash('message', $result['message']);

            return;
        }

        $this->organization = $this->organization->fresh(['modules']);
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

        session()->flash('message', 'Tribe access saved. This list controls which heritage tribes are licensed for integrations and API catalog rules. Teacher story access in the hub comes from the CMS Review Queue (approved comics), not from this screen alone.');
    }

    public function render()
    {
        $this->organization = $this->organization->fresh(['modules'])->loadCount('users');

        $org = $this->organization;

        $orgUsers = $org->users()
            ->with('roles')
            ->orderBy('name')
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

        $toggleService = app(OrganisationModuleToggleService::class);
        $moduleStates = $modules->mapWithKeys(fn (Module $m) => [
            $m->id => $toggleService->isEnabledForOrganisation($org, $m),
        ]);

        return view('livewire.admin.organization-detail', [
            'orgUsers' => $orgUsers,
            'modules' => $modules,
            'moduleStates' => $moduleStates,
            'tribes' => $tribes,
            'activityLogs' => $activityLogs,
        ]);
    }

}
