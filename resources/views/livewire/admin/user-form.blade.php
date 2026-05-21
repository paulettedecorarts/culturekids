<div class="sa-user-form-view user-form-page">
    @if (session()->has('message'))
        <div class="user-form-toast user-form-toast--success" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
            <span>✓</span>
            <span>{{ session('message') }}</span>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="user-form-toast user-form-toast--error" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)">
            <span>✕</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <header class="user-form-header">
        <div class="user-form-header-main">
            <a href="{{ route('admin.users') }}" class="user-form-back" aria-label="Back to users">←</a>
            <div class="user-form-header-text">
                <h1 class="sa-page-title">{{ $editing ? 'Configure Profile' : 'Add Account' }}</h1>
                <div class="sa-breadcrumb">User ID: {{ $editing ? 'U-00'.$user->id : 'New Registration' }} · RBAC Authorization</div>
            </div>
        </div>
        <div class="user-form-header-actions">
            <x-livewire-submit-button
                type="button"
                wire:click="save"
                target="save"
                :loading="$editing ? __('Updating…') : __('Adding…')"
            >
                {{ $editing ? 'Update Account' : 'Add Account' }}
            </x-livewire-submit-button>
        </div>
    </header>

    <div class="user-form-stack">
        <section class="user-form-card">
            <h2 class="user-form-card-title">Account Configuration</h2>

            <div class="user-form-grid">
                <div class="user-form-field user-form-field--wide">
                    <label class="user-form-label">Official Full Name</label>
                    <input wire:model="name" type="text" class="user-form-input">
                    @error('name') <div class="user-form-error">{{ $message }}</div> @enderror
                </div>

                <div class="user-form-field user-form-field--aside">
                    <label class="user-form-label">Host Organization</label>
                    <select wire:model.number="organisation_id" class="user-form-input user-form-select">
                        <option value="">Global / Platform Level</option>
                        @foreach($organisations as $org)
                            <option value="{{ $org->id }}">{{ $org->name }}</option>
                        @endforeach
                    </select>
                    <p class="user-form-hint">Assigning an organization limits the agent's authority to the school's data enclave unless the "Super Admin" role is active.</p>
                </div>

                <div class="user-form-field user-form-field--wide">
                    <label class="user-form-label">System ID (Email Address)</label>
                    <input wire:model="email" type="email" class="user-form-input">
                    @error('email') <div class="user-form-error">{{ $message }}</div> @enderror
                    @if(!$editing)
                        <p class="user-form-hint">User will receive an email to set their own password.</p>
                    @endif
                </div>
            </div>

            @if($editing)
                <p class="user-form-meta">
                    👤 Account established on <b>{{ $user->created_at->format('M d, Y @ H:i') }}</b>.
                    Last profile synchronization detected <b>{{ $user->updated_at->diffForHumans() }}</b>.
                </p>
            @endif
        </section>

        <section class="user-form-card">
            <h3 class="user-form-card-title user-form-card-title--sm">Select Role</h3>
            <div class="user-form-roles">
                @foreach($roles as $role)
                    <label class="user-form-role">
                        <input type="radio" wire:model="selectedRole" value="{{ $role->name }}" class="user-form-role-input">
                        <span class="user-form-role-body">
                            <span class="user-form-role-name">{{ Str::title(str_replace('_', ' ', $role->name)) }}</span>
                            <span class="user-form-role-meta">Platform Level {{ $role->id }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
            @error('selectedRole') <div class="user-form-error user-form-error--block">{{ $message }}</div> @enderror
        </section>
    </div>

    <style>
        .user-form-page { min-width: 0; max-width: 100%; }
        .user-form-toast {
            position: fixed;
            top: 16px;
            right: 16px;
            left: 16px;
            z-index: 9999;
            padding: 14px 18px;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
            font-weight: 700;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        @media (min-width: 480px) {
            .user-form-toast { left: auto; max-width: 420px; }
        }
        .user-form-toast--success { background: #10B981; color: #fff; }
        .user-form-toast--error { background: #EF4444; color: #fff; }

        .user-form-header {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            justify-content: space-between;
            gap: var(--sp-4);
            margin-bottom: var(--sp-6);
        }
        .user-form-header-main {
            display: flex;
            align-items: flex-start;
            gap: var(--sp-4);
            min-width: 0;
            flex: 1 1 240px;
        }
        .user-form-back {
            flex-shrink: 0;
            background: var(--cms-surface-raised);
            color: var(--cms-text);
            width: 44px;
            height: 44px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            border: 1px solid var(--cms-border);
        }
        .user-form-header-text { min-width: 0; }
        .user-form-header-text .sa-page-title { margin: 0 0 6px; word-break: break-word; }
        .user-form-header-actions {
            display: flex;
            gap: 12px;
            width: 100%;
        }
        @media (min-width: 640px) {
            .user-form-header-actions { width: auto; }
        }

        .user-form-stack { display: flex; flex-direction: column; gap: var(--sp-6); }
        .user-form-card {
            background: var(--cms-surface-raised);
            border: 1px solid var(--cms-border);
            border-radius: var(--r-lg);
            padding: var(--sp-5);
        }
        @media (min-width: 768px) {
            .user-form-card { padding: var(--sp-8); border-radius: 32px; }
        }
        .user-form-card-title {
            font-family: var(--font-display);
            font-size: clamp(18px, 4vw, 24px);
            color: var(--cms-text);
            margin: 0 0 var(--sp-6);
        }
        .user-form-card-title--sm { font-size: clamp(16px, 3.5vw, 20px); margin-bottom: var(--sp-5); }

        .user-form-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: var(--sp-5);
        }
        @media (min-width: 900px) {
            .user-form-grid {
                grid-template-columns: 1fr 1fr minmax(260px, 400px);
                gap: var(--sp-6);
            }
            .user-form-field--wide { grid-column: span 2; }
            .user-form-field--aside { grid-row: span 2; }
        }
        .user-form-field { min-width: 0; }
        .user-form-label {
            display: block;
            font-size: 11px;
            font-weight: 800;
            color: var(--stone);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 12px;
        }
        .user-form-input {
            width: 100%;
            box-sizing: border-box;
            background: var(--cms-input-bg);
            border: 1px solid var(--cms-border);
            border-radius: 16px;
            padding: 14px 16px;
            color: var(--cms-text);
            font-family: var(--font-admin);
            font-size: 14px;
        }
        @media (min-width: 768px) {
            .user-form-input { padding: 18px; }
        }
        .user-form-select { cursor: pointer; }
        .user-form-hint {
            margin: 12px 0 0;
            font-size: 11px;
            color: var(--cms-text-muted);
            font-weight: 700;
            line-height: 1.45;
        }
        .user-form-error {
            color: var(--clay-red);
            font-size: 11px;
            font-weight: 700;
            margin-top: 8px;
        }
        .user-form-error--block { margin-top: 16px; }
        .user-form-meta {
            margin: var(--sp-6) 0 0;
            padding-top: var(--sp-6);
            border-top: 1px solid var(--cms-border);
            color: var(--cms-text-muted);
            font-size: 12px;
            line-height: 1.5;
        }

        .user-form-roles {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
        }
        @media (min-width: 480px) {
            .user-form-roles { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (min-width: 900px) {
            .user-form-roles { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        }
        .user-form-role {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 16px;
            background: var(--cms-surface);
            border: 1px solid var(--cms-border);
            border-radius: 20px;
            cursor: pointer;
            transition: border-color 0.2s;
            min-width: 0;
        }
        .user-form-role:has(.user-form-role-input:checked) {
            border-color: var(--savanna-gold);
            background: rgba(212, 160, 23, 0.06);
        }
        .user-form-role-input {
            flex-shrink: 0;
            width: 20px;
            height: 20px;
            margin-top: 2px;
            accent-color: var(--clay-red);
        }
        .user-form-role-body { min-width: 0; }
        .user-form-role-name {
            display: block;
            font-size: 14px;
            font-weight: 800;
            color: var(--cms-text);
            word-break: break-word;
        }
        .user-form-role-meta {
            display: block;
            font-size: 10px;
            color: var(--cms-text-muted);
            font-weight: 700;
            text-transform: uppercase;
            margin-top: 2px;
        }

        @media (max-width: 639px) {
            .user-form-header-actions .lw-submit-btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</div>
