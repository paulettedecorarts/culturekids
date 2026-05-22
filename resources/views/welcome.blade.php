<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Paulette Culture Kids') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @include('layouts.partials.portal-theme-vars')

    <style>
        :root {
            --font-landing: 'Inter', system-ui, sans-serif;
        }
        body { margin: 0; }
    </style>
    <style>{!! file_get_contents(resource_path('css/landing.css')) !!}</style>
</head>
<body>
<div class="landing">
    <header class="landing-header" id="landing-header">
        <div class="landing__container landing-header__inner">
            <a href="/" class="landing-logo" aria-label="{{ config('app.name') }} home">
                <span class="landing-logo__globe" aria-hidden="true">🌍</span>
                <span class="landing-logo__text">
                    <span class="landing-logo__orange">Paulette</span>
                    <span class="landing-logo__navy"> Culture </span>
                    <span class="landing-logo__orange">Kids</span>
                </span>
            </a>

            <nav class="landing-nav" aria-label="Main">
                <button type="button" class="landing-nav__toggle" aria-expanded="false" aria-controls="landing-nav-menu" id="landing-nav-toggle" aria-label="Open menu">
                    <span></span><span></span><span></span>
                </button>
                <ul class="landing-nav__links" id="landing-nav-menu">
                    <li><a href="#tribes" class="landing-nav__link">Tribes</a></li>
                    <li><a href="#features" class="landing-nav__link">Stories</a></li>
                    <li><a href="#schools" class="landing-nav__link">For Schools</a></li>
                    <li><a href="#pricing" class="landing-nav__link">Pricing</a></li>
                    <li><a href="{{ route('login') }}" class="landing-nav__link">Login</a></li>
                </ul>
                <a href="{{ route('register') }}" class="landing-btn landing-btn--primary">Start Free Trial</a>
            </nav>
        </div>
    </header>

    <section class="landing-hero" aria-labelledby="hero-title">
        <div class="landing__container">
            <h1 id="hero-title" class="landing-hero__title">
                Bring <span class="landing-hero__highlight">Africa's Stories</span> to Life
            </h1>
            <p class="landing-hero__subtitle">
                Interactive cultural comics, songs, and language learning for children ages 2–6 across 65+ Ugandan tribes.
            </p>
            <div class="landing-hero__actions">
                <a href="{{ route('register') }}" class="landing-btn landing-btn--primary landing-btn--hero-primary">
                    <span class="landing-btn__icon" aria-hidden="true">🚀</span>
                    Start Free Trial
                </a>
                <a href="#download" class="landing-btn landing-btn--hero-secondary">
                    <span class="landing-btn__icon" aria-hidden="true">📱</span>
                    Download App
                </a>
            </div>
        </div>
    </section>

    <section class="landing-features" id="features" aria-labelledby="features-heading">
        <div class="landing-features__grid">
            <article class="landing-feature">
                <div class="landing-feature__icon" aria-hidden="true">
                    <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M8 6h28a4 4 0 0 1 4 4v28a4 4 0 0 1-4 4H8a4 4 0 0 1-4-4V10a4 4 0 0 1 4-4z" fill="#B8D4F0"/>
                        <path d="M12 14h24v3H12v-3zm0 8h20v2H12v-2zm0 6h16v2H12v-2z" fill="#5B9BD5"/>
                        <path d="M28 32l8 6V14l-8 6v12z" fill="#2E6DB4"/>
                    </svg>
                </div>
                <h2 id="features-heading" class="landing-feature__title">Cultural Comics</h2>
                <p class="landing-feature__desc">Age-adaptive stories from 65+ Ugandan tribes with audio read-aloud</p>
            </article>
            <article class="landing-feature">
                <div class="landing-feature__icon" aria-hidden="true">
                    <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="24" cy="28" r="14" fill="#D4B8F0"/>
                        <path d="M24 8v32M16 16c4-6 16-6 16 0M14 24c-2 4 20 4 20 0" stroke="#7B4BB8" stroke-width="3" stroke-linecap="round"/>
                        <ellipse cx="24" cy="30" rx="6" ry="4" fill="#9B6FD4"/>
                    </svg>
                </div>
                <h2 class="landing-feature__title">Songs &amp; Language</h2>
                <p class="landing-feature__desc">Traditional songs and vocabulary in authentic tribal languages</p>
            </article>
            <article class="landing-feature">
                <div class="landing-feature__icon" aria-hidden="true">
                    <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M6 32L38 10l4 8-32 22-4-8z" fill="#7EB8E8"/>
                        <path d="M38 10l4 8-8 4-4-8 8-4z" fill="#4A90D9"/>
                        <path d="M10 36l-4-4 6-2 2 6z" fill="#2E6DB4"/>
                    </svg>
                </div>
                <h2 class="landing-feature__title">Works Offline</h2>
                <p class="landing-feature__desc">Download content bundles for full offline use in low-connectivity areas</p>
            </article>
        </div>
    </section>

    <section class="landing-tribes" id="tribes" aria-labelledby="tribes-heading">
        <div class="landing-tribes__inner">
        <h2 id="tribes-heading" class="landing-tribes__title">Explore 65+ Ugandan Tribes</h2>
        <div class="landing-tribes__row">
            <a href="{{ route('login') }}" class="landing-tribe-chip">
                <span class="landing-tribe-chip__dot landing-tribe-chip__dot--buganda" aria-hidden="true">👑</span>
                Buganda
            </a>
            <a href="{{ route('login') }}" class="landing-tribe-chip">
                <span class="landing-tribe-chip__dot landing-tribe-chip__dot--acholi" aria-hidden="true">🏹</span>
                Acholi
            </a>
            <a href="{{ route('login') }}" class="landing-tribe-chip">
                <span class="landing-tribe-chip__dot landing-tribe-chip__dot--basoga" aria-hidden="true">🌊</span>
                Basoga
            </a>
            <a href="{{ route('login') }}" class="landing-tribe-chip">
                <span class="landing-tribe-chip__dot landing-tribe-chip__dot--iteso" aria-hidden="true">🌾</span>
                Iteso
            </a>
            <a href="{{ route('login') }}" class="landing-tribe-chip">
                <span class="landing-tribe-chip__dot landing-tribe-chip__dot--banyankole" aria-hidden="true">🐄</span>
                Banyankole
            </a>
            <a href="{{ route('login') }}" class="landing-tribe-chip">
                <span class="landing-tribe-chip__dot landing-tribe-chip__dot--alur" aria-hidden="true">🎵</span>
                Alur
            </a>
            <a href="{{ route('login') }}" class="landing-tribe-chip landing-tribe-chip--more">
                <span class="landing-tribe-chip__dot" aria-hidden="true">⭐</span>
                + 59 more →
            </a>
        </div>
        </div>
    </section>

    <section class="landing-cta" id="schools" aria-labelledby="cta-title">
        <div class="landing__container">
            <h2 id="cta-title" class="landing-cta__title">Ready to start your child's cultural journey?</h2>
            <p class="landing-cta__subtitle">
                Join 2,847 children learning about their heritage through stories and songs
            </p>
            <a href="{{ route('register') }}" class="landing-btn landing-btn--primary landing-btn--cta">Create Free Account</a>
        </div>
    </section>

    <section id="pricing" class="landing-cta" style="display:none" aria-hidden="true"></section>
    <section id="download" style="position:absolute;visibility:hidden;height:0" aria-hidden="true"></section>
</div>

<script>
    (function () {
        var header = document.getElementById('landing-header');
        var toggle = document.getElementById('landing-nav-toggle');
        if (!header || !toggle) return;
        toggle.addEventListener('click', function () {
            var open = header.classList.toggle('landing-header--open');
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
        document.querySelectorAll('.landing-nav__link').forEach(function (link) {
            link.addEventListener('click', function () {
                header.classList.remove('landing-header--open');
                toggle.setAttribute('aria-expanded', 'false');
            });
        });
    })();
</script>
</body>
</html>
