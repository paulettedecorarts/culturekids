<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonationController extends Controller
{
    public function stop(Request $request)
    {
        // Security check: only allow stopping if actually impersonating
        if (!session('impersonating')) {
            abort(403, 'Not currently impersonating.');
        }

        $originalUserId = session('original_user_id');
        $currentUser = auth()->user();

        // Log the stop impersonation
        AuditLog::record('STOP_IMPERSONATE', "users/{$currentUser->id}", [
            'impersonated_user' => $currentUser->email,
        ]);

        // Clear impersonation session
        session()->forget(['impersonating', 'impersonator_id', 'original_user_id']);

        // Login back as the original super admin
        $originalUser = User::findOrFail($originalUserId);
        Auth::login($originalUser);

        return redirect()->route('admin.dashboard')->with('message', 'Impersonation stopped successfully.');
    }
}
