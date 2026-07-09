<?php

use App\Actions\Auth\RegisterParent;
use App\Actions\Auth\RegisterSchoolOrganisation;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $account_type = 'parent';

    public string $organisation_name = '';

    public string $admin_name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function updatedAccountType(): void
    {
        if ($this->account_type === 'parent') {
            $this->reset('organisation_name');
            $this->resetErrorBag('organisation_name');
        }
    }

    public function register(RegisterSchoolOrganisation $registerSchool, RegisterParent $registerParent): void
    {
        $rules = [
            'account_type' => ['required', 'in:parent,school'],
            'admin_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ];

        if ($this->account_type === 'school') {
            $rules['organisation_name'] = ['required', 'string', 'min:3', 'max:100'];
        }

        $validated = $this->validate($rules);

        $user = $this->account_type === 'school'
            ? $registerSchool->register(
                $validated['organisation_name'],
                $validated['admin_name'],
                $validated['email'],
                $validated['password'],
            )
            : $registerParent->register(
                $validated['admin_name'],
                $validated['email'],
                $validated['password'],
            );

        event(new Registered($user));

        session([
            'pending_verification_user_id' => $user->id,
            'pending_verification_remember' => false,
        ]);

        $this->redirect(route('verification.enter-code', absolute: false), navigate: true);
    }
}; ?>

<div>
    <x-slot name="title">Create your account</x-slot>

    <p class="guest-lead">
        @if ($account_type === 'school')
            {{ __('Register your school or organisation. You will be the administrator.') }}
        @else
            {{ __('Sign up as a parent to explore heritage activities with your children.') }}
        @endif
    </p>

    <div class="register-type-toggle" role="tablist" aria-label="{{ __('Account type') }}">
        <button
            type="button"
            role="tab"
            class="register-type-toggle__option @if ($account_type === 'parent') is-active @endif"
            wire:click="$set('account_type', 'parent')"
            aria-selected="{{ $account_type === 'parent' ? 'true' : 'false' }}"
        >
            {{ __('Family account') }}
        </button>
        <button
            type="button"
            role="tab"
            class="register-type-toggle__option @if ($account_type === 'school') is-active @endif"
            wire:click="$set('account_type', 'school')"
            aria-selected="{{ $account_type === 'school' ? 'true' : 'false' }}"
        >
            {{ __('School / organisation') }}
        </button>
    </div>

    <form
        wire:submit="register"
        @class(['register-form-grid' => $account_type === 'school'])
    >
        @if ($account_type === 'school')
            <div class="input-group">
                <label class="input-label" for="organisation_name">{{ __('Organisation name') }}</label>
                <input wire:model="organisation_name" id="organisation_name" class="form-input" type="text" name="organisation_name" required autofocus autocomplete="organization" />
                @error('organisation_name') <div class="input-error">{{ $message }}</div> @enderror
            </div>

            <div class="input-group">
                <label class="input-label" for="admin_name">{{ __('Administrator name') }}</label>
                <input wire:model="admin_name" id="admin_name" class="form-input" type="text" name="admin_name" required autocomplete="name" />
                @error('admin_name') <div class="input-error">{{ $message }}</div> @enderror
            </div>
        @else
            <div class="input-group span-full">
                <label class="input-label" for="admin_name">{{ __('Your name') }}</label>
                <input wire:model="admin_name" id="admin_name" class="form-input" type="text" name="admin_name" required autofocus autocomplete="name" />
                @error('admin_name') <div class="input-error">{{ $message }}</div> @enderror
            </div>
        @endif

        <div class="input-group span-full">
            <label class="input-label" for="email">{{ __('Email address') }}</label>
            <input wire:model="email" id="email" class="form-input" type="email" name="email" required autocomplete="username" />
            @error('email') <div class="input-error">{{ $message }}</div> @enderror
        </div>

        <div @class(['span-full' => $account_type === 'parent'])>
            <x-guest.password-input
                id="password"
                :label="__('Password')"
                wire:model="password"
                error="password"
                autocomplete="new-password"
                required
            />
        </div>

        <div @class(['span-full' => $account_type === 'parent'])>
            <x-guest.password-input
                id="password_confirmation"
                :label="__('Confirm password')"
                wire:model="password_confirmation"
                error="password_confirmation"
                autocomplete="new-password"
                required
            />
        </div>

        <div class="span-full">
            <x-guest.submit-button target="register" :loading="__('Creating account…')">
                {{ __('Create account') }} →
            </x-guest.submit-button>

            <div class="auth-links">
                <a class="auth-link" href="{{ route('login') }}" wire:navigate>{{ __('Already have an account? Sign in') }}</a>
            </div>
        </div>
    </form>
</div>
