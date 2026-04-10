<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Module;
use App\Models\Organisation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrganisationModuleAdminController extends Controller
{
    public function update(Request $request, Organisation $organisation): JsonResponse
    {
        $data = $request->validate([
            'modules' => 'required|array|min:1',
            'modules.*.id' => 'required|integer|exists:modules,id',
            'modules.*.enabled' => 'required|boolean',
        ]);

        $modulesById = Module::query()
            ->whereIn('id', collect($data['modules'])->pluck('id'))
            ->get()
            ->keyBy('id');

        foreach ($data['modules'] as $row) {
            $module = $modulesById->get($row['id']);
            if (! $module) {
                continue;
            }
            if (! $module->is_enabled && $row['enabled']) {
                continue;
            }

            if ($organisation->modules()->where('modules.id', $row['id'])->exists()) {
                $organisation->modules()->updateExistingPivot($row['id'], ['is_enabled' => $row['enabled']]);
            } else {
                $organisation->modules()->attach($row['id'], ['is_enabled' => $row['enabled']]);
            }
        }

        AuditLog::record('ORG_MODULES_API_SYNC', "organisations/{$organisation->id}", [
            'modules' => $data['modules'],
        ]);

        return response()->json(['ok' => true]);
    }
}
