<div>
    <div class="cms-header">
        <div>
            <h1 class="cms-page-title">Content Review Queue</h1>
            <div class="cms-breadcrumb">Management · {{ $organization }} · Approval</div>
        </div>
    </div>

    @if (session()->has('message'))
        <div style="margin-bottom:12px; padding:10px 14px; border:1px solid #DCFCE7; background:#F0FDF4; color:#166534; border-radius:10px; font-size:12px; font-weight:700;">
            {{ session('message') }}
        </div>
    @endif

    <div class="cms-stats-row">
        <div class="cms-stat"><div class="cms-stat-val">{{ $reviewComics->count() }}</div><div class="cms-stat-label">Comics In Review</div></div>
        <div class="cms-stat"><div class="cms-stat-val">{{ $reviewSongs->count() }}</div><div class="cms-stat-label">Songs In Review</div></div>
        <div class="cms-stat"><div class="cms-stat-val">{{ $reviewComics->count() + $reviewSongs->count() }}</div><div class="cms-stat-label">Total Pending</div></div>
        <div class="cms-stat"><div class="cms-stat-val">Ready</div><div class="cms-stat-label">Approval Queue</div></div>
    </div>

    <div style="display:grid; grid-template-columns:1fr 1fr; gap:var(--sp-6);">
        <div class="cms-asset-table">
            <div class="cms-table-header" style="grid-template-columns:2fr 1fr 180px;">
                <span>Comics Awaiting Approval</span><span>Updated</span><span>Actions</span>
            </div>
            @forelse($reviewComics as $comic)
                <div class="cms-table-row" style="grid-template-columns:2fr 1fr 180px;">
                    <span style="font-weight:700">{{ $comic->title }}</span>
                    <span style="font-size:12px; color:var(--stone)">{{ $comic->updated_at?->diffForHumans() }}</span>
                    <span style="display:flex; gap:6px;">
                        <button
                            class="btn btn-primary btn-sm"
                            wire:click="approveComic({{ $comic->id }})"
                            wire:loading.attr="disabled"
                            wire:target="approveComic({{ $comic->id }})"
                            wire:loading.class="opacity-50 cursor-not-allowed"
                        >
                            <span wire:loading.remove wire:target="approveComic({{ $comic->id }})">Approve</span>
                            <span wire:loading wire:target="approveComic({{ $comic->id }})">Approving...</span>
                        </button>
                        <button
                            class="btn btn-ghost btn-sm"
                            wire:click="rejectComic({{ $comic->id }})"
                            wire:loading.attr="disabled"
                            wire:target="rejectComic({{ $comic->id }})"
                            wire:loading.class="opacity-50 cursor-not-allowed"
                        >
                            <span wire:loading.remove wire:target="rejectComic({{ $comic->id }})">Reject</span>
                            <span wire:loading wire:target="rejectComic({{ $comic->id }})">Rejecting...</span>
                        </button>
                    </span>
                </div>
            @empty
                <div style="padding:16px; color:var(--stone); font-weight:700;">No comics awaiting review.</div>
            @endforelse
        </div>

        <div class="cms-asset-table">
            <div class="cms-table-header" style="grid-template-columns:2fr 1fr 180px;">
                <span>Songs Awaiting Approval</span><span>Updated</span><span>Actions</span>
            </div>
            @forelse($reviewSongs as $song)
                <div class="cms-table-row" style="grid-template-columns:2fr 1fr 180px;">
                    <span style="font-weight:700">{{ $song->title }}</span>
                    <span style="font-size:12px; color:var(--stone)">{{ $song->updated_at?->diffForHumans() }}</span>
                    <span style="display:flex; gap:6px;">
                        <button
                            class="btn btn-primary btn-sm"
                            wire:click="approveSong({{ $song->id }})"
                            wire:loading.attr="disabled"
                            wire:target="approveSong({{ $song->id }})"
                            wire:loading.class="opacity-50 cursor-not-allowed"
                        >
                            <span wire:loading.remove wire:target="approveSong({{ $song->id }})">Approve</span>
                            <span wire:loading wire:target="approveSong({{ $song->id }})">Approving...</span>
                        </button>
                        <button
                            class="btn btn-ghost btn-sm"
                            wire:click="rejectSong({{ $song->id }})"
                            wire:loading.attr="disabled"
                            wire:target="rejectSong({{ $song->id }})"
                            wire:loading.class="opacity-50 cursor-not-allowed"
                        >
                            <span wire:loading.remove wire:target="rejectSong({{ $song->id }})">Reject</span>
                            <span wire:loading wire:target="rejectSong({{ $song->id }})">Rejecting...</span>
                        </button>
                    </span>
                </div>
            @empty
                <div style="padding:16px; color:var(--stone); font-weight:700;">No songs awaiting review.</div>
            @endforelse
        </div>
    </div>
</div>
