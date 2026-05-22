<?php

use App\Livewire\Actions\Logout;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public function mount(): void
    {
        if (! auth()->check()) {
            $this->redirect(route('login', absolute: false), navigate: true);

            return;
        }

        if (auth()->user()->hasVerifiedEmail()) {
            $this->redirect(route('dashboard', absolute: false), navigate: true);

            return;
        }

        session([
            'pending_verification_user_id' => auth()->id(),
            'pending_verification_remember' => false,
        ]);

        $this->redirect(route('verification.enter-code', absolute: false), navigate: true);
    }

    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<div>
    <p class="guest-lead">{{ __('Redirecting to verification…') }}</p>
</div>
