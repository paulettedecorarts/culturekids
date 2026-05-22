<div>
    <div class="cms-header">
        <div>
            <h1 class="cms-page-title">Cultural Translations</h1>
            <div class="cms-breadcrumb">Content · Language · Mapping</div>
        </div>
        <div style="display:flex; gap:12px">
            <button class="btn btn-ghost btn-sm">Export CSV</button>
            <button class="btn btn-primary btn-sm">+ Add Language</button>
        </div>
    </div>

    <!-- Language stats row -->
    <div class="cms-stats-row">
        <div class="cms-stat">
            <div class="sa-stat-val" style="font-size:32px; font-weight:800; color:var(--ink)">23</div>
            <div class="cms-stat-label">Active Languages</div>
        </div>
        <div class="cms-stat">
            <div class="sa-stat-val" style="font-size:32px; font-weight:800; color:var(--ink)">84%</div>
            <div class="cms-stat-label">Coverage Done</div>
        </div>
        <div class="cms-stat">
            <div class="sa-stat-val" style="font-size:32px; font-weight:800; color:var(--ink)">1,200</div>
            <div class="cms-stat-label">Phrases Mapped</div>
        </div>
        <div class="cms-stat">
            <div class="sa-stat-val" style="font-size:32px; font-weight:800; color:var(--ink)">41</div>
            <div class="cms-stat-label">Pending Review</div>
        </div>
    </div>

    <div class="cms-asset-table">
        <div class="cms-table-header" style="grid-template-columns:2fr 1fr 1fr 1fr 100px">
            <span>{{ heritage('people') }} Context</span>
            <span>Primary Lang</span>
            <span>Alt Lang</span>
            <span>Completeness</span>
            <span>Actions</span>
        </div>

        @foreach(['Buganda', 'Acholi', 'Basoga', 'Iteso'] as $t)
        <div class="cms-table-row" style="grid-template-columns:2fr 1fr 1fr 1fr 100px">
            <div style="display:flex; align-items:center; gap:16px;">
                 <div class="cms-asset-thumb" style="background:var(--cream-mid); color:var(--ink); font-size:14px; font-weight:800">{{ substr($t,0,2) }}</div>
                 <div>
                    <div class="cms-asset-name">{{ $t }}</div>
                    <div class="cms-asset-sub">{{ rand(140, 260) }} Phrases</div>
                 </div>
            </div>
            <span style="font-size:12px; font-weight:700; color:var(--ink)">{{ ['Luganda', 'Luo', 'Lusoga', 'Ateso'][$loop->index] }}</span>
            <span style="font-size:12px; font-weight:600; color:var(--stone)">English · Swahili</span>
            <div>
                 @php $p = rand(60, 100); @endphp
                 <div style="display:flex; align-items:center; gap:8px;">
                     <div style="flex:1; height:6px; background:var(--cream-mid); border-radius:3px; overflow:hidden;">
                        <div style="width:{{ $p }}%; height:100%; background:{{ $p == 100 ? 'var(--banana-green)' : 'var(--sunfire)' }};"></div>
                     </div>
                     <span style="font-size:11px; font-weight:800; color:var(--ink)">{{ $p }}%</span>
                 </div>
            </div>
            <div style="display:flex; gap:8px"><button class="btn btn-ghost btn-sm" style="padding:4px 12px; font-size:10px">Edit</button></div>
        </div>
        @endforeach
    </div>
</div>
