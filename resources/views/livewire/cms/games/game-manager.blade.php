<div class="game-manager-page">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--sp-5);gap:var(--sp-3);flex-wrap:wrap">
        <div>
            <div class="sa-page-title">Game Activities</div>
            <div class="sa-breadcrumb">Matching, quiz, rhythm, memory, and cultural challenge games</div>
        </div>
        <a href="{{ route($routePrefix . '.games.create') }}" class="btn btn-primary" style="padding:12px 28px;border-radius:14px;font-weight:800;font-size:13px;box-shadow:0 8px 24px rgba(196,75,43,0.3);text-decoration:none">
            + Create Game
        </a>
    </div>

    @if(session()->has('message'))
        <div style="background:rgba(74,124,89,.12);border:1px solid rgba(74,124,89,.35);color:var(--banana-light);padding:10px 14px;border-radius:10px;margin-bottom:var(--sp-4);font-size:12px;font-weight:700">
            {{ session('message') }}
        </div>
    @endif

    {{-- Stats --}}
    <div class="sa-stats-row" style="grid-template-columns:repeat(5,minmax(0,1fr));gap:var(--sp-3);margin-bottom:var(--sp-4)">
        <div class="sa-stat">
            <div class="sa-stat-val">{{ $this->games->total() }}</div>
            <div class="sa-stat-label">Total</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">{{ \App\Models\Game::where('status','published')->count() }}</div>
            <div class="sa-stat-label">Published</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">{{ \App\Models\Game::where('game_type','matching')->count() }}</div>
            <div class="sa-stat-label">Matching</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">{{ \App\Models\Game::where('game_type','quiz')->count() }}</div>
            <div class="sa-stat-label">Quiz</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">{{ \App\Models\Game::where('game_type','memory')->count() }}</div>
            <div class="sa-stat-label">Memory</div>
        </div>
    </div>

    {{-- Filters --}}
    <div style="display:flex;gap:var(--sp-3);margin-bottom:var(--sp-4);flex-wrap:wrap">
        <input wire:model.live.debounce.300ms="search" placeholder="Search games..." style="padding:8px 14px;border-radius:var(--r-full);border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.06);color:#fff;font-family:var(--font-admin);font-size:12px;outline:none;flex:1;min-width:180px">
        <select wire:model.live="typeFilter" style="padding:8px 14px;border-radius:var(--r-full);border:1px solid rgba(255,255,255,.12);background:#1a2744;color:#fff;font-family:var(--font-admin);font-size:12px;outline:none">
            <option value="">All Types</option>
            @foreach($gameTypes as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </select>
        <select wire:model.live="tribeFilter" style="padding:8px 14px;border-radius:var(--r-full);border:1px solid rgba(255,255,255,.12);background:#1a2744;color:#fff;font-family:var(--font-admin);font-size:12px;outline:none">
            <option value="">All Tribes</option>
            @foreach($this->tribes as $tribe)
                <option value="{{ $tribe->id }}">{{ $tribe->name }}</option>
            @endforeach
        </select>
        <select wire:model.live="statusFilter" style="padding:8px 14px;border-radius:var(--r-full);border:1px solid rgba(255,255,255,.12);background:#1a2744;color:#fff;font-family:var(--font-admin);font-size:12px;outline:none">
            <option value="">All Status</option>
            <option value="published">Published</option>
            <option value="draft">Draft</option>
            <option value="archived">Archived</option>
        </select>
    </div>

    {{-- Table --}}
    <div class="sa-table-wrap">
        <div class="sa-table-head" style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr 1fr 1fr;gap:var(--sp-3);padding:12px 16px;background:rgba(255,255,255,.04);border-radius:8px;font-size:11px;font-weight:700;color:rgba(255,255,255,.6);text-transform:uppercase;letter-spacing:.5px">
            <span>Game</span>
            <span>Type</span>
            <span>Tribe</span>
            <span>Status</span>
            <span>Questions</span>
            <span>Actions</span>
        </div>

        @forelse($this->games as $game)
            <div class="sa-table-row" style="display:grid;grid-template-columns:2fr 1fr 1fr 1fr 1fr 1fr;gap:var(--sp-3);padding:12px 16px;border-bottom:1px solid rgba(255,255,255,.06);align-items:center">
                <div style="display:flex;align-items:center;gap:12px;min-width:0">
                    <div style="font-size:20px;width:32px;text-align:center">{{ $game->game_type_icon }}</div>
                    <div style="min-width:0">
                        <div style="font-weight:700;color:#fff;font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $game->title }}</div>
                        <div style="font-size:11px;color:rgba(255,255,255,.4)">Ages {{ $game->age_range }} • {{ $game->time_limit_seconds ? $game->time_limit_seconds.'s limit' : 'No time limit' }}</div>
                    </div>
                </div>
                <span style="background:rgba(74,124,89,.2);color:#6FA882;padding:2px 8px;border-radius:999px;font-size:9px;font-weight:700;text-transform:uppercase">{{ $game->game_type_label }}</span>
                <span style="font-size:12px;color:rgba(255,255,255,.6)">{{ $game->tribe->name }}</span>
                <span style="padding:2px 8px;border-radius:12px;font-size:10px;font-weight:700;
                    {{ $game->status === 'published' ? 'background:rgba(74,124,89,.2);color:#4A7C59;border:1px solid rgba(74,124,89,.35)' : 'background:rgba(212,160,23,.2);color:#F2CB5A;border:1px solid rgba(212,160,23,.45)' }}">
                    {{ ucfirst($game->status) }}
                </span>
                <span style="font-size:12px;color:rgba(255,255,255,.6)">{{ $game->questions_count }} questions</span>
                <div style="display:flex;gap:6px">
                    <a href="{{ route($routePrefix . '.games.show', $game->id) }}" class="btn btn-sm" style="background:rgba(212,160,23,.18);color:#F2CB5A;border:1px solid rgba(212,160,23,.3);padding:4px 10px;border-radius:var(--r-full);font-size:10px;font-weight:700;text-decoration:none">View</a>
                    <a href="{{ route($routePrefix . '.games.edit', $game->id) }}" class="btn btn-sm" style="background:rgba(59,130,246,.18);color:#60A5FA;border:1px solid rgba(59,130,246,.3);padding:4px 10px;border-radius:var(--r-full);font-size:10px;font-weight:700;text-decoration:none">Edit</a>
                </div>
            </div>
        @empty
            <div style="padding:40px;text-align:center;color:rgba(255,255,255,.5)">
                <div style="font-size:48px;margin-bottom:16px">🎯</div>
                <div style="font-size:16px;font-weight:600;margin-bottom:8px">No games yet</div>
                <a href="{{ route($routePrefix . '.games.create') }}" class="btn btn-primary" style="text-decoration:none">Create First Game</a>
            </div>
        @endforelse
    </div>

    <div style="margin-top:12px">{{ $this->games->links() }}</div>
</div>