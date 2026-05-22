@php
    $l = $landing ?? [];
    $primary = $l['primary_color'] ?? '#C44B2B';
    $secondary = $l['secondary_color'] ?? '#1E2D4A';
    $accent = $l['accent_color'] ?? '#F2CB5A';
    $heroStart = $l['hero_bg_start'] ?? '#FFF8F0';
    $heroEnd = $l['hero_bg_end'] ?? '#E8F4FC';
    $fontHeading = $l['font_heading'] ?? 'Baloo 2';
    $fontBody = $l['font_body'] ?? 'Inter';
    $headline = trim(($l['hero_headline'] ?? 'Bring').' '.($l['hero_highlight'] ?? "Africa's Stories").' '.($l['hero_headline_suffix'] ?? 'to Life'));
    $fontsQuery = urlencode($fontHeading.'|'.$fontBody);
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $seoTitle ?? config('app.name') }}</title>
    @if (!empty($seoDescription))
        <meta name="description" content="{{ $seoDescription }}">
    @endif

    @include('layouts.partials.brand-head')

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family={{ $fontsQuery }}:wght@400;600;700;800&display=swap" rel="stylesheet">

    @include('layouts.partials.portal-theme-vars')

    <style>
        :root {
            --font-landing: '{{ $fontBody }}', system-ui, sans-serif;
            --font-landing-display: '{{ $fontHeading }}', system-ui, sans-serif;
            --landing-primary: {{ $primary }};
            --landing-secondary: {{ $secondary }};
            --landing-accent: {{ $accent }};
            --landing-hero-start: {{ $heroStart }};
            --landing-hero-end: {{ $heroEnd }};
        }
        body { margin: 0; }
    </style>
    <style>{!! file_get_contents(resource_path('css/landing.css')) !!}</style>
    <style>{!! file_get_contents(resource_path('css/brand-logo.css')) !!}</style>
</head>
<body>
<div class="landing">
    <header class="landing-header" id="landing-header">
        <div class="landing__container landing-header__inner">
            <a href="/" class="landing-logo" aria-label="{{ config('brand.name') }} home">
                <x-brand-logo variant="full" />
            </a>

            <nav class="landing-nav" aria-label="Main">
                <button type="button" class="landing-nav__toggle" aria-expanded="false" aria-controls="landing-nav-menu" id="landing-nav-toggle" aria-label="Open menu">
                    <span></span><span></span><span></span>
                </button>
                <ul class="landing-nav__links" id="landing-nav-menu">
                    <li><a href="#peoples" class="landing-nav__link">{{ heritage('people_plural') }}</a></li>
                    <li><a href="#stories" class="landing-nav__link">Stories</a></li>
                    <li><a href="#schools" class="landing-nav__link">For Schools</a></li>
                    <li><a href="#pricing" class="landing-nav__link">Pricing</a></li>
                    <li><a href="{{ route('login') }}" class="landing-nav__link">Login</a></li>
                </ul>
                <a href="{{ route('register') }}" class="landing-btn landing-btn--primary">Start Free Trial</a>
            </nav>
        </div>
    </header>

    <section class="landing-hero landing-hero--split" aria-labelledby="hero-title">
        <div class="landing__container landing-hero__grid">
            <div class="landing-hero__copy">
                <h1 id="hero-title" class="landing-hero__title">
                    {{ $l['hero_headline'] ?? 'Bring' }}
                    <span class="landing-hero__highlight">{{ $l['hero_highlight'] ?? "Africa's Stories" }}</span>
                    {{ $l['hero_headline_suffix'] ?? 'to Life' }}
                </h1>
                <p class="landing-hero__subtitle">
                    {{ $l['hero_subtitle'] ?? 'Interactive cultural comics, songs, and language learning for children ages 2–6 — celebrating Uganda\'s peoples and heritage.' }}
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
            <div class="landing-hero__visual" aria-hidden="false">
                @if ($heroImageUrl ?? null)
                    <img
                        src="{{ $heroImageUrl }}"
                        alt="{{ $heroComic?->title ? 'Comic: '.$heroComic->title : 'Paulette Culture Kids hero illustration' }}"
                        class="landing-hero__image"
                        width="520"
                        height="520"
                        loading="eager"
                        decoding="async"
                    >
                @else
                    <div class="landing-hero__placeholder">
                        <x-brand-logo variant="mark" class="landing-hero__placeholder-logo" />
                        <p>Stories that celebrate {{ strtolower(heritage('ugandan_peoples')) }}</p>
                    </div>
                @endif
                @if ($heroComic ?? null)
                    <p class="landing-hero__comic-caption">
                        <span aria-hidden="true">📖</span>
                        {{ $heroComic->title }}
                        @if ($heroComic->tribe)
                            · {{ $heroComic->tribe->name }}
                        @endif
                    </p>
                @endif
            </div>
        </div>
    </section>

    <section class="landing-features" id="features" aria-label="Platform highlights">
        <div class="landing-features__grid">
            <article class="landing-feature">
                <div class="landing-feature__icon" aria-hidden="true">
                    <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M8 6h28a4 4 0 0 1 4 4v28a4 4 0 0 1-4 4H8a4 4 0 0 1-4-4V10a4 4 0 0 1 4-4z" fill="#B8D4F0"/>
                        <path d="M12 14h24v3H12v-3zm0 8h20v2H12v-2zm0 6h16v2H12v-2z" fill="#5B9BD5"/>
                        <path d="M28 32l8 6V14l-8 6v12z" fill="#2E6DB4"/>
                    </svg>
                </div>
                <h2 class="landing-feature__title">Cultural Comics</h2>
                <p class="landing-feature__desc">Age-adaptive stories from {{ $peoplesCount ?? 65 }}+ {{ strtolower(heritage('ugandan_peoples')) }} with audio read-aloud</p>
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
                <p class="landing-feature__desc">Traditional songs and vocabulary in {{ heritage('local_languages') }}</p>
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

    <section class="landing-tribes" id="peoples" aria-labelledby="peoples-heading">
        <div class="landing-tribes__inner">
            <h2 id="peoples-heading" class="landing-tribes__title">{{ $peoplesSectionTitle ?? heritage('explore_peoples_count', ['count' => $peoplesCount ?? 65]) }}</h2>
            <div class="landing-tribes__row">
                @forelse ($featuredPeoples ?? [] as $person)
                    <a href="{{ route('login') }}" class="landing-tribe-chip">
                        <span class="landing-tribe-chip__dot" aria-hidden="true">{{ $person->hero_emoji ?: '🌍' }}</span>
                        {{ $person->name }}
                    </a>
                @empty
                    <a href="{{ route('login') }}" class="landing-tribe-chip"><span class="landing-tribe-chip__dot" aria-hidden="true">👑</span> Buganda</a>
                    <a href="{{ route('login') }}" class="landing-tribe-chip"><span class="landing-tribe-chip__dot" aria-hidden="true">🏹</span> Acholi</a>
                    <a href="{{ route('login') }}" class="landing-tribe-chip"><span class="landing-tribe-chip__dot" aria-hidden="true">🌊</span> Basoga</a>
                @endforelse
                @php $shown = ($featuredPeoples ?? collect())->count(); $more = max(0, ($peoplesCount ?? 65) - $shown); @endphp
                @if ($more > 0)
                    <a href="{{ route('login') }}" class="landing-tribe-chip landing-tribe-chip--more">
                        <span class="landing-tribe-chip__dot" aria-hidden="true">⭐</span>
                        + {{ $more }} more →
                    </a>
                @endif
            </div>
        </div>
    </section>

    <section class="landing-section landing-section--white" id="stories" aria-labelledby="stories-heading">
        <div class="landing-section__inner">
            <span class="landing-section__label">Stories</span>
            <h2 id="stories-heading" class="landing-section__title">Comic story packs children love</h2>
            <p class="landing-section__lead">
                Age-banded adventures (2–3, 3–4, 4–5, 5–6) with panel-by-panel audio, heritage vocabulary, and {{ heritage('people_specific') }} {{ heritage('heritage_heroes') }} — ready for classroom or home.
            </p>
            <div class="landing-stories__grid">
                <article class="landing-story-card">
                    <span class="landing-story-card__emoji" aria-hidden="true">🐇</span>
                    <h3 class="landing-story-card__title">The Clever Hare</h3>
                    <p class="landing-story-card__meta">Buganda · Ages 3–4</p>
                    <p class="landing-story-card__desc">A trickster tale with Luganda phrases, read-aloud on every panel, and gentle comprehension prompts.</p>
                </article>
                <article class="landing-story-card">
                    <span class="landing-story-card__emoji" aria-hidden="true">🥁</span>
                    <h3 class="landing-story-card__title">Drums of Acholi</h3>
                    <p class="landing-story-card__meta">Acholi · Ages 4–5</p>
                    <p class="landing-story-card__desc">Rhythm, community, and northern heritage — interactive panels plus song tie-ins from the {{ strtolower(heritage('people_library')) }}.</p>
                </article>
                <article class="landing-story-card">
                    <span class="landing-story-card__emoji" aria-hidden="true">🦁</span>
                    <h3 class="landing-story-card__title">Savanna Friends</h3>
                    <p class="landing-story-card__meta">{{ heritage('mixed_peoples') }} · Ages 2–3</p>
                    <p class="landing-story-card__desc">Short, audio-first episodes for early learners with large touch targets and simple cultural greetings.</p>
                </article>
            </div>
            <div class="landing-section__cta-row">
                <a href="{{ route('register') }}" class="landing-btn landing-btn--primary">Browse story library</a>
            </div>
        </div>
    </section>

    <section class="landing-section landing-section--beige" id="schools" aria-labelledby="schools-heading">
        <div class="landing-section__inner">
            <span class="landing-section__label">For Schools</span>
            <h2 id="schools-heading" class="landing-section__title">Built for classrooms and organisations</h2>
            <p class="landing-section__lead">
                Give every school its own branded space — org admins manage teachers, approve content, toggle learning modules, and track engagement from one hub.
            </p>
            <div class="landing-schools__grid">
                <ul class="landing-schools__list">
                    <li class="landing-schools__item">
                        <span class="landing-schools__icon" aria-hidden="true">🏫</span>
                        <div>
                            <h3 class="landing-schools__item-title">Organisation dashboards</h3>
                            <p class="landing-schools__item-desc">Org admins review content, manage classrooms, and invite teachers — scoped to your school only.</p>
                        </div>
                    </li>
                    <li class="landing-schools__item">
                        <span class="landing-schools__icon" aria-hidden="true">🎨</span>
                        <div>
                            <h3 class="landing-schools__item-title">Custom branding</h3>
                            <p class="landing-schools__item-desc">Set your default theme, logo, and colours so the CMS and mobile app reflect your identity.</p>
                        </div>
                    </li>
                    <li class="landing-schools__item">
                        <span class="landing-schools__icon" aria-hidden="true">📦</span>
                        <div>
                            <h3 class="landing-schools__item-title">Offline bundles</h3>
                            <p class="landing-schools__item-desc">Download .ckb packs for rural sites — stories, songs, and activities that sync when you reconnect.</p>
                        </div>
                    </li>
                    <li class="landing-schools__item">
                        <span class="landing-schools__icon" aria-hidden="true">🧩</span>
                        <div>
                            <h3 class="landing-schools__item-title">Modular curriculum</h3>
                            <p class="landing-schools__item-desc">Enable comics, songs, puzzles, games, and more per organisation — pay only for what you use.</p>
                        </div>
                    </li>
                </ul>
                <aside class="landing-schools__panel" aria-label="School plan highlights">
                    <h3 class="landing-schools__panel-title">School plan includes</h3>
                    <div class="landing-schools__stat"><span>Teachers &amp; classrooms</span><strong>Unlimited</strong></div>
                    <div class="landing-schools__stat"><span>Content review queue</span><strong>✓</strong></div>
                    <div class="landing-schools__stat"><span>Per-org module toggles</span><strong>✓</strong></div>
                    <div class="landing-schools__stat"><span>Analytics &amp; reports</span><strong>✓</strong></div>
                    <div class="landing-schools__stat"><span>Theme engine</span><strong>✓</strong></div>
                    <a href="{{ route('register') }}" class="landing-btn landing-btn--primary" style="width:100%; margin-top:24px; justify-content:center">Request school access</a>
                </aside>
            </div>
        </div>
    </section>

    <section class="landing-section landing-section--white" id="pricing" aria-labelledby="pricing-heading">
        <div class="landing-section__inner">
            <span class="landing-section__label">Pricing</span>
            <h2 id="pricing-heading" class="landing-section__title">Plans that scale with you</h2>
            <p class="landing-section__lead">
                From families exploring at home to districts rolling out culturally grounded learning — start free and upgrade when you are ready.
            </p>
            <div class="landing-pricing__grid">
                <article class="landing-price-card">
                    <h3 class="landing-price-card__name">Free</h3>
                    <p class="landing-price-card__price">$0</p>
                    <p class="landing-price-card__note">For parents &amp; trial classrooms</p>
                    <ul class="landing-price-card__features">
                        <li>Sample story packs &amp; songs</li>
                        <li>One child profile</li>
                        <li>Platform default branding</li>
                        <li>Community support</li>
                    </ul>
                    <a href="{{ route('register') }}" class="landing-btn landing-btn--outline-dark">Get started</a>
                </article>
                <article class="landing-price-card landing-price-card--featured">
                    <span class="landing-price-card__badge">Most popular</span>
                    <h3 class="landing-price-card__name">School</h3>
                    <p class="landing-price-card__price">Custom <span>/ year</span></p>
                    <p class="landing-price-card__note">Per organisation · modular add-ons</p>
                    <ul class="landing-price-card__features">
                        <li>{{ heritage('full_catalogue') }}</li>
                        <li>Org admin &amp; teacher portals</li>
                        <li>Review queue &amp; approvals</li>
                        <li>Offline bundles &amp; kiosk mode</li>
                        <li>Custom themes &amp; modules</li>
                    </ul>
                    <a href="{{ route('register') }}" class="landing-btn landing-btn--primary">Start free trial</a>
                </article>
                <article class="landing-price-card">
                    <h3 class="landing-price-card__name">Enterprise</h3>
                    <p class="landing-price-card__price">Let's talk</p>
                    <p class="landing-price-card__note">Districts, NGOs &amp; multi-site rollouts</p>
                    <ul class="landing-price-card__features">
                        <li>Everything in School</li>
                        <li>Multi-organisation management</li>
                        <li>Priority onboarding &amp; training</li>
                        <li>SLA &amp; dedicated support</li>
                        <li>API &amp; integration options</li>
                    </ul>
                    <a href="{{ route('register') }}" class="landing-btn landing-btn--outline-dark">Contact sales</a>
                </article>
            </div>
        </div>
    </section>

    <section class="landing-section landing-section--navy landing-download" id="download" aria-labelledby="download-heading">
        <div class="landing-section__inner">
            <span class="landing-section__label">Mobile app</span>
            <h2 id="download-heading" class="landing-section__title">Download the Culture Kids app</h2>
            <p class="landing-section__lead" style="margin-left:auto; margin-right:auto">
                Stories, songs, and activities on phones and tablets — works offline after you download your {{ strtolower(heritage('people_bundles')) }}.
            </p>
            <div class="landing-download__stores">
                <a href="{{ route('register') }}" class="landing-download__store landing-download__store--apple" aria-label="Download on the App Store">
                    <span class="landing-download__store-icon">
                        @include('components.landing.icon-app-store')
                    </span>
                    <span class="landing-download__store-text">App Store</span>
                </a>
                <a href="{{ route('register') }}" class="landing-download__store landing-download__store--google" aria-label="Get it on Google Play">
                    <span class="landing-download__store-icon">
                        @include('components.landing.icon-google-play')
                    </span>
                    <span class="landing-download__store-text">Google Play</span>
                </a>
            </div>
        </div>
    </section>

    <section class="landing-cta" id="cta" aria-labelledby="cta-title">
        <div class="landing__container">
            <h2 id="cta-title" class="landing-cta__title">{{ $l['cta_title'] ?? "Ready to start your child's cultural journey?" }}</h2>
            <p class="landing-cta__subtitle">
                {{ $l['cta_subtitle'] ?? 'Join thousands of children learning about their heritage through stories and songs.' }}
            </p>
            <a href="{{ route('register') }}" class="landing-btn landing-btn--primary landing-btn--cta">Create Free Account</a>
        </div>
    </section>
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

        document.querySelectorAll('.landing-nav__link[href^="#"]').forEach(function (link) {
            link.addEventListener('click', function () {
                header.classList.remove('landing-header--open');
                toggle.setAttribute('aria-expanded', 'false');
            });
        });
    })();
</script>
</body>
</html>
