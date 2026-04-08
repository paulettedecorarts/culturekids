<div class="story-detail" wire:poll.5s="refreshStatus">
    @if (session()->has('message'))
        <div class="story-detail-flash">✨ {{ session('message') }}</div>
    @endif

    @if($processingStatus && $processingStatus->isProcessing())
        <div class="story-detail-banner story-detail-banner--progress">
            <div class="story-detail-banner-shimmer" aria-hidden="true"></div>
            <div class="story-detail-banner-inner">
                <div class="story-detail-banner-icon" aria-hidden="true">
                    <svg class="story-detail-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10" stroke-dasharray="60" stroke-dashoffset="15" opacity="0.3"/>
                        <circle cx="12" cy="12" r="10" stroke-dasharray="15" stroke-linecap="round"/>
                    </svg>
                </div>
                <div class="story-detail-banner-body">
                    <div class="story-detail-banner-top">
                        <h3 class="story-detail-banner-title">
                            {{ $processingStatus->status === 'pending' ? '⏳ Queued for processing' : '⚙️ Extracting panels' }}
                        </h3>
                        <span class="story-detail-banner-pct">{{ $processingStatus->progress_percentage }}%</span>
                    </div>
                    <p class="story-detail-banner-text">
                        @if($processingStatus->current_file)
                            Now: <strong>{{ basename($processingStatus->current_file) }}</strong>
                        @else
                            PDFs and uploads are processed in the background. This page refreshes every few seconds.
                        @endif
                    </p>
                    <div class="story-detail-progress-track">
                        <div class="story-detail-progress-fill" style="width: {{ $processingStatus->progress_percentage }}%"></div>
                    </div>
                    <div class="story-detail-banner-meta">
                        <span>Files: <strong>{{ $processingStatus->processed_files }}/{{ $processingStatus->total_files }}</strong></span>
                        @if($processingStatus->failed_files > 0)
                            <span class="story-detail-text-warn">Failed: <strong>{{ $processingStatus->failed_files }}</strong></span>
                        @endif
                        <span class="story-detail-banner-meta-right">
                            Started {{ $processingStatus->started_at?->diffForHumans() ?? 'recently' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($processingFailure && !$processingStatus)
        <div class="story-detail-banner story-detail-banner--error" role="alert">
            <div class="story-detail-banner-inner story-detail-banner-inner--row">
                <span class="story-detail-error-icon" aria-hidden="true">⚠</span>
                <div>
                    <div class="story-detail-banner-title story-detail-banner-title--sm">Panel processing failed</div>
                    <p class="story-detail-banner-text story-detail-banner-text--tight">
                        {{ $processingFailure->error_message ?? 'The background job did not finish successfully.' }}
                    </p>
                    <p class="story-detail-hint">
                        Re-upload panels or PDF from <a href="{{ route('admin.stories.edit', $story->id) }}" class="story-detail-link">Edit story</a>, then run the queue worker.
                    </p>
                </div>
            </div>
        </div>
    @endif

    <header class="story-detail-header">
        <div class="story-detail-header-left">
            <a href="{{ route('admin.stories') }}" class="story-detail-back" aria-label="Back to stories">←</a>
            <div>
                <h1 class="sa-page-title story-detail-title">{{ $story->title }}</h1>
                <p class="sa-breadcrumb story-detail-sub">
                    {{ $story->tribe->name }}
                    · Ages {{ $story->age_range }}
                    · {{ $panels->count() }} {{ $panels->count() === 1 ? 'panel' : 'panels' }}
                    @if($story->status === 'published')
                        · <span class="story-detail-status story-detail-status--live">Published</span>
                    @elseif($story->status === 'review')
                        · <span class="story-detail-status story-detail-status--review">In review</span>
                    @else
                        · <span class="story-detail-status story-detail-status--draft">Draft</span>
                    @endif
                </p>
            </div>
        </div>
        <div class="story-detail-actions">
            <a href="{{ route('admin.stories.panels', $story->id) }}" class="story-detail-btn story-detail-btn--accent">🎨 Edit panels</a>
            <a href="{{ route('admin.stories.edit', $story->id) }}" class="story-detail-btn story-detail-btn--ghost">✏️ Edit story</a>
        </div>
    </header>

    <div class="story-detail-layout">
        <aside class="story-detail-aside">
            <div class="story-detail-card">
                <div class="story-detail-cover">
                    @if($story->cover_image_path)
                        @if(str_ends_with(strtolower($story->cover_image_path), '.pdf'))
                            <div class="story-detail-cover-fallback">
                                <span class="story-detail-cover-emoji">📄</span>
                                <span class="story-detail-cover-label">PDF cover</span>
                            </div>
                        @else
                            <img src="{{ asset('storage/' . $story->cover_image_path) }}" alt="" class="story-detail-cover-img">
                        @endif
                    @else
                        <div class="story-detail-cover-fallback">
                            <span class="story-detail-cover-emoji">📖</span>
                            <span class="story-detail-cover-label">No cover</span>
                        </div>
                    @endif
                </div>
                <p class="story-detail-id">Story #{{ $story->id }}</p>

                <div class="story-detail-tribe">
                    <span class="story-detail-kicker">Tribe</span>
                    <div class="story-detail-tribe-row">
                        <div class="story-detail-tribe-emoji" style="background: {{ $story->tribe->color ? $story->tribe->color.'33' : 'rgba(255,255,255,0.08)' }}">
                            {{ $story->tribe->hero_emoji ?? '🌍' }}
                        </div>
                        <div>
                            <div class="story-detail-tribe-name">{{ $story->tribe->name }}</div>
                            <div class="story-detail-tribe-region">{{ $story->tribe->region ?? '—' }}</div>
                        </div>
                    </div>
                </div>

                <dl class="story-detail-stats">
                    <div class="story-detail-stat">
                        <dt>Age band</dt>
                        <dd>{{ $story->age_min }}–{{ $story->age_max }} yrs</dd>
                    </div>
                    <div class="story-detail-stat">
                        <dt>Star points</dt>
                        <dd>⭐ {{ $story->star_points }}</dd>
                    </div>
                    <div class="story-detail-stat">
                        <dt>Panels</dt>
                        <dd>{{ $panels->count() }}</dd>
                    </div>
                    <div class="story-detail-stat">
                        <dt>Updated</dt>
                        <dd>{{ $story->updated_at->format('M j, Y') }}</dd>
                    </div>
                </dl>
            </div>
        </aside>

        <main class="story-detail-main">
            @if($story->description)
                <section class="story-detail-card story-detail-section">
                    <h2 class="story-detail-h2">Description</h2>
                    <div class="story-detail-prose">{!! nl2br(e($story->description)) !!}</div>
                </section>
            @endif

            <section class="story-detail-card story-detail-section story-detail-section--panels" wire:key="panels-{{ $story->id }}-{{ $panels->count() }}">
                @if($panels->isEmpty())
                    <div class="story-detail-empty">
                        <span class="story-detail-empty-icon" aria-hidden="true">📖</span>
                        <h2 class="story-detail-h2 story-detail-h2--center">No panels yet</h2>
                        <p class="story-detail-empty-text">
                            Add images or a PDF from <strong>Edit story</strong>, or open <strong>Edit panels</strong> to manage pages and audio.
                        </p>
                        <div class="story-detail-empty-actions">
                            <a href="{{ route('admin.stories.edit', $story->id) }}" class="story-detail-btn story-detail-btn--accent">✏️ Edit story</a>
                            <a href="{{ route('admin.stories.panels', $story->id) }}" class="story-detail-btn story-detail-btn--ghost">🎨 Edit panels</a>
                        </div>
                    </div>
                @else
                    <div class="story-detail-panels-head">
                        <h2 class="story-detail-h2">Panels</h2>
                        <span class="story-detail-panels-count">
                            {{ $currentPanel + 1 }} / {{ $panels->count() }}
                        </span>
                    </div>

                    <div class="story-detail-stage">
                        @if($currentPanelModel)
                            @php
                                $path = $currentPanelModel->image_path;
                                $isPdf = str_ends_with(strtolower($path), '.pdf');
                            @endphp
                            @if($isPdf)
                                <div class="story-detail-stage-pdf">
                                    <span class="story-detail-stage-emoji">📄</span>
                                    <p>PDF panel — open in a new tab to view.</p>
                                    <a href="{{ asset('storage/' . $path) }}" target="_blank" rel="noopener" class="story-detail-btn story-detail-btn--accent">Open PDF</a>
                                </div>
                            @else
                                <img src="{{ asset('storage/' . $path) }}" alt="Panel {{ $currentPanel + 1 }}" class="story-detail-stage-img">
                            @endif
                            @if($currentPanelModel->caption)
                                <p class="story-detail-caption">{{ $currentPanelModel->caption }}</p>
                            @endif
                        @endif
                    </div>

                    <div class="story-detail-nav">
                        <button type="button" wire:click="previousPanel" class="story-detail-nav-btn" @disabled($currentPanel === 0)>← Previous</button>
                        <button type="button" wire:click="nextPanel" class="story-detail-nav-btn" @disabled($currentPanel >= $panels->count() - 1)>Next →</button>
                    </div>

                    <div class="story-detail-thumbs" role="tablist" aria-label="Panel thumbnails">
                        @foreach($panels as $index => $panel)
                            @php $pPath = $panel->image_path; $thumbPdf = str_ends_with(strtolower($pPath), '.pdf'); @endphp
                            <button
                                type="button"
                                wire:click="goToPanel({{ $index }})"
                                class="story-detail-thumb {{ $currentPanel === $index ? 'is-active' : '' }}"
                                aria-label="Panel {{ $index + 1 }}"
                                aria-current="{{ $currentPanel === $index ? 'true' : 'false' }}"
                            >
                                @if($thumbPdf)
                                    <span class="story-detail-thumb-pdf">PDF</span>
                                @else
                                    <img src="{{ asset('storage/' . $pPath) }}" alt="" loading="lazy">
                                @endif
                                <span class="story-detail-thumb-badge">{{ $index + 1 }}</span>
                            </button>
                        @endforeach
                    </div>
                @endif
            </section>
        </main>
    </div>

    <style>
        .story-detail { width: 100%; max-width: 100%; min-width: 0; }
        .story-detail-flash {
            background: rgba(74,124,89,0.12);
            border: 1px solid rgba(74,124,89,0.35);
            color: var(--banana-light);
            padding: 12px 20px;
            border-radius: 12px;
            margin-bottom: var(--sp-6);
            font-size: 12px;
            font-weight: 700;
        }
        .story-detail-banner {
            border-radius: 20px;
            padding: 22px 24px;
            margin-bottom: var(--sp-6);
            position: relative;
            overflow: hidden;
        }
        .story-detail-banner--progress {
            background: linear-gradient(135deg, rgba(212,160,23,0.12), rgba(232,135,42,0.1));
            border: 2px solid rgba(212,160,23,0.45);
        }
        .story-detail-banner--error {
            background: rgba(196,75,43,0.1);
            border: 1px solid rgba(196,75,43,0.35);
        }
        .story-detail-banner-shimmer {
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.06), transparent);
            animation: story-detail-shimmer 2.5s infinite;
            pointer-events: none;
        }
        @keyframes story-detail-shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }
        .story-detail-banner-inner { position: relative; z-index: 1; display: flex; gap: 18px; align-items: flex-start; }
        .story-detail-banner-inner--row { align-items: flex-start; gap: 16px; }
        .story-detail-banner-icon {
            width: 52px; height: 52px; flex-shrink: 0;
            border-radius: 14px;
            background: rgba(212,160,23,0.2);
            display: flex; align-items: center; justify-content: center;
            color: var(--savanna-gold);
        }
        .story-detail-spin { width: 28px; height: 28px; animation: story-detail-spin 1.8s linear infinite; }
        @keyframes story-detail-spin { to { transform: rotate(360deg); } }
        .story-detail-banner-body { flex: 1; min-width: 0; }
        .story-detail-banner-top { display: flex; align-items: center; gap: 12px; margin-bottom: 8px; flex-wrap: wrap; }
        .story-detail-banner-title { margin: 0; font-size: 17px; font-weight: 800; color: var(--savanna-gold); }
        .story-detail-banner-title--sm { font-size: 15px; color: var(--clay-red-light); }
        .story-detail-banner-pct { margin-left: auto; font-size: 22px; font-weight: 800; color: var(--savanna-gold); }
        .story-detail-banner-text { margin: 0 0 14px; font-size: 13px; color: rgba(255,255,255,0.72); line-height: 1.55; }
        .story-detail-banner-text--tight { margin-bottom: 8px; }
        .story-detail-text-warn { color: var(--clay-red-light); }
        .story-detail-progress-track {
            height: 10px; border-radius: 8px; background: rgba(0,0,0,0.35); overflow: hidden; margin-bottom: 12px;
        }
        .story-detail-progress-fill {
            height: 100%; border-radius: 8px;
            background: linear-gradient(90deg, var(--savanna-gold), #E8872A);
            transition: width 0.4s ease;
        }
        .story-detail-banner-meta { display: flex; flex-wrap: wrap; gap: 16px; font-size: 12px; color: rgba(255,255,255,0.55); }
        .story-detail-banner-meta-right { margin-left: auto; }
        .story-detail-error-icon { font-size: 28px; line-height: 1; flex-shrink: 0; }
        .story-detail-hint { margin: 0; font-size: 12px; color: rgba(255,255,255,0.45); }
        .story-detail-link { color: var(--savanna-gold); font-weight: 700; }

        .story-detail-header {
            display: flex; flex-wrap: wrap; align-items: flex-start; justify-content: space-between;
            gap: var(--sp-4); margin-bottom: var(--sp-6);
        }
        .story-detail-header-left { display: flex; align-items: flex-start; gap: 16px; min-width: 0; }
        .story-detail-back {
            flex-shrink: 0; width: 44px; height: 44px; border-radius: 14px;
            background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1);
            color: #fff; display: flex; align-items: center; justify-content: center; text-decoration: none;
        }
        .story-detail-title { margin-bottom: 4px; word-break: break-word; }
        .story-detail-sub { margin: 0; line-height: 1.5; }
        .story-detail-status { font-weight: 700; }
        .story-detail-status--live { color: var(--banana-light); }
        .story-detail-status--review { color: #E8872A; }
        .story-detail-status--draft { color: var(--savanna-gold); }
        .story-detail-actions { display: flex; flex-wrap: wrap; gap: 10px; }
        .story-detail-btn {
            display: inline-flex; align-items: center; justify-content: center;
            padding: 10px 20px; border-radius: 14px; font-weight: 800; font-size: 12px;
            text-decoration: none; border: 1px solid transparent; cursor: pointer;
            font-family: inherit; transition: background 0.15s, border-color 0.15s;
        }
        .story-detail-btn--accent {
            background: rgba(212,160,23,0.15); color: var(--savanna-gold);
            border-color: rgba(212,160,23,0.35);
        }
        .story-detail-btn--ghost {
            background: rgba(255,255,255,0.05); color: #fff; border-color: rgba(255,255,255,0.12);
        }
        .story-detail-nav-btn {
            background: rgba(255,255,255,0.06); color: #fff; border: 1px solid rgba(255,255,255,0.12);
            padding: 12px 22px; border-radius: 12px; font-weight: 700; font-size: 13px; cursor: pointer; font-family: inherit;
        }
        .story-detail-nav-btn:disabled { opacity: 0.35; cursor: not-allowed; }

        .story-detail-layout {
            display: grid;
            grid-template-columns: minmax(0, 300px) minmax(0, 1fr);
            gap: clamp(20px, 3vw, 36px);
            align-items: start;
        }
        @media (max-width: 1024px) {
            .story-detail-layout { grid-template-columns: 1fr; }
        }

        .story-detail-card {
            background: rgba(255,255,255,0.035);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: var(--r-xl);
            padding: clamp(20px, 2.5vw, 28px);
        }
        .story-detail-cover {
            border-radius: 20px; overflow: hidden;
            aspect-ratio: 3/4; max-height: 340px;
            background: linear-gradient(145deg, rgba(196,75,43,0.25), rgba(30,45,74,0.6));
            display: flex; align-items: center; justify-content: center; margin-bottom: 16px;
        }
        .story-detail-cover-img { width: 100%; height: 100%; object-fit: cover; }
        .story-detail-cover-fallback { text-align: center; padding: 24px; color: rgba(255,255,255,0.55); }
        .story-detail-cover-emoji { font-size: 52px; display: block; margin-bottom: 8px; }
        .story-detail-cover-label { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; }
        .story-detail-id { text-align: center; font-size: 12px; color: rgba(255,255,255,0.38); font-weight: 700; margin: 0 0 20px; }

        .story-detail-kicker {
            display: block; font-size: 10px; font-weight: 800; letter-spacing: 0.12em;
            text-transform: uppercase; color: rgba(255,255,255,0.35); margin-bottom: 10px;
        }
        .story-detail-tribe-row { display: flex; gap: 12px; align-items: center; }
        .story-detail-tribe-emoji {
            width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center;
            font-size: 22px; border: 1px solid rgba(255,255,255,0.08);
        }
        .story-detail-tribe-name { font-weight: 800; color: #fff; font-size: 15px; }
        .story-detail-tribe-region { font-size: 12px; color: rgba(255,255,255,0.42); margin-top: 2px; }

        .story-detail-stats {
            margin: 20px 0 0; padding: 0; display: grid; gap: 12px;
        }
        .story-detail-stat {
            display: flex; justify-content: space-between; align-items: baseline; gap: 12px;
            padding: 12px 14px; border-radius: 12px;
            background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.05);
        }
        .story-detail-stat dt { margin: 0; font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.08em; color: rgba(255,255,255,0.35); }
        .story-detail-stat dd { margin: 0; font-size: 14px; font-weight: 700; color: #fff; }

        .story-detail-section { margin-bottom: 0; }
        .story-detail-h2 {
            font-family: var(--font-display); font-size: clamp(20px, 2.2vw, 26px);
            font-weight: 800; color: #fff; margin: 0 0 14px;
        }
        .story-detail-h2--center { text-align: center; }
        .story-detail-prose { font-size: 15px; line-height: 1.75; color: rgba(255,255,255,0.78); }

        .story-detail-empty { text-align: center; padding: clamp(32px, 5vw, 56px) 20px; }
        .story-detail-empty-icon { font-size: 56px; opacity: 0.35; display: block; margin-bottom: 12px; }
        .story-detail-empty-text { max-width: 420px; margin: 0 auto 24px; font-size: 14px; color: rgba(255,255,255,0.5); line-height: 1.6; }
        .story-detail-empty-actions { display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; }

        .story-detail-panels-head {
            display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 18px; flex-wrap: wrap;
        }
        .story-detail-panels-count { font-size: 13px; font-weight: 700; color: rgba(255,255,255,0.45); }

        .story-detail-stage {
            border-radius: 20px; background: rgba(0,0,0,0.35);
            border: 1px solid rgba(255,255,255,0.06);
            min-height: min(60vh, 560px);
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            padding: 24px; margin-bottom: 18px;
        }
        .story-detail-stage-img { max-width: 100%; max-height: min(58vh, 520px); width: auto; height: auto; object-fit: contain; border-radius: 8px; }
        .story-detail-stage-pdf { text-align: center; color: rgba(255,255,255,0.65); max-width: 360px; }
        .story-detail-stage-emoji { font-size: 56px; display: block; margin-bottom: 12px; }
        .story-detail-stage-pdf p { margin: 0 0 18px; font-size: 14px; line-height: 1.5; }
        .story-detail-caption {
            margin: 16px 0 0; width: 100%; max-width: 640px; text-align: center;
            font-size: 14px; color: rgba(255,255,255,0.72); line-height: 1.55;
        }

        .story-detail-nav { display: flex; justify-content: center; gap: 12px; margin-bottom: 20px; flex-wrap: wrap; }

        .story-detail-thumbs {
            display: flex; flex-wrap: wrap; gap: 10px; justify-content: center;
        }
        .story-detail-thumb {
            position: relative; width: 76px; height: 76px; padding: 0; border-radius: 12px; overflow: hidden;
            border: 2px solid rgba(255,255,255,0.12); background: rgba(0,0,0,0.35);
            cursor: pointer; flex-shrink: 0; transition: border-color 0.15s, transform 0.15s;
        }
        .story-detail-thumb:hover { border-color: rgba(212,160,23,0.5); transform: translateY(-2px); }
        .story-detail-thumb.is-active { border-color: var(--savanna-gold); box-shadow: 0 0 0 1px rgba(212,160,23,0.25); }
        .story-detail-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .story-detail-thumb-pdf {
            width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;
            font-size: 11px; font-weight: 800; color: rgba(255,255,255,0.55); letter-spacing: 0.04em;
        }
        .story-detail-thumb-badge {
            position: absolute; bottom: 4px; left: 4px; background: rgba(0,0,0,0.82);
            color: #fff; font-size: 10px; font-weight: 800; padding: 2px 6px; border-radius: 4px;
        }
    </style>
</div>
