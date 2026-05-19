<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OrganisationModuleResolver;
use App\Services\OrganisationThemeResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrganisationThemeController extends Controller
{
    public function show(
        Request $request,
        OrganisationThemeResolver $themeResolver,
        OrganisationModuleResolver $moduleResolver,
    ): JsonResponse {
        $user = $request->user();
        $themeEngineEnabled = $moduleResolver->isEnabledForUser($user, 'theme_engine');

        $theme = $themeEngineEnabled
            ? $themeResolver->resolveForUser($user)
            : $themeResolver->resolveForOrganisation(null);

        return response()->json([
            'theme' => $theme,
            'theme_engine_enabled' => $themeEngineEnabled,
        ]);
    }
}
