<?php

namespace App\Support\Heritage;

use App\Models\ChildProfile;
use App\Support\ChildProfileAccess;
use Illuminate\Http\Request;

final class HeritageChildSession
{
    public const SESSION_KEY = 'heritage.child_profile_id';

    public static function activeProfileId(Request $request): ?int
    {
        $id = $request->session()->get(self::SESSION_KEY);

        return is_numeric($id) ? (int) $id : null;
    }

    public static function setActiveProfile(Request $request, int $childProfileId): void
    {
        $request->session()->put(self::SESSION_KEY, $childProfileId);
    }

    public static function clear(Request $request): void
    {
        $request->session()->forget(self::SESSION_KEY);
    }

    public static function resolveActiveProfile(Request $request): ChildProfile
    {
        $user = $request->user();

        if ($user->hasRole('child')) {
            return ChildProfileAccess::ensureForUser($user)
                ?? ChildProfileAccess::queryFor($user)->firstOrFail();
        }

        $profileId = self::activeProfileId($request);

        if ($profileId === null) {
            abort(403, 'Select a child profile to continue.');
        }

        return ChildProfileAccess::findForUserOrFail($user, $profileId);
    }
}
