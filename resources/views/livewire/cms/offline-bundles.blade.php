<div>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--sp-5);flex-wrap:wrap;gap:var(--sp-3)">
        <div>
            <div class="sa-page-title">Offline bundles</div>
            <div class="sa-breadcrumb">Published story packs · .ckb packages for low-connectivity sync (spec: BuildOfflineBundle)</div>
        </div>
    </div>

    @if (session()->has('message'))
        <div style="background:rgba(74,124,89,0.12);border:1px solid rgba(74,124,89,0.35);color:var(--banana-light);padding:10px 14px;border-radius:10px;margin-bottom:var(--sp-4);font-size:12px;font-weight:700">
            {{ session('message') }}
        </div>
    @endif

    <div class="sa-table-wrap">
        <div class="sa-table-head" style="display:grid;grid-template-columns:2fr 1fr 1fr 120px;gap:var(--sp-3)">
            <span>Story pack</span>
            <span>Bundle</span>
            <span>Updated</span>
            <span>Actions</span>
        </div>
        @forelse ($comics as $comic)
            <div class="sa-table-row" style="display:grid;grid-template-columns:2fr 1fr 1fr 120px;gap:var(--sp-3);align-items:center">
                <div>
                    <div style="font-weight:700;color:#fff;font-size:14px">{{ $comic->title }}</div>
                    <div style="font-size:11px;color:rgba(255,255,255,.35)">{{ $comic->tribe?->name ?? '—' }}</div>
                </div>
                <div style="font-size:11px;color:rgba(255,255,255,.55)">
                    @if ($comic->bundle_path)
                        <span style="color:var(--banana-mid)">Ready</span>
                        <div style="font-family:monospace;font-size:9px;opacity:.7;margin-top:2px">{{ \Illuminate\Support\Str::limit($comic->bundle_hash ?? '—', 12) }}</div>
                    @else
                        <span style="color:var(--savanna-gold)">Not built</span>
                    @endif
                </div>
                <div style="font-size:12px;color:rgba(255,255,255,.45)">{{ $comic->updated_at?->diffForHumans() }}</div>
                <div>
                    <button type="button" wire:click="rebuild({{ $comic->id }})" wire:loading.attr="disabled" class="btn btn-sm" style="background:rgba(212,160,23,.18);color:#F2CB5A;border:1px solid rgba(212,160,23,.3);padding:6px 12px;border-radius:var(--r-full);font-size:10px;font-weight:700;cursor:pointer;font-family:var(--font-admin)">
                        Rebuild
                    </button>
                </div>
            </div>
        @empty
            <div style="padding:var(--sp-8);text-align:center;color:rgba(255,255,255,.35);font-size:14px">No published story packs yet.</div>
        @endforelse
    </div>
</div>
