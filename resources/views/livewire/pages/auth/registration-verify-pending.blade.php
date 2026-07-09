<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $email = '';

    public function mount(): void
    {
        $this->email = (string) session('registration_email', '');
    }
}; ?>

<div>
    <x-slot name="title">{{ __('Verify your email') }}</x-slot>

    <p class="guest-lead">
        @if ($email)
            {!! __('We sent a verification link to <strong>:email</strong>. Open that link to activate your account, then sign in.', ['email' => e($email)]) !!}
        @else
            {{ __('Check your inbox for the verification link we sent you. Open it to activate your account, then sign in.') }}
        @endif
    </p>

    @if (session('status') === 'verification-link-sent')
        <div class="verify-banner verify-banner--success">
            {{ __('A new verification link has been sent to your email address.') }}
        </div>
    @endif

    @if ($email)
        <form
            method="POST"
            action="{{ route('registration.resend-verification') }}"
            class="verify-resend-form"
            x-data="{ submitting: false }"
            x-on:submit="submitting = true"
        >
            @csrf
            <input type="hidden" name="email" value="{{ $email }}">
            <button
                type="submit"
                class="btn-primary btn-primary--outline guest-submit-btn"
                :disabled="submitting"
                :class="{ 'guest-submit-btn--loading': submitting }"
            >
                <span x-show="!submitting">{{ __('Resend verification email') }}</span>
                <span class="guest-submit-btn__loading" x-show="submitting" x-cloak>
                    <svg class="guest-submit-btn__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                        <circle cx="12" cy="12" r="10" stroke-width="3" stroke-dasharray="32" stroke-dashoffset="8" opacity="0.25"/>
                        <path d="M12 2a10 10 0 0 1 10 10" stroke-width="3" stroke-linecap="round"/>
                    </svg>
                    {{ __('Sending…') }}
                </span>
            </button>
        </form>
    @endif

    <div class="auth-links" style="margin-top: 24px;">
        <a class="auth-link" href="{{ route('login') }}" wire:navigate>{{ __('Back to sign in') }}</a>
    </div>
</div>
