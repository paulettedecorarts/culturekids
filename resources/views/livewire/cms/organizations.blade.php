<div>
    <div class="cms-header">
        <div><h1 class="cms-page-title">Organizations</h1><div class="cms-breadcrumb">Platform · Tenants · Access</div></div>
        <button class="btn btn-primary btn-sm">+ New Org</button>
    </div>
    <div class="cms-stats-row">
        <div class="cms-stat"><div class="sa-stat-val">12</div><div class="cms-stat-label">Total Orgs</div></div>
        <div class="cms-stat"><div class="sa-stat-val">840</div><div class="cms-stat-label">Total Users</div></div>
        <div class="cms-stat"><div class="sa-stat-val">98%</div><div class="cms-stat-label">SLA Uptime</div></div>
        <div class="cms-stat"><div class="sa-stat-val">2.1k</div><div class="cms-stat-label">Active Subs</div></div>
    </div>
    <div class="cms-asset-table">
        <div class="cms-table-header" style="grid-template-columns:2fr 1fr 1fr 100px">
            <span>Organization</span><span>Owner</span><span>Users</span><span>Actions</span>
        </div>
        @foreach(['Paulette Labs', 'Culture Kids Global', 'Heritage Uganda'] as $o)
        <div class="cms-table-row" style="grid-template-columns:2fr 1fr 1fr 100px">
            <span style="font-weight:700">{{ $o }}</span>
            <span style="font-size:12px; color:var(--stone)">admin@paulette.com</span>
            <span style="font-weight:700">{{ rand(40, 400) }}</span>
            <button class="btn btn-ghost btn-sm" style="font-size:10px">Manage</button>
        </div>
        @endforeach
    </div>
</div>
