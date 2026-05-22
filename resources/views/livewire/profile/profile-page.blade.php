@php
    use App\Support\PortalHome;

    $dashboardRoute = PortalHome::dashboardRouteName(auth()->user());
    $inPortal = PortalHome::layoutFor(auth()->user()) !== 'layouts.app';
@endphp

<div class="profile-page {{ $inPortal ? 'profile-page--portal' : 'profile-page--app' }}">
    <header class="profile-page__header">
        <div class="profile-page__intro">
            <p class="profile-page__eyebrow">{{ __('Account') }}</p>
            <h1 class="profile-page__title">{{ __('Settings') }}</h1>
            <p class="profile-page__lead">
                {{ __('Manage your name, email, password, and account security.') }}
            </p>
        </div>
        @if ($inPortal)
            <a href="{{ route($dashboardRoute) }}" class="profile-page__back btn btn-ghost btn-sm" wire:navigate>
                <span aria-hidden="true">←</span>
                {{ __('Back to dashboard') }}
            </a>
        @endif
    </header>

    <div class="profile-page__user">
        <div class="profile-page__avatar" aria-hidden="true">{{ $user->initials() }}</div>
        <div class="profile-page__user-meta">
            <strong>{{ $user->name }}</strong>
            <span>{{ $user->email }}</span>
            @if ($user->roles->isNotEmpty())
                <span class="profile-page__role">{{ $user->roles->first()->name }}</span>
            @endif
        </div>
    </div>

    <div class="profile-page__sections">
        <section class="profile-section" aria-labelledby="profile-section-info">
            <div class="profile-section__head">
                <span class="profile-section__icon" aria-hidden="true">👤</span>
                <div>
                    <h2 id="profile-section-info" class="profile-section__title">{{ __('Profile') }}</h2>
                    <p class="profile-section__desc">{{ __('Your display name and sign-in email.') }}</p>
                </div>
            </div>
            <div class="profile-section__body">
                <livewire:profile.update-profile-information-form />
            </div>
        </section>

        <section class="profile-section" aria-labelledby="profile-section-password">
            <div class="profile-section__head">
                <span class="profile-section__icon" aria-hidden="true">🔐</span>
                <div>
                    <h2 id="profile-section-password" class="profile-section__title">{{ __('Password') }}</h2>
                    <p class="profile-section__desc">{{ __('Use a strong, unique password for this account.') }}</p>
                </div>
            </div>
            <div class="profile-section__body">
                <livewire:profile.update-password-form />
            </div>
        </section>

        <section class="profile-section profile-section--danger" aria-labelledby="profile-section-delete">
            <div class="profile-section__head">
                <span class="profile-section__icon profile-section__icon--danger" aria-hidden="true">⚠️</span>
                <div>
                    <h2 id="profile-section-delete" class="profile-section__title">{{ __('Danger zone') }}</h2>
                    <p class="profile-section__desc">{{ __('Permanently remove your account and all associated data.') }}</p>
                </div>
            </div>
            <div class="profile-section__body">
                <livewire:profile.delete-user-form />
            </div>
        </section>
    </div>

    <style>
        .profile-page {
            --profile-accent: var(--clay-red, #c44b2b);
            --profile-accent-hover: var(--clay-red-dark, #a33d24);
            --profile-surface: var(--cms-surface, #fff);
            --profile-surface-raised: var(--cms-surface-raised, #f8f6f2);
            --profile-border: var(--cms-border, #e8e2d8);
            --profile-text: var(--cms-text, #1a1208);
            --profile-muted: var(--cms-text-muted, #6b5d4d);
            --profile-input-bg: var(--cms-input-bg, #fff);
            --profile-input-border: var(--cms-input-border, #d4cbc0);
            --profile-shadow: var(--cms-shadow, 0 8px 24px rgba(26, 18, 8, 0.06));
            --profile-radius: var(--r-lg, 16px);
            --profile-font: var(--font-admin, 'Bricolage Grotesque', system-ui, sans-serif);
            --profile-display: var(--font-display, 'Baloo 2', system-ui, sans-serif);
            max-width: 820px;
            margin: 0 auto;
            padding-bottom: var(--sp-8, 32px);
        }

        .profile-page--app {
            --profile-surface: #fff;
            --profile-surface-raised: #f9fafb;
            --profile-border: #e5e7eb;
            --profile-text: #111827;
            --profile-muted: #6b7280;
            --profile-input-bg: #fff;
            --profile-input-border: #d1d5db;
            --profile-accent: #1f2937;
            --profile-accent-hover: #374151;
            padding: 2rem 1rem 3rem;
        }

        .profile-page__header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: var(--sp-4, 16px);
            flex-wrap: wrap;
            margin-bottom: var(--sp-6, 24px);
        }

        .profile-page__eyebrow {
            margin: 0 0 6px;
            font-family: var(--profile-font);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--profile-muted);
        }

        .profile-page__title {
            margin: 0 0 8px;
            font-family: var(--profile-display);
            font-size: clamp(1.75rem, 4vw, 2.25rem);
            font-weight: 800;
            line-height: 1.1;
            color: var(--profile-text);
            letter-spacing: -0.02em;
        }

        .profile-page__lead {
            margin: 0;
            max-width: 36rem;
            font-family: var(--profile-font);
            font-size: 14px;
            line-height: 1.55;
            color: var(--profile-muted);
        }

        .profile-page__back {
            flex-shrink: 0;
            text-decoration: none;
            white-space: nowrap;
        }

        .profile-page__user {
            display: flex;
            align-items: center;
            gap: var(--sp-4, 16px);
            padding: var(--sp-5, 20px) var(--sp-6, 24px);
            margin-bottom: var(--sp-6, 24px);
            background: linear-gradient(135deg, var(--profile-surface-raised) 0%, var(--profile-surface) 100%);
            border: 1px solid var(--profile-border);
            border-radius: var(--profile-radius);
            box-shadow: var(--profile-shadow);
        }

        .profile-page__avatar {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 56px;
            height: 56px;
            border-radius: 14px;
            background: var(--profile-accent);
            color: #fff;
            font-family: var(--profile-font);
            font-size: 18px;
            font-weight: 800;
            letter-spacing: 0.04em;
            flex-shrink: 0;
        }

        .profile-page__user-meta {
            display: flex;
            flex-direction: column;
            gap: 4px;
            min-width: 0;
        }

        .profile-page__user-meta strong {
            font-family: var(--profile-font);
            font-size: 17px;
            font-weight: 800;
            color: var(--profile-text);
        }

        .profile-page__user-meta > span:not(.profile-page__role) {
            font-size: 14px;
            color: var(--profile-muted);
            word-break: break-all;
        }

        .profile-page__role {
            display: inline-block;
            width: fit-content;
            margin-top: 4px;
            padding: 4px 10px;
            border-radius: 999px;
            background: var(--profile-surface-raised);
            border: 1px solid var(--profile-border);
            font-family: var(--profile-font);
            font-size: 11px;
            font-weight: 800;
            text-transform: capitalize;
            color: var(--profile-muted);
        }

        .profile-page__sections {
            display: flex;
            flex-direction: column;
            gap: var(--sp-5, 20px);
        }

        .profile-section {
            background: var(--profile-surface);
            border: 1px solid var(--profile-border);
            border-radius: var(--profile-radius);
            box-shadow: var(--profile-shadow);
            overflow: hidden;
        }

        .profile-section--danger {
            border-color: rgba(185, 28, 28, 0.25);
            background: linear-gradient(180deg, var(--profile-surface) 0%, rgba(254, 242, 242, 0.35) 100%);
        }

        [data-cms-theme="dark"] .profile-section--danger,
        [data-sa-theme="dark"] .profile-section--danger,
        [data-th-theme="dark"] .profile-section--danger {
            background: linear-gradient(180deg, var(--profile-surface) 0%, rgba(127, 29, 29, 0.12) 100%);
            border-color: rgba(248, 113, 113, 0.25);
        }

        .profile-section__head {
            display: flex;
            align-items: flex-start;
            gap: var(--sp-4, 16px);
            padding: var(--sp-5, 20px) var(--sp-6, 24px);
            border-bottom: 1px solid var(--profile-border);
            background: var(--profile-surface-raised);
        }

        .profile-section__icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: var(--profile-surface);
            border: 1px solid var(--profile-border);
            font-size: 20px;
            flex-shrink: 0;
        }

        .profile-section__icon--danger {
            background: rgba(254, 226, 226, 0.8);
            border-color: rgba(248, 113, 113, 0.35);
        }

        .profile-section__title {
            margin: 0 0 4px;
            font-family: var(--profile-font);
            font-size: 1.125rem;
            font-weight: 800;
            color: var(--profile-text);
        }

        .profile-section__desc {
            margin: 0;
            font-family: var(--profile-font);
            font-size: 13px;
            line-height: 1.45;
            color: var(--profile-muted);
        }

        .profile-section__body {
            padding: var(--sp-6, 24px);
        }

        /* Nested Livewire profile forms (Breeze markup) */
        .profile-page .profile-section__body > section {
            margin: 0;
        }

        .profile-page .profile-section__body > section > header {
            display: none;
        }

        .profile-page .profile-section__body form {
            margin-top: 0;
        }

        .profile-page .profile-section__body .space-y-6 > * + * {
            margin-top: var(--sp-5, 20px);
        }

        .profile-page label,
        .profile-page .profile-section__body label {
            display: block;
            margin-bottom: 6px;
            font-family: var(--profile-font);
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: var(--profile-muted) !important;
        }

        .profile-page input[type="text"],
        .profile-page input[type="email"],
        .profile-page input[type="password"] {
            display: block;
            width: 100%;
            max-width: 100%;
            padding: 12px 14px;
            font-family: var(--profile-font);
            font-size: 15px;
            color: var(--profile-text);
            background: var(--profile-input-bg);
            border: 1px solid var(--profile-input-border);
            border-radius: 10px;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
        }

        .profile-page input:focus {
            outline: none;
            border-color: var(--profile-accent);
            box-shadow: 0 0 0 3px rgba(196, 75, 43, 0.15);
        }

        .profile-page--app input:focus {
            box-shadow: 0 0 0 3px rgba(31, 41, 55, 0.12);
        }

        .profile-page .text-gray-900,
        .profile-page .text-gray-800,
        .profile-page .text-gray-600 {
            color: var(--profile-muted) !important;
        }

        .profile-page .text-sm {
            font-family: var(--profile-font);
            font-size: 13px;
            line-height: 1.5;
        }

        .profile-page .flex.items-center.gap-4 {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: var(--sp-3, 12px);
            margin-top: var(--sp-5, 20px);
            padding-top: var(--sp-4, 16px);
            border-top: 1px solid var(--profile-border);
        }

        .profile-page button[type="submit"],
        .profile-page .profile-section__body button.inline-flex.bg-gray-800 {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 11px 22px !important;
            font-family: var(--profile-font) !important;
            font-size: 13px !important;
            font-weight: 800 !important;
            letter-spacing: 0.02em !important;
            text-transform: none !important;
            color: #fff !important;
            background: var(--profile-accent) !important;
            border: none !important;
            border-radius: 999px !important;
            cursor: pointer;
            transition: background 0.15s ease, transform 0.1s ease;
        }

        .profile-page button[type="submit"]:hover,
        .profile-page .profile-section__body button.inline-flex.bg-gray-800:hover {
            background: var(--profile-accent-hover) !important;
        }

        .profile-page button[type="submit"]:active {
            transform: scale(0.98);
        }

        .profile-page .text-green-600 {
            color: #15803d !important;
            font-weight: 700;
        }

        [data-cms-theme="dark"] .profile-page .text-green-600,
        [data-sa-theme="dark"] .profile-page .text-green-600,
        [data-th-theme="dark"] .profile-page .text-green-600 {
            color: #86efac !important;
        }

        .profile-page button.underline {
            color: var(--profile-accent) !important;
            font-weight: 700;
            text-decoration: underline;
            background: none;
            border: none;
            padding: 0;
            cursor: pointer;
        }

        .profile-page .profile-section--danger button.inline-flex.items-center.px-4.py-2.bg-red-600,
        .profile-page .profile-section--danger .bg-red-600 {
            background: #dc2626 !important;
            border-radius: 999px !important;
            padding: 11px 22px !important;
            font-family: var(--profile-font) !important;
            font-size: 13px !important;
            font-weight: 800 !important;
            text-transform: none !important;
            letter-spacing: 0 !important;
        }

        .profile-page .profile-section--danger button.inline-flex.items-center.px-4.py-2.bg-red-600:hover {
            background: #b91c1c !important;
        }

        .profile-page .profile-section--danger button.inline-flex.items-center.px-4.py-2.bg-white,
        .profile-page .profile-section--danger button.border-gray-300 {
            background: var(--profile-surface) !important;
            color: var(--profile-text) !important;
            border: 1px solid var(--profile-border) !important;
            border-radius: 999px !important;
            padding: 11px 22px !important;
            font-weight: 700 !important;
            text-transform: none !important;
        }

        /* Modal (delete confirmation) */
        .profile-page [x-show] ~ div,
        .profile-page .fixed.inset-0 {
            font-family: var(--profile-font);
        }

        .profile-page .bg-white.dark\:bg-gray-800 {
            background: var(--profile-surface) !important;
            border: 1px solid var(--profile-border);
            border-radius: var(--profile-radius);
        }

        .profile-page .bg-gray-500 {
            background: rgba(26, 18, 8, 0.45) !important;
        }

        @media (max-width: 640px) {
            .profile-page__header {
                flex-direction: column;
            }

            .profile-page__back {
                width: 100%;
                justify-content: center;
            }

            .profile-section__head {
                padding: var(--sp-4, 16px);
            }

            .profile-section__body {
                padding: var(--sp-4, 16px);
            }
        }
    </style>
</div>
