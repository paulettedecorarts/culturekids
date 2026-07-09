<?php

namespace App\Http\Controllers\Api;

use App\Actions\Family\SyncParentApprovedTribes;
use App\Http\Controllers\Controller;
use App\Models\Tribe;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FamilyTribeAccessController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user->hasRole('parent') || $user->hasRole('child'), 403);

        $parent = \App\Support\FamilyTribeAccess::familyAccount($user);
        $approvedIds = $parent->approvedTribeIds();

        $tribes = Tribe::query()
            ->when($approvedIds !== [], fn ($q) => $q->whereIn('id', $approvedIds))
            ->when($approvedIds === [], fn ($q) => $q->whereRaw('1 = 0'))
            ->orderBy('name')
            ->get(['id', 'name', 'hero_name', 'hero_emoji', 'hero_icon', 'greeting', 'region', 'color'])
            ->map(fn (Tribe $tribe) => [
                'id' => $tribe->id,
                'name' => $tribe->name,
                'hero' => $tribe->hero_name,
                'region' => $tribe->region,
                'color' => $tribe->color,
                'icon' => $tribe->resolvedIcon(),
            ]);

        return response()->json([
            'approved_tribe_ids' => $approvedIds,
            'tribes' => $tribes,
        ]);
    }

    public function update(Request $request, SyncParentApprovedTribes $syncParentApprovedTribes): JsonResponse
    {
        $parent = $request->user();
        abort_unless($parent->hasRole('parent'), 403);

        $validated = $request->validate([
            'approved_tribe_ids' => ['required', 'array', 'min:1'],
            'approved_tribe_ids.*' => ['integer', 'exists:tribes,id'],
            'tribe_preferences' => ['sometimes', 'array', 'min:1'],
            'tribe_preferences.*' => ['integer', 'exists:tribes,id'],
        ]);

        $tribeIds = $validated['approved_tribe_ids'] ?? $validated['tribe_preferences'];

        $syncParentApprovedTribes->sync($parent, $tribeIds);

        return response()->json([
            'message' => 'Family tribe access updated.',
            'approved_tribe_ids' => $parent->fresh()->approvedTribeIds(),
        ]);
    }
}
