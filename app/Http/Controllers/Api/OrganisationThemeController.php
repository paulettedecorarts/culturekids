<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OrganisationThemeResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrganisationThemeController extends Controller
{
    public function show(Request $request, OrganisationThemeResolver $resolver): JsonResponse
    {
        $theme = $resolver->resolveForUser($request->user());

        return response()->json([
            'theme' => $theme,
        ]);
    }
}
