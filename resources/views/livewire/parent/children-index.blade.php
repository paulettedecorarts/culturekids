<div class="fh-page">
    <div class="fh-page-header header">
        <div>
            <h1 class="page-title">{{ __('My children') }}</h1>
            <div class="breadcrumb">{{ __('Family Hub') }} · {{ __('Profiles') }}</div>
        </div>
        <div class="fh-header-actions th-header-actions">
            <a href="{{ route('parent.children.create') }}" class="fh-btn fh-btn--primary">{{ __('Add child') }}</a>
        </div>
    </div>

    @if (session('status'))
        <div class="fh-alert fh-alert--success" role="status">
            <span aria-hidden="true">✓</span>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    @if ($children->isEmpty())
        <div class="fh-empty">
            <div class="fh-empty__icon" aria-hidden="true">👨‍👩‍👧</div>
            <h2 class="fh-empty__title">{{ __('No child profiles yet') }}</h2>
            <p class="fh-empty__text">{{ __('Create a profile for each learner in your family. They can sign in with their own PIN and explore Heritage Heroes.') }}</p>
            <a href="{{ route('parent.children.create') }}" class="fh-btn fh-btn--primary">{{ __('Create first profile') }}</a>
        </div>
    @else
        <div class="fh-child-grid">
            @foreach ($children as $child)
                <article class="fh-child-card" wire:key="child-{{ $child->id }}">
                    <div class="fh-child-card__head">
                        <div class="fh-child-card__avatar" aria-hidden="true">
                            {{ $child->avatar ?: \Illuminate\Support\Str::substr($child->name, 0, 1) }}
                        </div>
                        <div style="min-width:0">
                            <h2 class="fh-child-card__name">{{ $child->name }}</h2>
                            <p class="fh-child-card__meta">{{ $child->age_band ?: __('Age band pending') }}</p>
                        </div>
                    </div>

                    <div class="fh-child-card__stats">
                        <div class="fh-child-card__stat">
                            <span class="fh-child-card__stat-value">{{ $child->total_stars }}</span>
                            <span class="fh-child-card__stat-label">{{ __('Stars') }}</span>
                        </div>
                        <div class="fh-child-card__stat">
                            <span class="fh-child-card__stat-value">{{ $child->dob ? \Illuminate\Support\Carbon::parse($child->dob)->age : '—' }}</span>
                            <span class="fh-child-card__stat-label">{{ __('Age') }}</span>
                        </div>
                    </div>

                    @if ($child->childUser?->email)
                        <p class="fh-child-card__email">
                            <strong>{{ __('Child login email') }}</strong>
                            {{ $child->childUser->email }}
                        </p>
                    @endif

                    <div class="fh-child-card__actions">
                        <button
                            type="button"
                            class="fh-btn fh-btn--primary fh-btn--sm"
                            wire:click="playAs({{ $child->id }})"
                            wire:loading.attr="disabled"
                            wire:target="playAs({{ $child->id }})"
                        >
                            <span wire:loading.remove wire:target="playAs({{ $child->id }})">{{ __('Play Heritage') }}</span>
                            <span wire:loading wire:target="playAs({{ $child->id }})">{{ __('Opening…') }}</span>
                        </button>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>
