@php
    use App\Support\PortalHome;

    $dashboardRoute = PortalHome::dashboardRouteName(auth()->user());
    $inPortal = PortalHome::layoutFor(auth()->user()) !== 'layouts.app';
@endphp

<div class="{{ $inPortal ? 'sa-profile-page' : '' }}">
    @if ($inPortal)
        <div class="sa-page-header" style="display:flex;align-items:flex-start;justify-content:space-between;gap:var(--sp-4);margin-bottom:var(--sp-6);flex-wrap:wrap">
            <div>
                <h1 class="sa-page-title" style="margin:0 0 6px">Account settings</h1>
                <div class="sa-breadcrumb">Profile · Password · Security</div>
            </div>
            <a href="{{ route($dashboardRoute) }}" class="btn btn-ghost" style="text-decoration:none">← Back to dashboard</a>
        </div>
    @endif

    <div class="{{ $inPortal ? 'sa-profile-stack' : 'py-12 max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6' }}">
        <div class="{{ $inPortal ? 'sa-profile-card' : 'p-4 sm:p-8 bg-white shadow sm:rounded-lg' }}">
            <div class="{{ $inPortal ? '' : 'max-w-xl' }}">
                <livewire:profile.update-profile-information-form />
            </div>
        </div>

        <div class="{{ $inPortal ? 'sa-profile-card' : 'p-4 sm:p-8 bg-white shadow sm:rounded-lg' }}">
            <div class="{{ $inPortal ? '' : 'max-w-xl' }}">
                <livewire:profile.update-password-form />
            </div>
        </div>

        <div class="{{ $inPortal ? 'sa-profile-card' : 'p-4 sm:p-8 bg-white shadow sm:rounded-lg' }}">
            <div class="{{ $inPortal ? '' : 'max-w-xl' }}">
                <livewire:profile.delete-user-form />
            </div>
        </div>
    </div>
</div>

@if ($inPortal)
    <style>
        .sa-profile-page { max-width: 720px; }
        .sa-profile-stack { display: flex; flex-direction: column; gap: var(--sp-5); }
        .sa-profile-card {
            background: var(--cms-surface);
            border: 1px solid var(--cms-border);
            border-radius: var(--r-lg);
            padding: var(--sp-6);
        }
        .sa-profile-card .text-gray-600,
        .sa-profile-card .text-gray-900 { color: var(--cms-text) !important; }
        .sa-profile-card label { color: var(--cms-text-muted) !important; }
        .sa-profile-card input[type="text"],
        .sa-profile-card input[type="email"],
        .sa-profile-card input[type="password"] {
            width: 100%;
            background: var(--cms-input-bg);
            border: 1px solid var(--cms-input-border);
            border-radius: 10px;
            padding: 10px 14px;
            color: var(--cms-text);
        }
        .sa-profile-card .bg-white { background: var(--cms-surface) !important; }
        .sa-profile-card .bg-gray-100 { background: var(--cms-surface-raised) !important; }
        .sa-profile-card .border-gray-200 { border-color: var(--cms-border) !important; }
    </style>
@endif
