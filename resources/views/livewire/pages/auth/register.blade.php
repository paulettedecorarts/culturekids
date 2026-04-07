<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered($user = User::create($validated)));

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <x-slot name="title">Create your account</x-slot>

    <form wire:submit="register">
        <!-- Name -->
        <div class="input-group">
            <label class="input-label" for="name">Full Name</label>
            <input wire:model="name" id="name" class="form-input" type="text" name="name" required autofocus autocomplete="name" />
            @error('name') <div class="input-error">{{ $message }}</div> @enderror
        </div>

        <!-- Email Address -->
        <div class="input-group">
            <label class="input-label" for="email">Email Address</label>
            <input wire:model="email" id="email" class="form-input" type="email" name="email" required autocomplete="username" />
            @error('email') <div class="input-error">{{ $message }}</div> @enderror
        </div>

        <!-- Password -->
        <div class="input-group">
            <label class="input-label" for="password">Password</label>
            <input wire:model="password" id="password" class="form-input" type="password" name="password" required autocomplete="new-password" />
            @error('password') <div class="input-error">{{ $message }}</div> @enderror
        </div>

        <!-- Confirm Password -->
        <div class="input-group">
            <label class="input-label" for="password_confirmation">Confirm Password</label>
            <input wire:model="password_confirmation" id="password_confirmation" class="form-input" type="password" name="password_confirmation" required autocomplete="new-password" />
            @error('password_confirmation') <div class="input-error">{{ $message }}</div> @enderror
        </div>

        <button type="submit" class="btn-primary">Create Account →</button>

        <div class="auth-links">
            <a class="auth-link" href="{{ route('login') }}" wire:navigate>Already have an account? Sign in</a>
        </div>
    </form>
</div>
