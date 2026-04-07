<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome to Paulette Culture Kids</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;500;600;700;800&family=Bricolage+Grotesque:wght@200;300;400;500;600;700;800&family=DM+Serif+Display:ital@0;1&family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">

    <!-- Styles -->
    <style>
        :root {
            --clay-red:#C44B2B; --clay-red-light:#E06444; --clay-red-dark:#9A3218;
            --sunfire:#E8872A; --sunfire-light:#F2A84E; --sunfire-pale:#FDF0DE;
            --savanna-gold:#D4A017; --savanna-light:#F2CB5A;
            --banana-green:#4A7C59; --banana-mid:#6FA882; --banana-light:#B8D9C6; --leaf-pale:#EBF5EE;
            --barkcloth-warm:#8B5E3C; --barkcloth-mid:#B07D52; --barkcloth-pale:#F2E8DC;
            --indigo-night:#1E2D4A; --sky-dusk:#2E4D8A; --sky-mid:#4A72C4;
            --ink:#1A1208; --ink-mid:#3D2F1A; --ink-light:#6B5544;
            --stone:#9C8875; --stone-light:#C4B5A5;
            --cream:#FAF6F0; --cream-warm:#F5EDE0; --cream-mid:#EDE0CE; --white:#FFFFFF;
            
            --font-display:'Baloo 2', cursive;
            --font-child:'Nunito', sans-serif;
            --font-editorial:'DM Serif Display', serif;
            --font-admin:'Bricolage Grotesque', sans-serif;
            
            --sp-1:4px; --sp-2:8px; --sp-3:12px; --sp-4:16px; --sp-5:20px;
            --sp-6:24px; --sp-8:32px; --sp-10:40px; --sp-12:48px; --sp-16:64px;
            --r-sm:8px; --r-md:16px; --r-lg:24px; --r-xl:32px; --r-full:9999px;
            
            --shadow-xl:0 16px 48px rgba(26,18,8,.20);
            --ease-spring:cubic-bezier(.34,1.56,.64,1);
            --dur-fast:150ms;
        }

        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        body {
            background: var(--cream);
            color: var(--ink);
            font-family: var(--font-admin);
            line-height: 1.5;
            overflow-x: hidden;
        }

        .hero {
            min-height: 100vh;
            background: var(--indigo-night);
            position: relative;
            display: flex;
            align-items: center;
            overflow: hidden;
            padding: var(--sp-12) 0;
        }

        .hero-pattern {
            position: absolute;
            inset: 0;
            background-image: repeating-linear-gradient(60deg,transparent,transparent 40px,rgba(196,75,43,.06) 40px,rgba(196,75,43,.06) 41px),repeating-linear-gradient(-60deg,transparent,transparent 40px,rgba(232,135,42,.04) 40px,rgba(232,135,42,.04) 41px);
        }

        .hero-accent { position: absolute; border-radius: 50%; pointer-events: none; }
        .hero-accent-1 { width: 500px; height: 500px; background: radial-gradient(circle,rgba(196,75,43,.25) 0%,transparent 70%); top: -100px; right: -100px; }
        .hero-accent-2 { width: 300px; height: 300px; background: radial-gradient(circle,rgba(212,160,23,.2) 0%,transparent 70%); bottom: 50px; left: -50px; }

        .hero-inner {
            position: relative;
            z-index: 1;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 var(--sp-6);
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: var(--sp-16);
            align-items: center;
        }

        @media (max-width: 992px) {
            .hero-inner { grid-template-columns: 1fr; text-align: center; justify-items: center; }
            .hero-phone-wrap { display: none; }
        }

        .hero-eyebrow {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: var(--sunfire-light);
            margin-bottom: var(--sp-4);
            display: flex;
            align-items: center;
            gap: var(--sp-2);
        }

        .hero-headline {
            font-family: var(--font-display);
            font-size: clamp(38px, 5vw, 66px);
            font-weight: 800;
            line-height: 1.05;
            color: #fff;
            margin-bottom: var(--sp-6);
            letter-spacing: -1px;
        }

        .hero-headline em { font-style: normal; color: var(--sunfire-light); display: block; }

        .hero-body {
            font-size: 17px;
            color: rgba(255,255,255,.65);
            line-height: 1.8;
            margin-bottom: var(--sp-8);
            max-width: 480px;
        }

        .hero-tags { display: flex; flex-wrap: wrap; gap: var(--sp-2); margin-bottom: var(--sp-8); }
        .hero-tag { background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.12); color: rgba(255,255,255,.75); padding: 5px 12px; border-radius: var(--r-full); font-size: 11px; font-weight: 600; }

        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: var(--sp-2);
            padding: 12px 24px; border-radius: var(--r-full); border: none; cursor: pointer;
            font-family: var(--font-child); font-weight: 800; font-size: 14px; transition: all var(--dur-fast);
            text-decoration: none;
        }
        .btn-primary { background: var(--clay-red); color: #fff; box-shadow: 0 4px 0 var(--clay-red-dark); }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 0 var(--clay-red-dark); }
        .btn-ghost { background: transparent; color: #fff; border: 2px solid rgba(255,255,255,.3); }
        .btn-ghost:hover { background: rgba(255,255,255,.1); border-color: #fff; }
        .btn-lg { padding: 16px 36px; font-size: 17px; border-radius: var(--r-xl); }

        .hero-phone-wrap { position: relative; }
        .hero-phone {
            width: 260px; background: var(--ink); border-radius: 40px; padding: 14px;
            box-shadow: 0 32px 80px rgba(0,0,0,.5); position: relative; z-index: 2;
        }
        .hero-phone-screen { background: #FAF6F0; border-radius: 32px; overflow: hidden; aspect-ratio: 9/19; display: flex; flex-direction: column; }
        
        /* Simulated Phone UI */
        .phone-topbar { background: var(--clay-red); padding: 16px 16px 12px; display: flex; align-items: center; gap: 8px; }
        .phone-logo { font-family: var(--font-display); font-size: 14px; font-weight: 800; color: #fff; }
        .phone-avatar { width: 24px; height: 24px; border-radius: 50%; background: var(--sunfire-light); margin-left: auto; }
        .phone-content { padding: 16px; flex: 1; }
        .phone-greeting { font-family: var(--font-child); font-size: 14px; font-weight: 800; margin-bottom: 20px; }
        .phone-greeting span { color: var(--clay-red); }
        .phone-row-label { font-size: 9px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; color: var(--stone); margin-bottom: 10px; }
        .phone-cards { display: flex; gap: 10px; margin-bottom: 24px; }
        .phone-card { width: 90px; height: 60px; border-radius: 12px; background: linear-gradient(135deg, var(--clay-red), var(--clay-red-dark)); display: flex; align-items: center; justify-content: center; font-size: 24px; }
        .phone-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; }
        .phone-tile { height: 50px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 20px; }

        .hero-float {
            position: absolute; color: #fff; padding: 10px 16px; border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,.3); font-size: 11px; font-weight: 700; z-index: 3;
            animation: floatY 3s ease-in-out infinite;
        }
        .hero-float-1 { top: 40px; right: -20px; background: var(--sunfire); }
        .hero-float-2 { bottom: 80px; left: -30px; background: var(--banana-green); animation-delay: 0.5s; }

        @keyframes floatY { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-10px); } }

        .page-wrap { max-width: 1200px; margin: 0 auto; padding: var(--sp-16) var(--sp-6); }
        .section-label { font-size: 11px; font-weight: 700; letter-spacing: 2px; text-transform: uppercase; color: var(--clay-red); margin-bottom: var(--sp-3); display: block; text-align: center; }
        .section-title { font-family: var(--font-editorial); font-size: clamp(32px, 4vw, 48px); font-style: italic; color: var(--ink); margin-bottom: var(--sp-8); text-align: center; }
        
        .grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: var(--sp-8); }
        @media (max-width: 768px) { .grid-3 { grid-template-columns: 1fr; } }
        
        .card { background: #fff; border-radius: var(--r-xl); padding: var(--sp-8); box-shadow: 0 10px 30px rgba(26,18,8,.05); text-align: center; }
        .card-icon { font-size: 48px; margin-bottom: var(--sp-4); }
        .card-title { font-family: var(--font-display); font-size: 20px; font-weight: 700; margin-bottom: var(--sp-2); }
        .card-body { font-size: 14px; color: var(--ink-light); line-height: 1.6; }
    </style>
</head>
<body>
    <div class="hero">
        <div class="hero-pattern"></div>
        <div class="hero-accent hero-accent-1"></div>
        <div class="hero-accent hero-accent-2"></div>
        <div class="hero-inner">
            <div>
                <p class="hero-eyebrow">Cultural Learning Platform · Uganda</p>
                <h1 class="hero-headline">Stories woven<em>into every child.</em></h1>
                <p class="hero-body">Paulette Culture Kids brings 65+ Ugandan tribes to life through comics, songs, flashcards and interactive stories — for children ages 2–6 and their families.</p>
                
                <div class="hero-tags">
                    <span class="hero-tag">65+ Uganda Tribes</span>
                    <span class="hero-tag">Luganda · English · Swahili</span>
                    <span class="hero-tag">Offline‑First</span>
                    <span class="hero-tag">Child · Teacher · Parent</span>
                </div>

                <div style="display:flex; gap:var(--sp-4); flex-wrap:wrap">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" class="btn btn-primary btn-lg">Back to App →</a>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-primary btn-lg">▶ Explore Demo</a>
                            @if (Route::has('register'))
                                <a href="{{ route('login') }}" class="btn btn-ghost btn-lg">Sign In →</a>
                            @endif
                        @endauth
                    @endif
                </div>
            </div>

            <div class="hero-phone-wrap">
                <div class="hero-float hero-float-1">🌍 65 Ugandan Tribes</div>
                <div class="hero-float hero-float-2">📴 Works Offline</div>
                <div class="hero-phone">
                    <div class="hero-phone-screen">
                        <div class="phone-topbar">
                            <div class="phone-logo">Paulette</div>
                            <div class="phone-avatar"></div>
                        </div>
                        <div class="phone-content">
                            <p class="phone-greeting">Mwasuze mutya, <span>Aisha!</span> 🌞</p>
                            <p class="phone-row-label">Continue Reading</p>
                            <div class="phone-cards">
                                <div class="phone-card">🐇</div>
                                <div class="phone-card" style="background:var(--banana-green)">🥁</div>
                            </div>
                            <p class="phone-row-label">Activities</p>
                            <div class="phone-grid">
                                <div class="phone-tile" style="background:var(--clay-red)">📚</div>
                                <div class="phone-tile" style="background:var(--sunfire)">🎵</div>
                                <div class="phone-tile" style="background:var(--banana-green)">🧩</div>
                                <div class="phone-tile" style="background:var(--sky-dusk)">✏️</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="page-wrap">
        <span class="section-label">Why Paulette Culture Kids</span>
        <h2 class="section-title">Built for every role in the village</h2>
        
        <div class="grid-3">
            <div class="card">
                <div class="card-icon">🌍</div>
                <h3 class="card-title">65+ Uganda Tribes</h3>
                <p class="card-body">Every tribe with its language, comics, songs and vocabulary — mapped, accurate, and growing.</p>
            </div>
            <div class="card">
                <div class="card-icon">📴</div>
                <h3 class="card-title">Offline First</h3>
                <p class="card-body">Download story packs, songs and flashcards for rural classrooms. Sync when reconnected.</p>
            </div>
            <div class="card">
                <div class="card-icon">🎭</div>
                <h3 class="card-title">Every Role, One Platform</h3>
                <p class="card-body">Children, parents, teachers and admins — all have purpose-built, culturally rich dashboards.</p>
            </div>
        </div>

        <div style="text-align:center; margin-top:var(--sp-12)">
            <a href="{{ route('login') }}" class="btn btn-primary btn-lg">Start the Demo →</a>
        </div>
    </div>
</body>
</html>
