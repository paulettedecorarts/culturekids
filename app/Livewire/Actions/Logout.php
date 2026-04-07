<?php

namespace App\Livewire\Actions;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Logout
{
    /**
     * Log the current user out of the application.
     */
    public function __invoke(): void
    {
        // If impersonating, log the stop action before logout
        if (session('impersonating')) {
            \App\Models\AuditLog::record('STOP_IMPERSONATE', 'logout', [
                'impersonated_user' => auth()->user()?->email,
                'reason' => 'User logged out',
            ]);
        }

        Auth::guard('web')->logout();

        Session::invalidate();
        Session::regenerateToken();
    }
}
