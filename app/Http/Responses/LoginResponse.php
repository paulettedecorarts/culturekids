<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     */
    public function toResponse($request): RedirectResponse|JsonResponse
    {
        $user = auth()->user();

        // Redirect based on user role
        if ($user->hasRole('super_admin')) {
            return redirect()->intended('/admin/dashboard');
        }

        if ($user->hasRole('cms_editor')) {
            return redirect()->intended('/cms/editor/dashboard');
        }

        if ($user->hasRole('org_admin')) {
            return redirect()->intended('/cms/admin/dashboard');
        }

        if ($user->hasRole('teacher')) {
            return redirect()->intended('/teacher/dashboard');
        }

        // If no role matches, redirect to home page
        return redirect('/');
    }
}
