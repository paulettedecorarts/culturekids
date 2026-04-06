<div>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--sp-5);flex-wrap:wrap;gap:var(--sp-3)">
        <div>
            <div class="sa-page-title">Activities</div>
            <div class="sa-breadcrumb">Super Admin · Songs, Vocab Packs, Worksheets & More</div>
        </div>
        <div style="display:flex;gap:var(--sp-2);align-items:center">
            <span class="sa-badge">⚡ SUPER ADMIN</span>
            <button class="btn btn-primary btn-sm" style="background:var(--clay-red); border:none; color:#fff; padding: var(--sp-2) var(--sp-4); border-radius: var(--r-full); font-weight:700; cursor:pointer;">+ Add Activity</button>
        </div>
    </div>

    <!-- Stats & Filters -->
    <div class="sa-stats-row" style="grid-template-columns: repeat(4, 1fr); gap: var(--sp-3); margin-bottom: var(--sp-4)">
        <div class="sa-stat">
            <div class="sa-stat-val">7</div>
            <div class="sa-stat-label">Total</div>
            <div class="sa-stat-delta">All types</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">2</div>
            <div class="sa-stat-label">Songs</div>
            <div class="sa-stat-delta">Audio Content</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">2</div>
            <div class="sa-stat-label">Vocab Packs</div>
            <div class="sa-stat-delta">Flashcard sets</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">4</div>
            <div class="sa-stat-label">Published</div>
            <div class="sa-stat-delta">Live</div>
        </div>
    </div>

    <div style="display:flex;gap:var(--sp-3);margin-bottom:var(--sp-4);flex-wrap:wrap">
        <input placeholder="🔍 Search activities…" style="padding:8px 14px;border-radius:var(--r-full);border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.06);color:#fff;font-family:var(--font-admin);font-size:12px;outline:none;flex:1;min-width:180px">
        <select style="padding:8px 14px;border-radius:var(--r-full);border:1px solid rgba(255,255,255,.12);background:#1a2744;color:#fff;font-family:var(--font-admin);font-size:12px;outline:none">
            <option value="">All Types</option>
            <option value="song">Songs</option>
            <option value="vocab">Vocab Pack</option>
            <option value="worksheet">Worksheet</option>
        </select>
    </div>

    <div class="sa-table-wrap">
        <div class="sa-table-head" style="grid-template-columns:2.5fr 1fr 1fr 1fr 140px">
            <span>Activity</span>
            <span>Type</span>
            <span>Tribe</span>
            <span>Status</span>
            <span>Actions</span>
        </div>

        <!-- Sample Row 1 -->
        <div class="sa-table-row" style="grid-template-columns:2.5fr 1fr 1fr 1fr 140px">
            <div style="display:flex;align-items:center;gap:12px">
                <div style="font-size:20px;width:32px;text-align:center">🎵</div>
                <div>
                    <div style="font-weight:600;color:#fff;font-size:13px">Engoma — Village Drum</div>
                    <div style="font-size:11px;color:rgba(255,255,255,.3)">Traditional Luganda drum song…</div>
                </div>
            </div>
            <span style="background:rgba(232,135,42,.2);color:#F2A84E;padding:2px 8px;border-radius:999px;font-size:9px;font-weight:700;text-transform:capitalize">song</span>
            <span style="font-size:12px;color:rgba(255,255,255,.6)">Buganda</span>
            <span class="status-pill status-published">Published</span>
            <div style="display:flex;gap:6px">
                <button class="btn btn-sm" style="background:rgba(212,160,23,.18);color:#F2CB5A;border:1px solid rgba(212,160,23,.3);padding:4px 10px;border-radius:var(--r-full);font-size:10px;font-weight:700;cursor:pointer">Edit</button>
                <button class="btn btn-sm" style="background:rgba(196,75,43,.2);color:#E06444;border:1px solid rgba(196,75,43,.3);padding:4px 10px;border-radius:var(--r-full);font-size:10px;font-weight:700;cursor:pointer">Delete</button>
            </div>
        </div>

        <!-- Sample Row 2 -->
        <div class="sa-table-row" style="grid-template-columns:2.5fr 1fr 1fr 1fr 140px">
            <div style="display:flex;align-items:center;gap:12px">
                <div style="font-size:20px;width:32px;text-align:center">🎴</div>
                <div>
                    <div style="font-weight:600;color:#fff;font-size:13px">Animals Vocab Pack</div>
                    <div style="font-size:11px;color:rgba(255,255,255,.3)">12 Luganda animal flashcards…</div>
                </div>
            </div>
            <span style="background:rgba(74,124,89,.2);color:#6FA882;padding:2px 8px;border-radius:999px;font-size:9px;font-weight:700;text-transform:capitalize">vocab</span>
            <span style="font-size:12px;color:rgba(255,255,255,.6)">Buganda</span>
            <span class="status-pill status-published">Published</span>
            <div style="display:flex;gap:6px">
                <button class="btn btn-sm" style="background:rgba(212,160,23,.18);color:#F2CB5A;border:1px solid rgba(212,160,23,.3);padding:4px 10px;border-radius:var(--r-full);font-size:10px;font-weight:700;cursor:pointer">Edit</button>
                <button class="btn btn-sm" style="background:rgba(196,75,43,.2);color:#E06444;border:1px solid rgba(196,75,43,.3);padding:4px 10px;border-radius:var(--r-full);font-size:10px;font-weight:700;cursor:pointer">Delete</button>
            </div>
        </div>
    </div>
</div>
