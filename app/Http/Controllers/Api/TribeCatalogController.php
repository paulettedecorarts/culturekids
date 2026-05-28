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
        $search = $request->query('search');

        $query = Tribe::query()
            ->orderBy('name');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('hero_name', 'like', "%{$search}%")
                  ->orWhere('region', 'like', "%{$search}%");
            });
        }

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
        ])->map(function ($tribe) {
            return [
                'id' => $tribe->id,
                'name' => $tribe->name,
                'hero' => $tribe->hero_name,
                'language' => 'Luganda', // Default for now
                'region' => $tribe->region,
                'color' => $tribe->color,
                'icon' => $tribe->hero_emoji ?? $tribe->hero_icon,
                'animal' => '', // Not in current schema
            ];
        });

        return response()->json($tribes);
    }
}
