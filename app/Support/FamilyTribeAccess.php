<?php

namespace App\Support;

use App\Models\ChildProfile;
use App\Models\User;

class FamilyTribeAccess
{
    /**
     * Parent account that controls tribe access for the whole family.
     */
    public static function familyAccount(User $user): User
    {
        if ($user->hasRole('parent')) {
            return $user;
        }

        if ($user->hasRole('child')) {
            $profile = ChildProfile::query()
                ->where('child_user_id', $user->id)
                ->with('user')
                ->first();

            if ($profile?->user) {
                return $profile->user;
            }
        }

        return $user;
    }

    /**
     * @return list<int>
     */
    public static function approvedTribeIdsFor(User $user): array
    {
        return self::familyAccount($user)->approvedTribeIds();
    }

    public static function hasApprovedTribes(User $user): bool
    {
        return self::familyAccount($user)->hasApprovedTribes();
    }

    /**
     * Family (parent/child) accounts require parent-approved tribes.
     * Individual learners and organisation users do not.
     */
    public static function requiresApprovedTribes(User $user): bool
    {
        if ($user->hasRole('individual')) {
            return false;
        }

        $familyUser = self::familyAccount($user);

        if ($familyUser->organisation_id) {
            return false;
        }

        return $familyUser->hasRole('parent') || $user->hasRole('child');
    }

    public static function ensureTribeAllowed(User $user, int $tribeId): void
    {
        if (! self::requiresApprovedTribes($user)) {
            return;
        }

        $familyUser = self::familyAccount($user);

        abort_unless(in_array($tribeId, $familyUser->approvedTribeIds(), true), 403, 'This tribe has not been approved for your family.');
    }
}
