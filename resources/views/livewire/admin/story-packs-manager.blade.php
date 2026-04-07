<div>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--sp-5);flex-wrap:wrap;gap:var(--sp-3)">
        <div>
            <div class="sa-page-title">Story Packs</div>
            <div class="sa-breadcrumb">Content · Library of Comics & Audio Books</div>
        </div>
        <div style="display:flex;gap:var(--sp-2);align-items:center">
            <span class="sa-badge">⚡ SUPER ADMIN</span>
            <button class="btn btn-primary btn-sm" style="background:var(--clay-red); border:none; color:#fff; padding: var(--sp-2) var(--sp-4); border-radius: var(--r-full); font-weight:700; cursor:pointer;">+ Add Story</button>
        </div>
    </div>

    <!-- Stats & Filters -->
    <div class="sa-stats-row" style="grid-template-columns: repeat(4, 1fr); gap: var(--sp-3); margin-bottom: var(--sp-4)">
        <div class="sa-stat">
            <div class="sa-stat-val">6</div>
            <div class="sa-stat-label">Total</div>
            <div class="sa-stat-delta">All time</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">3</div>
            <div class="sa-stat-label">Published</div>
            <div class="sa-stat-delta">Live in app</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">1</div>
            <div class="sa-stat-label">In Review</div>
            <div class="sa-stat-delta">Awaiting Review</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">2</div>
            <div class="sa-stat-label">Draft</div>
            <div class="sa-stat-delta">Not submitted</div>
        </div>
    </div>

    <div style="display:flex;gap:var(--sp-3);margin-bottom:var(--sp-4);flex-wrap:wrap">
        <input placeholder="🔍 Search stories…" style="padding:8px 14px;border-radius:var(--r-full);border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.06);color:#fff;font-family:var(--font-admin);font-size:12px;outline:none;flex:1;min-width:180px">
        <select style="padding:8px 14px;border-radius:var(--r-full);border:1px solid rgba(255,255,255,.12);background:#1a2744;color:#fff;font-family:var(--font-admin);font-size:12px;outline:none">
            <option value="">All Statuses</option>
            <option value="published">Published</option>
            <option value="review">In Review</option>
            <option value="draft">Draft</option>
        </select>
    </div>

    <div class="sa-table-wrap">
        <div class="sa-table-head" style="grid-template-columns:2.5fr 1fr 1fr 1fr 100px 140px">
            <span>Story</span>
            <span>Tribe</span>
            <span>Age Range</span>
            <span>Language</span>
            <span>Status</span>
            <span>Actions</span>
        </div>

        <!-- Sample Row 1 -->
        <div class="sa-table-row" style="grid-template-columns:2.5fr 1fr 1fr 1fr 100px 140px">
            <div style="display:flex;align-items:center;gap:12px">
                <div style="width:40px;height:32px;background:linear-gradient(135deg,#C44B2B,#6B2010);border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0">🐇</div>
                <div>
                    <div style="font-weight:600;color:#fff;font-size:13px">The Clever Hare</div>
                    <div style="font-size:11px;color:rgba(255,255,255,.3)">12 panels · id-001</div>
                </div>
            </div>
            <span style="font-size:12px;color:rgba(255,255,255,.6)">Buganda</span>
            <span style="font-size:12px;color:rgba(255,255,255,.6)">3–5 yrs</span>
            <span style="font-size:11px;color:rgba(255,255,255,.5)">Luganda</span>
            <span class="status-pill status-published">Published</span>
            <div style="display:flex;gap:6px">
                <button class="btn btn-sm" style="background:rgba(212,160,23,.18);color:#F2CB5A;border:1px solid rgba(212,160,23,.3);padding:4px 10px;border-radius:var(--r-full);font-size:10px;font-weight:700;cursor:pointer">Edit</button>
                <button class="btn btn-sm" style="background:rgba(196,75,43,.2);color:#E06444;border:1px solid rgba(196,75,43,.3);padding:4px 10px;border-radius:var(--r-full);font-size:10px;font-weight:700;cursor:pointer">Delete</button>
            </div>
        </div>

        <!-- Sample Row 2 -->
        <div class="sa-table-row" style="grid-template-columns:2.5fr 1fr 1fr 1fr 100px 140px">
            <div style="display:flex;align-items:center;gap:12px">
                <div style="width:40px;height:32px;background:linear-gradient(135deg,#4A7C59,#2D5438);border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0">🌿</div>
                <div>
                    <div style="font-weight:600;color:#fff;font-size:13px">Garden Words</div>
                    <div style="font-size:11px;color:rgba(255,255,255,.3)">8 panels · id-002</div>
                </div>
            </div>
            <span style="font-size:12px;color:rgba(255,255,255,.6)">Acholi</span>
            <span style="font-size:12px;color:rgba(255,255,255,.6)">2–4 yrs</span>
            <span style="font-size:11px;color:rgba(255,255,255,.5)">Acholi</span>
            <span class="status-pill status-review">In Review</span>
            <div style="display:flex;gap:6px">
                <button class="btn btn-sm" style="background:rgba(212,160,23,.18);color:#F2CB5A;border:1px solid rgba(212,160,23,.3);padding:4px 10px;border-radius:var(--r-full);font-size:10px;font-weight:700;cursor:pointer">Edit</button>
                <button class="btn btn-sm" style="background:rgba(196,75,43,.2);color:#E06444;border:1px solid rgba(196,75,43,.3);padding:4px 10px;border-radius:var(--r-full);font-size:10px;font-weight:700;cursor:pointer">Delete</button>
            </div>
        </div>
    </div>
</div>

