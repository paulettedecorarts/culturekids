<div>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--sp-5);flex-wrap:wrap;gap:var(--sp-3)">
        <div>
            <div class="sa-page-title">Impersonate User</div>
            <div class="sa-breadcrumb">Super Admin · User Debugging</div>
        </div>
        <div style="display:flex;gap:var(--sp-2);align-items:center">
            <span class="sa-badge">🎭 IMPERSONATION</span>
        </div>
    </div>

    <!-- Warning Banner -->
    <div style="background:rgba(232,135,42,.15);border:1px solid rgba(232,135,42,.3);color:var(--sunfire-light);padding:var(--sp-4);border-radius:var(--r-md);margin-bottom:var(--sp-5);display:flex;align-items:start;gap:var(--sp-3)">
        <span style="font-size:24px">⚠️</span>
        <div>
            <div style="font-weight:700;font-size:14px;margin-bottom:4px">Impersonation Mode</div>
            <div style="font-size:13px;color:var(--cms-text-muted)">
                You can temporarily log in as any user to debug issues. All actions during impersonation are logged with your admin ID. 
                You cannot impersonate other super administrators.
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div style="display:grid;grid-template-columns:2fr 1fr;gap:var(--sp-3);margin-bottom:var(--sp-4)">
        <input 
            type="text" 
            wire:model.live.debounce.300ms="search" 
            placeholder="Search by name or email..."
            style="background:var(--cms-input-bg);border:1px solid var(--cms-border);border-radius:var(--r-sm);padding:var(--sp-2) var(--sp-3);color:var(--cms-text);font-size:14px;font-family:var(--font-admin)"
        />
        
        <select 
            wire:model.live="roleFilter"
            style="background:var(--cms-input-bg);border:1px solid var(--cms-border);border-radius:var(--r-sm);padding:var(--sp-2) var(--sp-3);color:var(--cms-text);font-size:14px;font-family:var(--font-admin)"
        >
            <option value="">All Roles</option>
            @foreach($roles as $role)
                <option value="{{ $role->name }}">{{ ucwords(str_replace('_', ' ', $role->name)) }}</option>
            @endforeach
        </select>
    </div>

    @if(session('message'))
        <div style="background:rgba(111,168,130,.2);border:1px solid rgba(111,168,130,.4);color:var(--banana-mid);padding:var(--sp-3) var(--sp-4);border-radius:var(--r-sm);margin-bottom:var(--sp-4);font-size:14px">
            {{ session('message') }}
        </div>
    @endif

    @if(session('error'))
        <div style="background:rgba(196,75,43,.2);border:1px solid rgba(196,75,43,.4);color:var(--clay-red-light);padding:var(--sp-3) var(--sp-4);border-radius:var(--r-sm);margin-bottom:var(--sp-4);font-size:14px">
            {{ session('error') }}
        </div>
    @endif

    <!-- Users Table -->
    <div class="sa-table-wrap">
        <div class="sa-table-head" style="grid-template-columns:2fr 1fr 1fr 1fr 120px">
            <span>User</span>
            <span>Role</span>
            <span>Organization</span>
            <span>Last Active</span>
            <span>Actions</span>
        </div>
        
        @forelse($users as $user)
            <div class="sa-table-row" style="grid-template-columns:2fr 1fr 1fr 1fr 120px">
                <div>
                    <div style="font-weight:600;color:var(--cms-text);font-size:12px">{{ $user->email }}</div>
                    <div style="font-size:10px;color:var(--cms-text-muted)">{{ $user->name }}</div>
                </div>
                
                @php
                    $role = $user->roles->first();
                    $roleClass = match($role?->name) {
                        'super_admin' => 'role-super',
                        'org_admin' => 'role-admin',
                        'cms_editor' => 'role-editor',
                        'teacher' => 'role-teacher',
                        default => 'role-parent'
                    };
                @endphp
                <span class="role-chip {{ $roleClass }}">
                    {{ $role ? ucwords(str_replace('_', ' ', $role->name)) : 'No Role' }}
                </span>
                
                <span style="font-size:11px;color:var(--cms-text-muted)">
                    {{ $user->organisation?->name ?? 'Global' }}
                </span>
                
                <span style="font-size:11px;color:var(--cms-text-muted)">
                    {{ $user->updated_at->diffForHumans() }}
                </span>
                
                @if($user->hasRole('super_admin'))
                    <button 
                        class="btn btn-sm" 
                        style="background:var(--cms-surface-raised);color:var(--cms-text-muted);padding:3px 7px;font-size:9px;cursor:not-allowed"
                        disabled
                    >
                        Cannot Impersonate
                    </button>
                @else
                    <button 
                        wire:click="impersonate({{ $user->id }})"
                        wire:confirm="Are you sure you want to impersonate {{ $user->name }}? All actions will be logged."
                        class="btn btn-sm" 
                        style="background:rgba(232,135,42,.2);color:var(--sunfire-light);padding:3px 7px;font-size:9px;border:1px solid rgba(232,135,42,.3)"
                    >
                        🎭 Impersonate
                    </button>
                @endif
            </div>
        @empty
            <div class="sa-table-row" style="grid-template-columns:1fr">
                <div style="text-align:center;color:var(--cms-text-muted);padding:var(--sp-4)">
                    No users found.
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div style="margin-top:var(--sp-4)">
        {{ $users->links() }}
    </div>
</div>
