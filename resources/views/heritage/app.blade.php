@extends('layouts.heritage')

@section('content')
<script>
    window.HERITAGE_BOOTSTRAP = @json($bootstrap);
</script>
<div id="appPage">
    <div class="topbar">
        <div class="tb-brand">
            <img src="{{ asset(config('brand.logo', 'images/brand/paulette-comics-logo.png')) }}" alt="" onerror="this.style.display='none'">
            <div class="tb-brand-txt">
                <span>{{ config('brand.name', 'Paulette Culture Kids') }}</span>
                <span>Heritage Heroes</span>
            </div>
        </div>
        <div class="tb-right">
            @if ($bootstrap['user']['role'] === 'parent' && $bootstrap['routes']['selectChild'])
                <a href="{{ $bootstrap['routes']['selectChild'] }}" class="hdr-btn" style="text-decoration:none;margin-right:8px">
                    👤 {{ $child->name }}
                </a>
            @endif
            <div class="uc">
                <div class="uav" id="uav">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                <span class="unm" id="unm">{{ $user->name }}</span>
            </div>
            <form method="POST" action="{{ route('logout') }}" style="margin:0">
                @csrf
                <button type="submit" class="btn-so">Sign Out</button>
            </form>
        </div>
    </div>
    <div id="stars"></div>
    <div class="toast" id="toast">
        <div class="t-ico" id="tIco">⭐</div>
        <div>
            <div class="t-title" id="tTitle"></div>
            <div class="t-sub" id="tSub"></div>
        </div>
    </div>
    <div class="cfc" id="cfc"></div>

    <div id="heritage-root" style="padding-top:54px">
        <div id="app">
            <header>
                <div class="logo" onclick="navStack=[];_applyView('home')">
                    <div class="logo-ico">🦅</div>
                    <div>
                        <div class="logo-t">Heritage Heroes</div>
                        <div class="logo-s">Uganda · {{ number_format($bootstrap['stats']['activities'] ?? 0) }}+ Activities</div>
                    </div>
                </div>
                <div class="hdr-r">
                    <button class="hdr-btn" id="btnP" type="button" onclick="nav('passport')">🎫 Passport</button>
                    <button class="hdr-btn hidden" id="btnBack" type="button" onclick="goBack()">← Back</button>
                    <div class="star-pill">⭐ <span id="totS">0</span></div>
                </div>
            </header>

            <div class="view active" id="view-home">
                <div class="hero-sec">
                    <h1>Uganda Heritage Heroes</h1>
                    <p>{{ number_format($bootstrap['stats']['activities'] ?? 0) }}+ activities across {{ $bootstrap['stats']['tribes'] ?? 11 }} tribes — language, songs, mazes, clan stories, puzzles, missions &amp; more!</p>
                    <div class="h-stats">
                        <div class="h-stat"><div class="h-sn">{{ $bootstrap['stats']['tribes'] ?? 11 }}</div><div class="h-sl">Tribes</div></div>
                        <div class="h-stat"><div class="h-sn">{{ number_format($bootstrap['stats']['activities'] ?? 0) }}</div><div class="h-sl">Activities</div></div>
                        <div class="h-stat"><div class="h-sn">{{ $bootstrap['stats']['categories'] ?? 6 }}</div><div class="h-sl">Categories</div></div>
                        <div class="h-stat"><div class="h-sn">2–10</div><div class="h-sl">Ages</div></div>
                    </div>
                </div>
                <div class="s-title">Choose Your <em>Tribe</em></div>
                <div class="tgrid" id="tribeGrid"></div>
                <div class="lb">
                    <div class="s-title" style="padding:0;margin-bottom:6px">Your <em>Progress</em></div>
                    <p style="color:var(--muted);font-size:.86rem;margin-bottom:0">Stars for {{ $child->name }}</p>
                    <div class="lb-grid" id="lbGrid"></div>
                </div>
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
