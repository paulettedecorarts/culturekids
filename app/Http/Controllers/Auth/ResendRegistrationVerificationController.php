<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ResendRegistrationVerificationController extends Controller
{
    /**
     * Resend verification code for a guest who just registered (no login).
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $user = User::query()
            ->where('email', strtolower($validated['email']))
            ->first();

        if ($user && ! $user->hasVerifiedEmail()) {
            session([
                'pending_verification_user_id' => $user->id,
                'pending_verification_remember' => false,
            ]);

            $user->sendEmailVerificationNotification();
        }

        return redirect()
            ->route('verification.enter-code')
            ->with('status', 'verification-code-sent');
    }
}
