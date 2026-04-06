<div>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--sp-5)">
        <div>
            <div class="sa-page-title">User Management</div>
            <div class="sa-breadcrumb">All users across all organizations</div>
        </div>
        <button class="btn btn-primary btn-sm">+ New User</button>
    </div>

    <!-- Filters -->
    <div style="display:flex; gap:var(--sp-4); margin-bottom:var(--sp-6);">
        <input 
            type="text" 
            wire:model.live.debounce.300ms="search" 
            placeholder="Search by name or email..." 
            style="background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.07); border-radius:var(--r-md); padding:var(--sp-3) var(--sp-4); color:#fff; flex:1; font-family:var(--font-admin); font-size:12px; outline:none;"
        >
        
        <select 
            wire:model.live="roleFilter" 
            style="background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.07); border-radius:var(--r-md); padding:var(--sp-3) var(--sp-4); color:#fff; font-family:var(--font-admin); font-size:12px; outline:none; appearance:none; width:200px; cursor:pointer;"
        >
            <option value="" style="color:#000">All Roles</option>
            @foreach($roles as $role)
                <option value="{{ $role }}" style="color:#000">{{ Str::title(str_replace('_', ' ', $role)) }}</option>
            @endforeach
        </select>
    </div>

    <!-- Users Datagrid -->
    <div class="sa-table-wrap">
        <div class="sa-table-head" style="grid-template-columns:2fr 1fr 1fr 1fr 80px">
            <span>User</span>
            <span>Role</span>
            <span>Organization</span>
            <span>Joined</span>
            <span>Actions</span>
        </div>
        
        @forelse($users as $user)
            <div class="sa-table-row" style="grid-template-columns:2fr 1fr 1fr 1fr 80px">
                <div>
                    <div style="font-weight:600;color:#fff;font-size:12px">{{ $user->email }}</div>
                    <div style="font-size:10px;color:rgba(255,255,255,.3)">{{ $user->name }}</div>
                </div>
                
                @if($user->hasRole('super_admin'))
                    <span class="role-chip role-super">Super Admin</span>
                @elseif($user->hasRole('org_admin'))
                    <span class="role-chip role-admin">Org Admin</span>
                @elseif($user->hasRole('cms_editor'))
                    <span class="role-chip role-editor">CMS Editor</span>
                @elseif($user->hasRole('teacher'))
                    <span class="role-chip role-teacher">Teacher</span>
                @elseif($user->hasRole('parent'))
                    <span class="role-chip role-parent">Parent</span>
                @else
                    <span class="role-chip role-child">Child</span>
                @endif

                <span style="font-size:11px;color:rgba(255,255,255,.5)">
                    {{ $user->organisation ? $user->organisation->name : 'Global' }}
                </span>
                
                <span style="font-size:11px;color:rgba(255,255,255,.35)">
                    {{ $user->created_at->diffForHumans() }}
                </span>
                
                <div style="display:flex;gap:3px">
                    <button class="btn btn-sm" style="background:rgba(255,255,255,.07);color:rgba(255,255,255,.5);padding:3px 7px;font-size:9px" onclick="toast('Editing user…')">
                        Edit
                    </button>
                    @if($user->id !== auth()->id())
                    <button class="btn btn-sm" style="background:rgba(196,75,43,.15);color:var(--clay-red-light);padding:3px 7px;font-size:9px">
                        Del
                    </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="sa-table-row" style="grid-template-columns:1fr">
                <div style="text-align:center;color:rgba(255,255,255,.3);padding:var(--sp-8)">
                    <div style="font-size:32px;margin-bottom:var(--sp-3)">🔍</div>
                    <div style="font-size:15px;font-weight:600">No users found</div>
                    <div style="font-size:13px;margin-top:var(--sp-1)">Adjust your search or filter and try again.</div>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination Links -->
    <div style="margin-top:var(--sp-6);">
        {{ $users->links(data: ['scrollTo' => false]) }}
    </div>
</div>
