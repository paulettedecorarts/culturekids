<?php

use App\Actions\Auth\RegisterSchoolOrganisation;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $organisation_name = '';

    public string $admin_name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    /**
     * Register a school organisation and its org admin.
     */
    public function register(RegisterSchoolOrganisation $registerSchool): void
    {
        $validated = $this->validate([
            'organisation_name' => ['required', 'string', 'min:3', 'max:100'],
            'admin_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        event(new Registered($user = $registerSchool->register(
            $validated['organisation_name'],
            $validated['admin_name'],
            $validated['email'],
            $validated['password'],
        )));

        session([
            'pending_verification_user_id' => $user->id,
            'pending_verification_remember' => false,
        ]);

        $this->redirect(route('verification.enter-code', absolute: false), navigate: true);
    }
}; ?>

<div>
    <x-slot name="title">Register your school</x-slot>

    <p class="guest-lead">Create your organisation account. You will be the school administrator.</p>

    <form wire:submit="register" class="register-form-grid">
        <div class="input-group">
            <label class="input-label" for="organisation_name">Organisation Name</label>
            <input wire:model="organisation_name" id="organisation_name" class="form-input" type="text" name="organisation_name" required autofocus autocomplete="organization" />
            @error('organisation_name') <div class="input-error">{{ $message }}</div> @enderror
        </div>

        <div class="input-group">
            <label class="input-label" for="admin_name">Administrator Name</label>
            <input wire:model="admin_name" id="admin_name" class="form-input" type="text" name="admin_name" required autocomplete="name" />
            @error('admin_name') <div class="input-error">{{ $message }}</div> @enderror
        </div>

        <div class="input-group span-full">
            <label class="input-label" for="email">Email Address</label>
            <input wire:model="email" id="email" class="form-input" type="email" name="email" required autocomplete="username" />
            @error('email') <div class="input-error">{{ $message }}</div> @enderror
        </div>

        <div class="input-group">
            <label class="input-label" for="password">Password</label>
            <input wire:model="password" id="password" class="form-input" type="password" name="password" required autocomplete="new-password" />
            @error('password') <div class="input-error">{{ $message }}</div> @enderror
        </div>

        <div class="input-group">
            <label class="input-label" for="password_confirmation">Confirm Password</label>
            <input wire:model="password_confirmation" id="password_confirmation" class="form-input" type="password" name="password_confirmation" required autocomplete="new-password" />
            @error('password_confirmation') <div class="input-error">{{ $message }}</div> @enderror
        </div>

        <div class="span-full">
            <button type="submit" class="btn-primary">Create School Account →</button>

            <div class="auth-links">
                <a class="auth-link" href="{{ route('login') }}" wire:navigate>Already have an account? Sign in</a>
            </div>
        </div>
    </form>
</div>
