<div class="sa-tribe-manager-view">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--sp-8)">
        <div>
            <h1 class="sa-page-title">Heritage Tribes</h1>
            <div class="sa-breadcrumb">Culture Management · Ancestral Registry</div>
        </div>
        <a href="{{ route($routePrefix . '.tribes.create') }}" class="btn btn-primary" style="padding:12px 28px; border-radius:14px; font-weight:800; font-size:13px; box-shadow: 0 8px 24px rgba(196,75,43,0.3); text-decoration:none">+ Register New Tribe</a>
    </div>

    @if (session()->has('message'))
        <div style="background:rgba(74,124,89,0.1); border:1px solid rgba(74,124,89,0.3); color:var(--banana-light); padding:16px 24px; border-radius:16px; margin-bottom:32px; font-size:13px; font-weight:700">
            ✨ {{ session('message') }}
        </div>
    @endif

    <!-- Filters Bar -->
    <div style="background:var(--cms-surface); border:1px solid var(--cms-border); border-radius:32px; padding:32px; margin-bottom:40px">
        <div style="position:relative">
            <span style="position:absolute; left:20px; top:50%; transform:translateY(-50%); opacity:0.3">🔍</span>
            <input 
                type="text" 
                wire:model.live.debounce.300ms="search" 
                placeholder="Search tribes by name or region..." 
                style="width:100%; background:var(--cms-surface); border:1px solid var(--cms-border); border-radius:16px; padding:16px 16px 16px 52px; color:var(--cms-text); font-family:var(--font-admin); font-size:13px; outline:none;"
            >
        </div>
    </div>

    <!-- Tribes Grid/Table -->
    <div style="background:var(--cms-surface); border:1px solid var(--cms-border); border-radius:32px; overflow:hidden">
        <div style="display:grid; grid-template-columns:2fr 1fr 1fr 1fr minmax(140px, auto); padding:24px 32px; background:var(--cms-surface); border-bottom:1px solid var(--cms-border-subtle); font-size:11px; font-weight:800; color:var(--cms-text-muted); text-transform:uppercase; letter-spacing:1px">
            <span>Tribe Identity</span>
            <span>Ancestral Region</span>
            <span>Guardian Hero</span>
            <span>Greeting</span>
            <span style="text-align:right">Management</span>
        </div>

        @forelse($tribes as $tribe)
            <div style="display:grid; grid-template-columns:2fr 1fr 1fr 1fr minmax(140px, auto); padding:24px 32px; align-items:center; border-bottom:1px solid var(--cms-border-subtle); transition:all 0.2s" class="cms-hover-row">
                <a href="{{ route($routePrefix . '.tribes.detail', $tribe->id) }}" style="display:flex; align-items:center; gap:20px; text-decoration:none">
                    <div style="width:52px; height:52px; border-radius:16px; background:{{ $tribe->color }}; border: 1px solid var(--cms-border); display:flex; align-items:center; justify-content:center; font-size:24px; box-shadow:0 8px 16px {{ $tribe->color.'30' }}">
                        {{ $tribe->hero_emoji ?: '🗺️' }}
                    </div>
                    <div>
                        <div style="font-weight:800; color:var(--cms-text); font-size:16px; margin-bottom:2px">{{ $tribe->name }}</div>
                        <div style="font-size:12px; color:var(--cms-text-muted); text-transform:uppercase; font-weight:800; letter-spacing:1px">{{ $tribe->activities_count ?? $tribe->activities->count() }} Heritage Events</div>
                    </div>
                </a>

                <div style="font-size:14px; color:var(--cms-text-muted); font-weight:700">
                    {{ $tribe->region }}
                </div>

                <div style="font-size:14px; color:var(--cms-text-muted); font-weight:700">
                    {{ $tribe->hero_name }}
                </div>

                <div style="font-size:15px; color:{{ $tribe->color }}; font-weight:800; font-family:var(--font-display)">
                    {{ $tribe->greeting }}
                </div>

                <div class="sa-table-actions" style="justify-content:flex-end">
                    <a href="{{ route($routePrefix . '.tribes.edit', $tribe->id) }}" class="sa-icon-action" title="Edit tribe">⚙️</a>
                    <button type="button" wire:click="delete({{ $tribe->id }})" onclick="return confirm('Archive heritage record permanently?') || event.stopImmediatePropagation()" class="sa-icon-action sa-icon-action--danger" title="Delete tribe">🗑</button>
                </div>
            </div>
        @empty
            <div style="padding:100px; text-align:center; opacity:0.3">
                <div style="font-size:64px; margin-bottom:24px">🗺️</div>
                <div style="font-size:15px; font-weight:700">No heritage tribes registered.</div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div style="margin-top:40px">
        {{ $tribes->links(data: ['scrollTo' => false]) }}
    </div>
</div>
