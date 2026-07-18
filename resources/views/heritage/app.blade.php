@extends('layouts.heritage')

@section('content')
<script>
    window.HERITAGE_BOOTSTRAP = @json($bootstrap);
</script>
<div id="appPage">
    <header class="hh-shell">
        <button type="button" class="hh-shell__brand" onclick="goHome()">
            <img src="{{ asset(config('brand.logo', 'images/brand/paulette-comics-logo.png')) }}" alt="" onerror="this.style.display='none'">
            <span>
                <strong>Heritage Heroes</strong>
                <small>Uganda learning</small>
            </span>
        </button>

        <div class="hh-shell__actions">
            <div class="hh-profile" id="hhProfile">
                <button type="button" class="hh-profile__trigger" id="hhProfileBtn" aria-expanded="false" aria-controls="hhProfilePanel" onclick="toggleChildProfile()">
                    <span class="hh-profile__avatar" aria-hidden="true">{{ $child->avatar ?: mb_substr($child->name, 0, 1) }}</span>
                    <span class="hh-profile__name">{{ $child->name }}</span>
                    <span class="hh-profile__chev" aria-hidden="true">▾</span>
                </button>

                <div class="hh-profile__panel" id="hhProfilePanel" hidden>
                    <div class="hh-profile__head">
                        <div class="hh-profile__avatar hh-profile__avatar--lg" aria-hidden="true">{{ $child->avatar ?: mb_substr($child->name, 0, 1) }}</div>
                        <div>
                            <strong id="hhProfileName">{{ $child->name }}</strong>
                            <span id="hhProfileAge">{{ $child->age_band ? 'Ages '.$child->age_band : __('Learner profile') }}</span>
                        </div>
                    </div>

                    <div class="hh-profile__stats">
                        <div class="hh-profile__stat">
                            <strong id="hhStatStars">{{ number_format($bootstrap['childStats']['stars'] ?? 0) }}</strong>
                            <span>{{ __('Total stars') }}</span>
                        </div>
                        <div class="hh-profile__stat">
                            <strong id="hhStatActivities">{{ ($bootstrap['childStats']['activitiesCompleted'] ?? 0).' / '.($bootstrap['childStats']['activitiesTotal'] ?? 0) }}</strong>
                            <span>{{ __('Activities') }}</span>
                        </div>
                        <div class="hh-profile__stat">
                            <strong id="hhStatTribes">{{ ($bootstrap['childStats']['tribesStarted'] ?? 0).' / '.($bootstrap['childStats']['tribesTotal'] ?? 0) }}</strong>
                            <span>{{ __('Tribes started') }}</span>
                        </div>
                        <div class="hh-profile__stat">
                            <strong id="hhStatComplete">{{ $bootstrap['childStats']['tribesCompleted'] ?? 0 }}</strong>
                            <span>{{ __('Tribes complete') }}</span>
                        </div>
                    </div>

                    <div class="hh-profile__actions">
                        @if ($bootstrap['routes']['selectChild'])
                            <a href="{{ $bootstrap['routes']['selectChild'] }}" class="hh-profile__link">{{ __('Switch child') }}</a>
                        @endif
                        @if ($bootstrap['routes']['exitToParent'] ?? null)
                            <form method="POST" action="{{ $bootstrap['routes']['exitToParent'] }}" style="margin:0">
                                @csrf
                                <button type="submit" class="hh-profile__parent">{{ __('Back to Family Hub') }}</button>
                            </form>
                        @endif
                        @if ($bootstrap['routes']['exitToIndividual'] ?? null)
                            <form method="POST" action="{{ $bootstrap['routes']['exitToIndividual'] }}" style="margin:0">
                                @csrf
                                <button type="submit" class="hh-profile__parent">{{ __('Back to Learner Hub') }}</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <button type="button" class="hh-chip" id="btnP" onclick="nav('passport')">Passport</button>
            <button type="button" class="hh-chip hidden" id="btnBack" onclick="goBack()">Back</button>
            <div class="hh-chip hh-chip--stars">⭐ <span id="totS">0</span></div>
            <form method="POST" action="{{ route('logout') }}" style="margin:0">
                @csrf
                <button type="submit" class="hh-chip hh-chip--ghost">Sign out</button>
            </form>
        </div>
    </header>

    <div id="stars"></div>
    <div class="toast" id="toast">
        <div class="t-ico" id="tIco">⭐</div>
        <div>
            <div class="t-title" id="tTitle"></div>
            <div class="t-sub" id="tSub"></div>
        </div>
    </div>
    <div class="cfc" id="cfc"></div>

    <div id="heritage-root">
        <div id="app">
            <div class="view active" id="view-home">
                <section class="hh-home-intro">
                    <p class="hh-home-eyebrow">Learning for ages 2–10</p>
                    <h1>Explore Uganda’s heritage tribes</h1>
                    <p>Choose a tribe to open language, music, puzzles, missions, and clan stories. Progress is saved for <strong id="hhChildName">{{ $child->name }}</strong>.</p>
                </section>

                <section class="hh-home-progress-band">
                    <div class="hh-home-progress-band__head">
                        <h2>Progress overview</h2>
                        <p>Stars and completion for each tribe.</p>
                    </div>
                    <div class="hh-progress-grid hh-progress-grid--horizontal" id="lbGrid"></div>
                </section>

                <section class="hh-home-section">
                    <div class="hh-home-section__head">
                        <h2>Choose a tribe</h2>
                        <p>Pick where to start. You can switch tribes any time.</p>
                    </div>
                    <div class="hh-tribe-grid" id="tribeGrid"></div>
                </section>
            </div>

            <div class="view" id="view-tribe">
                <div class="tb-banner" id="tbBanner"></div>
                <div class="tps" id="tbProg"></div>
                <div class="fwrap">
                    <div class="fbar" id="fbar"></div>
                    <div class="dbar" id="dbar"></div>
                </div>
                <div class="agrid" id="actGrid"></div>
            </div>

            <div class="view" id="view-act">
                <div id="act-view">
                    <div class="av-back"><button class="av-bb" type="button" onclick="goBack()">← Back to activities</button></div>
                    <div class="av-card" id="avCard"></div>
                    <div class="s-title" style="padding:0;margin:18px 0 13px">More in this category</div>
                    <div class="near-grid" id="nearGrid"></div>
                </div>
            </div>

            <div class="view" id="view-passport">
                <div class="pv">
                    <div class="pv-title">🎫 Heritage Hero Passport</div>
                    <div class="pv-sub">Complete all activities in a tribe to earn their stamp!</div>
                    <div class="pg" id="passGrid"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
