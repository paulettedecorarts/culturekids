<div class="lang-show-page">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--sp-5);gap:var(--sp-3);flex-wrap:wrap">
        <div>
            <a href="{{ route($this->portalCanEditContent() ? $routePrefix . '.language-activities' : 'cms.admin.approved-content') }}" class="btn btn-ghost btn-sm" style="text-decoration:none;margin-bottom:8px;display:inline-block">← {{ $this->portalCanEditContent() ? 'Language Activities' : 'Approved Content' }}</a>
            <div class="sa-page-title">{{ $activity->activity_type_icon }} {{ $activity->title }}</div>
            <div class="sa-breadcrumb">{{ $activity->activity_type_label }} • {{ strtoupper($activity->language_code) }} • {{ $activity->tribe->name }}</div>
        </div>
        @if($this->portalCanEditContent())
            <div style="display:flex;gap:var(--sp-3);flex-wrap:wrap">
                <button wire:click="edit" class="btn btn-primary">Edit Activity</button>
                <a href="{{ route($routePrefix . '.language-activities') }}" class="btn btn-ghost" style="text-decoration:none">Back</a>
            </div>
        @endif
    </div>

    @if(session()->has('message'))
        <div style="background:rgba(74,124,89,.12);border:1px solid rgba(74,124,89,.35);color:var(--banana-light);padding:10px 14px;border-radius:10px;margin-bottom:var(--sp-4);font-size:12px;font-weight:700">
            {{ session('message') }}
        </div>
    @endif

    <div class="sa-content-card" style="margin-bottom:var(--sp-4)">
        {{-- Details grid --}}
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:var(--sp-4);margin-bottom:var(--sp-4)">
            <div>
                <div class="act-label" style="margin-bottom:var(--sp-3)">Activity Details</div>
                <div style="display:flex;flex-direction:column;gap:var(--sp-2)">
                    @foreach([
                        ['Tribe', $activity->tribe->name ?? 'N/A'],
                        ['Language', strtoupper($activity->language_code)],
                        ['Type', $activity->activity_type_label],
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

            <div>
                <div class="act-label" style="margin-bottom:var(--sp-3)">Statistics</div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--sp-2)">
                    <div style="background:var(--cms-surface-raised);border:1px solid var(--cms-border);border-radius:8px;padding:var(--sp-2);text-align:center">
                        <div style="font-size:22px;font-weight:800;color:#60A5FA">{{ $activity->words->count() }}</div>
                        <div style="font-size:10px;color:var(--cms-text-muted)">Words</div>
                    </div>
                    <div style="background:var(--cms-surface-raised);border:1px solid var(--cms-border);border-radius:8px;padding:var(--sp-2);text-align:center">
                        <div style="font-size:22px;font-weight:800;color:#4A7C59">{{ $activity->attempts->count() }}</div>
                        <div style="font-size:10px;color:var(--cms-text-muted)">Attempts</div>
                    </div>
                    <div style="background:var(--cms-surface-raised);border:1px solid var(--cms-border);border-radius:8px;padding:var(--sp-2);text-align:center">
                        <div style="font-size:22px;font-weight:800;color:#F2CB5A">{{ $activity->attempts->where('completed', true)->count() }}</div>
                        <div style="font-size:10px;color:var(--cms-text-muted)">Completed</div>
                    </div>
                    <div style="background:var(--cms-surface-raised);border:1px solid var(--cms-border);border-radius:8px;padding:var(--sp-2);text-align:center">
                        <div style="font-size:22px;font-weight:800;color:#9C88FF">{{ $activity->attempts->avg('stars_earned') ? round($activity->attempts->avg('stars_earned'), 1) : '—' }}</div>
                        <div style="font-size:10px;color:var(--cms-text-muted)">Avg Stars</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Description --}}
        @if($activity->description)
        <div style="margin-bottom:var(--sp-4)">
            <div class="act-label" style="margin-bottom:var(--sp-2)">Description</div>
            <p style="color:var(--cms-text);font-size:13px;line-height:1.5;margin:0">{{ $activity->description }}</p>
        </div>
        @endif

        {{-- Cultural note --}}
        @if($activity->cultural_note)
        <div style="margin-bottom:var(--sp-4);background:rgba(212,160,23,.08);border:1px solid rgba(212,160,23,.2);border-radius:8px;padding:var(--sp-3)">
            <div class="act-label" style="margin-bottom:var(--sp-2);color:#F2CB5A">🌍 Cultural Note</div>
            <p style="color:var(--cms-text);font-size:13px;line-height:1.5;margin:0">{{ $activity->cultural_note }}</p>
        </div>
        @endif

        {{-- Sentence (for jumble/builder) --}}
        @if($activity->full_sentence)
        <div style="margin-bottom:var(--sp-4)">
            <div class="act-label" style="margin-bottom:var(--sp-2)">Full Sentence</div>
            <div style="background:var(--cms-surface-raised);border:1px solid var(--cms-border);border-radius:8px;padding:var(--sp-3)">
                <div style="color:var(--cms-text);font-size:15px;font-weight:600;margin-bottom:4px">{{ $activity->full_sentence }}</div>
                @if($activity->sentence_translation)
                    <div style="color:var(--cms-text-muted);font-size:12px;font-style:italic">{{ $activity->sentence_translation }}</div>
                @endif
            </div>
        </div>
        @endif

        {{-- Audio --}}
        @if($activity->audio_path)
        <div style="margin-bottom:var(--sp-4)">
            <div class="act-label" style="margin-bottom:var(--sp-2)">Audio</div>
            <audio controls style="width:100%;max-width:400px">
                <source src="{{ asset('storage/' . $activity->audio_path) }}">
            </audio>
        </div>
        @endif

        {{-- Words list --}}
        @if($activity->words->count() > 0)
        <div>
            <div class="act-label" style="margin-bottom:var(--sp-3)">Words ({{ $activity->words->count() }})</div>
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:var(--sp-2)">
                @foreach($activity->words as $word)
                <div style="background:var(--cms-surface-raised);border:1px solid var(--cms-border);border-radius:8px;padding:var(--sp-2)">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
                        @if($word->emoji)
                            <span style="font-size:20px">{{ $word->emoji }}</span>
                        @endif
                        <div>
                            <div style="color:var(--cms-text);font-size:14px;font-weight:700">{{ $word->word }}</div>
                            <div style="color:var(--cms-text-muted);font-size:11px">{{ $word->translation }}</div>
                        </div>
                        @if($word->is_correct_answer)
                            <span style="margin-left:auto;background:rgba(74,124,89,.2);color:#4A7C59;padding:1px 6px;border-radius:8px;font-size:9px;font-weight:700">✓ Correct</span>
                        @endif
                        @if($word->is_fixed)
                            <span style="margin-left:auto;background:rgba(59,130,246,.2);color:#60A5FA;padding:1px 6px;border-radius:8px;font-size:9px;font-weight:700">Fixed</span>
                        @endif
                    </div>
                    @if($word->phonetic)
                        <div style="color:var(--cms-text-muted);font-size:10px;font-style:italic">/ {{ $word->phonetic }} /</div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>