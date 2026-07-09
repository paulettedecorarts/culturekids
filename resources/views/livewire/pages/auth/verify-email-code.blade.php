<?php

use App\Models\User;
use App\Models\VerificationCode;
use App\Support\PortalHome;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $code = '';

    public ?string $email = null;

    public function mount(): void
    {
        $user = $this->pendingUser();

        if (! $user || ! $user->requiresEmailVerification()) {
            $this->redirect(route('login', absolute: false), navigate: true);

            return;
        }

        $this->email = $user->email;
    }

    public function verifyCode(): void
    {
        $this->validate([
            'code' => ['required', 'string', 'size:6', 'regex:/^\d{6}$/'],
        ]);

        $user = $this->pendingUser();

        if (! $user) {
            $this->redirect(route('login', absolute: false), navigate: true);

            return;
        }

        $verificationCode = VerificationCode::query()
            ->where('user_id', $user->id)
            ->where('code', $this->code)
            ->where('is_used', false)
            ->first();

        if (! $verificationCode || ! $verificationCode->isValid()) {
            $this->addError('code', __('Invalid or expired verification code. Request a new code and try again.'));

            return;
        }

        $verificationCode->markAsUsed();

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        $remember = (bool) session('pending_verification_remember', false);

        Auth::login($user, $remember);
        Session::regenerate();
        $this->clearPendingVerificationSession();

        $this->redirect(route(PortalHome::dashboardRouteName($user), absolute: false), navigate: true);
    }

    public function resendCode(): void
    {
        $user = $this->pendingUser();

        if (! $user) {
            $this->redirect(route('login', absolute: false), navigate: true);

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-code-sent');
        $this->dispatch('guest-toast', type: 'success', message: __('A new verification code has been sent to your email.'));
    }

    protected function pendingUser(): ?User
    {
        $userId = session('pending_verification_user_id');

        if (! $userId) {
            return null;
        }

        return User::query()->find($userId);
    }

    protected function clearPendingVerificationSession(): void
    {
        session()->forget([
            'pending_verification_user_id',
            'pending_verification_remember',
            'registration_email',
        ]);
    }
}; ?>

<div>
    <x-slot name="title">Enter verification code</x-slot>

    <p class="guest-lead">
        @if ($email)
            Enter the 6-digit code we sent to <strong>{{ $email }}</strong>. It expires in 15 minutes.
        @else
            Enter the 6-digit verification code from your email.
        @endif
    </p>

    @if (session('status') === 'verification-code-sent')
        <div class="verify-banner verify-banner--success">
            {{ __('A new verification code has been sent.') }}
        </div>
    @endif

    <form wire:submit="verifyCode">
        <div class="input-group">
            <label class="input-label" for="code">Verification code</label>
            <input
                wire:model="code"
                id="code"
                class="form-input code-input"
                type="text"
                inputmode="numeric"
                pattern="[0-9]*"
                maxlength="6"
                autocomplete="one-time-code"
                placeholder="000000"
                required
                autofocus
            />
            @error('code') <div class="input-error">{{ $message }}</div> @enderror
        </div>

        <x-guest.submit-button target="verifyCode" :loading="__('Verifying…')">
            {{ __('Verify & continue') }} →
        </x-guest.submit-button>
    </form>

    <div class="auth-links" style="margin-top: 20px;">
        <button
            type="button"
            wire:click="resendCode"
            class="auth-link auth-link-button"
            wire:loading.attr="disabled"
            wire:target="resendCode"
        >
            <span wire:loading.remove wire:target="resendCode">{{ __("Didn't receive it? Resend code") }}</span>
            <span wire:loading wire:target="resendCode">{{ __('Sending code…') }}</span>
        </button>
        <a class="auth-link" href="{{ route('login') }}" wire:navigate>Back to sign in</a>
    </div>
</div>
