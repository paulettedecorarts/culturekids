<div class="maze-show-page">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--sp-5);gap:var(--sp-3);flex-wrap:wrap">
        <div>
            <a href="{{ route($routePrefix . '.mazes') }}" class="btn btn-ghost btn-sm" style="text-decoration:none;margin-bottom:8px;display:inline-block">← Mazes</a>
            <div class="sa-page-title">{{ $maze->maze_type_icon }} {{ $maze->title }}</div>
            <div class="sa-breadcrumb">{{ $maze->maze_type_label }} • {{ $maze->difficulty_label }} • {{ $maze->tribe->name }}</div>
        </div>
        <div style="display:flex;gap:var(--sp-3);flex-wrap:wrap">
            <button wire:click="edit" class="btn btn-primary">Edit Maze</button>
            <a href="{{ route($routePrefix . '.mazes') }}" class="btn btn-ghost" style="text-decoration:none">Back</a>
        </div>
    </div>

    @if(session()->has('message'))
        <div style="background:rgba(74,124,89,.12);border:1px solid rgba(74,124,89,.35);color:var(--banana-light);padding:10px 14px;border-radius:10px;margin-bottom:var(--sp-4);font-size:12px;font-weight:700">
            {{ session('message') }}
        </div>
    @endif

    <div class="sa-content-card" style="margin-bottom:var(--sp-4)">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:var(--sp-4);margin-bottom:var(--sp-4)">
            <div>
                <div class="act-label" style="margin-bottom:var(--sp-3)">Maze Details</div>
                <div style="display:flex;flex-direction:column;gap:var(--sp-2)">
                    @foreach([
                        ['Tribe', $maze->tribe->name ?? 'N/A'],
                        ['Type', $maze->maze_type_label],
                        ['Difficulty', $maze->difficulty_label],
                        ['Age Range', $maze->age_range],
                        ['Star Points', $maze->star_points],
                        ['Grid Size', $maze->grid_rows . '×' . $maze->grid_cols],
                        ['Hero', $maze->hero_character ?: '—'],
                        ['Time Limit', $maze->time_limit_seconds ? $maze->time_limit_seconds.'s' : 'No limit'],
                        ['Collectibles', count($maze->collectibles ?? [])],
                    ] as [$label, $value])
                    <div style="display:flex;justify-content:space-between">
                        <span style="color:rgba(255,255,255,.6);font-size:12px">{{ $label }}</span>
                        <span style="color:#fff;font-size:12px;font-weight:600">{{ $value }}</span>
                    </div>
                    @endforeach
                    <div style="display:flex;justify-content:space-between">
                        <span style="color:rgba(255,255,255,.6);font-size:12px">Status</span>
                        <span style="padding:2px 8px;border-radius:12px;font-size:10px;font-weight:700;
                            {{ $maze->status === 'published' ? 'background:rgba(74,124,89,.2);color:#4A7C59;border:1px solid rgba(74,124,89,.35)' : 'background:rgba(212,160,23,.2);color:#F2CB5A;border:1px solid rgba(212,160,23,.45)' }}">
                            {{ ucfirst($maze->status) }}
                        </span>
                    </div>
                </div>
            </div>

            <div>
                <div class="act-label" style="margin-bottom:var(--sp-3)">Statistics</div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--sp-2)">
                    @foreach([
                        [$maze->attempts->count(), 'Attempts', '#60A5FA'],
                        [$maze->attempts->where('completed', true)->count(), 'Completed', '#4A7C59'],
                        [$maze->attempts->avg('time_spent_seconds') ? gmdate('i:s', $maze->attempts->avg('time_spent_seconds')) : '—', 'Avg Time', '#F2CB5A'],
                        [$maze->attempts->avg('stars_earned') ? round($maze->attempts->avg('stars_earned'), 1) : '—', 'Avg Stars', '#9C88FF'],
                    ] as [$val, $label, $color])
                    <div style="background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:8px;padding:var(--sp-2);text-align:center">
                        <div style="font-size:22px;font-weight:800;color:{{ $color }}">{{ $val }}</div>
                        <div style="font-size:10px;color:rgba(255,255,255,.5)">{{ $label }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        @if($maze->description)
        <div style="margin-bottom:var(--sp-4)">
            <div class="act-label" style="margin-bottom:var(--sp-2)">Description</div>
            <p style="color:rgba(255,255,255,.8);font-size:13px;line-height:1.5;margin:0">{{ $maze->description }}</p>
        </div>
        @endif

        @if($maze->cultural_note)
        <div style="margin-bottom:var(--sp-4);background:rgba(212,160,23,.08);border:1px solid rgba(212,160,23,.2);border-radius:8px;padding:var(--sp-3)">
            <div class="act-label" style="margin-bottom:var(--sp-2);color:#F2CB5A">🌍 Cultural Note</div>
            <p style="color:rgba(255,255,255,.8);font-size:13px;line-height:1.5;margin:0">{{ $maze->cultural_note }}</p>
        </div>
        @endif

        {{-- Maze preview --}}
        @if($maze->grid)
        <div style="margin-bottom:var(--sp-4)">
            <div class="act-label" style="margin-bottom:var(--sp-2)">Maze Preview</div>
            <div style="overflow-x:auto">
                <div style="display:inline-grid;gap:2px;background:rgba(255,255,255,.05);padding:8px;border-radius:8px;grid-template-columns:repeat({{ $maze->grid_cols }}, 20px)">
                    @foreach($maze->grid as $r => $row)
                        @foreach($row as $c => $cell)
                            @php
                                $isStart = ($maze->start_position['row'] ?? -1) === $r && ($maze->start_position['col'] ?? -1) === $c;
                                $isEnd   = ($maze->end_position['row'] ?? -1) === $r && ($maze->end_position['col'] ?? -1) === $c;
                                $collectibleHere = collect($maze->collectibles ?? [])->first(fn($col) => $col['row'] === $r && $col['col'] === $c);
                            @endphp
                            <div style="width:20px;height:20px;border-radius:2px;display:flex;align-items:center;justify-content:center;font-size:10px;
                                {{ $isStart ? 'background:rgba(74,124,89,.6)' : ($isEnd ? 'background:rgba(196,75,43,.6)' : ($collectibleHere ? 'background:rgba(212,160,23,.4)' : ($cell ? 'background:#1a2744' : 'background:rgba(255,255,255,.08)'))) }}">
                                @if($isStart) 🟢
                                @elseif($isEnd) 🔴
                                @elseif($collectibleHere) {{ $collectibleHere['emoji'] ?? '💎' }}
                                @endif
                            </div>
                        @endforeach
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- Collectibles --}}
        @if(count($maze->collectibles ?? []) > 0)
        <div>
            <div class="act-label" style="margin-bottom:var(--sp-2)">Collectibles ({{ count($maze->collectibles) }})</div>
            <div style="display:flex;flex-wrap:wrap;gap:8px">
                @foreach($maze->collectibles as $col)
                    <div style="background:rgba(212,160,23,.1);border:1px solid rgba(212,160,23,.2);border-radius:8px;padding:6px 12px;display:flex;align-items:center;gap:8px">
                        <span style="font-size:18px">{{ $col['emoji'] }}</span>
                        <div>
                            <div style="color:#fff;font-size:12px;font-weight:600">{{ $col['label'] ?: 'Item' }}</div>
                            <div style="color:rgba(255,255,255,.5);font-size:10px">Row {{ $col['row'] }}, Col {{ $col['col'] }} {{ $col['required'] ? '• Required' : '' }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>