<div>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--sp-5);flex-wrap:wrap;gap:var(--sp-3)">
        <div>
            <div class="sa-page-title">Assets</div>
            <div class="sa-breadcrumb">Content · Local storage index (uploaded files)</div>
        </div>
        <a class="btn btn-primary btn-sm" href="{{ route('cms.editor.story-packs') }}" style="background:var(--banana-green); text-decoration:none;">+ Upload via Story Pack</a>
    </div>

    <div class="sa-stats-row">
        <div class="sa-stat">
            <div class="sa-stat-val">{{ $stats['total_count'] }}</div>
            <div class="sa-stat-label">Total Assets</div>
            <div class="sa-stat-delta">Indexed from local storage</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">{{ $stats['image_count'] }}</div>
            <div class="sa-stat-label">Images / SVG</div>
            <div class="sa-stat-delta">Covers & extracted panels</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">{{ $stats['audio_count'] }}</div>
            <div class="sa-stat-label">Audio</div>
            <div class="sa-stat-delta">Panel narration & songs</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">{{ $stats['total_storage_human'] }}</div>
            <div class="sa-stat-label">Local Storage Used</div>
            <div class="sa-stat-delta">`storage/app/public` scoped assets</div>
        </div>
    </div>

    @if (session()->has('message'))
        <div style="background:rgba(74,124,89,.12);border:1px solid rgba(74,124,89,.35);color:var(--banana-light);padding:10px 14px;border-radius:10px;margin-bottom:var(--sp-4);font-size:12px;font-weight:700">
            {{ session('message') }}
        </div>
    @endif

    @if($stats['usage_alert'] !== 'ok')
        <div style="background:{{ $stats['usage_alert'] === 'critical' ? 'rgba(196,75,43,.14)' : 'rgba(232,135,42,.14)' }};border:1px solid {{ $stats['usage_alert'] === 'critical' ? 'rgba(196,75,43,.45)' : 'rgba(232,135,42,.45)' }};color:#fff;padding:10px 14px;border-radius:10px;margin-bottom:var(--sp-4);font-size:12px;font-weight:700">
            Storage usage is {{ $stats['usage_percent'] }}% of quota ({{ $stats['total_storage_human'] }} / {{ $stats['quota_human'] }}).
        </div>
    @endif

    <div style="margin-bottom:var(--sp-4)">
        <div style="font-size:11px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;color:rgba(255,255,255,.45);margin-bottom:10px">
            Folder Usage
        </div>
        <div class="sa-stats-row" style="grid-template-columns:repeat(4,minmax(0,1fr));gap:var(--sp-3);margin-bottom:0">
            @forelse($folderBreakdown as $folder => $info)
                <div class="sa-stat" style="padding:16px 18px">
                    <div style="font-size:11px;color:rgba(255,255,255,.55);font-weight:700;margin-bottom:6px">{{ $folder }}</div>
                    <div style="font-size:24px;font-weight:800;color:#fff;line-height:1.1">{{ $info['count'] }}</div>
                    <div style="font-size:10px;color:rgba(255,255,255,.38);font-weight:700;text-transform:uppercase;letter-spacing:.7px;margin-top:4px">Files</div>
                    <div style="font-size:12px;color:var(--savanna-gold);font-weight:700;margin-top:6px">{{ $info['size_human'] }}</div>
                </div>
            @empty
                <div class="sa-stat" style="grid-column:1/-1;padding:16px 18px;color:rgba(255,255,255,.5)">No folder usage to display.</div>
            @endforelse
        </div>
    </div>

    <div style="display:flex; gap:var(--sp-2); margin-bottom:var(--sp-4); align-items:center; flex-wrap:wrap;">
        <input
            type="text"
            wire:model.live.debounce.300ms="search"
            placeholder="Search assets..."
            style="background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.1); border-radius:var(--r-full); padding:var(--sp-2) var(--sp-4); color:#fff; font-size:12px; outline:none; min-width:250px;"
        >
        <select
            wire:model.live="typeFilter"
            style="background:rgba(255,255,255,.05); border:1px solid rgba(255,255,255,.1); border-radius:var(--r-full); padding:var(--sp-2) var(--sp-4); color:#fff; font-size:12px; outline:none;"
        >
            <option value="">All Types</option>
            <option value="image">Images</option>
            <option value="audio">Audio</option>
            <option value="pdf">PDFs</option>
        </select>
        @if($isSuperAdmin)
            <label style="display:flex;align-items:center;gap:8px;color:rgba(255,255,255,.75);font-size:12px;padding:0 6px">
                <input type="checkbox" wire:model.live="showOrphans">
                Show orphan files ({{ $stats['orphan_count'] }})
            </label>
        @endif
        <button type="button" wire:click="exportCsv" class="btn btn-ghost btn-sm" style="text-decoration:none">Export CSV</button>
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

        @forelse($assets as $asset)
            <div class="sa-table-row" style="grid-template-columns:80px 2fr 1fr 1fr 1fr 100px">
                <div style="width:40px;height:40px;background:rgba(255,255,255,.05);border-radius:4px;display:flex;align-items:center;justify-content:center;font-size:18px">{{ $asset['icon'] }}</div>
                <div>
                    <div style="font-weight:600;color:#fff;font-size:13px">{{ $asset['name'] }}</div>
                    <div style="font-size:11px;color:rgba(255,255,255,.3)">{{ $asset['uploaded_label'] }}</div>
                </div>
                <span style="font-size:12px;color:rgba(255,255,255,.6)">{{ $asset['type_label'] }}</span>
                <span style="font-size:12px;color:rgba(255,255,255,.6)">{{ $asset['size_human'] }}</span>
                <span style="font-size:12px;color:{{ $asset['is_orphan'] ? '#fda4af' : 'var(--savanna-gold)' }}">
                    {{ $asset['is_orphan'] ? 'Orphan' : $asset['linked_pack'] }}
                </span>
                <div style="display:flex;gap:6px">
                    <a href="{{ $asset['url'] }}" target="_blank" rel="noopener" class="btn btn-ghost btn-sm" style="padding:3px 8px;font-size:9px;text-decoration:none">Open</a>
                    @if($asset['is_orphan'])
                        <button
                            type="button"
                            wire:click="deleteAsset('{{ $asset['path'] }}')"
                            wire:confirm="Delete this orphan asset from local storage?"
                            class="btn btn-ghost btn-sm"
                            style="padding:3px 8px;font-size:9px;color:#fecaca;border-color:rgba(196,75,43,.4)"
                        >
                            Delete
                        </button>
                    @endif
                </div>
            </div>
            @if(!empty($asset['linked_refs']))
                <div class="sa-table-row" style="grid-template-columns:1fr;padding-top:0">
                    <span style="font-size:11px;color:rgba(255,255,255,.45)">Linked refs: {{ implode(', ', $asset['linked_refs']) }}</span>
                </div>
            @endif
        @empty
            <div style="padding:22px;color:rgba(255,255,255,.5)">No assets found in the scanned storage paths.</div>
        @endforelse
    </div>

    <div style="margin-top:12px">
        {{ $assets->links() }}
    </div>
</div>
