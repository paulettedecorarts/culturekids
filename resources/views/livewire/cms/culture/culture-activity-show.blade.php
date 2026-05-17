<div class="culture-show-page">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--sp-5);gap:var(--sp-3);flex-wrap:wrap">
        <div>
            <a href="{{ route($this->portalContentListRoute($routePrefix . '.culture-activities')) }}" wire:navigate class="btn btn-ghost btn-sm" style="text-decoration:none;margin-bottom:8px;display:inline-block">← {{ $this->portalContentListLabel('Culture Activities') }}</a>
            <div class="sa-page-title">{{ $activity->culture_type_icon }} {{ $activity->title }}</div>
            <div class="sa-breadcrumb">{{ $activity->culture_type_label }} • {{ $activity->tribe->name }} • Ages {{ $activity->age_range }}</div>
        </div>
        @if($this->portalCanEditContent())
            <div style="display:flex;gap:var(--sp-3);flex-wrap:wrap">
                <button wire:click="edit" class="btn btn-primary">Edit Activity</button>
                <a href="{{ route($routePrefix . '.culture-activities') }}" class="btn btn-ghost" style="text-decoration:none">Back</a>
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
                        ['Type', $activity->culture_type_label],
                        ['Difficulty', ucfirst($activity->difficulty_level)],
                        ['Age Range', $activity->age_range],
                        ['Star Points', $activity->star_points],
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

            {{-- Clan info --}}
            @if($activity->clan_name)
            <div>
                <div class="act-label" style="margin-bottom:var(--sp-3)">Clan Information</div>
                <div style="background:rgba(212,160,23,.08);border:1px solid rgba(212,160,23,.2);border-radius:10px;padding:16px">
                    <div style="font-size:32px;margin-bottom:8px">{{ $activity->clan_emoji }}</div>
                    <div style="color:#F2CB5A;font-size:16px;font-weight:700;margin-bottom:4px">{{ $activity->clan_name }}</div>
                    @if($activity->clan_totem)
                        <div style="color:var(--cms-text-muted);font-size:12px;margin-bottom:2px">🐾 Totem: {{ $activity->clan_totem }}</div>
                    @endif
                    @if($activity->clan_role)
                        <div style="color:var(--cms-text-muted);font-size:12px">⚔️ Role: {{ $activity->clan_role }}</div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Stats --}}
            <div>
                <div class="act-label" style="margin-bottom:var(--sp-3)">Statistics</div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--sp-2)">
                    @foreach([
                        [$activity->attempts->count(), 'Attempts', '#60A5FA'],
                        [$activity->attempts->where('completed', true)->count(), 'Completed', '#4A7C59'],
                        [$activity->attempts->avg('score') ? round($activity->attempts->avg('score')) : '—', 'Avg Score', '#F2CB5A'],
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

        @if($activity->proverb)
        <div style="margin-bottom:var(--sp-4);background:rgba(212,160,23,.08);border:1px solid rgba(212,160,23,.2);border-radius:8px;padding:var(--sp-3)">
            <div class="act-label" style="margin-bottom:var(--sp-2);color:#F2CB5A">📜 Clan Proverb</div>
            <div style="color:var(--cms-text);font-size:14px;font-style:italic;margin-bottom:4px">"{{ $activity->proverb }}"</div>
            @if($activity->proverb_translation)
                <div style="color:var(--cms-text-muted);font-size:12px">{{ $activity->proverb_translation }}</div>
            @endif
        </div>
        @endif

        @if($activity->cultural_note)
        <div style="margin-bottom:var(--sp-4);background:rgba(59,130,246,.08);border:1px solid rgba(59,130,246,.2);border-radius:8px;padding:var(--sp-3)">
            <div class="act-label" style="margin-bottom:var(--sp-2);color:#60A5FA">🌍 Cultural Note</div>
            <p style="color:var(--cms-text);font-size:13px;line-height:1.5;margin:0">{{ $activity->cultural_note }}</p>
        </div>
        @endif

        @if($activity->content)
        <div style="margin-bottom:var(--sp-4)">
            <div class="act-label" style="margin-bottom:var(--sp-2)">Main Content</div>
            <div style="background:var(--cms-surface);border:1px solid var(--cms-border);border-radius:8px;padding:var(--sp-3);color:var(--cms-text);font-size:13px;line-height:1.7">
                {!! nl2br(e($activity->content)) !!}
            </div>
        </div>
        @endif

        @if(count($activity->content_sections ?? []) > 0)
        <div style="margin-bottom:var(--sp-4)">
            <div class="act-label" style="margin-bottom:var(--sp-2)">Content Sections</div>
            @foreach($activity->content_sections as $section)
            <div style="background:var(--cms-surface);border:1px solid var(--cms-border);border-radius:8px;padding:var(--sp-3);margin-bottom:8px">
                @if($section['title'])
                    <div style="color:#F2CB5A;font-size:13px;font-weight:700;margin-bottom:6px">{{ $section['title'] }}</div>
                @endif
                <div style="color:var(--cms-text);font-size:13px;line-height:1.6">{{ $section['text'] }}</div>
            </div>
            @endforeach
        </div>
        @endif

        @if(count($activity->quiz_questions ?? []) > 0)
        <div>
            <div class="act-label" style="margin-bottom:var(--sp-2)">Quiz Questions ({{ count($activity->quiz_questions) }})</div>
            @foreach($activity->quiz_questions as $i => $q)
            <div style="background:var(--cms-surface);border:1px solid var(--cms-border);border-radius:8px;padding:10px 14px;margin-bottom:6px;display:flex;gap:12px;align-items:flex-start">
                <div style="width:24px;height:24px;border-radius:50%;background:rgba(212,160,23,.2);border:1px solid rgba(212,160,23,.3);display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;color:#F2CB5A;flex-shrink:0">{{ $i + 1 }}</div>
                <div>
                    <div style="color:var(--cms-text);font-size:12px;font-weight:600">{{ $q['question'] }}</div>
                    <div style="color:rgba(74,124,89,.8);font-size:11px;margin-top:2px">✓ {{ $q['answer'] }}</div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>