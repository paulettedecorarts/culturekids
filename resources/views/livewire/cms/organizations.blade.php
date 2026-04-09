<div>
    <div class="cms-header">
        <div><h1 class="cms-page-title">My Organization</h1><div class="cms-breadcrumb">Management · Tenant Profile · Access</div></div>
        <button class="btn btn-primary btn-sm" wire:click="save">💾 Save Profile</button>
    </div>
    @if (session()->has('message'))
        <div style="margin-bottom:12px; padding:10px 14px; border:1px solid #DCFCE7; background:#F0FDF4; color:#166534; border-radius:10px; font-size:12px; font-weight:700;">
            {{ session('message') }}
        </div>
    @endif
    <div class="cms-stats-row">
        <div class="cms-stat"><div class="sa-stat-val">{{ $totalUsers }}</div><div class="cms-stat-label">Total Users</div></div>
        <div class="cms-stat"><div class="sa-stat-val">{{ $adminCount }}</div><div class="cms-stat-label">Admins</div></div>
        <div class="cms-stat"><div class="sa-stat-val">{{ $editorCount }}</div><div class="cms-stat-label">Editors</div></div>
        <div class="cms-stat"><div class="sa-stat-val">{{ $teacherCount }}</div><div class="cms-stat-label">Teachers</div></div>
    </div>

    <div style="background:#fff; border:1px solid var(--cream-mid); border-radius:var(--r-xl); padding:var(--sp-6); box-shadow:0 8px 32px rgba(26,18,8,.05)">
        <div class="cms-table-header" style="grid-template-columns:repeat(2,1fr); margin:-24px -24px 20px;">
            <span>Organization Profile</span><span>Current Settings</span>
        </div>
        <div style="display:grid; grid-template-columns:repeat(2,1fr); gap:var(--sp-4)">
            <div>
                <label style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; color:var(--stone); margin-bottom:6px">Name</label>
                <input type="text" wire:model.live="name" style="width:100%; padding:10px 12px; border-radius:10px; border:1px solid var(--cream-mid)">
            </div>
            <div>
                <label style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; color:var(--stone); margin-bottom:6px">Code</label>
                <input type="text" value="{{ $code }}" disabled style="width:100%; padding:10px 12px; border-radius:10px; border:1px solid var(--cream-mid); background:#F8F8F8">
            </div>
            <div>
                <label style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; color:var(--stone); margin-bottom:6px">Address</label>
                <input type="text" wire:model.live="address" style="width:100%; padding:10px 12px; border-radius:10px; border:1px solid var(--cream-mid)">
            </div>
            <div>
                <label style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; color:var(--stone); margin-bottom:6px">Plan / Status</label>
                <div style="display:flex; gap:8px; align-items:center; height:42px;">
                    <span class="status-pill status-published">{{ strtoupper($plan) }}</span>
                    <span class="status-pill {{ $status === 'active' ? 'status-published' : 'status-draft' }}">{{ strtoupper($status) }}</span>
                </div>
            </div>
            <div style="grid-column:1 / -1;">
                <label style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; color:var(--stone); margin-bottom:6px">Description</label>
                <textarea wire:model.live="description" rows="4" style="width:100%; padding:10px 12px; border-radius:10px; border:1px solid var(--cream-mid)"></textarea>
            </div>
        </div>
    </div>
</div>
