<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $password = '';

    /**
     * Confirm the current user's password.
     */
    public function confirmPassword(): void
    {
        $this->validate([
            'password' => ['required', 'string'],
        ]);

        if (! Auth::guard('web')->validate([
            'email' => Auth::user()->email,
            'password' => $this->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        session(['auth.password_confirmed_at' => time()]);

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <x-slot name="title">{{ __('Confirm password') }}</x-slot>

    <p class="guest-lead">
        {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
    </p>

    <form wire:submit="confirmPassword">
        <x-guest.password-input
            id="password"
            :label="__('Password')"
            wire:model="password"
            error="password"
            autocomplete="current-password"
            autofocus
            required
        />

        <x-guest.submit-button target="confirmPassword" :loading="__('Confirming…')">
            {{ __('Confirm') }} →
        </x-guest.submit-button>
    </form>
</div>
