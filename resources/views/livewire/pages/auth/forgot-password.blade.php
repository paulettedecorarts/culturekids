<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $status = Password::sendResetLink(
            $this->only('email')
        );

        if ($status != Password::RESET_LINK_SENT) {
            $this->addError('email', __($status));

            return;
        }

        $this->reset('email');

        session()->flash('status', __($status));
    }
}; ?>

<div>
    <x-slot name="title">{{ __('Forgot password') }}</x-slot>

    <p class="guest-lead">
        {{ __('Enter your email and we will send you a link to choose a new password.') }}
    </p>

    @if (session('status'))
        <div class="verify-banner verify-banner--success">
            {{ session('status') }}
        </div>
    @endif

    <form wire:submit="sendPasswordResetLink">
        <div class="input-group">
            <label class="input-label" for="email">{{ __('Email address') }}</label>
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
            @error('email')
                <div class="input-error">{{ $message }}</div>
            @enderror
        </div>

        <x-guest.submit-button target="sendPasswordResetLink" :loading="__('Sending link…')">
            {{ __('Email password reset link') }}
        </x-guest.submit-button>

        <div class="auth-links">
            <a class="auth-link" href="{{ route('login') }}" wire:navigate>{{ __('Back to sign in') }}</a>
        </div>
    </form>
</div>
