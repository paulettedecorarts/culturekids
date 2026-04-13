<div class="teacher-dashboard">
    <div class="header">
        <div>
            <h1 class="page-title">{{ __('This week’s lessons') }}</h1>
            <div class="breadcrumb">
                @if ($activeClassroom)
                    {{ $activeClassroom->name }} · {{ $weekLabel }}
                @else
                    {{ __('No class assigned') }}
                @endif
            </div>
        </div>
        <div style="display:flex; gap:12px; flex-wrap:wrap; align-items:center">
            <div style="display:flex; align-items:center; gap:8px; font-size:12px; font-weight:700; color:var(--stone);">
                <button type="button" wire:click="$set('weekOffset', {{ $weekOffset - 1 }})" class="btn btn-outline btn-sm" style="padding:8px 12px; border-radius:10px">←</button>
                <span>{{ $weekOffset === 0 ? __('This week') : __('Week offset').' '.$weekOffset }}</span>
                <button type="button" wire:click="$set('weekOffset', {{ $weekOffset + 1 }})" class="btn btn-outline btn-sm" style="padding:8px 12px; border-radius:10px">→</button>
                @if ($weekOffset !== 0)
                    <button type="button" wire:click="$set('weekOffset', 0)" class="btn btn-ghost btn-sm" style="padding:8px 12px; font-size:11px">{{ __('Today’s week') }}</button>
                @endif
            </div>
            @if ($activeClassroom)
                <button type="button" wire:click="openCreateModal" class="btn btn-primary" style="padding:10px 20px; font-size:12px">+ {{ __('New lesson') }}</button>
            @endif
        </div>
    </div>

    @if (session()->has('message'))
        <div style="margin-bottom:16px; padding:12px 16px; border-radius:12px; background:#F0FDF4; border:1px solid #BBF7D0; color:#166534; font-size:13px; font-weight:700;">
            {{ session('message') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div style="margin-bottom:16px; padding:12px 16px; border-radius:12px; background:#FEF2F2; border:1px solid #FECACA; color:#991B1B; font-size:13px; font-weight:700;">
            {{ session('error') }}
        </div>
    @endif

    <div class="stats-grid">
        @foreach($stats as $s)
            <div class="stat-card">
                <div class="stat-val">{{ $s['val'] }}</div>
                <div class="stat-label">{{ $s['label'] }}</div>
                @if($s['delta']) <div class="stat-delta">{{ $s['delta'] }}</div> @endif
            </div>
        @endforeach
    </div>

    <div class="tab-nav">
        <span class="tab-item active">🗓️ {{ __('Lessons') }}</span>
        <a href="{{ route('teacher.class') }}" wire:navigate class="tab-item">👪 {{ __('My class') }}</a>
        <a href="{{ route('teacher.reports') }}" wire:navigate class="tab-item">📊 {{ __('Reports') }}</a>
    </div>

    @if (count($classrooms) === 0)
        <div style="background:#FFFBEB; border:1px solid #FDE68A; border-radius:24px; padding:32px; font-weight:600; color:#92400E;">
            {{ __('You have no classes assigned yet. Ask your organisation admin to assign you as the teacher for a classroom.') }}
        </div>
    @else
        <div style="margin-bottom:20px; max-width:420px">
            <label style="font-size:11px; font-weight:800; color:var(--stone); text-transform:uppercase; letter-spacing:1px; display:block; margin-bottom:8px">{{ __('Class') }}</label>
            <select wire:model.live="activeClassroomId" style="width:100%; padding:12px 16px; border-radius:14px; border:2px solid var(--cream-mid); font-family:var(--font-admin); font-size:14px; background:#fff;">
                @foreach ($classrooms as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
            </select>
        </div>

        <div style="background:#fff; border-radius:24px; border:1px solid var(--cream-mid); overflow:hidden; box-shadow:0 8px 32px rgba(26,18,8,.04)">
            @if ($lessonPlans->isEmpty())
                <div style="padding:40px; text-align:center; color:var(--stone); font-weight:700;">
                    {{ __('No lessons scheduled for this week. Add one with “New lesson”.') }}
                </div>
            @else
                <table style="width:100%; border-collapse:collapse; text-align:left">
                    <thead>
                        <tr style="background:#FDFBFA; border-bottom:1px solid var(--cream-mid); font-size:10px; font-weight:800; color:var(--stone); text-transform:uppercase; letter-spacing:1.5px">
                            <th style="padding:16px 24px">{{ __('Lesson') }}</th>
                            <th style="padding:16px 24px">{{ __('Tribe') }}</th>
                            <th style="padding:16px 24px">{{ __('When') }}</th>
                            <th style="padding:16px 24px">{{ __('Status') }}</th>
                            <th style="padding:16px 24px; text-align:right">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lessonPlans as $plan)
                            @php
                                $lessonable = $plan->lessonable;
                                $slot = $this->slotLabel($plan);
                                $isComic = $lessonable instanceof \App\Models\Comic;
                                $isSong = $lessonable instanceof \App\Models\Song;
                                $title = $lessonable?->title ?? '—';
                                $tribeName = $lessonable?->tribe?->name ?? '—';
                                $emoji = $isComic ? ($lessonable->tribe?->hero_emoji ?? '📖') : '🖨';
                                $meta = $isComic ? ($lessonable->age_range.' · '.__('story')) : (str_replace('_', ' ', $lessonable->type ?? '').' · '.__('activity'));
                            @endphp
                            <tr wire:key="lp-{{ $plan->id }}" style="border-bottom:1px solid var(--cream-mid); transition:background 0.2s">
                                <td style="padding:20px 24px">
                                    <div style="display:flex; align-items:center; gap:16px">
                                        <div style="width:40px; height:32px; border-radius:8px; background:linear-gradient(135deg,var(--cream-warm),var(--cream-mid)); display:flex; align-items:center; justify-content:center; font-size:18px;">{{ $emoji }}</div>
                                        <div>
                                            <div style="font-size:14px; font-weight:800; color:var(--ink)">{{ $title }}</div>
                                            <div style="font-size:11px; font-weight:700; color:var(--stone); margin-top:2px">{{ ucfirst($meta) }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td style="padding:20px 24px; font-size:13px; font-weight:700; color:var(--ink-light)">{{ $tribeName }}</td>
                                <td style="padding:20px 24px; font-size:12px; font-weight:700; color:var(--stone); white-space:nowrap">
                                    {{ $plan->scheduled_on->isoFormat('ddd MMM D') }}
                                </td>
                                <td style="padding:20px 24px">
                                    @if ($slot === 'done')
                                        <span style="display:inline-flex; align-items:center; gap:8px; padding:6px 14px; border-radius:99px; background:#ecfdf5; color:#059669; font-size:10px; font-weight:800; text-transform:uppercase;">{{ __('Done') }}</span>
                                    @elseif ($slot === 'today')
                                        <span style="display:inline-flex; align-items:center; gap:8px; padding:6px 14px; border-radius:99px; background:#fffbeb; color:#d97706; font-size:10px; font-weight:800; text-transform:uppercase;">{{ __('Today') }}</span>
                                    @elseif ($slot === 'tomorrow')
                                        <span style="display:inline-flex; align-items:center; gap:8px; padding:6px 14px; border-radius:99px; background:#f0f9ff; color:#0284c7; font-size:10px; font-weight:800; text-transform:uppercase;">{{ __('Tomorrow') }}</span>
                                    @elseif ($slot === 'overdue')
                                        <span style="display:inline-flex; align-items:center; gap:8px; padding:6px 14px; border-radius:99px; background:#fef2f2; color:#b91c1c; font-size:10px; font-weight:800; text-transform:uppercase;">{{ __('Overdue') }}</span>
                                    @else
                                        <span style="display:inline-flex; align-items:center; gap:8px; padding:6px 14px; border-radius:99px; background:#f4f4f5; color:#52525b; font-size:10px; font-weight:800; text-transform:uppercase;">{{ __('Upcoming') }}</span>
                                    @endif
                                </td>
                                <td style="padding:20px 24px; text-align:right">
                                    <div style="display:flex; gap:6px; justify-content:flex-end; flex-wrap:wrap">
                                        @if ($isComic)
                                            <a href="{{ route('teacher.stories.show', $lessonable->id) }}" wire:navigate class="btn btn-outline btn-sm" style="padding:6px 12px; font-size:11px; text-decoration:none">{{ __('Read') }}</a>
                                            <a href="{{ route('teacher.stories.show', $lessonable->id) }}?print=1" target="_blank" rel="noopener" class="btn btn-outline btn-sm" style="padding:6px 12px; font-size:11px; text-decoration:none">{{ __('Print') }}</a>
                                        @elseif ($isSong)
                                            @if ($lessonable->audio_path)
                                                <a href="{{ Storage::url($lessonable->audio_path) }}" target="_blank" rel="noopener" class="btn btn-primary btn-sm" style="padding:6px 12px; font-size:11px; text-decoration:none">🎵 {{ __('Play') }}</a>
                                            @endif
                                        @else
                                            @if ($url = $lessonable->printableAssetUrl())
                                                <a href="{{ $url }}" target="_blank" rel="noopener" class="btn btn-primary btn-sm" style="padding:6px 12px; font-size:11px; text-decoration:none">{{ __('PDF') }}</a>
                                            @endif
                                        @endif
                                        @if (! $plan->isCompleted())
                                            <button type="button" wire:click="markCompleted({{ $plan->id }})" class="btn btn-primary btn-sm" style="padding:6px 12px; font-size:11px">{{ __('Done') }}</button>
                                        @else
                                            <button type="button" wire:click="markPlanned({{ $plan->id }})" class="btn btn-ghost btn-sm" style="padding:6px 12px; font-size:11px">{{ __('Reopen') }}</button>
                                        @endif
                                        <button type="button" wire:click="deleteLesson({{ $plan->id }})" wire:confirm="{{ __('Remove this lesson from the plan?') }}" class="btn btn-ghost btn-sm" style="padding:6px 12px; font-size:11px; color:var(--clay-red)">{{ __('Remove') }}</button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    @endif

    @if ($showCreateModal)
        <div style="position:fixed; inset:0; background:rgba(26,18,8,.45); z-index:1000; display:flex; align-items:center; justify-content:center; padding:24px;" wire:click.self="closeCreateModal">
            <div style="background:#fff; border-radius:28px; max-width:480px; width:100%; padding:28px; box-shadow:0 24px 64px rgba(0,0,0,.15);" @click.stop>
                <h3 style="font-family:var(--font-display); font-size:22px; font-weight:800; margin-bottom:8px">{{ __('Add lesson') }}</h3>
                <p style="font-size:13px; color:var(--stone); margin-bottom:20px">{{ __('Choose a story or activity your organisation can access, and the day you plan to run it.') }}</p>

                <div style="display:flex; flex-direction:column; gap:16px">
                    <div>
                        <label style="font-size:11px; font-weight:800; color:var(--stone); text-transform:uppercase;">{{ __('Content type') }}</label>
                        <select wire:model.live="content_kind" style="width:100%; margin-top:6px; padding:12px; border-radius:12px; border:2px solid var(--cream-mid); font-size:14px;">
                            <option value="comic">{{ __('Story / comic') }}</option>
                            <option value="song">{{ __('Song') }}</option>
                            <option value="activity">{{ __('Activity') }}</option>
                        </select>
                    </div>

                    @if ($content_kind === 'comic')
                        <div>
                            <label style="font-size:11px; font-weight:800; color:var(--stone); text-transform:uppercase;">{{ __('Story') }}</label>
                            <select wire:model.live="selected_comic_id" style="width:100%; margin-top:6px; padding:12px; border-radius:12px; border:2px solid var(--cream-mid); font-size:14px;">
                                <option value="">{{ __('Select…') }}</option>
                                @foreach ($comicOptions as $co)
                                    <option value="{{ $co->id }}">{{ $co->title }}</option>
                                @endforeach
                            </select>
                            @error('selected_comic_id') <div style="color:#b91c1c; font-size:12px; margin-top:4px">{{ $message }}</div> @enderror
                        </div>
                    @elseif ($content_kind === 'song')
                        <div>
                            <label style="font-size:11px; font-weight:800; color:var(--stone); text-transform:uppercase;">{{ __('Song') }}</label>
                            <select wire:model.live="selected_song_id" style="width:100%; margin-top:6px; padding:12px; border-radius:12px; border:2px solid var(--cream-mid); font-size:14px;">
                                <option value="">{{ __('Select…') }}</option>
                                @foreach ($songOptions as $so)
                                    <option value="{{ $so->id }}">{{ $so->title }}</option>
                                @endforeach
                            </select>
                            @error('selected_song_id') <div style="color:#b91c1c; font-size:12px; margin-top:4px">{{ $message }}</div> @enderror
                        </div>
                    @else
                        <div>
                            <label style="font-size:11px; font-weight:800; color:var(--stone); text-transform:uppercase;">{{ __('Activity') }}</label>
                            <select wire:model.live="selected_activity_id" style="width:100%; margin-top:6px; padding:12px; border-radius:12px; border:2px solid var(--cream-mid); font-size:14px;">
                                <option value="">{{ __('Select…') }}</option>
                                @foreach ($activityOptions as $ao)
                                    <option value="{{ $ao->id }}">{{ $ao->title }} ({{ $ao->type }})</option>
                                @endforeach
                            </select>
                            @error('selected_activity_id') <div style="color:#b91c1c; font-size:12px; margin-top:4px">{{ $message }}</div> @enderror
                        </div>
                    @endif

                    <div>
                        <label style="font-size:11px; font-weight:800; color:var(--stone); text-transform:uppercase;">{{ __('Scheduled day') }}</label>
                        <input type="date" wire:model="form_scheduled_on" style="width:100%; margin-top:6px; padding:12px; border-radius:12px; border:2px solid var(--cream-mid); font-size:14px;">
                        @error('form_scheduled_on') <div style="color:#b91c1c; font-size:12px; margin-top:4px">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label style="font-size:11px; font-weight:800; color:var(--stone); text-transform:uppercase;">{{ __('Notes') }} ({{ __('optional') }})</label>
                        <textarea wire:model="form_notes" rows="2" style="width:100%; margin-top:6px; padding:12px; border-radius:12px; border:2px solid var(--cream-mid); font-size:14px; font-family:var(--font-admin);"></textarea>
                    </div>
                </div>

                <div style="display:flex; justify-content:flex-end; gap:10px; margin-top:24px;">
                    <button type="button" wire:click="closeCreateModal" class="btn btn-outline" style="padding:10px 20px; border-radius:12px;">{{ __('Cancel') }}</button>
                    <button type="button" wire:click="saveLesson" wire:loading.attr="disabled" class="btn btn-primary lesson-save-btn" style="padding:10px 20px; border-radius:12px;">
                        <span class="save-text">{{ __('Save') }}</span>
                        <span class="saving-text" style="display:none; align-items:center; gap:8px;">
                            <svg style="width:14px; height:14px; animation:spin 1s linear infinite;" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <circle cx="12" cy="12" r="10" stroke-width="3" stroke-dasharray="32" stroke-dashoffset="8" opacity="0.25"/>
                                <path d="M12 2a10 10 0 0 1 10 10" stroke-width="3" stroke-linecap="round"/>
                            </svg>
                            {{ __('Saving...') }}
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <style>
        @keyframes spin { to { transform: rotate(360deg); } }
        .lesson-save-btn[disabled] { opacity: 0.7; pointer-events: none; cursor: not-allowed; }
        .lesson-save-btn[disabled] .save-text { display: none; }
        .lesson-save-btn[disabled] .saving-text { display: flex !important; }
    </style>
</div>
