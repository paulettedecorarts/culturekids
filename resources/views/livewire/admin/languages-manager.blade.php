<div>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--sp-5);flex-wrap:wrap;gap:var(--sp-3)">
        <div>
            <div class="sa-page-title">Language Registry</div>
            <div class="sa-breadcrumb">Super Admin · Platform · Dialect & Translation coverage</div>
        </div>
        <div style="display:flex;gap:var(--sp-2);align-items:center">
            <span class="sa-badge">⚡ SUPER ADMIN</span>
            <button class="btn btn-primary btn-sm" style="background:var(--clay-red); border:none; color:#fff; padding: var(--sp-2) var(--sp-4); border-radius: var(--r-full); font-weight:700; cursor:pointer;">+ Add Language</button>
        </div>
    </div>

    <!-- Stats strip -->
    <div class="sa-stats-row" style="grid-template-columns:repeat(4,1fr);gap:var(--sp-3);margin-bottom:var(--sp-5)">
        <div class="sa-stat">
            <div class="sa-stat-val">23</div>
            <div class="sa-stat-label">Active Languages</div>
            <div class="sa-stat-delta">Native dialects</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">84%</div>
            <div class="sa-stat-label">System Trans.</div>
            <div class="sa-stat-delta">Overall coverage</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">12</div>
            <div class="sa-stat-label">Audio Packs</div>
            <div class="sa-stat-delta">TTS/Voiceover</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">3</div>
            <div class="sa-stat-label">Pending</div>
            <div class="sa-stat-delta">In translation</div>
        </div>
    </div>

    <div class="sa-table-wrap">
        <div class="sa-table-head" style="grid-template-columns:1.5fr 1fr 1fr 1fr 140px">
            <span>Language / Dialect</span>
            <span>Code</span>
            <span>Coverage</span>
            <span>Status</span>
            <span>Actions</span>
        </div>

        <!-- Sample Row 1 -->
        <div class="sa-table-row" style="grid-template-columns:1.5fr 1fr 1fr 1fr 140px">
            <div style="display:flex;align-items:center;gap:12px">
                <span style="font-size:20px">🇺🇬</span>
                <div style="font-weight:600;color:#fff;font-size:13px">Luganda</div>
            </div>
            <code style="background:rgba(255,255,255,.05);padding:2px 6px;border-radius:4px;font-size:11px;color:var(--savanna-gold)">lug-UG</code>
            <div style="flex:1;display:flex;align-items:center;gap:10px">
                <div style="flex:1;height:4px;background:rgba(255,255,255,.1);border-radius:2px;overflow:hidden">
                    <div style="width:95%;height:100%;background:var(--banana-green)"></div>
                </div>
                <span style="font-size:10px;font-weight:700;color:var(--banana-mid)">95%</span>
            </div>
            <span class="status-pill status-published">Verified</span>
            <div style="display:flex;gap:6px">
                <button class="btn btn-sm" style="background:rgba(255,255,255,.07);color:rgba(255,255,255,.5);padding:3px 8px;font-size:9px">Manage</button>
            </div>
        </div>

        <!-- Sample Row 2 -->
        <div class="sa-table-row" style="grid-template-columns:1.5fr 1fr 1fr 1fr 140px">
            <div style="display:flex;align-items:center;gap:12px">
                <span style="font-size:20px">🇺🇬</span>
                <div style="font-weight:600;color:#fff;font-size:13px">Acholi</div>
            </div>
            <code style="background:rgba(255,255,255,.05);padding:2px 6px;border-radius:4px;font-size:11px;color:var(--savanna-gold)">ach-UG</code>
            <div style="flex:1;display:flex;align-items:center;gap:10px">
                <div style="flex:1;height:4px;background:rgba(255,255,255,.1);border-radius:2px;overflow:hidden">
                    <div style="width:62%;height:100%;background:var(--sunfire)"></div>
                </div>
                <span style="font-size:10px;font-weight:700;color:var(--sunfire-light)">62%</span>
            </div>
            <span class="status-pill status-review">Partial</span>
            <div style="display:flex;gap:6px">
                <button class="btn btn-sm" style="background:rgba(255,255,255,.07);color:rgba(255,255,255,.5);padding:3px 8px;font-size:9px">Manage</button>
            </div>
        </div>
    </div>
</div>

