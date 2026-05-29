<?php

namespace App\Support;

use App\Models\ChildProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Resolves child profiles for both:
 * - Parent accounts (profiles.user_id = parent)
 * - Child logins (profiles.child_user_id = child user, or org profiles.user_id = child user)
 */
final class ChildProfileAccess
{
    public static function queryFor(User $user): Builder
    {
        return ChildProfile::query()->where(function (Builder $q) use ($user): void {
            $q->where('user_id', $user->id)
                ->orWhere('child_user_id', $user->id);
        });
    }

    public static function findForUserOrFail(User $user, int $childProfileId): ChildProfile
    {
        return self::queryFor($user)->where('id', $childProfileId)->firstOrFail();
    }

    public static function canAccess(User $user, ChildProfile $child): bool
    {
        return (int) $child->user_id === (int) $user->id
            || (int) $child->child_user_id === (int) $user->id;
    }
}
