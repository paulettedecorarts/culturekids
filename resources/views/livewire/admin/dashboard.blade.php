<div>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--sp-5);flex-wrap:wrap;gap:var(--sp-3)">
        <div>
            <div class="sa-page-title">System Dashboard</div>
            <div class="sa-breadcrumb">Super Admin · Live Overview</div>
        </div>
        <div style="display:flex;gap:var(--sp-2);align-items:center">
            <span class="sa-badge">⚡ SUPER ADMIN</span>
        </div>
    </div>

    <!-- Top Stats -->
    <div class="sa-stats-row">
        <div class="sa-stat">
            <div class="sa-stat-val">{{ $organizationsCount }}</div>
            <div class="sa-stat-label">Organizations</div>
            <div class="sa-stat-delta">↑ Active</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">{{ number_format($usersCount) }}</div>
            <div class="sa-stat-label">Total Users</div>
            <div class="sa-stat-delta">↑ Platform-wide</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">{{ $tribesCount }}</div>
            <div class="sa-stat-label">Tribes Configured</div>
            <div class="sa-stat-delta">Out of 65 possible</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">{{ number_format($activitiesCount) }}</div>
            <div class="sa-stat-label">Content Items</div>
            <div class="sa-stat-delta">Activities populated</div>
        </div>
    </div>

    <!-- Recent Users Table -->
    <p style="font-size:12px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:rgba(255,255,255,.25);margin-bottom:var(--sp-3);margin-top:var(--sp-6)">Recent Users</p>
    <div class="sa-table-wrap">
        <div class="sa-table-head" style="grid-template-columns:3fr 2fr 2fr 1fr 100px">
            <span>User</span>
            <span>Role</span>
            <span>Organization</span>
            <span>Joined</span>
            <span>Actions</span>
        </div>
        
        @forelse($recentUsers as $user)
            <div class="sa-table-row" style="grid-template-columns:3fr 2fr 2fr 1fr 100px">
                <div style="display:flex;align-items:center;gap:var(--sp-3)">
                    <div style="width:32px;height:32px;border-radius:50%;background:rgba(255,255,255,.1);display:flex;align-items:center;justify-content:center;font-size:14px;">
                        {{ substr($user->name, 0, 1) }}
                    </div>
                    <div>
                        <div style="font-weight:600;color:#fff;font-size:14px">{{ $user->email }}</div>
                        <div style="font-size:12px;color:rgba(255,255,255,.3)">{{ $user->name }}</div>
                    </div>
                </div>
                
                <div>
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
                        <span class="role-chip role-child">Child / Basic</span>
                    @endif
                </div>

                <span style="font-size:13px;color:rgba(255,255,255,.5)">
                    {{ $user->organisation ? $user->organisation->name : 'Global (No Org)' }}
                </span>
                
                <span style="font-size:13px;color:rgba(255,255,255,.35)">
                    {{ $user->created_at->diffForHumans() }}
                </span>
                
                <button class="btn btn-sm" style="background:rgba(255,255,255,.07);color:rgba(255,255,255,.5);padding:5px 12px;font-size:12px;border-radius:var(--r-sm);border:none;cursor:pointer;">
                    Manage
                </button>
            </div>
        @empty
            <div class="sa-table-row" style="grid-template-columns:1fr">
                <div style="text-align:center;color:rgba(255,255,255,.3);padding:var(--sp-4)">No users found.</div>
            </div>
        @endforelse
    </div>
</div>
