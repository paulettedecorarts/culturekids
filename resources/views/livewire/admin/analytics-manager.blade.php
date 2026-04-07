<div>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--sp-5);flex-wrap:wrap;gap:var(--sp-3)">
        <div>
            <div class="sa-page-title">Analytics Dashboard</div>
            <div class="sa-breadcrumb">Platform · Global Learning Metrics, Sync Status & Engagement</div>
        </div>
        <div style="display:flex;gap:var(--sp-2);align-items:center">
             <div style="background:rgba(74,124,89,.2); border:1px solid rgba(74,124,89,.4); padding:4px 12px; border-radius:999px; display:flex; align-items:center; gap:8px">
                <div style="width:8px; height:8px; border-radius:50%; background:var(--banana-green)"></div>
                <span style="font-size:11px; font-weight:700; color:var(--banana-green)">SYNC LIVE</span>
            </div>
            <button class="btn btn-ghost btn-sm">Export CSV</button>
        </div>
    </div>

    <!-- Analytics Stats -->
    <div class="sa-stats-row">
        <div class="sa-stat">
            <div class="sa-stat-val">12,847</div>
            <div class="sa-stat-label">Active Pupils</div>
            <div class="sa-stat-delta">+842 this week</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">48,205</div>
            <div class="sa-stat-label">Story Reads</div>
            <div class="sa-stat-delta">+1,200 today</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">92%</div>
            <div class="sa-stat-label">Completion Rate</div>
            <div class="sa-stat-delta" style="color:var(--banana-green)">↑ 3.2% better</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">Uganda</div>
            <div class="sa-stat-label">Primary Region</div>
            <div class="sa-stat-delta">Central Uganda</div>
        </div>
    </div>

    <!-- Analytics Grid (Chart Placeholders) -->
    <div style="display:grid; grid-template-columns: 2fr 1fr; gap:var(--sp-6); margin-top:var(--sp-6);">
        <!-- Timeline Chart -->
        <div style="background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.07); border-radius:var(--r-2xl); padding:var(--sp-6);">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:var(--sp-8)">
                <h3 style="font-size:16px; font-weight:800; color:#fff; font-family:var(--font-display);">Learning Engagement Timeline</h3>
                <div style="display:flex; gap:10px">
                    <span style="font-size:11px; color:rgba(255,255,255,.4); font-weight:700;">DAILY</span>
                    <span style="font-size:11px; color:#fff; font-weight:700; border-bottom:2px solid var(--savanna-gold)">WEEKLY</span>
                    <span style="font-size:11px; color:rgba(255,255,255,.4); font-weight:700;">MONTHLY</span>
                </div>
            </div>
            <!-- CSS Chart Simulation -->
            <div style="height:250px; display:flex; align-items:flex-end; gap:var(--sp-4); padding-bottom:var(--sp-2);">
                <div style="flex:1; height:45%; background:linear-gradient(to top, var(--savanna-gold), transparent); border-radius:4px 4px 0 0; position:relative;" onmouseover="this.style.background='var(--savanna-gold)'" onmouseout="this.style.background='linear-gradient(to top, var(--savanna-gold), transparent)'"></div>
                <div style="flex:1; height:65%; background:linear-gradient(to top, var(--savanna-gold), transparent); border-radius:4px 4px 0 0; position:relative;"></div>
                <div style="flex:1; height:35%; background:linear-gradient(to top, var(--savanna-gold), transparent); border-radius:4px 4px 0 0; position:relative;"></div>
                <div style="flex:1; height:85%; background:linear-gradient(to top, var(--savanna-gold), transparent); border-radius:4px 4px 0 0; position:relative;"></div>
                <div style="flex:1; height:55%; background:linear-gradient(to top, var(--savanna-gold), transparent); border-radius:4px 4px 0 0; position:relative;"></div>
                <div style="flex:1; height:75%; background:linear-gradient(to top, var(--savanna-gold), transparent); border-radius:4px 4px 0 0; position:relative;"></div>
                <div style="flex:1; height:95%; background:linear-gradient(to top, var(--savanna-gold), transparent); border-radius:4px 4px 0 0; position:relative;"></div>
            </div>
            <div style="display:flex; justify-content:space-between; margin-top:10px; border-top:1px solid rgba(255,255,255,.05); padding-top:10px">
                <span style="font-size:11px; color:rgba(255,255,255,.3)">MON</span>
                <span style="font-size:11px; color:rgba(255,255,255,.3)">TUE</span>
                <span style="font-size:11px; color:rgba(255,255,255,.3)">WED</span>
                <span style="font-size:11px; color:rgba(255,255,255,.3)">THU</span>
                <span style="font-size:11px; color:rgba(255,255,255,.3)">FRI</span>
                <span style="font-size:11px; color:rgba(255,255,255,.3)">SAT</span>
                <span style="font-size:11px; color:#fff; font-weight:800">SUN (Today)</span>
            </div>
        </div>

        <!-- Top Content -->
        <div style="background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.07); border-radius:var(--r-2xl); padding:var(--sp-6);">
            <h3 style="font-size:16px; font-weight:800; color:#fff; margin-bottom:var(--sp-6); font-family:var(--font-display);">Top Performer Stories</h3>
            <div style="display:grid; gap:var(--sp-4);">
                <div style="display:flex; align-items:center; gap:var(--sp-3)">
                    <div style="background:var(--clay-red); width:32px; height:32px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:14px">🐇</div>
                    <div style="flex:1">
                        <div style="font-size:13px; font-weight:700; color:#fff">The Clever Hare</div>
                        <div style="font-size:11px; color:rgba(255,255,255,.4)">4.2k reads this week</div>
                    </div>
                </div>
                <div style="display:flex; align-items:center; gap:var(--sp-3)">
                    <div style="background:var(--banana-green); width:32px; height:32px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:14px">🌿</div>
                    <div style="flex:1">
                        <div style="font-size:13px; font-weight:700; color:#fff">Garden Words</div>
                        <div style="font-size:11px; color:rgba(255,255,255,.4)">3.8k reads this week</div>
                    </div>
                </div>
                <div style="display:flex; align-items:center; gap:var(--sp-3)">
                    <div style="background:var(--sunfire); width:32px; height:32px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:14px">🥁</div>
                    <div style="flex:1">
                        <div style="font-size:13px; font-weight:700; color:#fff">Drum Songs Vol 1</div>
                        <div style="font-size:11px; color:rgba(255,255,255,.4)">2.9k reads this week</div>
                    </div>
                </div>
            </div>
            <button class="btn btn-ghost btn-sm" style="width:100%; margin-top:var(--sp-8)">View Full Report</button>
        </div>
    </div>
</div>
