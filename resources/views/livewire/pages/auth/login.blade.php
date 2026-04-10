<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        if (auth()->user()->hasRole('super_admin')) {
            $this->redirectIntended(default: route('admin.dashboard', absolute: false), navigate: true);
        } elseif (auth()->user()->hasRole('cms_editor')) {
            $this->redirectIntended(default: route('cms.editor.dashboard', absolute: false), navigate: true);
        } elseif (auth()->user()->hasRole('org_admin')) {
            $this->redirectIntended(default: route('cms.admin.dashboard', absolute: false), navigate: true);
        } elseif (auth()->user()->hasRole('teacher')) {
            $this->redirectIntended(default: route('teacher.dashboard', absolute: false), navigate: true);
        } elseif (auth()->user()->hasRole('child')) {
            $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
        } else {
            $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
        }
    }
}; ?>

<div>
    <x-slot name="title">Sign in to your dashboard</x-slot>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div style="margin-bottom:24px;">
        <p style="font-size:10px; font-weight:700; letter-spacing:1.5px; text-transform:uppercase; color:var(--stone); margin-bottom:12px">Choose your role</p>
        <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:12px;">
            <div style="text-align:center; padding:12px 8px; border-radius:16px; border:2px solid var(--cream-mid); cursor:pointer; background:var(--cream); transition:all 0.2s;" onmouseover="this.style.borderColor='var(--clay-red)';" onmouseout="this.style.borderColor='var(--cream-mid)';">
                 <div style="font-size:24px; margin-bottom:4px">👧</div>
                 <div style="font-size:10px; font-weight:700; color:var(--ink)">Child</div>
            </div>
            <div style="text-align:center; padding:12px 8px; border-radius:16px; border:2px solid var(--clay-red); cursor:pointer; background:rgba(196,75,43,.05); transition:all 0.2s;">
                 <div style="font-size:24px; margin-bottom:4px">👪</div>
                 <div style="font-size:10px; font-weight:700; color:var(--clay-red)">Parent</div>
            </div>
            <div style="text-align:center; padding:12px 8px; border-radius:16px; border:2px solid var(--cream-mid); cursor:pointer; background:var(--cream); transition:all 0.2s;" onmouseover="this.style.borderColor='var(--clay-red)';" onmouseout="this.style.borderColor='var(--cream-mid)';">
                 <div style="font-size:24px; margin-bottom:4px">👩‍🏫</div>
                 <div style="font-size:10px; font-weight:700; color:var(--ink)">Teacher</div>
            </div>
        </div>
    </div>

    <form wire:submit="login">
        <!-- Email Address -->
        <div class="input-group">
            <label class="input-label" for="email">Email</label>
            <input wire:model="form.email" id="email" class="form-input" type="email" name="email" required autofocus autocomplete="username" />
            @error('form.email') <div class="input-error">{{ $message }}</div> @enderror
        </div>

        <!-- Password -->
        <div class="input-group">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                <label class="input-label" for="password" style="margin-bottom:0">Password</label>
                @if (Route::has('password.request'))
                    <a class="auth-link" style="font-size:11px" href="{{ route('password.request') }}" wire:navigate>Forgot?</a>
                @endif
            </div>
            <input wire:model="form.password" id="password" class="form-input" type="password" name="password" required autocomplete="current-password" />
            @error('form.password') <div class="input-error">{{ $message }}</div> @enderror
        </div>

        <!-- Remember Me -->
        <div style="margin-bottom:24px; display:flex; align-items:center;">
            <input wire:model="form.remember" id="remember" type="checkbox" style="accent-color:var(--clay-red); width:16px; height:16px;">
            <label for="remember" style="margin-left:8px; font-size:13px; color:var(--stone); font-weight:600; cursor:pointer;">Remember me</label>
        </div>

        <button type="submit" class="btn-primary">Sign In →</button>
        
        <div class="auth-links">
            <a class="auth-link" href="{{ route('register') }}" wire:navigate>Don't have an account? Sign up</a>
        </div>
    </form>
</div>
