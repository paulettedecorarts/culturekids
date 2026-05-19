<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AgeProfile;
use App\Services\OrganisationModuleResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgeProfileController extends Controller
{
    public function index(Request $request, OrganisationModuleResolver $moduleResolver): JsonResponse
    {
        $user = $request->user();

        $profiles = AgeProfile::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('min_age')
            ->get()
            ->map(fn (AgeProfile $profile) => $moduleResolver->formatAgeProfileForApi($profile, $user))
            ->values();

        return response()->json([
            'age_profiles' => $profiles,
        ]);
    }
}
