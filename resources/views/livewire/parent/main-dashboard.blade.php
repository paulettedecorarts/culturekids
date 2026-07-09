<div class="fh-page">
    <div class="fh-page-header header">
        <div>
            <h1 class="page-title">{{ __('Hello, :name', ['name' => $parentName]) }}</h1>
            <div class="breadcrumb">{{ __('Family Hub') }} · {{ __('Overview') }}</div>
        </div>
        <div class="fh-header-actions th-header-actions">
            <a href="{{ route('parent.children.create') }}" class="fh-btn fh-btn--primary">{{ __('Add child') }}</a>
        </div>
    </div>

    <div class="fh-metrics">
        @foreach ($stats as $s)
            <div class="fh-metric">
                <div class="fh-metric__value">{{ $s['attainment'] }}</div>
                <div class="fh-metric__label">{{ $s['label'] }}</div>
            </div>
        @endforeach
    </div>

    <div class="fh-dashboard-grid">
        <div>
            <div class="fh-hero">
                <h2 class="fh-hero__title">{{ __('Heritage Heroes') }}</h2>
                <p class="fh-hero__text">
                    @if ($children->isEmpty())
                        {{ __('Add a child profile to explore tribes, stories, and cultural activities together.') }}
                    @else
                        {{ __('Launch the interactive heritage experience for your family.') }}
                    @endif
                </p>
                <div class="fh-hero__actions">
                    @if ($children->isEmpty())
                        <a href="{{ route('parent.children.create') }}" class="fh-btn fh-btn--light">{{ __('Create child profile') }}</a>
                    @else
                        <a href="{{ route('heritage.app') }}" class="fh-btn fh-btn--light">{{ __('Play now') }}</a>
                        <a href="{{ route('parent.children.index') }}" class="fh-btn fh-btn--ghost">{{ __('Manage children') }}</a>
                    @endif
                </div>
            </div>

            <div class="fh-form-panel" style="padding:22px 24px">
                <h2 class="fh-form-panel__title" style="margin-bottom:16px">{{ __('Your children') }}</h2>
                @if ($children->isEmpty())
                    <p class="fh-aside-card__text" style="margin:0">{{ __('No profiles yet. Add one to start tracking stars and progress.') }}</p>
                @else
                    <div class="fh-mini-list">
                        @foreach ($children as $child)
                            <div class="fh-mini-item" wire:key="dash-child-{{ $child->id }}">
                                <div class="fh-mini-item__avatar" aria-hidden="true">
                                    {{ $child->avatar ?: \Illuminate\Support\Str::substr($child->name, 0, 1) }}
                                </div>
                                <div style="min-width:0">
                                    <div class="fh-mini-item__name">{{ $child->name }}</div>
                                    <div class="fh-mini-item__meta">
                                        {{ $child->age_band ?: __('Age band pending') }} · {{ __(':stars stars', ['stars' => $child->total_stars]) }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div style="margin-top:16px">
                        <a href="{{ route('parent.children.index') }}" class="fh-btn fh-btn--outline fh-btn--sm">{{ __('View all profiles') }}</a>
                    </div>
                @endif
            </div>
        </div>

        <aside>
            <div class="fh-aside-card">
                <h3 class="fh-aside-card__title">{{ __('Family tips') }}</h3>
                <p class="fh-aside-card__text">
                    {{ __('Each child gets a unique login email and a 4-digit PIN. You manage profiles here; they play in Heritage Heroes.') }}
                </p>
            </div>
            <div class="fh-aside-card">
                <h3 class="fh-aside-card__title">{{ __('Account') }}</h3>
                <a href="{{ route('profile') }}" class="fh-btn fh-btn--outline fh-btn--sm" style="width:100%">{{ __('Profile settings') }}</a>
            </div>
        </aside>
    </div>
</div>
