<div>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--sp-5);flex-wrap:wrap;gap:var(--sp-3)">
        <div>
            <div class="sa-page-title">Analytics Dashboard</div>
            <div class="sa-breadcrumb">Platform · Global Learning Metrics, Sync Status & Engagement</div>
        </div>
        <div style="display:flex;gap:var(--sp-2);align-items:center">
            <div style="background:rgba(74,124,89,.2);border:1px solid rgba(74,124,89,.4);padding:4px 12px;border-radius:999px;display:flex;align-items:center;gap:8px">
                <div style="width:8px;height:8px;border-radius:50%;background:var(--banana-green)"></div>
                <span style="font-size:11px;font-weight:700;color:var(--banana-green)">LIVE DATA</span>
            </div>
        </div>
    </div>

    @include('livewire.admin.partials.engagement-analytics', ['analytics' => $analytics, 'showFullLink' => false])
</div>
