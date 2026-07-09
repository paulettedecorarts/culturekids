<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Request;

class PortalHome
{
    /**
     * Named route for the authenticated user's primary dashboard.
     */
    public static function dashboardRouteName(?User $user): string
    {
        if (! $user) {
            return 'login';
        }

        if ($user->hasRole('super_admin')) {
            return 'admin.dashboard';
        }

        if ($user->hasRole('cms_editor')) {
            return 'cms.editor.dashboard';
        }

        if ($user->hasRole('org_admin')) {
            return 'cms.admin.dashboard';
        }

        if ($user->hasRole('teacher')) {
            return 'teacher.dashboard';
        }

        if ($user->hasRole('parent')) {
            return 'parent.dashboard';
        }

        if ($user->hasRole('child')) {
            return 'heritage.app';
        }

        return 'dashboard';
    }

    /**
     * Blade layout for account settings (profile) per portal.
     */
    public static function layoutFor(?User $user): string
    {
        if (! $user) {
            return 'layouts.app';
        }

        if ($user->hasRole('super_admin')) {
            return 'layouts.admin';
        }

        if ($user->hasRole('cms_editor') || $user->hasRole('org_admin')) {
            return 'layouts.cms';
        }

        if ($user->hasRole('teacher')) {
            return 'layouts.teacher';
        }

        if ($user->hasRole('parent')) {
            return 'layouts.parent';
        }

        return 'layouts.app';
    }

    /**
     * @return list<string>
     */
    public static function dashboardRouteNames(): array
    {
        return [
            'dashboard',
            'admin.dashboard',
            'cms.editor.dashboard',
            'cms.admin.dashboard',
            'teacher.dashboard',
            'parent.dashboard',
            'heritage.app',
        ];
    }

    public static function isDashboardRoute(Request $request): bool
    {
        foreach (self::dashboardRouteNames() as $name) {
            if ($request->routeIs($name)) {
                return true;
            }
        }

        return false;
    }
}
