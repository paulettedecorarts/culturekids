<div class="fh-page">
    <div class="fh-page-header header">
        <div>
            <div class="fh-section-label">{{ __('Learner Dashboard') }}</div>
            <h1 class="page-title">{{ __(":name's Learning", ['name' => $learnerName]) }}</h1>
            <div class="breadcrumb">{{ __('Learner Hub') }} · {{ __('Overview') }}</div>
        </div>
        <div class="fh-header-actions th-header-actions">
            <a href="{{ route('heritage.app') }}" class="fh-btn fh-btn--primary">{{ __('Play Heritage Heroes') }}</a>
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
                    {{ __('Explore Uganda’s tribes, language, music, and missions at your own pace. Your stars and progress are saved to this account.') }}
                </p>
                <div class="fh-hero__actions">
                    <a href="{{ route('heritage.app') }}" class="fh-btn fh-btn--light">{{ __('Continue learning') }}</a>
                    <a href="{{ route('profile') }}" class="fh-btn fh-btn--ghost">{{ __('Profile settings') }}</a>
                </div>
            </div>

            <div class="fh-form-panel" style="padding:22px 24px;margin-bottom:24px">
                <p class="fh-panel-kicker">{{ __('Your progress') }}</p>
                @if (empty($progressRows))
                    <p class="fh-aside-card__text" style="margin:0">{{ __('Open Heritage Heroes to start your first tribe.') }}</p>
                @else
                    <div class="fh-progress-list">
                        @foreach ($progressRows as $row)
                            <div class="fh-progress-row">
                                <div class="fh-progress-row__head">
                                    <span class="fh-progress-row__label">{{ $row['label'] }}</span>
                                    <span class="fh-progress-row__value fh-progress-row__value--{{ $row['tone'] }}">{{ $row['value'] }}</span>
                                </div>
                                <div class="fh-progress-strip">
                                    <div class="fh-progress-fill fh-progress-fill--{{ $row['tone'] }}" style="width: {{ max(0, min(100, $row['pct'])) }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="fh-form-panel" style="padding:22px 24px">
                <p class="fh-panel-kicker">{{ __('Badges earned') }}</p>
                <div class="fh-badge-row">
                    @foreach ($badges as $badge)
                        <div class="fh-badge fh-badge--{{ $badge['color'] }}">
                            <span class="fh-badge__icon" aria-hidden="true">{{ $badge['icon'] }}</span>
                            <span class="fh-badge__name">{{ $badge['name'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <aside>
            <div class="fh-cultural-note">
                <p class="fh-cultural-note__kicker">{{ __('This week’s cultural note') }}</p>
                <p class="fh-cultural-note__quote">{{ $culturalQuote }}</p>
                <p class="fh-cultural-note__text">{{ $culturalNote }}</p>
            </div>

            <div class="fh-aside-card">
                <h3 class="fh-aside-card__title">{{ __('How it works') }}</h3>
                <p class="fh-aside-card__text">
                    {{ __('You learn as yourself — no child profiles or family approvals. Jump into Heritage Heroes whenever you are ready.') }}
                </p>
                <a href="{{ route('heritage.app') }}" class="fh-btn fh-btn--outline fh-btn--sm" style="width:100%;margin-top:12px">{{ __('Open Heritage Heroes') }}</a>
            </div>

            <div class="fh-aside-card">
                <h3 class="fh-aside-card__title">{{ __('Account') }}</h3>
                <a href="{{ route('profile') }}" class="fh-btn fh-btn--outline fh-btn--sm" style="width:100%">{{ __('Profile settings') }}</a>
            </div>
        </aside>
    </div>
</div>
