<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AgeProfile;
use Illuminate\Http\JsonResponse;

class AgeProfileController extends Controller
{
    public function index(): JsonResponse
    {
        $profiles = AgeProfile::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('min_age')
            ->get([
                'id',
                'name',
                'key',
                'min_age',
                'max_age',
                'icon_emoji',
                'color',
                'ui_scale',
                'touch_target_px',
                'reading_level',
                'activity_complexity',
                'content_access_rules',
                'ui_features',
                'is_audio_first',
            ]);

        return response()->json([
            'age_profiles' => $profiles,
        ]);
    }
}
