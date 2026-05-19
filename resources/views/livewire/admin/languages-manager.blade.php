<div class="languages-registry-page">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--sp-5);flex-wrap:wrap;gap:var(--sp-3)">
        <div>
            <div class="sa-page-title">Language Registry</div>
            <div class="sa-breadcrumb">Super Admin · Platform · Dialect and translation coverage</div>
        </div>
        <a class="btn btn-primary btn-sm" href="{{ route('admin.languages.create') }}" style="background:var(--clay-red); border:none; color:var(--cms-text); padding: var(--sp-2) var(--sp-4); border-radius: var(--r-full); font-weight:700; text-decoration:none">+ Add Language</a>
    </div>

    @if(session()->has('message'))
        <div style="background:rgba(74,124,89,.12);border:1px solid rgba(74,124,89,.35);color:var(--banana-light);padding:10px 14px;border-radius:10px;margin-bottom:var(--sp-4);font-size:12px;font-weight:700">
            {{ session('message') }}
        </div>
    @endif

    <div class="sa-stats-row" style="grid-template-columns:repeat(4,1fr);gap:var(--sp-3);margin-bottom:var(--sp-5)">
        <div class="sa-stat"><div class="sa-stat-val">{{ $stats['total'] }}</div><div class="sa-stat-label">Total Languages</div></div>
        <div class="sa-stat"><div class="sa-stat-val">{{ $stats['active'] }}</div><div class="sa-stat-label">Active</div></div>
        <div class="sa-stat"><div class="sa-stat-val">{{ $stats['avg_coverage'] }}%</div><div class="sa-stat-label">Avg Coverage</div></div>
        <div class="sa-stat"><div class="sa-stat-val">{{ $stats['audio'] }}</div><div class="sa-stat-label">Audio Packs</div></div>
    </div>

    <div style="display:flex; gap:var(--sp-2); margin-bottom:var(--sp-4); flex-wrap:wrap;">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search name, native name or code..." style="background:var(--cms-input-bg); border:1px solid var(--cms-border); border-radius:var(--r-full); padding:var(--sp-2) var(--sp-4); color:var(--cms-text); font-size:12px; outline:none; min-width:250px;">
        <select wire:model.live="statusFilter" style="background:var(--cms-input-bg); border:1px solid var(--cms-border); border-radius:var(--r-full); padding:var(--sp-2) var(--sp-4); color:var(--cms-text); font-size:12px; outline:none;">
            <option value="all">All status</option>
            <option value="verified">Verified</option>
            <option value="partial">Partial</option>
            <option value="pending">Pending</option>
        </select>
    </div>

    <div class="sa-table-wrap">
        <div class="sa-table-head" style="grid-template-columns:1.4fr 1fr 1.2fr 1fr minmax(120px, auto)">
            <span>Language / Dialect</span>
            <span>Code</span>
            <span>Coverage</span>
            <span>Status</span>
            <span>Actions</span>
        </div>

        @forelse($languages as $language)
            <div class="sa-table-row" style="grid-template-columns:1.4fr 1fr 1.2fr 1fr minmax(120px, auto)">
                <div style="display:flex;align-items:center;gap:12px">
                    <span style="font-size:20px">{{ $language->flag_emoji ?: '🗣️' }}</span>
                    <div>
                        <div style="font-weight:700;color:var(--cms-text);font-size:13px">{{ $language->name }}</div>
                        <div style="font-size:11px;color:var(--cms-text-muted)">{{ $language->native_name ?: '—' }}</div>
                    </div>
                </div>
                <code style="background:var(--cms-surface-raised);padding:2px 6px;border-radius:4px;font-size:11px;color:var(--savanna-gold)">{{ $language->code }}</code>
                <div style="display:flex;align-items:center;gap:10px">
                    <div style="flex:1;height:4px;background:var(--cms-surface-raised);border-radius:2px;overflow:hidden">
                        <div style="width:{{ $language->translation_coverage }}%;height:100%;background:{{ $language->translation_coverage >= 80 ? 'var(--banana-green)' : ($language->translation_coverage >= 50 ? 'var(--sunfire)' : 'var(--clay-red)') }}"></div>
                    </div>
                    <span style="font-size:10px;font-weight:700;color:var(--cms-text)">{{ $language->translation_coverage }}%</span>
                </div>
                <span class="status-pill {{ $language->status === 'verified' ? 'status-published' : ($language->status === 'partial' ? 'status-review' : 'status-draft') }}">{{ ucfirst($language->status) }}</span>
                <div><a class="sa-table-action sa-table-action--accent" href="{{ route('admin.languages.detail', ['id' => $language->id]) }}">Details</a></div>
            </div>
        @empty
            <div style="padding:20px;color:var(--cms-text-muted)">No languages found.</div>
        @endforelse
    </div>

    <div style="margin-top:12px">
        {{ $languages->links() }}
    </div>
</div>

