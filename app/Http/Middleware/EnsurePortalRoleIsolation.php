<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePortalRoleIsolation
{
    /**
     * Enforce strict role-portal isolation.
     *
     * Rules:
     * - User must have the required role for the portal.
     * - Super Admin cannot access role portals directly.
     * - Super Admin can access role portals only while impersonating.
     */
    public function handle(Request $request, Closure $next, string $requiredRole): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasRole($requiredRole)) {
            abort(403, 'Unauthorized portal access.');
        }

        $isImpersonating = (bool) session('impersonating');

        if ($isImpersonating) {
            $originalUserId = session('original_user_id');
            $impersonatorId = session('impersonator_id');

            if (! $originalUserId || ! $impersonatorId || (int) $originalUserId !== (int) $impersonatorId) {
                abort(403, 'Invalid impersonation session.');
            }

            $originalUser = User::find($originalUserId);
            if (! $originalUser || ! $originalUser->hasRole('super_admin')) {
                abort(403, 'Invalid impersonation origin.');
            }

            return $next($request);
        }

        // Direct super-admin entry into role portals is blocked.
        if ($user->hasRole('super_admin')) {
            abort(403, 'Super Admin must impersonate to access this portal.');
        }

        return $next($request);
    }
}
