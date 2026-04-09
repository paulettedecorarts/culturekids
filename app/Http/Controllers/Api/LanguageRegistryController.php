<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Http\JsonResponse;

class LanguageRegistryController extends Controller
{
    public function index(): JsonResponse
    {
        $languages = Language::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'native_name',
                'code',
                'flag_emoji',
                'translation_coverage',
                'audio_pack_available',
                'status',
            ]);

        return response()->json([
            'languages' => $languages,
        ]);
    }
}
