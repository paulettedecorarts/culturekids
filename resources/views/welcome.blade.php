<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome to Paulette Culture Kids</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@700;800&family=Bricolage+Grotesque:wght@700;800&family=DM+Serif+Display:ital@0;1&family=Nunito:wght@700;800&display=swap" rel="stylesheet">

    <!-- Styles -->
    <style>
        :root {
            --clay-red:#C44B2B; --clay-red-light:#E06444; 
            --sunfire:#E8872A; --sunfire-pale:#FDF0DE;
            --savanna-gold:#D4A017;
            --indigo-night:#1E2D4A; --sky-dusk:#2E4D8A;
            --ink:#1A1208; --stone:#9C8875;
            --cream:#FAF6F0; --cream-mid:#EDE0CE; --white:#FFFFFF;
            
            --font-display:'Baloo 2', cursive;
            --font-admin:'Bricolage Grotesque', sans-serif;
            --font-editorial:'DM Serif Display', serif;
            
            --r-xl:40px;
        }

        *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
        body { background: var(--white); color: var(--ink); font-family: var(--font-admin); overflow-x: hidden; line-height: 1.5; }

        /* HERO SECTION */
        .hero {
            min-height: 95vh; background: var(--indigo-night); position: relative;
            display: flex; align-items: center; justify-content: center; overflow: hidden; padding: 100px 0;
        }

        /* Pure CSS Diamond Texture (The sharp grid) */
        .hero::before {
            content:''; position:absolute; inset:0; opacity:0.12; z-index: 1;
            background: linear-gradient(45deg, rgba(255,255,255,0.2) 25%, transparent 25%, transparent 75%, rgba(255,255,255,0.2) 75%, rgba(255,255,255,0.2)),
                        linear-gradient(45deg, rgba(255,255,255,0.2) 25%, transparent 25%, transparent 75%, rgba(255,255,255,0.2) 75%, rgba(255,255,255,0.2));
            background-size: 80px 80px; background-position: 0 0, 40px 40px;
        }

        /* Light Leaks / Accents */
        .hero-accent { position: absolute; border-radius: 50%; filter: blur(100px); opacity: 0.4; z-index: 2; pointer-events: none; }
        .hero-accent-1 { width: 500px; height: 500px; background: var(--clay-red); top: -150px; right: -100px; }
        .hero-accent-2 { width: 400px; height: 400px; background: var(--savanna-gold); bottom: -100px; left: -100px; }

        .hero-inner {
            position: relative; z-index: 5; max-width: 1200px; margin: 0 auto; padding: 0 40px;
            display: grid; grid-template-columns: 1fr 420px; gap: 80px; align-items: center;
        }

        .hero-tagline { 
            color: var(--clay-red); font-size: 11px; font-weight: 800; letter-spacing: 2.5px; 
            text-transform: uppercase; margin-bottom: 24px; display: flex; align-items: center; gap: 12px; 
        }
        .hero-tagline::before { content: ''; width: 24px; height: 3px; background: var(--clay-red); border-radius: 4px; }

        .hero-headline {
            font-family: var(--font-display); font-size: 72px; font-weight: 800; line-height: 1.05; 
            color: #fff; margin-bottom: 32px; letter-spacing: -0.5px;
        }
        .hero-headline em { font-style: normal; color: var(--clay-red); display: block; }

        .hero-body { font-size: 19px; color: rgba(255,255,255,0.95); line-height: 1.6; margin-bottom: 48px; max-width: 520px; font-weight: 700; }

        .hero-pills { display: grid; grid-template-columns: auto auto auto; gap: 12px; width: fit-content; margin-bottom: 60px; }
        .hero-pill { background: rgba(255,255,255,.08); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,.12); padding: 10px 20px; border-radius: 99px; color: #fff; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; }

        .btn { display: inline-flex; align-items: center; gap: 12px; padding: 20px 48px; border-radius: 20px; font-size: 16px; font-weight: 800; text-decoration: none; transition: transform 0.2s; }
        .btn-primary { background: var(--clay-red); color: #fff; box-shadow: 0 8px 32px rgba(196,75,43,.4); }
        .btn-outline { border: 2.5px solid rgba(255,255,255,.2); color: #fff; }
        .btn:hover { transform: scale(1.02); }

        /* PHONE MOCKUP */
        .hero-phone-wrap { position: relative; }
        .hero-phone { 
            width: 300px; aspect-ratio: 9/19; background: #000; border-radius: 54px; 
            padding: 12px; border: 8px solid #222; box-shadow: 0 40px 100px rgba(0,0,0,.6); 
            position: relative; z-index: 10;
        }
        .phone-screen { background: var(--white); height: 100%; border-radius: 36px; overflow: hidden; display: flex; flex-direction: column; }
        .phone-header { background: var(--clay-red); padding: 18px 20px; color: #fff; font-family: var(--font-display); font-weight: 800; display: flex; justify-content: space-between; align-items: center; }
        .phone-content { padding: 24px; }
        .phone-greeting { font-weight: 800; font-size: 14px; color: var(--clay-red); margin-bottom: 20px; }
        .phone-label { font-size: 10px; font-weight: 800; color: var(--stone); text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 12px; }
        .phone-card { background: var(--cream); height: 74px; border-radius: 16px; margin-bottom: 16px; display: flex; align-items: center; justify-content: center; font-size: 32px; }
        .phone-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-top: 10px; }
        .phone-tile { aspect-ratio: 1; border-radius: 10px; background: var(--cream); display: flex; align-items: center; justify-content: center; font-size: 18px; }

        .floating { position: absolute; padding: 12px 24px; border-radius: 99px; font-size: 12px; font-weight: 800; color: #fff; box-shadow: 0 12px 32px rgba(0,0,0,.3); z-index: 20; white-space: nowrap; }
        .float-1 { background: var(--savanna-gold); top: 120px; right: -60px; }
        .float-2 { background: #2D5438; bottom: 100px; left: -80px; display: flex; align-items: center; gap: 10px; }

        /* VILLAGE SECTION */
        .page-wrap { max-width: 1200px; margin: 0 auto; padding: 120px 40px; text-align: center; }
        .section-label { color: var(--clay-red); font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 2.5px; margin-bottom: 16px; display: block; }
        .section-title { font-family: var(--font-editorial); font-size: 48px; font-style: italic; color: var(--ink); margin-bottom: 80px; }
        .grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 40px; }
        .card { background: #fff; border-radius: 40px; padding: 60px 40px; border: 1px solid var(--cream-mid); box-shadow: 0 8px 32px rgba(26,18,8,.04); transition: transform 0.3s; }
        .card:hover { transform: translateY(-10px); }
        .card-icon { font-size: 48px; margin-bottom: 32px; display: block; }
        .card-title { font-size: 20px; font-weight: 800; color: var(--ink); margin-bottom: 16px; }
        .card-body { font-size: 14px; color: var(--stone); line-height: 1.6; font-weight: 700; }
    </style>
</head>
<body>
    <div class="hero">
        <div class="hero-accent hero-accent-1"></div>
        <div class="hero-accent hero-accent-2"></div>
        <div class="hero-inner">
            <div>
                <p class="hero-tagline">Cultural Learning Platform · Uganda</p>
                <h1 class="hero-headline">Stories woven<em>into every child.</em></h1>
                <p class="hero-body">Paulette Culture Kids brings 65+ Ugandan tribes to life through comics, songs, flashcards and interactive stories — for children ages 2-6 and their families.</p>
                
                <div class="hero-pills">
                    <div class="hero-pill">65+ Uganda Tribes</div>
                    <div class="hero-pill">Luganda - English - Swahili</div>
                    <div class="hero-pill">Offline-First</div>
                    <div class="hero-pill">Child - Teacher - Parent</div>
                    <div class="hero-pill">CMS + Super Admin</div>
                    <div class="hero-pill">Kiosk / Museum Mode</div>
                </div>

                <div style="display:flex; gap:20px">
                    <a href="{{ route('teacher.dashboard') }}" class="btn btn-primary">▶ Explore Demo</a>
                    <a href="{{ route('login') }}" class="btn btn-outline">Sign In →</a>
                </div>
            </div>

            <div class="hero-phone-wrap">
                <div class="floating float-1">🔵 65 Ugandan Tribes</div>
                <div class="floating float-2">
                    <span style="background:var(--sunfire); width:12px; height:12px; border-radius:3px; display:flex; align-items:center; justify-content:center; font-size:8px">📴</span>
                    Works Offline
                </div>
                <div class="hero-phone">
                    <div class="phone-screen">
                        <div class="phone-header">
                            <span>Paulette</span>
                            <div style="width:24px; height:24px; background:rgba(255,255,255,0.2); border-radius:50%"></div>
                        </div>
                        <div class="phone-content">
                            <p class="phone-greeting">Mwasuze mutya, Aisha! 🦁</p>
                            <p class="phone-label">Continue Reading</p>
                            <div class="phone-card">🐇</div>
                            <div class="phone-card" style="margin-bottom:24px">🥁</div>
                            <p class="phone-label">Activities</p>
                            <div class="phone-grid">
                                <div class="phone-tile">📚</div>
                                <div class="phone-tile">🎵</div>
                                <div class="phone-tile">🧩</div>
                                <div class="phone-tile">🖍️</div>
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
                <span class="card-icon">🌍</span>
                <h3 class="card-title">65+ Uganda Tribes</h3>
                <p class="card-body">Every tribe with its language, comics, songs and vocabulary — mapped, accurate, and growing.</p>
            </div>
            <div class="card">
                <span class="card-icon">📴</span>
                <h3 class="card-title">Offline First</h3>
                <p class="card-body">Download story packs, songs and flashcards for rural classrooms. Sync when reconnected.</p>
            </div>
            <div class="card">
                <span class="card-icon">🎭</span>
                <h3 class="card-title">Every Role, One Platform</h3>
                <p class="card-body">Children, parents, teachers and admins — all have purpose-built, culturally rich dashboards.</p>
            </div>
        </div>

        <div style="margin-top:80px">
            <a href="{{ route('login') }}" class="btn btn-primary" style="padding:22px 64px; border-radius:24px; font-size:18px">Start the Demo →</a>
        </div>
    </div>
</body>
</html>
