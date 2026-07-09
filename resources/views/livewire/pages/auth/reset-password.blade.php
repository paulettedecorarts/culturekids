<?php

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    #[Locked]
    public string $token = '';

    public string $email = '';

    /** When true, email came from the signed invite link and is not shown in the UI. */
    public bool $emailLockedFromInvite = false;

    public string $password = '';

    public string $password_confirmation = '';

    /**
     * Mount the component.
     */
    public function mount(string $token): void
    {
        $this->token = $token;
        $this->email = (string) request()->query('email', '');
        $this->emailLockedFromInvite = $this->email !== '';
    }

    /**
     * Reset the password for the given user.
     */
    public function resetPassword(): void
    {
        $this->validate([
            'token' => ['required'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::reset(
            $this->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) {
                $user->forceFill([
                    'password' => Hash::make($this->password),
                    'remember_token' => Str::random(60),
                    'email_verified_at' => $user->email_verified_at ?? now(),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status != Password::PASSWORD_RESET) {
            $this->addError('email', __($status));

            return;
        }

        Session::flash('status', __($status));

        $this->redirectRoute('login', navigate: true);
    }
}; ?>

<div>
    <p class="guest-lead">
        @if ($emailLockedFromInvite)
            {{ __('Choose a password to finish activating your account, then sign in.') }}
        @else
            {{ __('Enter your email and choose a new password.') }}
        @endif
    </p>

    <form wire:submit="resetPassword">
        @if ($emailLockedFromInvite)
            <input type="hidden" wire:model="email" name="email" autocomplete="username">
        @else
            <div class="input-group">
                <label class="input-label" for="email">{{ __('Email') }}</label>
                <input
                    wire:model="email"
                    id="email"
                    class="form-input"
                    type="email"
                    name="email"
                    required
                    autofocus
                    autocomplete="username"
                >
            </div>
        @endif

        @error('email')
            <div class="input-group">
                <div class="input-error">{{ $message }}</div>
            </div>
        @enderror

        <x-guest.password-input
            id="password"
            :label="__('Password')"
            wire:model="password"
            error="password"
            autocomplete="new-password"
            :autofocus="$emailLockedFromInvite"
            required
        />

        <x-guest.password-input
            id="password_confirmation"
            :label="__('Confirm password')"
            wire:model="password_confirmation"
            error="password_confirmation"
            autocomplete="new-password"
            required
        />

        <x-guest.submit-button target="resetPassword" :loading="__('Saving…')" style="margin-top: 8px;">
            {{ __('Save password and continue') }}
        </x-guest.submit-button>

        <div class="auth-links">
            <a class="auth-link" href="{{ route('login') }}" wire:navigate>{{ __('Back to sign in') }}</a>
        </div>
    </form>
</div>
