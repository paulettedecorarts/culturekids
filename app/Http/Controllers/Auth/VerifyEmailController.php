<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VerifyEmailController extends Controller
{
    /**
     * Mark the user's email verified via signed link (no login required).
     */
    public function __invoke(Request $request, string $id, string $hash): RedirectResponse
    {
        $user = User::query()->findOrFail($id);

        if (! hash_equals((string) $id, (string) $user->getKey())) {
            abort(403);
        }

        if (! hash_equals(sha1($user->getEmailForVerification()), (string) $hash)) {
            abort(403);
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()
                ->route('login')
                ->with('status', 'email-already-verified');
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return redirect()
            ->route('login')
            ->with('status', 'email-verified');
    }
}
