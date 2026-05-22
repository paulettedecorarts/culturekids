<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Paulette Culture Kids') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;500;600;700;800&family=Bricolage+Grotesque:wght@200;300;400;500;600;700;800&family=Nunito:ital,wght@0,200..1000;1,200..1000&display=swap" rel="stylesheet">

        <!-- Styles -->
        @include('layouts.partials.portal-theme-vars')
        <style>
            :root {
                --indigo-night:#1E2D4A;

                --font-display:'Baloo 2', cursive;
                --font-child:'Nunito', sans-serif;
                --font-admin:'Bricolage Grotesque', sans-serif;
                
                --r-xl:32px; --r-full:9999px;
                --shadow-xl:0 16px 48px rgba(26,18,8,.20);
                --dur-fast:150ms;
            }

            body {
                font-family: var(--font-admin);
                background-color: var(--indigo-night);
                color: var(--white);
                margin: 0;
                padding: 0;
            }

            .guest-shell {
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 24px;
                position: relative;
                overflow: hidden;
            }

            /* Backdrop Accents */
            .orb { position: absolute; border-radius: 50%; pointer-events: none; opacity: 0.3; }
            .orb-primary { width: 500px; height: 500px; background: radial-gradient(circle, var(--clay-red), transparent 70%); top: -150px; right: -150px; }
            .orb-secondary { width: 300px; height: 300px; background: radial-gradient(circle, var(--banana-green), transparent 70%); bottom: -80px; left: -80px; }

            .guest-card {
                width: 100%;
                max-width: 440px;
                background: #FFFFFF;
                border-radius: var(--r-xl);
                padding: 48px;
                box-shadow: var(--shadow-xl);
                position: relative;
                z-index: 10;
                color: var(--ink);
            }

            .guest-logo {
                text-align: center;
                margin-bottom: 32px;
            }

            .logo-text {
                font-family: var(--font-display);
                font-size: 24px;
                font-weight: 800;
                color: var(--clay-red);
                margin-bottom: 4px;
            }

            .logo-sub {
                font-size: 13px;
                color: var(--stone);
                font-weight: 600;
            }

            /* Input Styling */
            .input-group { margin-bottom: 20px; }
            .input-label {
                display: block;
                font-size: 10px;
                font-weight: 700;
                letter-spacing: 1.5px;
                text-transform: uppercase;
                color: var(--stone);
                margin-bottom: 8px;
            }

            .form-input {
                width: 100%;
                padding: 12px 20px;
                border-radius: var(--r-full);
                border: 2px solid var(--cream-mid);
                background-color: var(--cream);
                font-family: inherit;
                font-size: 14px;
                color: var(--ink);
                outline: none;
                transition: border-color var(--dur-fast);
            }

            .form-input:focus { border-color: var(--clay-red); }

            .btn-primary {
                width: 100%;
                background: var(--clay-red);
                color: #FFFFFF;
                border: none;
                padding: 14px;
                border-radius: var(--r-full);
                font-family: var(--font-child);
                font-weight: 800;
                font-size: 15px;
                cursor: pointer;
                box-shadow: 0 4px 0 var(--clay-red-dark);
                transition: all var(--dur-fast);
                display: block;
                text-align: center;
                text-decoration: none;
            }

            .btn-primary:active { transform: translateY(2px); box-shadow: 0 2px 0 var(--clay-red-dark); }
            
            .auth-links {
                margin-top: 24px;
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 12px;
                font-size: 13px;
            }

            .auth-link { color: var(--stone); text-decoration: none; font-weight: 600; }
            .auth-link:hover { color: var(--clay-red); }

            .input-error { color: var(--clay-red); font-size: 11px; margin-top: 5px; font-weight: 700; }

            .guest-lead {
                text-align: center;
                font-size: 14px;
                line-height: 1.55;
                color: var(--stone);
                font-weight: 600;
                margin: -8px 0 28px;
            }
        </style>
    </head>
    <body>
        <div class="guest-shell">
            <div class="orb orb-primary"></div>
            <div class="orb orb-secondary"></div>

            <div class="guest-card">
                <div class="guest-logo">
                    <div class="logo-text">Paulette Culture Kids</div>
                    <div class="logo-sub">
                        @if (request()->routeIs('password.reset'))
                            Set your password
                        @else
                            {{ $title ?? 'Welcome Back' }}
                        @endif
                    </div>
                </div>

                {{ $slot }}
            </div>
        </div>
    </body>
</html>
