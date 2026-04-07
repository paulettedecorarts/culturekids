<div>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--sp-5);flex-wrap:wrap;gap:var(--sp-3)">
        <div>
            <div class="sa-page-title">Translations</div>
            <div class="sa-breadcrumb">Content · App Strings & Metadata Localization</div>
        </div>
        <div style="display:flex;gap:var(--sp-2);align-items:center">
            <button class="btn btn-primary btn-sm" style="background:var(--clay-red); border:none; color:#fff; padding: var(--sp-2) var(--sp-4); border-radius: var(--r-full); font-weight:700; cursor:pointer;">+ Add String Key</button>
        </div>
    </div>

    <!-- Stats -->
    <div class="sa-stats-row">
        <div class="sa-stat">
            <div class="sa-stat-val">1,245</div>
            <div class="sa-stat-label">Total Strings</div>
            <div class="sa-stat-delta">Core app dictionary</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val" style="color:var(--banana-green)">98%</div>
            <div class="sa-stat-label">Luganda Coverage</div>
            <div class="sa-stat-delta">25 missing strings</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val" style="color:var(--sunfire)">45%</div>
            <div class="sa-stat-label">Swahili Coverage</div>
            <div class="sa-stat-delta">680 missing strings</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">3</div>
            <div class="sa-stat-label">Translators</div>
            <div class="sa-stat-delta">Active this week</div>
        </div>
    </div>

    <!-- Toolbar -->
    <div style="display:flex; gap:var(--sp-2); margin-bottom:var(--sp-4);">
        <input type="text" placeholder="Search keys or translations..." style="background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.1); border-radius:var(--r-full); padding:var(--sp-2) var(--sp-4); color:#fff; font-size:12px; outline:none; min-width:250px;">
        <select style="background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.1); border-radius:var(--r-full); padding:var(--sp-2) var(--sp-4); color:#fff; font-size:12px; outline:none;">
            <option>Module: Story Reader</option>
            <option>Module: Navigation</option>
            <option>Module: Quizzes</option>
        </select>
        <button class="btn btn-ghost btn-sm" style="margin-left:auto;">Filter Missing</button>
    </div>

    <div class="sa-table-wrap">
        <div class="sa-table-head" style="grid-template-columns:1.5fr 2fr 2fr 2fr 100px">
            <span>String Key</span>
            <span>Base (English)</span>
            <span>Luganda</span>
            <span>Swahili</span>
            <span>Actions</span>
        </div>

        <div class="sa-table-row" style="grid-template-columns:1.5fr 2fr 2fr 2fr 100px">
            <span style="font-family:monospace;font-size:11px;color:rgba(255,255,255,.4)">reader.btn.next</span>
            <span style="font-size:12px;color:#fff;font-weight:600">Next Page</span>
            <span style="font-size:12px;color:var(--banana-green)">Omuko Gudako</span>
            <span style="font-size:12px;color:var(--banana-green)">Ukurasa Ufuatao</span>
            <div style="display:flex;gap:6px">
                <button class="btn btn-ghost btn-sm" style="padding:3px 8px;font-size:9px">Edit</button>
            </div>
        </div>

        <div class="sa-table-row" style="grid-template-columns:1.5fr 2fr 2fr 2fr 100px">
            <span style="font-family:monospace;font-size:11px;color:rgba(255,255,255,.4)">reader.msg.completed</span>
            <span style="font-size:12px;color:#fff;font-weight:600">Great job! You finished the story.</span>
            <span style="font-size:12px;color:var(--banana-green)">Wakoze bulungi! Omaze olugero.</span>
            <span style="font-size:12px;color:var(--clay-red);font-style:italic">Missing</span>
            <div style="display:flex;gap:6px">
                <button class="btn btn-ghost btn-sm" style="padding:3px 8px;font-size:9px;color:var(--savanna-gold);border-color:rgba(212,160,23,.3)">Translate</button>
            </div>
        </div>
        
        <div class="sa-table-row" style="grid-template-columns:1.5fr 2fr 2fr 2fr 100px">
            <span style="font-family:monospace;font-size:11px;color:rgba(255,255,255,.4)">vocab.btn.listen</span>
            <span style="font-size:12px;color:#fff;font-weight:600">Listen</span>
            <span style="font-size:12px;color:var(--banana-green)">Wuliriza</span>
            <span style="font-size:12px;color:var(--banana-green)">Sikiliza</span>
            <div style="display:flex;gap:6px">
                <button class="btn btn-ghost btn-sm" style="padding:3px 8px;font-size:9px">Edit</button>
            </div>
        </div>
    </div>
</div>
