<div>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--sp-5);flex-wrap:wrap;gap:var(--sp-3)">
        <div>
            <div class="sa-page-title">Audit Logs</div>
            <div class="sa-breadcrumb">Super Admin · Security & Compliance</div>
        </div>
        <div style="display:flex;gap:var(--sp-2);align-items:center">
            <span class="sa-badge">📋 AUDIT TRAIL</span>
        </div>
    </div>

    <!-- Filters -->
    <div style="display:grid;grid-template-columns:2fr 1fr 1fr;gap:var(--sp-3);margin-bottom:var(--sp-4)">
        <input 
            type="text" 
            wire:model.live.debounce.300ms="search" 
            placeholder="Search by action, resource, or user email..."
            style="background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:var(--r-sm);padding:var(--sp-2) var(--sp-3);color:#fff;font-size:14px;font-family:var(--font-admin)"
        />
        
        <select 
            wire:model.live="actionFilter"
            style="background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:var(--r-sm);padding:var(--sp-2) var(--sp-3);color:#fff;font-size:14px;font-family:var(--font-admin)"
        >
            <option value="">All Actions</option>
            @foreach($actions as $action)
                <option value="{{ $action }}">{{ $action }}</option>
            @endforeach
        </select>

        <input 
            type="date" 
            wire:model.live="dateFilter"
            style="background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:var(--r-sm);padding:var(--sp-2) var(--sp-3);color:#fff;font-size:14px;font-family:var(--font-admin)"
        />
    </div>

    @if(session('message'))
        <div style="background:rgba(111,168,130,.2);border:1px solid rgba(111,168,130,.4);color:var(--banana-mid);padding:var(--sp-3) var(--sp-4);border-radius:var(--r-sm);margin-bottom:var(--sp-4);font-size:14px">
            {{ session('message') }}
        </div>
    @endif

    <!-- Audit Logs Table -->
    <div class="sa-table-wrap">
        <div class="sa-table-head" style="grid-template-columns:130px 1fr 1fr 1fr 100px">
            <span>Timestamp</span>
            <span>Action</span>
            <span>User</span>
            <span>Resource</span>
            <span>Status</span>
        </div>
        
        @forelse($logs as $log)
            <div class="sa-table-row" style="grid-template-columns:130px 1fr 1fr 1fr 100px">
                <span style="font-size:10px;color:rgba(255,255,255,.35)">
                    {{ $log->created_at->format('Y-m-d H:i') }}
                </span>
                
                <span style="background:{{ $log->action === 'DELETE' ? '#FEE2E2' : ($log->action === 'IMPERSONATE' ? '#E0E7FF' : ($log->action === 'MODULE_TOGGLE' ? '#FEF3C7' : '#DBEAFE')) }};color:{{ $log->action === 'DELETE' ? '#991B1B' : ($log->action === 'IMPERSONATE' ? '#3730A3' : ($log->action === 'MODULE_TOGGLE' ? '#92400E' : '#1E40AF')) }};padding:2px 7px;border-radius:4px;font-size:9px;font-weight:700;display:inline-flex">
                    {{ $log->action }}
                </span>
                
                <div>
                    <div style="color:rgba(255,255,255,.7);font-size:12px">
                        {{ $log->user?->email ?? 'System' }}
                    </div>
                    @if($log->impersonator_id)
                        <div style="font-size:10px;color:rgba(255,255,255,.3)">
                            🎭 via {{ $log->impersonator?->email }}
                        </div>
                    @endif
                </div>
                
                <span style="color:rgba(255,255,255,.4);font-size:11px;font-family:monospace">
                    {{ $log->resource ?? '—' }}
                </span>
                
                <span class="status-pill status-{{ $log->status === 'success' ? 'published' : ($log->status === 'failed' ? 'draft' : 'review') }}">
                    {{ ucfirst($log->status) }}
                </span>
            </div>
        @empty
            <div class="sa-table-row" style="grid-template-columns:1fr">
                <div style="text-align:center;color:rgba(255,255,255,.3);padding:var(--sp-4)">
                    No audit logs found.
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div style="margin-top:var(--sp-4)">
        {{ $logs->links() }}
    </div>
</div>
