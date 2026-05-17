<div class="sa-user-management-view">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--sp-8)">
        <div>
            <h1 class="sa-page-title">User Management</h1>
            <div class="sa-breadcrumb">RBAC Control · Platform Access Directory</div>
        </div>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary" style="padding:12px 28px; border-radius:14px; font-weight:800; font-size:13px; box-shadow: 0 8px 24px rgba(196,75,43,0.3); text-decoration:none">+ Register New Account</a>
    </div>

    @if (session()->has('message'))
        <div style="background:rgba(74,124,89,0.1); border:1px solid rgba(74,124,89,0.3); color:var(--banana-light); padding:16px 24px; border-radius:16px; margin-bottom:32px; font-size:13px; font-weight:700">
            ✨ {{ session('message') }}
        </div>
    @endif

    <!-- Filters Bar -->
    <div style="background:var(--cms-surface); border:1px solid var(--cms-border); border-radius:32px; padding:32px; margin-bottom:40px">
        <div style="display:flex; gap:16px">
            <div style="flex:1; position:relative">
                <span style="position:absolute; left:20px; top:50%; transform:translateY(-50%); opacity:0.3">🔍</span>
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search" 
                    placeholder="Search by user name or official email..." 
                    style="width:100%; background:var(--cms-surface); border:1px solid var(--cms-border); border-radius:16px; padding:16px 16px 16px 52px; color:var(--cms-text); font-family:var(--font-admin); font-size:13px; outline:none;"
                >
            </div>
            
            <select wire:model.live="roleFilter" style="background:var(--cms-surface); border:1px solid var(--cms-border); border-radius:16px; padding:0 24px; color:var(--cms-text); font-family:var(--font-admin); font-size:13px; outline:none; cursor:pointer">
                <option value="">All Security Roles</option>
                @foreach($roles as $role)
                    <option value="{{ $role->name }}">{{ Str::title(str_replace('_', ' ', $role->name)) }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Users Table -->
    <div style="background:var(--cms-surface); border:1px solid var(--cms-border); border-radius:32px; overflow:hidden">
        <div style="display:grid; grid-template-columns:2fr 1fr 1fr 1fr 100px; padding:24px 32px; background:var(--cms-surface); border-bottom:1px solid var(--cms-border-subtle); font-size:11px; font-weight:800; color:var(--cms-text-muted); text-transform:uppercase; letter-spacing:1px">
            <span>Identity</span>
            <span>Security Role</span>
            <span>Organization</span>
            <span>Registered</span>
            <span style="text-align:right">Management</span>
        </div>

        @forelse($users as $user)
            <div style="display:grid; grid-template-columns:2fr 1fr 1fr 1fr 100px; padding:24px 32px; align-items:center; border-bottom:1px solid var(--cms-border-subtle); transition:all 0.2s" class="cms-hover-row">
                <a href="{{ route('admin.users.detail', $user->id) }}" style="display:flex; align-items:center; gap:16px; text-decoration:none">
                    <div style="width:40px; height:40px; border-radius:12px; background:{{ $user->hasRole('super_admin') ? 'var(--clay-red)' : 'rgba(255,255,255,0.05)' }}; border: 1px solid var(--cms-border); display:flex; align-items:center; justify-content:center; color:var(--cms-text); font-weight:800; font-family:var(--font-display)">
                        {{ substr($user->name, 0, 1) }}
                    </div>
                    <div>
                        <div style="font-weight:800; color:var(--cms-text); font-size:14px; margin-bottom:2px">{{ $user->name }}</div>
                        <div style="font-size:12px; color:var(--cms-text-muted)">{{ $user->email }}</div>
                    </div>
                </a>

                <div>
                    @foreach($user->roles as $role)
                        <span class="status-pill status-published" style="background: var(--cms-surface-raised); color:var(--cms-text); border: 1px solid var(--cms-border); font-size:10px; padding:4px 10px">
                            {{ Str::title(str_replace('_', ' ', $role->name)) }}
                        </span>
                    @endforeach
                </div>

                <div style="font-size:13px; color:var(--cms-text-muted); font-weight:700">
                    {{ $user->organisation ? $user->organisation->name : 'Global / Platform' }}
                </div>

                <div style="font-size:13px; color:var(--cms-text-muted); font-weight:700">
                    {{ $user->created_at->format('M d, Y') }}
                </div>

                <div style="display:flex; gap:8px; justify-content:flex-end">
                    <button 
                        wire:click="resendSetupEmail({{ $user->id }})" 
                        wire:loading.attr="disabled"
                        title="Resend Setup Email"
                        class="btn" 
                        style="width:36px; height:36px; background:var(--cms-surface-raised); color:var(--cms-text); border: 1px solid var(--cms-border); border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:14px; cursor:pointer">
                        <span wire:loading.remove wire:target="resendSetupEmail({{ $user->id }})">📧</span>
                        <span wire:loading wire:target="resendSetupEmail({{ $user->id }})" style="font-size:10px">⏳</span>
                    </button>
                    <a href="{{ route('admin.users.edit', $user->id) }}" class="btn" style="width:36px; height:36px; background:var(--cms-surface-raised); color:var(--cms-text); border: 1px solid var(--cms-border); border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:14px; text-decoration:none">⚙️</a>
                    @if($user->id !== auth()->id())
                        <button wire:click="delete({{ $user->id }})" onclick="return confirm('Archive this account permanently?') || event.stopImmediatePropagation()" class="btn" style="width:36px; height:36px; background:rgba(196,75,43,0.1); color:var(--clay-red); border:1px solid rgba(196,75,43,0.2); border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:14px">🗑</button>
                    @endif
                </div>
            </div>
        @empty
            <div style="padding:100px; text-align:center; opacity:0.3">
                <div style="font-size:64px; margin-bottom:24px">👤</div>
                <div style="font-size:15px; font-weight:700">No matching user records found</div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div style="margin-top:40px">
        {{ $users->links(data: ['scrollTo' => false]) }}
    </div>
</div>

