<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Paulette Culture Kids') }}</title>

        @include('layouts.partials.brand-head')
        <style>{!! file_get_contents(resource_path('css/brand-logo.css')) !!}</style>

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
                padding: 40px;
                box-shadow: var(--shadow-xl);
                position: relative;
                z-index: 10;
                color: var(--ink);
            }

            /* School registration — wider card, equal padding, two-column form */
            .guest-card--register {
                max-width: 680px;
                padding: 40px;
            }

            .guest-card--register .guest-logo {
                margin-bottom: 24px;
            }

            .guest-card--register .guest-lead {
                margin: 0 0 24px;
            }

            .register-form-grid {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                column-gap: 28px;
                row-gap: 0;
            }

            .register-form-grid > * {
                min-width: 0;
            }

            .register-form-grid .span-full {
                grid-column: 1 / -1;
            }

            @media (max-width: 640px) {
                .register-form-grid {
                    grid-template-columns: minmax(0, 1fr);
                    column-gap: 0;
                }
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
                display: block;
                width: 100%;
                max-width: 100%;
                box-sizing: border-box;
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

            .password-field__label-row {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 8px;
            }

            .password-field {
                position: relative;
            }

            .password-field__input {
                padding-right: 52px;
            }

            .password-field__toggle {
                position: absolute;
                right: 14px;
                top: 50%;
                transform: translateY(-50%);
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 4px;
                border: none;
                background: transparent;
                color: var(--stone);
                cursor: pointer;
                border-radius: 9999px;
                transition: color var(--dur-fast);
            }

            .password-field__toggle:hover {
                color: var(--clay-red);
            }

            [x-cloak] { display: none !important; }

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

            .btn-primary[disabled],
            .guest-submit-btn--loading {
                opacity: 0.75;
                cursor: not-allowed;
            }

            .guest-submit-btn__loading {
                display: none;
                align-items: center;
                justify-content: center;
                gap: 8px;
            }

            .guest-submit-btn__icon {
                width: 18px;
                height: 18px;
                animation: guest-spin 0.8s linear infinite;
            }

            @keyframes guest-spin {
                to { transform: rotate(360deg); }
            }

            .register-type-toggle {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 10px;
                margin-bottom: 24px;
                padding: 6px;
                border-radius: var(--r-full);
                background: var(--cream);
                border: 2px solid var(--cream-mid);
            }

            .register-type-toggle--three {
                grid-template-columns: repeat(3, minmax(0, 1fr));
                border-radius: 18px;
            }

            @media (max-width: 560px) {
                .register-type-toggle--three {
                    grid-template-columns: 1fr;
                }
            }

            .register-type-toggle__option {
                border: none;
                background: transparent;
                color: var(--stone);
                font-family: inherit;
                font-size: 13px;
                font-weight: 700;
                padding: 10px 14px;
                border-radius: var(--r-full);
                cursor: pointer;
                transition: all var(--dur-fast);
            }

            .register-type-toggle__option.is-active {
                background: #FFFFFF;
                color: var(--ink);
                box-shadow: 0 2px 8px rgba(30, 45, 74, 0.08);
            }

            .register-type-toggle__option:hover:not(.is-active) {
                color: var(--clay-red);
            }

            .auth-link-button {
                background: none;
                border: none;
                cursor: pointer;
                padding: 0;
                font: inherit;
            }

            .auth-link-button[disabled] {
                opacity: 0.6;
                cursor: not-allowed;
            }
            
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

            .verify-banner {
                margin-bottom: 20px;
                padding: 12px 16px;
                border-radius: 16px;
                font-size: 13px;
                font-weight: 600;
                text-align: center;
            }

            .verify-banner--success {
                background: rgba(46, 125, 50, 0.1);
                color: #2e7d32;
            }

            .verify-resend-form {
                margin-top: 8px;
            }

            .btn-primary--outline {
                background: #FFFFFF;
                color: var(--clay-red);
                border: 2px solid var(--clay-red);
                box-shadow: none;
            }

            .btn-primary--outline:active {
                transform: translateY(1px);
            }

            .login-status {
                margin-bottom: 20px;
                padding: 12px 16px;
                border-radius: 16px;
                font-size: 13px;
                font-weight: 600;
                text-align: center;
            }

            .login-status--success {
                background: rgba(46, 125, 50, 0.1);
                color: #2e7d32;
            }

            .login-status--info {
                background: rgba(30, 45, 74, 0.08);
                color: var(--ink);
            }

            .code-input {
                letter-spacing: 0.35em;
                text-align: center;
                font-size: 20px;
                font-weight: 800;
            }

            .guest-toast {
                position: fixed;
                top: 20px;
                left: 50%;
                transform: translateX(-50%);
                z-index: 100;
                max-width: min(420px, calc(100vw - 32px));
                padding: 12px 18px;
                border-radius: 9999px;
                font-size: 13px;
                font-weight: 700;
                text-align: center;
                box-shadow: var(--shadow-xl);
            }

            .guest-toast--warning { background: #F59E0B; color: #1A1208; }
            .guest-toast--success { background: #10B981; color: #fff; }
            .guest-toast--info { background: #FFFFFF; color: var(--ink); }

            .guest-modal-overlay {
                position: fixed;
                inset: 0;
                z-index: 90;
                background: rgba(30, 45, 74, 0.55);
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 24px;
            }

            .guest-modal {
                width: 100%;
                max-width: 400px;
                background: #FFFFFF;
                border-radius: 24px;
                padding: 28px;
                color: var(--ink);
                box-shadow: var(--shadow-xl);
            }

            .guest-modal-title {
                margin: 0 0 12px;
                font-family: var(--font-child);
                font-size: 18px;
                font-weight: 800;
                color: var(--clay-red);
            }

            .guest-modal-text {
                margin: 0 0 20px;
                font-size: 14px;
                line-height: 1.55;
                color: var(--stone);
                font-weight: 600;
            }

            .guest-modal-actions {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }
        </style>
    </head>
    <body>
        <div class="guest-shell">
            <div class="orb orb-primary"></div>
            <div class="orb orb-secondary"></div>

            <div @class(['guest-card', 'guest-card--register' => request()->routeIs('register')])>
                <div class="guest-logo guest-logo--image">
                    <x-brand-logo variant="full" />
                    <p class="guest-logo__sub">
                        @if (request()->routeIs('password.reset'))
                            {{ __('Set your password') }}
                        @else
                            {{ $title ?? __('Welcome back') }}
                        @endif
                    </p>
                </div>

                {{ $slot }}
            </div>
        </div>
    </body>
</html>
