<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Module;
use App\Models\Organisation;

class OrganisationModuleToggleService
{
    public function isEnabledForOrganisation(Organisation $org, Module $module): bool
    {
        if (! $module->is_enabled) {
            return false;
        }

        $org->loadMissing('modules');
        $attached = $org->modules->firstWhere('id', $module->id);

        if ($attached === null) {
            return true;
        }

        return (bool) $attached->pivot->is_enabled;
    }

    /**
     * @return array{ok: bool, enabled: bool, message: string|null}
     */
    public function toggle(Organisation $org, Module $module, ?string $auditAction = 'ORG_MODULE_TOGGLE'): array
    {
        if (! $module->is_enabled) {
            return [
                'ok' => false,
                'enabled' => false,
                'message' => 'This module is disabled platform-wide. Ask a platform administrator to enable it first.',
            ];
        }

        $org = $org->fresh();
        $attached = $org->modules()->where('modules.id', $module->id)->first();

        if ($attached) {
            $next = ! $attached->pivot->is_enabled;
            $org->modules()->updateExistingPivot($module->id, ['is_enabled' => $next]);
        } else {
            $org->modules()->attach($module->id, ['is_enabled' => false]);
            $next = false;
        }

        if ($auditAction !== null) {
            AuditLog::record($auditAction, "organisations/{$org->id}", [
                'module_key' => $module->key,
                'is_enabled' => $next,
            ]);
        }

        return [
            'ok' => true,
            'enabled' => $next,
            'message' => null,
        ];
    }
}
