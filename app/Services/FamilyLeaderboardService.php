<?php

namespace App\Services;

use App\Models\ChildProfile;
use App\Models\User;
use App\Support\ChildProfileAccess;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class FamilyLeaderboardService
{
    /**
     * Rank child profiles by total stars.
     *
     * Org children are ranked against their classmates (classroom scope);
     * everyone else is ranked against sibling profiles on the same account
     * (family scope).
     *
     * @return array<string, mixed>
     */
    public function build(User $user, int $activeChildId): array
    {
        // Authorisation: the active child must belong to the caller.
        ChildProfileAccess::findForUserOrFail($user, $activeChildId);

        [$children, $scope] = $this->resolvePool($user);

        $entries = [];
        $rank = 0;
        $previousStars = null;

        foreach ($children->values() as $index => $candidate) {
            $stars = (int) $candidate->total_stars;

            if ($previousStars === null || $stars < $previousStars) {
                $rank = $index + 1;
            }

            $entries[] = [
                'rank' => $rank,
                'child_profile_id' => $candidate->id,
                'name' => $candidate->name,
                'avatar' => $candidate->avatar,
                'age_band' => $candidate->age_band,
                'total_stars' => $stars,
                'is_active_child' => $candidate->id === $activeChildId,
            ];

            $previousStars = $stars;
        }

        return [
            'scope' => $scope,
            'active_child_id' => $activeChildId,
            'entries' => $entries,
            'total_children' => count($entries),
        ];
    }

    /**
     * Decide who the active child competes against.
     *
     * Org children (their own login carries an organisation) are ranked within
     * their classroom(s). Org members with no class, and all family accounts,
     * fall back to sibling profiles on the account.
     *
     * @return array{0: Collection<int, ChildProfile>, 1: string}
     */
    private function resolvePool(User $user): array
    {
        if ($user->organisation_id) {
            $classroomIds = DB::table('classroom_user')
                ->where('user_id', $user->id)
                ->pluck('classroom_id');

            if ($classroomIds->isNotEmpty()) {
                return [$this->classroomPool($classroomIds), 'classroom'];
            }
        }

        $children = ChildProfileAccess::queryFor($user)
            ->orderByDesc('total_stars')
            ->orderBy('name')
            ->get(['id', 'name', 'avatar', 'age_band', 'total_stars']);

        return [$children, 'family'];
    }

    /**
     * Child profiles for every classmate enrolled in the given classroom(s).
     *
     * Org children may be linked to their login via either `user_id`
     * (teacher-created) or `child_user_id` (parent-created), so we match both.
     *
     * @param  Collection<int, int>  $classroomIds
     * @return Collection<int, ChildProfile>
     */
    private function classroomPool(Collection $classroomIds): Collection
    {
        $classmateUserIds = DB::table('classroom_user')
            ->whereIn('classroom_id', $classroomIds)
            ->pluck('user_id')
            ->unique()
            ->all();

        return ChildProfile::query()
            ->where(function ($query) use ($classmateUserIds): void {
                $query->whereIn('child_user_id', $classmateUserIds)
                    ->orWhereIn('user_id', $classmateUserIds);
            })
            ->orderByDesc('total_stars')
            ->orderBy('name')
            ->get(['id', 'name', 'avatar', 'age_band', 'total_stars']);
    }
}
