<div class="sa-user-management-view users-mgmt-page">
    <header class="users-mgmt-header">
        <div class="users-mgmt-header-text">
            <h1 class="sa-page-title">User Management</h1>
            <div class="sa-breadcrumb">RBAC Control · Platform Access Directory</div>
        </div>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary users-mgmt-create-btn">+ Register New Account</a>
    </header>

    @if (session()->has('message'))
        <div class="users-mgmt-flash">{{ session('message') }}</div>
    @endif

    <div class="users-mgmt-filters">
        <div class="users-mgmt-search-wrap">
            <span class="users-mgmt-search-icon" aria-hidden="true">🔍</span>
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                class="users-mgmt-search"
                placeholder="Search by user name or official email..."
            >
        </div>
        <select wire:model.live="roleFilter" class="users-mgmt-role-select">
            <option value="">All Security Roles</option>
            @foreach($roles as $role)
                <option value="{{ $role->name }}">{{ Str::title(str_replace('_', ' ', $role->name)) }}</option>
            @endforeach
        </select>
    </div>

    <div class="sa-table-wrap users-mgmt-table">
        <div class="sa-table-head users-mgmt-head" style="grid-template-columns:2fr 1fr 1fr 1fr minmax(160px, auto)">
            <span>Identity</span>
            <span>Security Role</span>
            <span>Organization</span>
            <span>Registered</span>
            <span>Management</span>
        </div>

        @forelse($users as $user)
            <div class="sa-table-row users-mgmt-row" style="grid-template-columns:2fr 1fr 1fr 1fr minmax(160px, auto)">
                <div class="users-mgmt-cell users-mgmt-cell--identity" data-label="Identity">
                    <a href="{{ route('admin.users.detail', $user->id) }}" class="users-mgmt-identity-link">
                        <div class="users-mgmt-avatar" style="background:{{ $user->hasRole('super_admin') ? 'var(--clay-red)' : 'rgba(255,255,255,0.05)' }}">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                        <div class="users-mgmt-identity-text">
                            <div class="users-mgmt-name">{{ $user->name }}</div>
                            <div class="users-mgmt-email">{{ $user->email }}</div>
                        </div>
                    </a>
                </div>

                <div class="users-mgmt-cell" data-label="Security Role">
                    @foreach($user->roles as $role)
                        <span class="status-pill status-published users-mgmt-role-pill">
                            {{ Str::title(str_replace('_', ' ', $role->name)) }}
                        </span>
                    @endforeach
                </div>

                <div class="users-mgmt-cell users-mgmt-cell--muted" data-label="Organization">
                    {{ $user->organisation ? $user->organisation->name : 'Global / Platform' }}
                </div>

                <div class="users-mgmt-cell users-mgmt-cell--muted" data-label="Registered">
                    {{ $user->created_at->format('M d, Y') }}
                </div>

                <div class="sa-table-actions users-mgmt-actions" data-label="Management">
                    <button
                        type="button"
                        wire:click="resendSetupEmail({{ $user->id }})"
                        wire:loading.attr="disabled"
                        title="Resend setup email"
                        class="sa-icon-action"
                    >
                        <span wire:loading.remove wire:target="resendSetupEmail({{ $user->id }})">📧</span>
                        <span wire:loading wire:target="resendSetupEmail({{ $user->id }})">…</span>
                    </button>
                    <a href="{{ route('admin.users.edit', $user->id) }}" class="sa-icon-action" title="Edit user">⚙️</a>
                    @if($user->id !== auth()->id())
                        <button
                            type="button"
                            wire:click="delete({{ $user->id }})"
                            onclick="return confirm('Archive this account permanently?') || event.stopImmediatePropagation()"
                            class="sa-icon-action sa-icon-action--danger"
                            title="Delete user"
                        >🗑</button>
                    @endif
                </div>
            </div>
        @empty
            <div class="users-mgmt-empty">
                <div class="users-mgmt-empty-icon" aria-hidden="true">👤</div>
                <p>No matching user records found</p>
            </div>
        @endforelse
    </div>

    <div class="users-mgmt-pagination">
        {{ $users->links(data: ['scrollTo' => false]) }}
    </div>

    <style>
        .users-mgmt-page { min-width: 0; max-width: 100%; }

        .users-mgmt-header {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            justify-content: space-between;
            gap: var(--sp-4);
            margin-bottom: var(--sp-6);
        }
        .users-mgmt-header-text { min-width: 0; flex: 1 1 200px; }
        .users-mgmt-header-text .sa-page-title { margin: 0 0 6px; }
        .users-mgmt-create-btn {
            padding: 12px 20px;
            border-radius: 14px;
            font-weight: 800;
            font-size: 13px;
            text-decoration: none;
            width: 100%;
            text-align: center;
            justify-content: center;
            box-sizing: border-box;
        }
        @media (min-width: 640px) {
            .users-mgmt-create-btn { width: auto; padding: 12px 28px; }
        }

        .users-mgmt-flash {
            background: rgba(74, 124, 89, 0.1);
            border: 1px solid rgba(74, 124, 89, 0.3);
            color: var(--banana-light);
            padding: 14px 18px;
            border-radius: 16px;
            margin-bottom: var(--sp-5);
            font-size: 13px;
            font-weight: 700;
        }

        .users-mgmt-filters {
            display: flex;
            flex-direction: column;
            gap: 12px;
            background: var(--cms-surface);
            border: 1px solid var(--cms-border);
            border-radius: var(--r-lg);
            padding: var(--sp-4);
            margin-bottom: var(--sp-5);
        }
        @media (min-width: 768px) {
            .users-mgmt-filters {
                flex-direction: row;
                align-items: stretch;
                padding: var(--sp-5);
                border-radius: 32px;
                margin-bottom: var(--sp-6);
            }
        }
        .users-mgmt-search-wrap {
            flex: 1;
            position: relative;
            min-width: 0;
        }
        .users-mgmt-search-icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            opacity: 0.35;
            pointer-events: none;
        }
        .users-mgmt-search {
            width: 100%;
            box-sizing: border-box;
            background: var(--cms-input-bg);
            border: 1px solid var(--cms-border);
            border-radius: 16px;
            padding: 14px 14px 14px 44px;
            color: var(--cms-text);
            font-family: var(--font-admin);
            font-size: 14px;
            outline: none;
        }
        .users-mgmt-role-select {
            width: 100%;
            box-sizing: border-box;
            background: var(--cms-input-bg);
            border: 1px solid var(--cms-border);
            border-radius: 16px;
            padding: 14px 18px;
            color: var(--cms-text);
            font-family: var(--font-admin);
            font-size: 14px;
            outline: none;
            cursor: pointer;
        }
        @media (min-width: 768px) {
            .users-mgmt-role-select { width: auto; min-width: 200px; flex-shrink: 0; }
        }

        .users-mgmt-table { overflow: hidden; border-radius: var(--r-lg); }
        @media (min-width: 768px) {
            .users-mgmt-table { border-radius: 32px; }
        }

        .users-mgmt-identity-link {
            display: flex;
            align-items: center;
            gap: 14px;
            text-decoration: none;
            min-width: 0;
        }
        .users-mgmt-avatar {
            flex-shrink: 0;
            width: 40px;
            height: 40px;
            border-radius: 12px;
            border: 1px solid var(--cms-border);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--cms-text);
            font-weight: 800;
            font-family: var(--font-display);
        }
        .users-mgmt-identity-text { min-width: 0; }
        .users-mgmt-name {
            font-weight: 800;
            color: var(--cms-text);
            font-size: 14px;
            margin-bottom: 2px;
            word-break: break-word;
        }
        .users-mgmt-email {
            font-size: 12px;
            color: var(--cms-text-muted);
            word-break: break-all;
        }
        .users-mgmt-role-pill {
            background: var(--cms-surface-raised) !important;
            color: var(--cms-text) !important;
            border: 1px solid var(--cms-border) !important;
            font-size: 10px;
            padding: 4px 10px;
        }
        .users-mgmt-cell--muted {
            font-size: 13px;
            color: var(--cms-text-muted);
            font-weight: 700;
        }
        .users-mgmt-actions {
            justify-content: flex-start;
            flex-wrap: wrap;
            gap: 8px;
        }
        @media (min-width: 768px) {
            .users-mgmt-actions { justify-content: flex-end; }
        }

        .users-mgmt-empty {
            padding: 48px 24px;
            text-align: center;
            opacity: 0.45;
        }
        .users-mgmt-empty-icon { font-size: 48px; margin-bottom: 16px; }
        .users-mgmt-empty p { font-size: 15px; font-weight: 700; margin: 0; }

        .users-mgmt-pagination { margin-top: var(--sp-6); overflow-x: auto; }

        /* Mobile: card rows with labels */
        @media (max-width: 767px) {
            .users-mgmt-table .sa-table-head {
                display: none !important;
            }
            .users-mgmt-table .sa-table-row {
                display: flex !important;
                flex-direction: column !important;
                align-items: stretch !important;
                gap: 12px !important;
                padding: var(--sp-4) !important;
                grid-template-columns: unset !important;
            }
            .users-mgmt-cell:not(.users-mgmt-cell--identity)::before {
                content: attr(data-label);
                display: block;
                font-size: 10px;
                font-weight: 800;
                text-transform: uppercase;
                letter-spacing: 0.06em;
                color: var(--cms-text-muted);
                margin-bottom: 4px;
            }
            .users-mgmt-cell--identity::before {
                display: none;
            }
            .users-mgmt-actions {
                padding-top: var(--sp-2);
                border-top: 1px solid var(--cms-border-subtle);
                margin-top: var(--sp-1);
            }
            .users-mgmt-actions::before {
                content: attr(data-label);
                display: block;
                width: 100%;
                font-size: 10px;
                font-weight: 800;
                text-transform: uppercase;
                letter-spacing: 0.06em;
                color: var(--cms-text-muted);
                margin-bottom: 6px;
            }
        }
    </style>
</div>
