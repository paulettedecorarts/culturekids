<div>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--sp-5)">
        <div>
            <div class="sa-page-title">Organizations</div>
            <div class="sa-breadcrumb">Multi-org platform management</div>
        </div>
        <button class="btn btn-primary btn-sm">+ New Org</button>
    </div>

    <!-- Filters -->
    <div style="display:flex; gap:var(--sp-4); margin-bottom:var(--sp-6);">
        <input 
            type="text" 
            wire:model.live.debounce.300ms="search" 
            placeholder="Search by organization name or code..." 
            style="background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.07); border-radius:var(--r-md); padding:var(--sp-3) var(--sp-4); color:#fff; flex:1; font-family:var(--font-admin); font-size:12px; outline:none;"
        >
    </div>

    <!-- Organizations List -->
    <div style="display:flex;flex-direction:column;gap:var(--sp-3)">
        @forelse($organizations as $org)
            <div style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.07);border-radius:var(--r-xl);padding:var(--sp-5)">
                <div style="display:flex;align-items:center;gap:var(--sp-4);margin-bottom:var(--sp-4)">
                    <span style="font-size:32px">🏛</span>
                    <div style="flex:1">
                        <div style="font-family:var(--font-display);font-size:18px;font-weight:700;color:#fff">{{ $org->name }}</div>
                        <div style="font-size:12px;color:rgba(255,255,255,.4)">{{ $org->slug }}.paulette.app · {{ Str::title($org->plan ?? 'Standard') }} Plan</div>
                    </div>
                    <span class="status-pill status-published">Active</span>
                    <a href="{{ route('admin.organizations.detail', $org->id) }}" class="btn btn-sm" style="background:rgba(212,160,23,.15); color:var(--savanna-gold); border:1px solid rgba(212,160,23,.3); font-size:10px; display:inline-flex; align-items:center; text-decoration:none">Manage Entity</a>
                </div>
                <div style="display:flex;gap:var(--sp-6);font-size:12px;color:rgba(255,255,255,.5)">
                    <span>👥 {{ $org->users_count }} users</span>
                    <span>📚 Features available</span>
                    <span>🎨 Default theme</span>
                    <span>📴 Offline support</span>
                </div>
            </div>
        @empty
            <div style="text-align:center;color:rgba(255,255,255,.3);padding:var(--sp-8)">
                <div style="font-size:32px;margin-bottom:var(--sp-3)">🏢</div>
                <div style="font-size:15px;font-weight:600">No organizations found</div>
                <div style="font-size:13px;margin-top:var(--sp-1)">Adjust your search query.</div>
            </div>
        @endforelse
    </div>

    <!-- Pagination Links -->
    <div style="margin-top:var(--sp-6);">
        {{ $organizations->links(data: ['scrollTo' => false]) }}
    </div>
</div>
