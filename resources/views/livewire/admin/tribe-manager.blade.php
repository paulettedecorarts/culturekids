<div>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--sp-5);flex-wrap:wrap;gap:var(--sp-3)">
        <div>
            <div class="sa-page-title">Tribe Directory</div>
            <div class="sa-breadcrumb">Super Admin · Platform · All 65+ Tribes</div>
        </div>
        <div style="display:flex;gap:var(--sp-2);align-items:center">
            <span class="sa-badge">⚡ SUPER ADMIN</span>
            <button class="btn btn-primary btn-sm" style="background:var(--clay-red); border:none; color:#fff; padding: var(--sp-2) var(--sp-4); border-radius: var(--r-full); font-weight:700; cursor:pointer;" wire:click="$set('showForm', true)">+ Add Tribe</button>
        </div>
    </div>

    <!-- Stats -->
    <div class="sa-stats-row">
        <div class="sa-stat">
            <div class="sa-stat-val">65</div>
            <div class="sa-stat-label">Total Tribes</div>
            <div class="sa-stat-delta">Out of 65 possible</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">48</div>
            <div class="sa-stat-label">With Content</div>
            <div class="sa-stat-delta">Published</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">17</div>
            <div class="sa-stat-label">Pending</div>
            <div class="sa-stat-delta">Needs Assets</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">23</div>
            <div class="sa-stat-label">Languages</div>
            <div class="sa-stat-delta">Native coverage</div>
        </div>
    </div>

    @if($showForm)
    <div style="background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.07); border-radius:var(--r-xl); padding:var(--sp-6); margin-bottom:var(--sp-6);">
        <h3 style="font-family:var(--font-display); font-size:18px; font-weight:700; margin-bottom:var(--sp-4); color:var(--savanna-gold);">
            {{ $editingTribeId ? 'Edit Tribe' : 'Create New Tribe' }}
        </h3>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--sp-4);">
            <div style="display:flex; flex-direction:column; gap:5px;">
                <label style="font-size:11px; font-weight:700; color:rgba(255,255,255,.3); text-transform:uppercase;">Tribe Name</label>
                <input wire:model="name" type="text" style="background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.1); border-radius:var(--r-sm); padding:var(--sp-2) var(--sp-3); color:#fff; outline:none;">
            </div>
            <div style="display:flex; flex-direction:column; gap:5px;">
                <label style="font-size:11px; font-weight:700; color:rgba(255,255,255,.3); text-transform:uppercase;">Hero Name</label>
                <input wire:model="hero_name" type="text" style="background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.1); border-radius:var(--r-sm); padding:var(--sp-2) var(--sp-3); color:#fff; outline:none;">
            </div>
        </div>
        <div style="margin-top:var(--sp-4); display:flex; gap:var(--sp-3);">
            <button wire:click="save" class="btn btn-sm" style="background:var(--banana-green); border:none; color:#fff; padding: var(--sp-2) var(--sp-5); border-radius:var(--r-full); font-weight:700; cursor:pointer;">Save Tribe</button>
            <button wire:click="$set('showForm', false)" class="btn btn-sm" style="background:rgba(255,255,255,.1); border:none; color:#fff; padding: var(--sp-2) var(--sp-5); border-radius:var(--r-full); font-weight:700; cursor:pointer;">Cancel</button>
        </div>
    </div>
    @endif

    <div class="sa-table-wrap">
        <div class="sa-table-head" style="grid-template-columns:2fr 1fr 1fr 1fr 1fr 90px">
            <span>Tribe</span>
            <span>Language</span>
            <span>Region</span>
            <span>Comics</span>
            <span>Status</span>
            <span>Actions</span>
        </div>

        @foreach($tribes as $tribe)
        <div class="sa-table-row" style="grid-template-columns:2fr 1fr 1fr 1fr 1fr 90px">
            <div style="display:flex;align-items:center;gap:var(--sp-3)">
                <div style="width:36px;height:28px;background:linear-gradient(135deg,{{ $tribe->color ?? '#C44B2B' }},#000);border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0">{{ $tribe->hero_emoji }}</div>
                <div>
                    <div style="font-weight:600;color:#fff;font-size:13px">{{ $tribe->name }}</div>
                    <div style="font-size:11px;color:rgba(255,255,255,.3)">{{ $tribe->hero_name }}</div>
                </div>
            </div>
            <span style="font-size:12px;color:rgba(255,255,255,.6)">Luganda</span> <!-- Temp hardcoded lang -->
            <span style="font-size:12px;color:rgba(255,255,255,.6)">{{ $tribe->region }}</span>
            <span style="font-size:12px;color:#fff;font-weight:700">{{ rand(2, 12) }}</span>
            <span class="status-pill status-published">Published</span>
            <div style="display:flex;gap:6px">
                <button wire:click="edit({{ $tribe->id }})" class="btn btn-ghost btn-sm" style="padding:3px 8px;font-size:9px">Edit</button>
            </div>
        </div>
        @endforeach
    </div>

    <div style="margin-top:var(--sp-4);">
        {{ $tribes->links() }}
    </div>
</div>

