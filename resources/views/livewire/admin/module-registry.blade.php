<div>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--sp-5);flex-wrap:wrap;gap:var(--sp-3)">
        <div>
            <div class="sa-page-title">Modules Registry</div>
            <div class="sa-breadcrumb">Super Admin · Create, enable & manage platform modules</div>
        </div>
        <div style="display:flex;gap:var(--sp-2)">
            <span class="sa-badge">⚡ SUPER ADMIN</span>
            <button class="btn btn-primary btn-sm" style="background:var(--clay-red); border:none; color:#fff; padding: var(--sp-2) var(--sp-4); border-radius: var(--r-full); font-weight:700; cursor:pointer;">+ Add Module</button>
        </div>
    </div>

    <!-- Stats strip -->
    <div class="sa-stats-row" style="grid-template-columns:repeat(4,1fr);gap:var(--sp-3);margin-bottom:var(--sp-4)">
        <div class="sa-stat">
            <div class="sa-stat-val">9</div>
            <div class="sa-stat-label">Total</div>
            <div class="sa-stat-delta">Platform features</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">7</div>
            <div class="sa-stat-label">Enabled</div>
            <div class="sa-stat-delta">Currently active</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">2</div>
            <div class="sa-stat-label">Disabled</div>
            <div class="sa-stat-delta">Turned off</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">4</div>
            <div class="sa-stat-label">Free Tier</div>
            <div class="sa-stat-delta">All-access</div>
        </div>
    </div>

    <div class="sa-table-wrap">
        <div class="sa-table-head" style="grid-template-columns:2fr 1fr 1fr 80px 140px">
            <span>Module</span>
            <span>Category</span>
            <span>Plan</span>
            <span>Enabled</span>
            <span>Actions</span>
        </div>

        <!-- Sample Row 1 -->
        <div class="sa-table-row" style="grid-template-columns:2fr 1fr 1fr 80px 140px">
            <div style="display:flex;align-items:center;gap:10px">
                <div style="font-size:22px;width:32px;text-align:center">🌍</div>
                <div>
                    <div style="font-weight:600;color:#fff;font-size:12px">Tribe Directory</div>
                    <div style="font-size:10px;color:rgba(255,255,255,.3);font-family:monospace">tribe_directory</div>
                </div>
            </div>
            <span style="font-size:11px;color:rgba(255,255,255,.55)">Core</span>
            <span style="background:rgba(74,124,89,.2);color:#6FA882;padding:2px 8px;border-radius:999px;font-size:9px;font-weight:700">Free</span>
            <div style="display:flex;justify-content:center"><div class="toggle-switch on"></div></div>
            <div style="display:flex;gap:3px">
                <button style="background:rgba(212,160,23,.18);color:#F2CB5A;border:1px solid rgba(212,160,23,.3);padding:3px 7px;border-radius:999px;font-size:9px;font-weight:700;cursor:pointer">Edit</button>
                <button style="background:rgba(196,75,43,.2);color:#E06444;border:1px solid rgba(196,75,43,.3);padding:3px 7px;border-radius:999px;font-size:9px;font-weight:700;cursor:pointer">✕</button>
            </div>
        </div>

        <!-- Sample Row 2 -->
        <div class="sa-table-row" style="grid-template-columns:2fr 1fr 1fr 80px 140px">
            <div style="display:flex;align-items:center;gap:10px">
                <div style="font-size:22px;width:32px;text-align:center">📖</div>
                <div>
                    <div style="font-weight:600;color:#fff;font-size:12px">Comics & Stories</div>
                    <div style="font-size:10px;color:rgba(255,255,255,.3);font-family:monospace">comics</div>
                </div>
            </div>
            <span style="font-size:11px;color:rgba(255,255,255,.55)">Core</span>
            <span style="background:rgba(74,124,89,.2);color:#6FA882;padding:2px 8px;border-radius:999px;font-size:9px;font-weight:700">Free</span>
            <div style="display:flex;justify-content:center"><div class="toggle-switch on"></div></div>
            <div style="display:flex;gap:3px">
                <button style="background:rgba(212,160,23,.18);color:#F2CB5A;border:1px solid rgba(212,160,23,.3);padding:3px 7px;border-radius:999px;font-size:9px;font-weight:700;cursor:pointer">Edit</button>
                <button style="background:rgba(196,75,43,.2);color:#E06444;border:1px solid rgba(196,75,43,.3);padding:3px 7px;border-radius:999px;font-size:9px;font-weight:700;cursor:pointer">✕</button>
            </div>
        </div>
    </div>
</div>
