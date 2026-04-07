<div>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--sp-5);flex-wrap:wrap;gap:var(--sp-3)">
        <div>
            <div class="sa-page-title">Assets</div>
            <div class="sa-breadcrumb">Content · Media, Images, Audio & Extracted Panels</div>
        </div>
        <div style="display:flex;gap:var(--sp-2);align-items:center">
            <button class="btn btn-primary btn-sm" style="background:var(--banana-green); border:none; color:#fff; padding: var(--sp-2) var(--sp-4); border-radius: var(--r-full); font-weight:700; cursor:pointer;">+ Upload Asset</button>
        </div>
    </div>

    <!-- Stats -->
    <div class="sa-stats-row">
        <div class="sa-stat">
            <div class="sa-stat-val">847</div>
            <div class="sa-stat-label">Total Assets</div>
            <div class="sa-stat-delta">+62 this month</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">620</div>
            <div class="sa-stat-label">Images / SVG</div>
            <div class="sa-stat-delta">Comic panels</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">105</div>
            <div class="sa-stat-label">Audio</div>
            <div class="sa-stat-delta">Voiceovers & Songs</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">2.4 GB</div>
            <div class="sa-stat-label">S3 Storage</div>
            <div class="sa-stat-delta">Total consumed</div>
        </div>
    </div>

    <!-- Toolbar -->
    <div style="display:flex; gap:var(--sp-2); margin-bottom:var(--sp-4);">
        <input type="text" placeholder="Search assets..." style="background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.1); border-radius:var(--r-full); padding:var(--sp-2) var(--sp-4); color:#fff; font-size:12px; outline:none; min-width:250px;">
        <select style="background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.1); border-radius:var(--r-full); padding:var(--sp-2) var(--sp-4); color:#fff; font-size:12px; outline:none;">
            <option>All Types</option>
            <option>Images</option>
            <option>Audio</option>
            <option>PDFs</option>
        </select>
    </div>

    <div class="sa-table-wrap">
        <div class="sa-table-head" style="grid-template-columns:80px 2fr 1fr 1fr 1fr 100px">
            <span>Preview</span>
            <span>Asset Name</span>
            <span>Type</span>
            <span>File Size</span>
            <span>Linked Pack</span>
            <span>Actions</span>
        </div>

        <!-- Sample Row 1 -->
        <div class="sa-table-row" style="grid-template-columns:80px 2fr 1fr 1fr 1fr 100px">
            <div style="width:40px;height:40px;background:rgba(255,255,255,.05);border-radius:4px;display:flex;align-items:center;justify-content:center;font-size:18px">🖼️</div>
            <div>
                <div style="font-weight:600;color:#fff;font-size:13px">hero_jump_panel_03.png</div>
                <div style="font-size:11px;color:rgba(255,255,255,.3)">Uploaded today 10:45 AM</div>
            </div>
            <span style="font-size:12px;color:rgba(255,255,255,.6)">Image (PNG)</span>
            <span style="font-size:12px;color:rgba(255,255,255,.6)">345 KB</span>
            <span style="font-size:12px;color:var(--savanna-gold)">The Clever Hare</span>
            <div style="display:flex;gap:6px">
                <button class="btn btn-ghost btn-sm" style="padding:3px 8px;font-size:9px">View</button>
            </div>
        </div>

        <!-- Sample Row 2 -->
        <div class="sa-table-row" style="grid-template-columns:80px 2fr 1fr 1fr 1fr 100px">
            <div style="width:40px;height:40px;background:rgba(255,255,255,.05);border-radius:4px;display:flex;align-items:center;justify-content:center;font-size:18px">🎵</div>
            <div>
                <div style="font-weight:600;color:#fff;font-size:13px">hare_laughing_vo.mp3</div>
                <div style="font-size:11px;color:rgba(255,255,255,.3)">Uploaded yesterday</div>
            </div>
            <span style="font-size:12px;color:rgba(255,255,255,.6)">Audio (MP3)</span>
            <span style="font-size:12px;color:rgba(255,255,255,.6)">1.2 MB</span>
            <span style="font-size:12px;color:var(--savanna-gold)">The Clever Hare</span>
            <div style="display:flex;gap:6px">
                <button class="btn btn-ghost btn-sm" style="padding:3px 8px;font-size:9px">Play</button>
            </div>
        </div>
        
        <!-- Sample Row 3 -->
        <div class="sa-table-row" style="grid-template-columns:80px 2fr 1fr 1fr 1fr 100px">
            <div style="width:40px;height:40px;background:rgba(255,255,255,.05);border-radius:4px;display:flex;align-items:center;justify-content:center;font-size:18px">📄</div>
            <div>
                <div style="font-weight:600;color:#fff;font-size:13px">garden_words_source.pdf</div>
                <div style="font-size:11px;color:rgba(255,255,255,.3)">Uploaded 3 days ago</div>
            </div>
            <span style="font-size:12px;color:rgba(255,255,255,.6)">PDF Upload</span>
            <span style="font-size:12px;color:rgba(255,255,255,.6)">14.5 MB</span>
            <span style="font-size:12px;color:var(--savanna-gold)">Garden Words</span>
            <div style="display:flex;gap:6px">
                <button class="btn btn-ghost btn-sm" style="padding:3px 8px;font-size:9px">View</button>
            </div>
        </div>
    </div>
</div>
