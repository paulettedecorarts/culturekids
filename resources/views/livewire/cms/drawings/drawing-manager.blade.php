<div class="drawings-manager-page">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--sp-5);gap:var(--sp-3);flex-wrap:wrap">
        <div>
            <div class="sa-page-title">Drawing Activities</div>
            <div class="sa-breadcrumb">Coloring pages, hero drawings, design tools, and creative activities</div>
        </div>
        <a href="{{ route($routePrefix . '.drawings.create') }}" class="btn btn-primary" style="padding:12px 28px; border-radius:14px; font-weight:800; font-size:13px; box-shadow: 0 8px 24px rgba(196,75,43,0.3); text-decoration:none">
            + Create Drawing
        </a>
    </div>

    @if(session()->has('message'))
        <div style="background:rgba(74,124,89,.12);border:1px solid rgba(74,124,89,.35);color:var(--banana-light);padding:10px 14px;border-radius:10px;margin-bottom:var(--sp-4);font-size:12px;font-weight:700">
            {{ session('message') }}
        </div>
    @endif

    <div class="sa-stats-row" style="grid-template-columns: repeat(4, minmax(0,1fr)); gap: var(--sp-3); margin-bottom: var(--sp-4)">
        <div class="sa-stat">
            <div class="sa-stat-val">{{ $this->drawings->total() }}</div>
            <div class="sa-stat-label">Total</div>
            <div class="sa-stat-delta">All drawings</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">{{ \App\Models\Drawing::where('status', 'published')->count() }}</div>
            <div class="sa-stat-label">Published</div>
            <div class="sa-stat-delta">Live activities</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">{{ \App\Models\Drawing::where('drawing_type', 'coloring')->count() }}</div>
            <div class="sa-stat-label">Coloring Pages</div>
            <div class="sa-stat-delta">Fill-in activities</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">{{ \App\Models\Drawing::where('drawing_type', 'hero_drawing')->count() }}</div>
            <div class="sa-stat-label">Hero Drawings</div>
            <div class="sa-stat-delta">Character art</div>
        </div>
    </div>

    <div class="cms-toolbar-flex" style="display:flex;gap:var(--sp-3);margin-bottom:var(--sp-4);flex-wrap:wrap">
        <input wire:model.live.debounce.300ms="search" placeholder="Search drawings..." style="padding:8px 14px;border-radius:var(--r-full);border:1px solid var(--cms-input-border);background:var(--cms-input-bg);color:var(--cms-text);font-family:var(--font-admin);font-size:12px;outline:none;flex:1;min-width:180px">
        
        <select wire:model.live="typeFilter" style="padding:8px 14px;border-radius:var(--r-full);border:1px solid var(--cms-input-border);background:var(--cms-input-bg);color:var(--cms-text);font-family:var(--font-admin);font-size:12px;outline:none">
            <option value="">All Types</option>
            <option value="coloring">Coloring Page</option>
            <option value="hero_drawing">Hero Drawing</option>
            <option value="design_tool">Design Tool</option>
            <option value="free_draw">Free Drawing</option>
        </select>
        
        <select wire:model.live="tribeFilter" style="padding:8px 14px;border-radius:var(--r-full);border:1px solid var(--cms-input-border);background:var(--cms-input-bg);color:var(--cms-text);font-family:var(--font-admin);font-size:12px;outline:none">
            <option value="">All Tribes</option>
            @foreach($this->tribes as $tribe)
                <option value="{{ $tribe->id }}">{{ $tribe->name }}</option>
            @endforeach
        </select>
        
        <select wire:model.live="statusFilter" style="padding:8px 14px;border-radius:var(--r-full);border:1px solid var(--cms-input-border);background:var(--cms-input-bg);color:var(--cms-text);font-family:var(--font-admin);font-size:12px;outline:none">
            <option value="">All Status</option>
            <option value="published">Published</option>
            <option value="draft">Draft</option>
            <option value="archived">Archived</option>
        </select>
    </div>

    <div class="sa-table-wrap">
        <div class="sa-table-head" style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr 1fr 1fr;gap:var(--sp-3);padding:12px 16px;background:var(--cms-surface-raised);border-radius:8px;font-size:11px;font-weight:700;color:var(--cms-text-muted);text-transform:uppercase;letter-spacing:.5px">
            <span>Drawing</span>
            <span>Type</span>
            <span>Tribe</span>
            <span>Status</span>
            <span>Age</span>
            <span>Actions</span>
        </div>

        @forelse($this->drawings as $drawing)
            <div class="sa-table-row" style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr 1fr 1fr;gap:var(--sp-3);padding:12px 16px;border-bottom:1px solid var(--cms-border-subtle);align-items:center">
                <div style="display:flex;align-items:center;gap:12px;min-width:0">
                    <div style="font-size:20px;width:32px;text-align:center">
                        @if($drawing->drawing_type === 'coloring')
                            🎨
                        @elseif($drawing->drawing_type === 'hero_drawing')
                            🦸
                        @elseif($drawing->drawing_type === 'design_tool')
                            🛠️
                        @else
                            ✏️
                        @endif
                    </div>
                    <div style="min-width:0">
                        <div style="font-weight:700;color:var(--cms-text);font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $drawing->title }}</div>
                        <div style="font-size:11px;color:var(--cms-text-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $drawing->description ?: 'No description' }}</div>
                    </div>
                </div>
                
                <span style="background:rgba(74,124,89,.2);color:#6FA882;padding:2px 8px;border-radius:999px;font-size:9px;font-weight:700;text-transform:capitalize">{{ $drawing->drawing_type_display }}</span>
                
                <span style="font-size:12px;color:var(--cms-text-muted)">{{ $drawing->tribe->name }}</span>
                
                <span style="padding:2px 8px;border-radius:12px;font-size:10px;font-weight:700;
                    {{ $drawing->status === 'published' ? 'background:rgba(74,124,89,.2);color:#4A7C59;border:1px solid rgba(74,124,89,.35)' : 
                       ($drawing->status === 'draft' ? 'background:rgba(212,160,23,.2);color:#F2CB5A;border:1px solid rgba(212,160,23,.45)' : 'background:var(--cms-surface-raised);color:var(--cms-text-muted);border:1px solid var(--cms-border)') }}">
                    {{ ucfirst($drawing->status) }}
                </span>
                
                <span style="font-size:12px;color:var(--cms-text-muted)">{{ $drawing->age_range }}</span>
                
                <div style="display:flex;gap:6px">
                    <a href="{{ route($routePrefix . '.drawings.play', $drawing->id) }}" target="_blank" class="btn btn-sm" style="background:rgba(74,124,89,.18);color:#6FA882;border:1px solid rgba(74,124,89,.3);padding:4px 10px;border-radius:var(--r-full);font-size:10px;font-weight:700;text-decoration:none">Play</a>
                    <a href="{{ route($routePrefix . '.drawings.show', $drawing->id) }}" class="btn btn-sm" style="background:rgba(212,160,23,.18);color:#F2CB5A;border:1px solid rgba(212,160,23,.3);padding:4px 10px;border-radius:var(--r-full);font-size:10px;font-weight:700;text-decoration:none">View</a>
                    <a href="{{ route($routePrefix . '.drawings.edit', $drawing->id) }}" class="btn btn-sm" style="background:rgba(59,130,246,.18);color:#60A5FA;border:1px solid rgba(59,130,246,.3);padding:4px 10px;border-radius:var(--r-full);font-size:10px;font-weight:700;text-decoration:none">Edit</a>
                </div>
            </div>
        @empty
            <div style="padding:40px;text-align:center;color:var(--cms-text-muted)">
                <div style="font-size:48px;margin-bottom:16px">🎨</div>
                <div style="font-size:16px;font-weight:600;margin-bottom:8px">No drawing activities found</div>
                <div style="font-size:12px;margin-bottom:20px">Create your first drawing activity to get started</div>
                <a href="{{ route($routePrefix . '.drawings.create') }}" class="btn btn-primary" style="text-decoration:none">Create Drawing Activity</a>
            </div>
        @endforelse
    </div>

    <div style="margin-top:12px">
        {{ $this->drawings->links() }}
    </div>
</div>