<div class="ws-show-page">
    <style>
    .ws-show-grid { display:inline-grid;gap:2px;background:var(--cms-surface-raised);padding:8px;border-radius:8px; }
    .ws-show-cell { width:26px;height:26px;display:flex;align-items:center;justify-content:center;font-weight:900;font-size:11px;border-radius:4px;background:var(--cms-surface-raised);border:1px solid var(--cms-border);color:var(--cms-text); }
    .ws-show-cell.placed { background:rgba(212,160,23,.2);border-color:rgba(212,160,23,.4);color:#F2CB5A; }
    </style>

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--sp-5);gap:var(--sp-3);flex-wrap:wrap">
        <div>
            <a href="{{ route($this->portalContentListRoute($routePrefix . '.word-searches')) }}" wire:navigate class="btn btn-ghost btn-sm" style="text-decoration:none;margin-bottom:8px;display:inline-block">← {{ $this->portalContentListLabel('Word Searches') }}</a>
            <div class="sa-page-title">🔠 {{ $activity->title }}</div>
            <div class="sa-breadcrumb">{{ $activity->tribe->name }} • {{ ucfirst($activity->difficulty_level) }} • {{ $activity->grid_size }}×{{ $activity->grid_size }} grid • Ages {{ $activity->age_range }}</div>
        </div>
        @if($this->portalCanEditContent())
            <div style="display:flex;gap:var(--sp-3);flex-wrap:wrap">
                <button wire:click="edit" class="btn btn-primary">Edit Activity</button>
                <a href="{{ route($routePrefix . '.word-searches') }}" class="btn btn-ghost" style="text-decoration:none">Back</a>
            </div>
        @endif
    </div>

    @if(session()->has('message'))
        <div style="background:rgba(74,124,89,.12);border:1px solid rgba(74,124,89,.35);color:var(--banana-light);padding:10px 14px;border-radius:10px;margin-bottom:var(--sp-4);font-size:12px;font-weight:700">
            {{ session('message') }}
        </div>
    @endif

    <div class="sa-content-card" style="margin-bottom:var(--sp-4)">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:var(--sp-4);margin-bottom:var(--sp-4)">
            <div>
                <div class="act-label" style="margin-bottom:var(--sp-3)">Activity Details</div>
                <div style="display:flex;flex-direction:column;gap:var(--sp-2)">
                    @foreach([
                        ['Tribe', $activity->tribe->name ?? 'N/A'],
                        ['Difficulty', ucfirst($activity->difficulty_level)],
                        ['Age Range', $activity->age_range],
                        ['Star Points', $activity->star_points],
                        ['Grid Size', $activity->grid_size.'×'.$activity->grid_size],
                        ['Words', count($activity->words ?? [])],
                        ['Diagonals', $activity->allow_diagonal ? 'Yes' : 'No'],
                        ['Reverse', $activity->allow_reverse ? 'Yes' : 'No'],
                    ] as [$label, $value])
                    <div style="display:flex;justify-content:space-between">
                        <span style="color:var(--cms-text-muted);font-size:12px">{{ $label }}</span>
                        <span style="color:var(--cms-text);font-size:12px;font-weight:600">{{ $value }}</span>
                    </div>
                    @endforeach
                    <div style="display:flex;justify-content:space-between">
                        <span style="color:var(--cms-text-muted);font-size:12px">Status</span>
                        <span style="padding:2px 8px;border-radius:12px;font-size:10px;font-weight:700;
                            {{ $activity->status === 'published' ? 'background:rgba(74,124,89,.2);color:#4A7C59;border:1px solid rgba(74,124,89,.35)' : 'background:rgba(212,160,23,.2);color:#F2CB5A;border:1px solid rgba(212,160,23,.45)' }}">
                            {{ ucfirst($activity->status) }}
                        </span>
                    </div>
                </div>
            </div>

            <div>
                <div class="act-label" style="margin-bottom:var(--sp-3)">Statistics</div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--sp-2)">
                    @foreach([
                        [$activity->attempts->count(), 'Attempts', '#60A5FA'],
                        [$activity->attempts->where('completed', true)->count(), 'Completed', '#4A7C59'],
                        [$activity->attempts->avg('words_found') ? round($activity->attempts->avg('words_found'), 1) : '—', 'Avg Found', '#F2CB5A'],
                        [$activity->attempts->avg('stars_earned') ? round($activity->attempts->avg('stars_earned'), 1) : '—', 'Avg Stars', '#9C88FF'],
                    ] as [$val, $label, $color])
                    <div style="background:var(--cms-surface-raised);border:1px solid var(--cms-border);border-radius:8px;padding:var(--sp-2);text-align:center">
                        <div style="font-size:22px;font-weight:800;color:{{ $color }}">{{ $val }}</div>
                        <div style="font-size:10px;color:var(--cms-text-muted)">{{ $label }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        @if($activity->cultural_note)
        <div style="margin-bottom:var(--sp-4);background:rgba(212,160,23,.08);border:1px solid rgba(212,160,23,.2);border-radius:8px;padding:var(--sp-3)">
            <div class="act-label" style="margin-bottom:var(--sp-2);color:#F2CB5A">🌍 Cultural Note</div>
            <p style="color:var(--cms-text);font-size:13px;line-height:1.5;margin:0">{{ $activity->cultural_note }}</p>
        </div>
        @endif

        {{-- Words list --}}
        @if(count($activity->words ?? []) > 0)
        <div style="margin-bottom:var(--sp-4)">
            <div class="act-label" style="margin-bottom:var(--sp-2)">Words to Find</div>
            <div style="display:flex;flex-wrap:wrap;gap:8px">
                @foreach($activity->words as $word)
                <div style="background:rgba(212,160,23,.1);border:1px solid rgba(212,160,23,.2);border-radius:8px;padding:6px 14px">
                    <div style="color:#F2CB5A;font-size:13px;font-weight:700;font-family:monospace;letter-spacing:1px">{{ $word['word'] }}</div>
                    @if($word['translation'] ?? null)
                        <div style="color:var(--cms-text-muted);font-size:10px">{{ $word['translation'] }}</div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Grid preview --}}
        @if($activity->grid)
        @php
            $placedCells = [];
            foreach($activity->word_positions ?? [] as $pos) {
                foreach($pos['cells'] as $cell) {
                    $placedCells[$cell['row'].','.$cell['col']] = true;
                }
            }
        @endphp
        <div>
            <div class="act-label" style="margin-bottom:var(--sp-2)">Letter Grid</div>
            <div style="overflow-x:auto">
                <div class="ws-show-grid" style="grid-template-columns: repeat({{ count($activity->grid[0] ?? []) }}, 26px)">
                    @foreach($activity->grid as $r => $row)
                        @foreach($row as $c => $letter)
                            <div class="ws-show-cell {{ isset($placedCells[$r.','.$c]) ? 'placed' : '' }}">
                                {{ $letter }}
                            </div>
                        @endforeach
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>
</div>