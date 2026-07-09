<div class="fh-page">
    <div class="fh-page-header header">
        <div>
            <h1 class="page-title">{{ __('Add a child') }}</h1>
            <div class="breadcrumb">{{ __('Family Hub') }} · {{ __('New profile') }}</div>
        </div>
    </div>

    @if (session('message'))
        <div class="fh-alert fh-alert--info" role="status">{{ session('message') }}</div>
    @endif

    @if ($childCount === 0)
        <div class="fh-alert fh-alert--info">
            {{ __('Your first profile unlocks Heritage Heroes on the web for your family.') }}
        </div>
    @endif

    <div class="fh-form-layout">
        <div class="fh-form-panel">
            <h2 class="fh-form-panel__title">{{ __('Child details') }}</h2>
            <p class="fh-form-panel__lead">{{ __('We use this to personalise activities and track progress.') }}</p>

            <form wire:submit="save" class="fh-form">
                <div class="fh-field">
                    <label for="name">{{ __('Full name') }}</label>
                    <input wire:model="name" id="name" type="text" required autofocus autocomplete="name" placeholder="{{ __('e.g. Amina Okello') }}">
                    @error('name') <div class="fh-field__error">{{ $message }}</div> @enderror
                </div>

                <div class="fh-form-row">
                    <div class="fh-field">
                        <label for="date_of_birth">{{ __('Date of birth') }}</label>
                        <input wire:model="date_of_birth" id="date_of_birth" type="date" required max="{{ now()->subDay()->toDateString() }}">
                        @error('date_of_birth') <div class="fh-field__error">{{ $message }}</div> @enderror
                    </div>

                    <div class="fh-field">
                        <label for="avatar">{{ __('Avatar') }}</label>
                        <input wire:model="avatar" id="avatar" type="text" maxlength="10" placeholder="🦁">
                        <div class="fh-emoji-row" role="group" aria-label="{{ __('Pick an emoji') }}">
                            @foreach (['🦁', '🐘', '🦒', '🌟', '🎨', '📚'] as $emoji)
                                <button
                                    type="button"
                                    class="fh-emoji-btn @if($avatar === $emoji) is-selected @endif"
                                    wire:click="$set('avatar', '{{ $emoji }}')"
                                    aria-label="{{ $emoji }}"
                                >{{ $emoji }}</button>
                            @endforeach
                        </div>
                        @error('avatar') <div class="fh-field__error">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="fh-form-row">
                    <div class="fh-field">
                        <label for="pin">{{ __('4-digit PIN') }}</label>
                        <input wire:model="pin" id="pin" type="password" inputmode="numeric" pattern="[0-9]*" maxlength="4" required placeholder="••••">
                        @error('pin') <div class="fh-field__error">{{ $message }}</div> @enderror
                    </div>
                    <div class="fh-field">
                        <label for="pin_confirmation">{{ __('Confirm PIN') }}</label>
                        <input wire:model="pin_confirmation" id="pin_confirmation" type="password" inputmode="numeric" pattern="[0-9]*" maxlength="4" required placeholder="••••">
                    </div>
                </div>

                <p class="fh-form-note">
                    {{ __('A child login email is created automatically. They sign in with that email and the PIN you set here.') }}
                </p>

                <div class="fh-form-actions">
                    <x-parent.submit-button target="save" :loading="__('Creating…')">
                        {{ __('Create profile') }}
                    </x-parent.submit-button>
                    <a href="{{ route('parent.children.index') }}" class="fh-btn fh-btn--outline">{{ __('Cancel') }}</a>
                </div>
            </form>
        </div>

        <aside>
            <div class="fh-aside-card">
                <h3 class="fh-aside-card__title">{{ __('What happens next') }}</h3>
                <ul class="fh-aside-list">
                    <li>{{ __('Profile appears in My children') }}</li>
                    <li>{{ __('You can launch Heritage Heroes for them') }}</li>
                    <li>{{ __('Stars and progress sync to their profile') }}</li>
                </ul>
            </div>
            <div class="fh-aside-card">
                <h3 class="fh-aside-card__title">{{ __('Child sign-in') }}</h3>
                <p class="fh-aside-card__text">
                    {{ __('After saving, we show the generated login email on the children list. The PIN is only known to you and your child.') }}
                </p>
            </div>
        </aside>
    </div>
</div>
