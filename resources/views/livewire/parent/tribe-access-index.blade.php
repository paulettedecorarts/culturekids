<div class="fh-page">
    <div class="fh-page-header header">
        <div>
            <h1 class="page-title">{{ __('Tribe access') }}</h1>
            <div class="breadcrumb">{{ __('Family Hub') }} · {{ __('Management') }}</div>
        </div>
    </div>

    @if (session('status'))
        <div class="fh-alert fh-alert--success" role="status">
            <span aria-hidden="true">✓</span>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <div class="fh-form-panel">
        <div class="fh-form-panel__intro">
            <h2 class="fh-form-panel__title">{{ __('Family tribe library') }}</h2>
            <p class="fh-form-panel__text">
                {{ __('Choose which heritage tribes your family can explore. This applies to all children on your account — the same model used by the mobile apps.') }}
            </p>
            @if ($childCount > 0)
                <p class="fh-form-panel__text" style="margin-top:8px">
                    {{ trans_choice(':count child profile uses this list.|:count child profiles use this list.', $childCount, ['count' => $childCount]) }}
                </p>
            @endif
        </div>

        @error('approvedTribeIds')
            <div class="fh-alert fh-alert--warning" role="alert">
                <span>{{ $message }}</span>
            </div>
        @enderror

        <div class="fh-tribe-grid">
            @foreach ($tribes as $tribe)
                <label class="fh-tribe-option" wire:key="tribe-{{ $tribe->id }}">
                    <input
                        type="checkbox"
                        wire:model="approvedTribeIds"
                        value="{{ (string) $tribe->id }}"
                    >
                    <span class="fh-tribe-option__badge" style="--tribe-color: {{ $tribe->color ?: '#F97316' }}">
                        {{ $tribe->hero_emoji ?: '🌍' }}
                    </span>
                    <span class="fh-tribe-option__body">
                        <strong>{{ $tribe->name }}</strong>
                        <small>{{ $tribe->region ?: __('Uganda') }}</small>
                    </span>
                </label>
            @endforeach
        </div>

        @if ($tribes->isEmpty())
            <div class="fh-empty">
                <p class="fh-empty__text">{{ __('No tribes are available yet. Check back after content is published.') }}</p>
            </div>
        @endif

        <div class="fh-form-actions">
            <a href="{{ route('parent.dashboard') }}" class="fh-btn fh-btn--outline">{{ __('Back to dashboard') }}</a>
            <button type="button" class="fh-btn fh-btn--primary" wire:click="save" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="save">{{ __('Save tribe access') }}</span>
                <span wire:loading wire:target="save">{{ __('Saving…') }}</span>
            </button>
            @if ($childCount > 0 && count($approvedTribeIds) > 0)
                <button type="button" class="fh-btn fh-btn--secondary" wire:click="saveAndPlay" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="saveAndPlay">{{ __('Save & play Heritage') }}</span>
                    <span wire:loading wire:target="saveAndPlay">{{ __('Opening…') }}</span>
                </button>
            @endif
        </div>
    </div>
</div>
