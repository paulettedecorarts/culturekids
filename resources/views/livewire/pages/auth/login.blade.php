<?php

use App\Livewire\Forms\LoginForm;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    public bool $showUnverifiedDialog = false;

    public ?string $unverifiedEmail = null;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->ensureIsNotRateLimited();

        $user = User::query()->where('email', Str::lower($this->form->email))->first();

        if (! $user || ! Hash::check($this->form->password, $user->password)) {
            RateLimiter::hit($this->form->throttleKey());

            throw ValidationException::withMessages([
                'form.email' => trans('auth.failed'),
            ]);
        }

        if (! $user->hasVerifiedEmail()) {
            RateLimiter::clear($this->form->throttleKey());

            session([
                'pending_verification_user_id' => $user->id,
                'pending_verification_remember' => $this->form->remember,
            ]);

            $this->unverifiedEmail = $user->email;
            $this->showUnverifiedDialog = true;

            $this->dispatch(
                'guest-toast',
                type: 'warning',
                message: __('Your email is not verified yet. Resend a code to continue.')
            );

            return;
        }

        Auth::login($user, $this->form->remember);
        RateLimiter::clear($this->form->throttleKey());
        Session::regenerate();

        $this->redirectAfterLogin();
    }

    public function resendVerificationAndContinue(): void
    {
        $userId = session('pending_verification_user_id');

        if (! $userId) {
            $this->showUnverifiedDialog = false;

            return;
        }

        $user = User::query()->find($userId);

        if (! $user || $user->hasVerifiedEmail()) {
            $this->showUnverifiedDialog = false;

            return;
        }

        $user->sendEmailVerificationNotification();

        $this->showUnverifiedDialog = false;

        Session::flash('status', 'verification-code-sent');

        $this->redirect(route('verification.enter-code', absolute: false), navigate: true);
    }

    public function closeUnverifiedDialog(): void
    {
        $this->showUnverifiedDialog = false;
    }

    protected function redirectAfterLogin(): void
    {
        if (auth()->user()->hasRole('super_admin')) {
            $this->redirectIntended(default: route('admin.dashboard', absolute: false), navigate: true);
        } elseif (auth()->user()->hasRole('cms_editor')) {
            $this->redirectIntended(default: route('cms.editor.dashboard', absolute: false), navigate: true);
        } elseif (auth()->user()->hasRole('org_admin')) {
            $this->redirectIntended(default: route('cms.admin.dashboard', absolute: false), navigate: true);
        } elseif (auth()->user()->hasRole('teacher')) {
            $this->redirectIntended(default: route('teacher.dashboard', absolute: false), navigate: true);
        } else {
            $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
        }
    }
}; ?>

<div>
    <x-slot name="title">Sign in to your dashboard</x-slot>

    <div
        x-data="{ toast: { show: false, type: 'info', message: '' } }"
        x-on:guest-toast.window="toast = { show: true, type: $event.detail.type, message: $event.detail.message }; setTimeout(() => toast.show = false, 6000)"
    >
        <div
            x-show="toast.show"
            x-transition
            class="guest-toast"
            :class="'guest-toast--' + toast.type"
            x-text="toast.message"
            style="display: none;"
        ></div>

        @if (session('status') === 'email-verified')
            <div class="login-status login-status--success">
                Your email is verified. You can sign in now.
            </div>
        @endif

        <form wire:submit="login">
            <div class="input-group">
                <label class="input-label" for="email">Email</label>
                <input wire:model="form.email" id="email" class="form-input" type="email" name="email" required autofocus autocomplete="username" />
                @error('form.email') <div class="input-error">{{ $message }}</div> @enderror
            </div>

            <x-guest.password-input
                id="password"
                label="Password"
                wire:model="form.password"
                error="form.password"
                autocomplete="current-password"
                required
            >
                <x-slot:labelExtra>
                    @if (Route::has('password.request'))
                        <a class="auth-link" style="font-size:11px" href="{{ route('password.request') }}" wire:navigate>Forgot?</a>
                    @endif
                </x-slot:labelExtra>
            </x-guest.password-input>

            <div style="margin-bottom:24px; display:flex; align-items:center;">
                <input wire:model="form.remember" id="remember" type="checkbox" style="accent-color:var(--clay-red); width:16px; height:16px;">
                <label for="remember" style="margin-left:8px; font-size:13px; color:var(--stone); font-weight:600; cursor:pointer;">Remember me</label>
            </div>

            <button type="submit" class="btn-primary">Sign In →</button>

            <div class="auth-links">
                <a class="auth-link" href="{{ route('register') }}" wire:navigate>Don't have an account? Sign up</a>
            </div>
        </form>

        @if ($showUnverifiedDialog)
            <div class="guest-modal-overlay" wire:click="closeUnverifiedDialog">
                <div class="guest-modal" @click.stop>
                    <h3 class="guest-modal-title">Email not verified</h3>
                    <p class="guest-modal-text">
                        Your account <strong>{{ $unverifiedEmail }}</strong> must be verified before you can sign in.
                        We can send a new 6-digit code to your inbox.
                    </p>
                    <div class="guest-modal-actions">
                        <button type="button" class="btn-primary" wire:click="resendVerificationAndContinue">
                            Resend verification code
                        </button>
                        <button type="button" class="btn-primary btn-primary--outline" wire:click="closeUnverifiedDialog">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
