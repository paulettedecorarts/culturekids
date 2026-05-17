<div class="game-show-page">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--sp-5);gap:var(--sp-3);flex-wrap:wrap">
        <div>
            <a href="{{ route($this->portalContentListRoute($routePrefix . '.games')) }}" wire:navigate class="btn btn-ghost btn-sm" style="text-decoration:none;margin-bottom:8px;display:inline-block">← {{ $this->portalContentListLabel('Games') }}</a>
            <div class="sa-page-title">{{ $game->game_type_icon }} {{ $game->title }}</div>
            <div class="sa-breadcrumb">{{ $game->game_type_label }} • {{ $game->tribe->name }} • Ages {{ $game->age_range }}</div>
        </div>
        @if($this->portalCanEditContent())
            <div style="display:flex;gap:var(--sp-3);flex-wrap:wrap">
                <button wire:click="edit" class="btn btn-primary">Edit Game</button>
                <a href="{{ route($routePrefix . '.games') }}" class="btn btn-ghost" style="text-decoration:none">Back</a>
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
                <div class="act-label" style="margin-bottom:var(--sp-3)">Game Details</div>
                <div style="display:flex;flex-direction:column;gap:var(--sp-2)">
                    @foreach([
                        ['Tribe', $game->tribe->name ?? 'N/A'],
                        ['Type', $game->game_type_label],
                        ['Difficulty', ucfirst($game->difficulty_level)],
                        ['Age Range', $game->age_range],
                        ['Star Points', $game->star_points],
                        ['Lives', $game->lives],
                        ['Time Limit', $game->time_limit_seconds ? $game->time_limit_seconds.'s' : 'No limit'],
                        ['Questions/Round', $game->questions_per_round],
                        ['Shuffle', $game->shuffle_questions ? 'Yes' : 'No'],
                    ] as [$label, $value])
                    <div style="display:flex;justify-content:space-between">
                        <span style="color:var(--cms-text-muted);font-size:12px">{{ $label }}</span>
                        <span style="color:var(--cms-text);font-size:12px;font-weight:600">{{ $value }}</span>
                    </div>
                    @endforeach
                    <div style="display:flex;justify-content:space-between">
                        <span style="color:var(--cms-text-muted);font-size:12px">Status</span>
                        <span style="padding:2px 8px;border-radius:12px;font-size:10px;font-weight:700;
                            {{ $game->status === 'published' ? 'background:rgba(74,124,89,.2);color:#4A7C59;border:1px solid rgba(74,124,89,.35)' : 'background:rgba(212,160,23,.2);color:#F2CB5A;border:1px solid rgba(212,160,23,.45)' }}">
                            {{ ucfirst($game->status) }}
                        </span>
                    </div>
                </div>
            </div>

            <div>
                <div class="act-label" style="margin-bottom:var(--sp-3)">Statistics</div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--sp-2)">
                    @foreach([
                        [$game->questions->count(), 'Questions', '#60A5FA'],
                        [$game->attempts->count(), 'Attempts', '#4A7C59'],
                        [$game->attempts->where('completed', true)->count(), 'Completed', '#F2CB5A'],
                        [$game->attempts->avg('score') ? round($game->attempts->avg('score')) : '—', 'Avg Score', '#9C88FF'],
                    ] as [$val, $label, $color])
                    <div style="background:var(--cms-surface-raised);border:1px solid var(--cms-border);border-radius:8px;padding:var(--sp-2);text-align:center">
                        <div style="font-size:22px;font-weight:800;color:{{ $color }}">{{ $val }}</div>
                        <div style="font-size:10px;color:var(--cms-text-muted)">{{ $label }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        @if($game->description)
        <div style="margin-bottom:var(--sp-4)">
            <div class="act-label" style="margin-bottom:var(--sp-2)">Description</div>
            <p style="color:var(--cms-text);font-size:13px;line-height:1.5;margin:0">{{ $game->description }}</p>
        </div>
        @endif

        @if($game->cultural_note)
        <div style="margin-bottom:var(--sp-4);background:rgba(212,160,23,.08);border:1px solid rgba(212,160,23,.2);border-radius:8px;padding:var(--sp-3)">
            <div class="act-label" style="margin-bottom:var(--sp-2);color:#F2CB5A">🌍 Cultural Note</div>
            <p style="color:var(--cms-text);font-size:13px;line-height:1.5;margin:0">{{ $game->cultural_note }}</p>
        </div>
        @endif

        @if($game->questions->count() > 0)
        <div>
            <div class="act-label" style="margin-bottom:var(--sp-3)">Questions / Items ({{ $game->questions->count() }})</div>
            <div style="display:flex;flex-direction:column;gap:8px">
                @foreach($game->questions as $q)
                <div style="background:var(--cms-surface);border:1px solid var(--cms-border);border-radius:8px;padding:12px 16px;display:flex;align-items:center;gap:12px">
                    <span style="font-size:11px;font-weight:700;color:var(--cms-text-muted);min-width:24px">#{{ $loop->index + 1 }}</span>
                    @if($q->question_emoji)
                        <span style="font-size:20px">{{ $q->question_emoji }}</span>
                    @endif
                    <div style="flex:1;min-width:0">
                        <div style="color:var(--cms-text);font-size:13px;font-weight:600">{{ $q->question_text ?: '—' }}</div>
                        @if($q->match_text || $q->match_emoji)
                            <div style="color:var(--cms-text-muted);font-size:11px;margin-top:2px">
                                ↔ {{ $q->match_emoji }} {{ $q->match_text }}
                            </div>
                        @endif
                        @if($q->correct_answer)
                            <div style="color:rgba(74,124,89,.8);font-size:11px;margin-top:2px">✓ {{ $q->correct_answer }}</div>
                        @endif
                    </div>
                    @if($q->hint)
                        <span style="font-size:10px;color:var(--cms-text-muted);font-style:italic">💡 {{ $q->hint }}</span>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>