<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tribe;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TribeCatalogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user()->loadMissing('organisation');

        $query = Tribe::query()
            ->orderBy('name');

        $org = $user->organisation;
        if ($org) {
            $allowed = $org->restrictedTribeIds();
            if ($allowed !== null) {
                $query->whereIn('id', $allowed);
            }
        }

        $tribes = $query->get([
            'id',
            'name',
            'hero_name',
            'hero_emoji',
            'hero_icon',
            'greeting',
            'region',
            'color',
        ]);

        return response()->json([
            'tribes' => $tribes,
        ]);
    }
}
