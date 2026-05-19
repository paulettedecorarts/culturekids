<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OrganisationModuleResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrganisationModuleController extends Controller
{
    public function show(Request $request, OrganisationModuleResolver $resolver): JsonResponse
    {
        $user = $request->user();
        $modules = $resolver->modulesForUser($user);

        return response()->json([
            'organisation_id' => $user->organisation_id,
            'modules' => $modules,
            'enabled_keys' => collect($modules)
                ->filter(fn (array $row) => $row['enabled'])
                ->pluck('key')
                ->values()
                ->all(),
        ]);
    }
}
