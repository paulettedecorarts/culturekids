<div>
    <div class="cms-header">
        <div>
            <h1 class="cms-page-title">Content Review Queue</h1>
            <div class="cms-breadcrumb">Management · {{ $organization }} · Approval</div>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="cms-flash-success">
            {{ session('message') }}
        </div>
    @endif

    <div class="cms-stats-row" style="grid-template-columns:repeat(auto-fit, minmax(120px, 1fr));">
        <div class="cms-stat">
            <div class="cms-stat-val">{{ $pendingTotal }}</div>
            <div class="cms-stat-label">Total Pending</div>
        </div>
        @foreach($typeLabels as $typeKey => $typeLabel)
            <div class="cms-stat">
                <div class="cms-stat-val">{{ $countsByType[$typeKey] ?? 0 }}</div>
                <div class="cms-stat-label">{{ $typeLabel }}</div>
            </div>
        @endforeach
    </div>

    <div class="cms-asset-table">
        <div class="cms-table-header" style="grid-template-columns:120px 2fr 1fr 100px 180px;">
            <span>Type</span>
            <span>Title</span>
            <span>Updated</span>
            <span>Status</span>
            <span>Actions</span>
        </div>
        @forelse($pendingItems as $item)
            <div class="cms-table-row" style="grid-template-columns:120px 2fr 1fr 100px 180px; cursor:default;">
                <span style="font-size:11px; font-weight:800; text-transform:uppercase; color:var(--cms-text-muted);">{{ $item['type_label'] }}</span>
                <span style="font-weight:700">{{ $item['title'] }}</span>
                <span style="font-size:12px; color:var(--cms-text-muted)">{{ $item['updated_at']?->diffForHumans() }}</span>
                <span style="font-size:11px; font-weight:700; text-transform:capitalize; color:var(--cms-text-muted);">{{ $item['status'] ?? 'published' }}</span>
                <span style="display:flex; gap:6px;">
                    <button
                        class="btn btn-primary btn-sm"
                        wire:click="approve('{{ $item['content_type'] }}', {{ $item['id'] }})"
                        wire:loading.attr="disabled"
                        wire:target="approve('{{ $item['content_type'] }}', {{ $item['id'] }})"
                        wire:loading.class="opacity-50 cursor-not-allowed"
                    >
                        <span wire:loading.remove wire:target="approve('{{ $item['content_type'] }}', {{ $item['id'] }})">Approve</span>
                        <span wire:loading wire:target="approve('{{ $item['content_type'] }}', {{ $item['id'] }})">Approving...</span>
                    </button>
                    <button
                        class="btn btn-ghost btn-sm"
                        wire:click="reject('{{ $item['content_type'] }}', {{ $item['id'] }})"
                        wire:loading.attr="disabled"
                        wire:target="reject('{{ $item['content_type'] }}', {{ $item['id'] }})"
                        wire:loading.class="opacity-50 cursor-not-allowed"
                    >
                        <span wire:loading.remove wire:target="reject('{{ $item['content_type'] }}', {{ $item['id'] }})">Reject</span>
                        <span wire:loading wire:target="reject('{{ $item['content_type'] }}', {{ $item['id'] }})">Rejecting...</span>
                    </button>
                </span>
            </div>
        @empty
            <div style="padding:24px; color:var(--cms-text-muted); font-weight:700; text-align:center;">
                No content awaiting approval across any activity type.
            </div>
        @endforelse
    </div>

    @if($pendingItems->hasPages())
        {{ $pendingItems->links(data: ['scrollTo' => false]) }}
    @endif
</div>
