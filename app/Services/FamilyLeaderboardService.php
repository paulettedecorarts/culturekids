<?php

namespace App\Services;

use App\Models\ChildProfile;
use App\Models\User;
use App\Support\ChildProfileAccess;

class FamilyLeaderboardService
{
    /**
     * Rank sibling child profiles by total stars (same parent account).
     *
     * @return array<string, mixed>
     */
    public function build(User $user, int $activeChildId): array
    {
        ChildProfileAccess::findForUserOrFail($user, $activeChildId);

        $children = ChildProfileAccess::queryFor($user)
            ->orderByDesc('total_stars')
            ->orderBy('name')
            ->get(['id', 'name', 'avatar', 'age_band', 'total_stars']);

        $entries = [];
        $rank = 0;
        $previousStars = null;

        foreach ($children as $index => $child) {
            $stars = (int) $child->total_stars;

            if ($previousStars === null || $stars < $previousStars) {
                $rank = $index + 1;
            }

            $entries[] = [
                'rank' => $rank,
                'child_profile_id' => $child->id,
                'name' => $child->name,
                'avatar' => $child->avatar,
                'age_band' => $child->age_band,
                'total_stars' => $stars,
                'is_active_child' => $child->id === $activeChildId,
            ];

            $previousStars = $stars;
        }

        return [
            'scope' => 'family',
            'active_child_id' => $activeChildId,
            'entries' => $entries,
            'total_children' => count($entries),
        ];
    }
}
