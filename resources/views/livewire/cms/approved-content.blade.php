<div>
    <div class="cms-header">
        <div>
            <h1 class="cms-page-title">Approved Content</h1>
            <div class="cms-breadcrumb">Management · {{ $organization }} · Published Library</div>
        </div>
    </div>

    <div class="cms-stats-row" style="grid-template-columns:repeat(auto-fit, minmax(120px, 1fr));">
        <div class="cms-stat">
            <div class="cms-stat-val">{{ $approvedTotal }}</div>
            <div class="cms-stat-label">Total Approved</div>
        </div>
        @foreach($typeLabels as $typeKey => $typeLabel)
            <div class="cms-stat">
                <div class="cms-stat-val">{{ $countsByType[$typeKey] ?? 0 }}</div>
                <div class="cms-stat-label">{{ $typeLabel }}</div>
            </div>
        @endforeach
    </div>

    <div class="cms-asset-table">
        <div class="cms-table-header" style="grid-template-columns:120px 2fr 1fr 120px;">
            <span>Type</span>
            <span>Title</span>
            <span>Approved</span>
            <span>Action</span>
        </div>
        @forelse($approvedItems as $item)
            <div class="cms-table-row" style="grid-template-columns:120px 2fr 1fr 120px; cursor:default;">
                <span style="font-size:11px; font-weight:800; text-transform:uppercase; color:var(--cms-text-muted);">{{ $item['type_label'] }}</span>
                <span>
                    <div style="font-weight:700">{{ $item['title'] }}</div>
                    <div style="font-size:11px; color:var(--cms-text-muted)">Tribe: {{ $item['tribe'] ?? '—' }} · By {{ $item['approved_by'] }}</div>
                </span>
                <span style="font-size:12px; color:var(--cms-text-muted)">{{ $item['approved_at']?->diffForHumans() }}</span>
                @if($item['view_url'])
                    <a class="btn btn-primary btn-sm" href="{{ $item['view_url'] }}" style="text-decoration:none; justify-content:center;">View</a>
                @else
                    <span style="font-size:11px; color:var(--cms-text-muted); font-weight:600;">—</span>
                @endif
            </div>
        @empty
            <div style="padding:24px; color:var(--cms-text-muted); font-weight:700; text-align:center;">
                No approved content yet across any activity type.
            </div>
        @endforelse
    </div>

    @if($approvedItems->hasPages())
        <div style="margin-top:var(--sp-6);">
            {{ $approvedItems->links(data: ['scrollTo' => false]) }}
        </div>
    @endif
</div>
