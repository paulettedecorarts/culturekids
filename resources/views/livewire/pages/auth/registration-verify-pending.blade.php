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
    <x-slot name="title">Verify your email</x-slot>

    <p class="guest-lead">
        @if ($email)
            We sent a verification link to <strong>{{ $email }}</strong>. Open that link to activate your school account, then sign in.
        @else
            Check your inbox for the verification link we sent you. Open it to activate your school account, then sign in.
        @endif
    </p>

    @if (session('status') === 'verification-link-sent')
        <div class="verify-banner verify-banner--success">
            {{ __('A new verification link has been sent to your email address.') }}
        </div>
    @endif

    @if ($email)
        <form method="POST" action="{{ route('registration.resend-verification') }}" class="verify-resend-form">
            @csrf
            <input type="hidden" name="email" value="{{ $email }}">
            <button type="submit" class="btn-primary btn-primary--outline">Resend verification email</button>
        </form>
    @endif

    <div class="auth-links" style="margin-top: 24px;">
        <a class="auth-link" href="{{ route('login') }}" wire:navigate>Back to sign in</a>
    </div>
</div>
