<div
    class="sd-root"
    @if($processingStatus?->isProcessing())
        wire:poll.5s="refreshStatus"
    @endif
>
    @php
        $tribeTint = $story->tribe->color ?? '#D4A017';
        $statusKey = $story->status;
    @endphp

    @if (session()->has('message'))
        <div class="sd-flash" role="status">
            <span class="sd-flash__dot" aria-hidden="true"></span>
            {{ session('message') }}
        </div>
    @endif

    @if($processingStatus && $processingStatus->isProcessing())
        <div class="sd-banner sd-banner--progress">
            <div class="sd-banner__glow" aria-hidden="true"></div>
            <div class="sd-banner__grid">
                <div class="sd-banner__icon" aria-hidden="true">
                    <svg class="sd-spinner" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10" stroke-dasharray="60" stroke-dashoffset="15" opacity="0.25"/>
                        <path d="M12 2a10 10 0 0 1 10 10" stroke-linecap="round"/>
                    </svg>
                </div>
                <div class="sd-banner__body">
                    <div class="sd-banner__row">
                        <h2 class="sd-banner__title">
                            {{ $processingStatus->status === 'pending' ? 'Queued' : 'Extracting panels' }}
                        </h2>
                        <span class="sd-banner__pct">{{ $processingStatus->progress_percentage }}%</span>
                    </div>
                    <p class="sd-banner__text">
                        @if($processingStatus->current_file)
                            <span class="sd-banner__mono">{{ basename($processingStatus->current_file) }}</span>
                        @else
                            Background processing — this view updates automatically.
                        @endif
                    </p>
                    <div class="sd-progress">
                        <div class="sd-progress__fill" style="width: {{ $processingStatus->progress_percentage }}%"></div>
                    </div>
                    <div class="sd-banner__meta">
                        <span>{{ $processingStatus->processed_files }}/{{ $processingStatus->total_files }} files</span>
                        @if($processingStatus->failed_files > 0)
                            <span class="sd-banner__warn">{{ $processingStatus->failed_files }} failed</span>
                        @endif
                        <span class="sd-banner__meta-end">{{ $processingStatus->started_at?->diffForHumans() ?? 'Started recently' }}</span>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($processingFailure && !$processingStatus)
        <div class="sd-banner sd-banner--error" role="alert">
            <div class="sd-banner__grid sd-banner__grid--tight">
                <div class="sd-banner__icon sd-banner__icon--error" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 9v4M12 17h.01M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="sd-banner__title sd-banner__title--sm">Processing did not complete</h2>
                    <p class="sd-banner__text sd-banner__text--tight">{{ $processingFailure->error_message ?? 'The background job did not finish successfully.' }}</p>
                    <p class="sd-banner__hint">
                        Re-upload from <a href="{{ route($storyRouteBase . '.edit', $story->id) }}" class="sd-link">Edit story</a> and ensure the queue worker is running.
                    </p>
                </div>
            </div>
        </div>
    @endif

    <header class="sd-hero" style="--sd-tribe: {{ $tribeTint }}">
        <div class="sd-hero__mesh" aria-hidden="true"></div>
        <div class="sd-hero__inner">
            <div class="sd-hero__lead">
                <a href="{{ route($storyRouteBase) }}" class="sd-back" aria-label="Back to stories">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
                </a>
                <div class="sd-hero__copy">
                    <p class="sd-eyebrow">Story <span class="sd-eyebrow__sep">·</span> #{{ $story->id }}</p>
                    <h1 class="sd-title">{{ $story->title }}</h1>
                    <div class="sd-chips">
                        <span class="sd-chip sd-chip--tribe">{{ $story->tribe->name }}</span>
                        <span class="sd-chip">Ages {{ $story->age_range }}</span>
                        <span class="sd-chip">{{ $panels->count() }} {{ $panels->count() === 1 ? 'panel' : 'panels' }}</span>
                        @if($statusKey === 'published')
                            <span class="sd-pill sd-pill--live">Published</span>
                        @elseif($statusKey === 'review')
                            <span class="sd-pill sd-pill--review">In review</span>
                        @else
                            <span class="sd-pill sd-pill--draft">Draft</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="sd-hero__actions">
                <a href="{{ route($storyRouteBase . '.edit', $story->id) }}" class="sd-btn sd-btn--ghost">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    Edit story
                </a>
                <a href="{{ route($storyRouteBase . '.panels', $story->id) }}" class="sd-btn sd-btn--primary">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    Edit panels
                </a>
            </div>
        </div>
    </header>

    <div class="sd-workspace">
        <section class="sd-stage" aria-label="Panel preview" wire:key="sd-stage-{{ $story->id }}-{{ $panels->count() }}">
            @if($panels->isEmpty())
                <div class="sd-empty">
                    <div class="sd-empty__frame">
                        <svg class="sd-empty__icon" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25">
                            <rect x="4" y="3" width="16" height="18" rx="2"/>
                            <path d="M8 8h8M8 12h5"/>
                        </svg>
                        <h2 class="sd-empty__title">No panels yet</h2>
                        <p class="sd-empty__text">Upload a PDF or images from Edit story, then fine-tune order and audio in Edit panels.</p>
                        <div class="sd-empty__row">
                            <a href="{{ route($storyRouteBase . '.edit', $story->id) }}" class="sd-btn sd-btn--primary">Edit story</a>
                            <a href="{{ route($storyRouteBase . '.panels', $story->id) }}" class="sd-btn sd-btn--ghost">Edit panels</a>
                        </div>
                    </div>
                </div>
            @else
                <div class="sd-theatre">
                    <div class="sd-theatre__chrome">
                        <span class="sd-theatre__label">Preview</span>
                        <span class="sd-theatre__counter">{{ str_pad((string) ($currentPanel + 1), 2, '0', STR_PAD_LEFT) }} / {{ str_pad((string) $panels->count(), 2, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    <div class="sd-theatre__viewport">
                        @if($currentPanelModel)
                            @php
                                $path = $currentPanelModel->image_path;
                                $isPdf = str_ends_with(strtolower($path), '.pdf');
                            @endphp
                            @if($isPdf)
                                <div class="sd-theatre__pdf">
                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M10 13h4M10 17h4"/></svg>
                                    <p>PDF panel</p>
                                    <a href="{{ asset('storage/' . $path) }}" target="_blank" rel="noopener" class="sd-btn sd-btn--sm sd-btn--primary">Open file</a>
                                </div>
                            @else
                                <img
                                    src="{{ asset('storage/' . $path) }}"
                                    alt="Panel {{ $currentPanel + 1 }}"
                                    class="sd-theatre__img"
                                    loading="lazy"
                                />
                            @endif
                        @endif
                    </div>
                    @if($currentPanelModel && $currentPanelModel->caption)
                        <p class="sd-theatre__caption">{{ $currentPanelModel->caption }}</p>
                    @endif
                    <div class="sd-theatre__controls">
                        <button type="button" wire:click="previousPanel" class="sd-nav-btn" @disabled($currentPanel === 0) aria-label="Previous panel">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
                        </button>
                        @if($panels->count() <= 32)
                            <div class="sd-theatre__dots" aria-hidden="true">
                                @foreach($panels as $i => $_)
                                    <span class="sd-dot {{ $currentPanel === $i ? 'is-on' : '' }}"></span>
                                @endforeach
                            </div>
                        @else
                            <div class="sd-theatre__track" aria-hidden="true">
                                <div class="sd-theatre__track-fill" style="width: {{ $panels->count() > 1 ? (($currentPanel / ($panels->count() - 1)) * 100) : 100 }}%"></div>
                            </div>
                        @endif
                        <button type="button" wire:click="nextPanel" class="sd-nav-btn" @disabled($currentPanel >= $panels->count() - 1) aria-label="Next panel">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
                        </button>
                    </div>
                </div>

                <div class="sd-filmstrip-wrap">
                    <p class="sd-filmstrip__kicker">All pages</p>
                    <div class="sd-filmstrip" role="tablist" aria-label="Panel thumbnails">
                        @foreach($panels as $index => $panel)
                            @php $pPath = $panel->image_path; $thumbPdf = str_ends_with(strtolower($pPath), '.pdf'); @endphp
                            <button
                                type="button"
                                wire:click="goToPanel({{ $index }})"
                                class="sd-thumb {{ $currentPanel === $index ? 'is-active' : '' }}"
                                aria-label="Panel {{ $index + 1 }}"
                                aria-current="{{ $currentPanel === $index ? 'true' : 'false' }}"
                            >
                                <span class="sd-thumb__frame">
                                    @if($thumbPdf)
                                        <span class="sd-thumb__pdf">PDF</span>
                                    @else
                                        <img src="{{ asset('storage/' . $pPath) }}" alt="" loading="lazy" />
                                    @endif
                                </span>
                                <span class="sd-thumb__idx">{{ $index + 1 }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif
        </section>

        <aside class="sd-rail">
            <div class="sd-card sd-card--cover">
                <p class="sd-card__kicker">Cover</p>
                <div class="sd-cover">
                    @if($story->cover_image_path)
                        @if(str_ends_with(strtolower($story->cover_image_path), '.pdf'))
                            <div class="sd-cover__fallback">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
                                <span>PDF</span>
                            </div>
                        @else
                            <img src="{{ asset('storage/' . $story->cover_image_path) }}" alt="" class="sd-cover__img" loading="lazy" />
                        @endif
                    @else
                        <div class="sd-cover__fallback sd-cover__fallback--muted">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25"><rect x="4" y="3" width="16" height="18" rx="2"/></svg>
                            <span>No cover</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="sd-card">
                <p class="sd-card__kicker">Tribe</p>
                <div class="sd-tribe">
                    <div class="sd-tribe__badge" style="background: {{ $story->tribe->color ? $story->tribe->color.'26' : 'rgba(255,255,255,0.08)' }}">
                        {{ $story->tribe->hero_emoji ?: '🌍' }}
                    </div>
                    <div>
                        <div class="sd-tribe__name">{{ $story->tribe->name }}</div>
                        <div class="sd-tribe__meta">{{ $story->tribe->region ?? '—' }}</div>
                    </div>
                </div>
            </div>

            <div class="sd-metrics">
                <div class="sd-metric">
                    <span class="sd-metric__label">Age band</span>
                    <span class="sd-metric__val">{{ $story->age_min }}–{{ $story->age_max }} yrs</span>
                </div>
                <div class="sd-metric">
                    <span class="sd-metric__label">Star points</span>
                    <span class="sd-metric__val">{{ $story->star_points }}</span>
                </div>
                <div class="sd-metric">
                    <span class="sd-metric__label">Panels</span>
                    <span class="sd-metric__val">{{ $panels->count() }}</span>
                </div>
                <div class="sd-metric">
                    <span class="sd-metric__label">Updated</span>
                    <span class="sd-metric__val">{{ $story->updated_at->format('M j, Y') }}</span>
                </div>
            </div>

            @if($story->description)
                <div class="sd-card sd-card--prose">
                    <p class="sd-card__kicker">Synopsis</p>
                    <div class="sd-prose">{!! nl2br(e($story->description)) !!}</div>
                </div>
            @endif
        </aside>
    </div>

    <style>
        .sd-root {
            --sd-accent: var(--savanna-gold);
            --sd-accent-dim: rgba(212, 160, 23, 0.14);
            --sd-line: var(--cms-border);
            --sd-surface: var(--cms-surface);
            --sd-surface2: var(--cms-surface-raised);
            width: 100%;
            max-width: min(1680px, 100%);
            margin-inline: auto;
            min-width: 0;
        }

        .sd-flash {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 18px;
            border-radius: 14px;
            margin-bottom: var(--sp-5);
            font-size: 13px;
            font-weight: 600;
            color: var(--banana-light);
            background: rgba(74, 124, 89, 0.14);
            border: 1px solid rgba(74, 124, 89, 0.35);
        }
        .sd-flash__dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--banana-mid);
            box-shadow: 0 0 12px rgba(111, 168, 130, 0.7);
        }

        .sd-banner {
            border-radius: 20px;
            margin-bottom: var(--sp-5);
            position: relative;
            overflow: hidden;
        }
        .sd-banner--progress {
            border: 1px solid rgba(212, 160, 23, 0.35);
            background: linear-gradient(125deg, rgba(212, 160, 23, 0.12), rgba(232, 135, 42, 0.08), rgba(30, 45, 74, 0.35));
        }
        .sd-banner--error {
            border: 1px solid rgba(196, 75, 43, 0.4);
            background: linear-gradient(135deg, rgba(196, 75, 43, 0.12), rgba(17, 24, 39, 0.9));
        }
        .sd-banner__glow {
            position: absolute;
            inset: 0;
            background: linear-gradient(110deg, transparent 30%, var(--cms-surface-hover) 50%, transparent 70%);
            animation: sd-shimmer 2.8s ease-in-out infinite;
            pointer-events: none;
        }
        @keyframes sd-shimmer {
            0%, 100% { transform: translateX(-40%); opacity: 0.4; }
            50% { transform: translateX(40%); opacity: 0.9; }
        }
        .sd-banner__grid {
            position: relative;
            z-index: 1;
            display: grid;
            grid-template-columns: auto 1fr;
            gap: clamp(14px, 2vw, 22px);
            padding: clamp(18px, 2.2vw, 26px);
            align-items: start;
        }
        .sd-banner__grid--tight { align-items: center; }
        .sd-banner__icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(212, 160, 23, 0.18);
            color: var(--savanna-gold);
        }
        .sd-banner__icon--error {
            background: rgba(196, 75, 43, 0.2);
            color: var(--clay-red-light);
        }
        .sd-spinner {
            width: 26px;
            height: 26px;
            animation: sd-spin 1.1s linear infinite;
        }
        @keyframes sd-spin { to { transform: rotate(360deg); } }
        .sd-banner__row {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 6px;
        }
        .sd-banner__title {
            margin: 0;
            font-size: 16px;
            font-weight: 800;
            color: var(--savanna-gold);
            letter-spacing: -0.02em;
        }
        .sd-banner__title--sm { font-size: 15px; color: #fecaca; }
        .sd-banner__pct { font-size: 22px; font-weight: 800; color: var(--savanna-light); }
        .sd-banner__text {
            margin: 0 0 12px;
            font-size: 13px;
            line-height: 1.55;
            color: var(--cms-text-muted);
        }
        .sd-banner__text--tight { margin-bottom: 8px; }
        .sd-banner__mono {
            font-family: ui-monospace, monospace;
            font-size: 12px;
            color: var(--cms-text);
            word-break: break-all;
        }
        .sd-banner__hint {
            margin: 0;
            font-size: 12px;
            color: var(--cms-text-muted);
        }
        .sd-link {
            color: var(--savanna-gold);
            font-weight: 700;
            text-decoration: none;
        }
        .sd-link:hover { text-decoration: underline; }
        .sd-progress {
            height: 8px;
            border-radius: 999px;
            background: rgba(0, 0, 0, 0.35);
            overflow: hidden;
            margin-bottom: 10px;
        }
        .sd-progress__fill {
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, var(--savanna-gold), var(--sunfire));
            transition: width 0.45s ease;
        }
        .sd-banner__meta {
            display: flex;
            flex-wrap: wrap;
            gap: 12px 18px;
            font-size: 12px;
            color: var(--cms-text-muted);
        }
        .sd-banner__warn { color: #fecaca; font-weight: 700; }
        .sd-banner__meta-end { margin-left: auto; }

        .sd-hero {
            position: relative;
            border-radius: 24px;
            padding: clamp(18px, 2.5vw, 28px);
            margin-bottom: clamp(20px, 3vw, 32px);
            border: 1px solid var(--sd-line);
            background: linear-gradient(135deg, var(--cms-surface-raised), var(--cms-surface));
            overflow: hidden;
        }
        .sd-hero__mesh {
            position: absolute;
            inset: -40%;
            background:
                radial-gradient(ellipse 80% 60% at 10% 20%, color-mix(in srgb, var(--sd-tribe) 35%, transparent), transparent 55%),
                radial-gradient(ellipse 70% 50% at 90% 0%, rgba(212, 160, 23, 0.12), transparent 50%),
                radial-gradient(ellipse 60% 40% at 70% 100%, rgba(46, 77, 138, 0.2), transparent 45%);
            pointer-events: none;
        }
        .sd-hero__inner {
            position: relative;
            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            justify-content: space-between;
            gap: clamp(16px, 3vw, 28px);
        }
        .sd-hero__lead {
            display: flex;
            gap: 14px;
            min-width: 0;
            flex: 1 1 280px;
        }
        .sd-back {
            flex-shrink: 0;
            width: 44px;
            height: 44px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--cms-text);
            background: var(--cms-surface-raised);
            border: 1px solid var(--cms-border);
            transition: background 0.15s, transform 0.15s;
        }
        .sd-back:hover {
            background: var(--cms-surface-hover);
            transform: translateX(-2px);
        }
        .sd-eyebrow {
            margin: 0 0 6px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--cms-text-muted);
        }
        .sd-eyebrow__sep { opacity: 0.5; }
        .sd-title {
            margin: 0 0 12px;
            font-family: var(--font-display);
            font-size: clamp(26px, 3.2vw, 38px);
            font-weight: 800;
            line-height: 1.12;
            letter-spacing: -0.02em;
            color: var(--cms-text);
            word-break: break-word;
        }
        .sd-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }
        .sd-chip {
            font-size: 12px;
            font-weight: 600;
            padding: 5px 11px;
            border-radius: 999px;
            background: var(--cms-surface-raised);
            border: 1px solid var(--cms-border);
            color: var(--cms-text-muted);
        }
        .sd-chip--tribe {
            border-color: color-mix(in srgb, var(--sd-tribe) 45%, transparent);
            background: color-mix(in srgb, var(--sd-tribe) 18%, transparent);
            color: var(--cms-text);
        }
        .sd-pill {
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            padding: 5px 12px;
            border-radius: 999px;
        }
        .sd-pill--live { background: rgba(74, 124, 89, 0.25); color: var(--banana-light); border: 1px solid rgba(74, 124, 89, 0.45); }
        .sd-pill--review { background: rgba(232, 135, 42, 0.18); color: #FBD38D; border: 1px solid rgba(232, 135, 42, 0.4); }
        .sd-pill--draft { background: var(--cms-surface-raised); color: var(--cms-text-muted); border: 1px solid var(--cms-border); }

        .sd-hero__actions {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: flex-end;
            flex: 0 1 auto;
        }
        .sd-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 11px 18px;
            border-radius: 14px;
            font-size: 13px;
            font-weight: 700;
            font-family: inherit;
            text-decoration: none;
            border: 1px solid transparent;
            cursor: pointer;
            transition: background 0.15s, border-color 0.15s, transform 0.12s;
        }
        .sd-btn--primary {
            background: linear-gradient(145deg, rgba(212, 160, 23, 0.22), rgba(196, 75, 43, 0.15));
            border-color: rgba(212, 160, 23, 0.45);
            color: var(--cms-text);
        }
        .sd-btn--primary:hover { transform: translateY(-1px); background: linear-gradient(145deg, rgba(212, 160, 23, 0.32), rgba(196, 75, 43, 0.22)); }
        .sd-btn--ghost {
            background: var(--cms-surface-raised);
            border-color: var(--cms-border);
            color: var(--cms-text);
        }
        .sd-btn--ghost:hover { background: var(--cms-surface-hover); }
        .sd-btn--sm { padding: 8px 14px; font-size: 12px; border-radius: 12px; }

        .sd-workspace {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(260px, 340px);
            gap: clamp(20px, 2.5vw, 36px);
            align-items: start;
        }
        @media (max-width: 1100px) {
            .sd-workspace {
                grid-template-columns: 1fr;
            }
            .sd-rail {
                order: 2;
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
                gap: 16px;
            }
            .sd-card--cover { grid-column: 1 / -1; }
            .sd-metrics { grid-column: 1 / -1; }
        }
        @media (max-width: 640px) {
            .sd-hero__actions { width: 100%; justify-content: stretch; }
            .sd-hero__actions .sd-btn { flex: 1 1 calc(50% - 6px); justify-content: center; }
        }

        .sd-stage { min-width: 0; }

        .sd-empty {
            border-radius: 24px;
            border: 1px dashed var(--cms-border);
            background: var(--cms-surface);
            min-height: clamp(280px, 42vh, 480px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 20px;
        }
        .sd-empty__frame {
            text-align: center;
            max-width: 400px;
        }
        .sd-empty__icon {
            margin: 0 auto 16px;
            opacity: 0.35;
            color: var(--cms-text-muted);
        }
        .sd-empty__title {
            margin: 0 0 10px;
            font-family: var(--font-display);
            font-size: 22px;
            font-weight: 800;
            color: var(--cms-text);
        }
        .sd-empty__text {
            margin: 0 0 22px;
            font-size: 14px;
            line-height: 1.6;
            color: var(--cms-text-muted);
        }
        .sd-empty__row { display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; }

        .sd-theatre {
            border-radius: 24px;
            border: 1px solid var(--sd-line);
            background: linear-gradient(180deg, var(--cms-surface-raised), var(--cms-surface));
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.35);
            overflow: hidden;
        }
        .sd-theatre__chrome {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 18px;
            border-bottom: 1px solid var(--sd-line);
            background: rgba(0, 0, 0, 0.2);
        }
        .sd-theatre__label {
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: var(--cms-text-muted);
        }
        .sd-theatre__counter {
            font-variant-numeric: tabular-nums;
            font-size: 12px;
            font-weight: 700;
            color: var(--cms-text-muted);
        }
        .sd-theatre__viewport {
            position: relative;
            aspect-ratio: 16 / 9;
            width: 100%;
            max-height: min(72vh, 720px);
            background:
                radial-gradient(ellipse at 50% 0%, rgba(212, 160, 23, 0.08), transparent 55%),
                linear-gradient(165deg, #0f172a, #020617);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: clamp(12px, 2vw, 28px);
        }
        .sd-theatre__img {
            max-width: 100%;
            max-height: 100%;
            width: auto;
            height: auto;
            object-fit: contain;
            border-radius: 10px;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.45);
        }
        .sd-theatre__pdf {
            text-align: center;
            color: var(--cms-text-muted);
            max-width: 320px;
        }
        .sd-theatre__pdf p { margin: 10px 0 16px; font-size: 14px; }
        .sd-theatre__caption {
            margin: 0;
            padding: 14px 20px 6px;
            font-size: 14px;
            line-height: 1.55;
            color: var(--cms-text-muted);
            text-align: center;
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            background: rgba(0, 0, 0, 0.15);
        }
        .sd-theatre__controls {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 16px;
            padding: 14px 16px 18px;
            border-top: 1px solid var(--sd-line);
            background: rgba(0, 0, 0, 0.18);
        }
        .sd-nav-btn {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            border: 1px solid rgba(255, 255, 255, 0.12);
            background: var(--cms-surface-raised);
            color: var(--cms-text);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.15s, opacity 0.15s;
        }
        .sd-nav-btn:hover:not(:disabled) { background: var(--cms-surface-hover); }
        .sd-nav-btn:disabled { opacity: 0.3; cursor: not-allowed; }
        .sd-theatre__dots {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            justify-content: center;
            max-width: min(360px, 50vw);
        }
        .sd-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--cms-surface-raised);
            transition: transform 0.15s, background 0.15s;
        }
        .sd-dot.is-on {
            background: var(--savanna-gold);
            transform: scale(1.35);
            box-shadow: 0 0 12px rgba(212, 160, 23, 0.45);
        }
        .sd-theatre__track {
            width: min(280px, 40vw);
            height: 4px;
            border-radius: 999px;
            background: var(--cms-surface-raised);
            overflow: hidden;
        }
        .sd-theatre__track-fill {
            height: 100%;
            border-radius: inherit;
            background: linear-gradient(90deg, var(--savanna-gold), var(--sunfire));
            transition: width 0.25s ease;
        }

        .sd-filmstrip-wrap {
            margin-top: var(--sp-5);
        }
        .sd-filmstrip__kicker {
            margin: 0 0 10px;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--cms-text-muted);
        }
        .sd-filmstrip {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            padding-bottom: 8px;
            scroll-snap-type: x mandatory;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
            scrollbar-color: rgba(212, 160, 23, 0.35) transparent;
        }
        .sd-filmstrip::-webkit-scrollbar { height: 6px; }
        .sd-filmstrip::-webkit-scrollbar-thumb {
            background: rgba(212, 160, 23, 0.35);
            border-radius: 999px;
        }
        .sd-thumb {
            flex: 0 0 auto;
            scroll-snap-align: start;
            position: relative;
            padding: 0;
            border: none;
            background: transparent;
            cursor: pointer;
            border-radius: 12px;
            transition: transform 0.15s;
        }
        .sd-thumb:hover { transform: translateY(-2px); }
        .sd-thumb:focus-visible {
            outline: 2px solid var(--savanna-gold);
            outline-offset: 3px;
        }
        .sd-thumb__frame {
            display: block;
            width: clamp(72px, 14vw, 104px);
            aspect-ratio: 16 / 9;
            border-radius: 12px;
            overflow: hidden;
            border: 2px solid rgba(255, 255, 255, 0.12);
            background: rgba(0, 0, 0, 0.4);
            transition: border-color 0.15s, box-shadow 0.15s;
        }
        .sd-thumb.is-active .sd-thumb__frame {
            border-color: var(--savanna-gold);
            box-shadow: 0 0 0 1px rgba(212, 160, 23, 0.35), 0 12px 28px rgba(0, 0, 0, 0.35);
        }
        .sd-thumb__frame img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .sd-thumb__pdf {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.08em;
            color: var(--cms-text-muted);
        }
        .sd-thumb__idx {
            display: block;
            margin-top: 6px;
            text-align: center;
            font-size: 10px;
            font-weight: 800;
            color: var(--cms-text-muted);
        }
        .sd-thumb.is-active .sd-thumb__idx { color: var(--savanna-light); }

        .sd-rail {
            display: flex;
            flex-direction: column;
            gap: 16px;
            position: sticky;
            top: var(--sp-4);
        }
        @media (max-width: 1100px) {
            .sd-rail { position: static; }
        }

        .sd-card {
            border-radius: 20px;
            border: 1px solid var(--sd-line);
            background: var(--sd-surface);
            padding: 18px 18px 16px;
        }
        .sd-card--cover { padding-bottom: 14px; }
        .sd-card__kicker {
            margin: 0 0 12px;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--cms-text-muted);
        }
        .sd-cover {
            border-radius: 16px;
            overflow: hidden;
            aspect-ratio: 16 / 9;
            background: linear-gradient(145deg, rgba(196, 75, 43, 0.2), rgba(30, 45, 74, 0.55));
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .sd-cover__img { width: 100%; height: 100%; object-fit: cover; }
        .sd-cover__fallback {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            padding: 24px;
            color: var(--cms-text-muted);
            font-size: 12px;
            font-weight: 700;
        }
        .sd-cover__fallback--muted { opacity: 0.75; }

        .sd-tribe { display: flex; gap: 12px; align-items: center; }
        .sd-tribe__badge {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            border: 1px solid var(--cms-border);
        }
        .sd-tribe__name { font-weight: 800; font-size: 16px; color: var(--cms-text); }
        .sd-tribe__meta { font-size: 12px; color: var(--cms-text-muted); margin-top: 2px; }

        .sd-metrics {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .sd-metric {
            border-radius: 16px;
            padding: 12px 14px;
            background: rgba(0, 0, 0, 0.22);
            border: 1px solid rgba(255, 255, 255, 0.06);
        }
        .sd-metric__label {
            display: block;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--cms-text-muted);
            margin-bottom: 6px;
        }
        .sd-metric__val {
            font-size: 15px;
            font-weight: 700;
            color: var(--cms-text);
        }
        .sd-card--prose .sd-prose {
            font-size: 14px;
            line-height: 1.65;
            color: var(--cms-text-muted);
        }
    </style>
</div>
