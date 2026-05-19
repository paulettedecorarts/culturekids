<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Organisation;
use App\Services\OrganisationModuleToggleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrganisationModuleAdminController extends Controller
{
    public function update(
        Request $request,
        Organisation $organisation,
        OrganisationModuleToggleService $toggleService,
    ): JsonResponse {
        $data = $request->validate([
            'modules' => 'required|array|min:1',
            'modules.*.id' => 'required|integer|exists:modules,id',
            'modules.*.enabled' => 'required|boolean',
        ]);

        $modulesById = Module::query()
            ->whereIn('id', collect($data['modules'])->pluck('id'))
            ->get()
            ->keyBy('id');

        $organisation = $organisation->fresh();

        foreach ($data['modules'] as $row) {
            $module = $modulesById->get($row['id']);
            if (! $module) {
                continue;
            }

            $currentlyEnabled = $toggleService->isEnabledForOrganisation($organisation, $module);
            $desired = (bool) $row['enabled'];

            if ($currentlyEnabled === $desired) {
                continue;
            }

            $toggleService->toggle($organisation, $module, 'ORG_MODULES_API_SYNC');
            $organisation = $organisation->fresh(['modules']);
        }

        return response()->json(['ok' => true]);
    }
}
