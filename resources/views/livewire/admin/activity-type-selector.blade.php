<div class="activity-type-selector-page">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--sp-5);gap:var(--sp-3);flex-wrap:wrap">
        <div style="display:flex;align-items:center;gap:12px">
            <a href="{{ route($routePrefix . '.activities') }}" class="btn btn-ghost btn-sm" style="text-decoration:none">← Activities</a>
            <div>
                <div class="sa-page-title">Create Activity</div>
                <div class="sa-breadcrumb">Choose activity type to get started</div>
            </div>
        </div>
    </div>

    <div class="activity-type-grid">
        <div class="activity-type-card" wire:click="selectType('flashcard')">
            <div class="activity-type-icon">🃏</div>
            <div class="activity-type-title">Flashcard</div>
            <div class="activity-type-desc">Interactive cards with front/back content, emojis, and images for vocabulary learning</div>
            <div class="activity-type-count">{{ \App\Models\Activity::where('type', 'flashcard')->count() }} created</div>
            <div class="activity-type-status ready">Ready to use</div>
        </div>
        
        <div class="activity-type-card" wire:click="selectType('puzzle')">
            <div class="activity-type-icon">🧩</div>
            <div class="activity-type-title">Puzzle</div>
            <div class="activity-type-desc">Jigsaw puzzles with customizable piece count and difficulty levels</div>
            <div class="activity-type-count">{{ \App\Models\Activity::where('type', 'puzzle')->count() }} created</div>
            <div class="activity-type-status ready">Ready to use</div>
        </div>
        
        <div class="activity-type-card" wire:click="selectType('song')">
            <div class="activity-type-icon">🎵</div>
            <div class="activity-type-title">Song</div>
            <div class="activity-type-desc">Karaoke, lullabies, clan pride songs, and cultural music activities</div>
            <div class="activity-type-count">{{ \App\Models\Song::count() }} created</div>
            <div class="activity-type-status ready">Ready to use</div>
        </div>
        
        <div class="activity-type-card" wire:click="selectType('drawing')">
            <div class="activity-type-icon">🎨</div>
            <div class="activity-type-title">Drawing</div>
            <div class="activity-type-desc">Coloring pages, design tools, hero posters, and creative activities</div>
            <div class="activity-type-count">{{ \Illuminate\Support\Facades\Schema::hasTable('drawings') ? \App\Models\Drawing::count() : 0 }} created</div>
            <div class="activity-type-status ready">Ready to use</div>
        </div>
        
        <div class="activity-type-card" wire:click="selectType('language')">
            <div class="activity-type-icon">🔤</div>
            <div class="activity-type-title">Language</div>
            <div class="activity-type-desc">Word tracing, audio matching, pronunciation, and proverb games</div>
            <div class="activity-type-count">{{ \Illuminate\Support\Facades\Schema::hasTable('language_activities') ? \App\Models\LanguageActivity::count() : 0 }} created</div>
            <div class="activity-type-status ready">Ready to use</div>
        </div>
        
        <div class="activity-type-card disabled">
            <div class="activity-type-icon">🎯</div>
            <div class="activity-type-title">Game</div>
            <div class="activity-type-desc">Interactive games, quizzes, cultural challenges, and missions</div>
            <div class="activity-type-count">Coming soon</div>
            <div class="activity-type-status coming-soon">In development</div>
        </div>

        <div class="activity-type-card disabled">
            <div class="activity-type-icon">🌀</div>
            <div class="activity-type-title">Maze</div>
            <div class="activity-type-desc">Path-finding mazes with varying difficulty levels</div>
            <div class="activity-type-count">Coming soon</div>
            <div class="activity-type-status coming-soon">In development</div>
        </div>

        <div class="activity-type-card disabled">
            <div class="activity-type-icon">🔍</div>
            <div class="activity-type-title">Spot the Difference</div>
            <div class="activity-type-desc">Visual comparison games with cultural scenes</div>
            <div class="activity-type-count">Coming soon</div>
            <div class="activity-type-status coming-soon">In development</div>
        </div>

        <div class="activity-type-card disabled">
            <div class="activity-type-icon">🔠</div>
            <div class="activity-type-title">Word Search</div>
            <div class="activity-type-desc">Find hidden words including clan names and cultural terms</div>
            <div class="activity-type-count">Coming soon</div>
            <div class="activity-type-status coming-soon">In development</div>
        </div>
    </div>

    <style>
        .activity-type-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: var(--sp-4);
            margin-bottom: var(--sp-6);
        }

        .activity-type-card {
            background: rgba(255,255,255,.04);
            border: 1px solid rgba(255,255,255,.08);
            border-radius: var(--r-lg);
            padding: var(--sp-5);
            cursor: pointer;
            transition: all 0.3s cubic-bezier(.34,1.56,.64,1);
            position: relative;
            overflow: hidden;
        }

        .activity-type-card::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 50% 0%, var(--savanna-gold) 0%, transparent 68%);
            opacity: 0;
            transition: opacity 0.3s;
        }

        .activity-type-card:hover:not(.disabled) {
            border-color: var(--savanna-gold);
            transform: translateY(-8px) scale(1.02);
            box-shadow: var(--sh);
        }

        .activity-type-card:hover:not(.disabled)::before {
            opacity: 0.1;
        }

        .activity-type-card.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .activity-type-icon {
            font-size: 3rem;
            line-height: 1;
            margin-bottom: var(--sp-3);
            position: relative;
            z-index: 1;
        }

        .activity-type-title {
            font-family: var(--font-display);
            font-size: 1.4rem;
            font-weight: 900;
            color: #fff;
            margin-bottom: var(--sp-2);
            position: relative;
            z-index: 1;
        }

        .activity-type-desc {
            font-size: 0.9rem;
            color: var(--muted);
            line-height: 1.5;
            margin-bottom: var(--sp-3);
            position: relative;
            z-index: 1;
        }

        .activity-type-count {
            font-size: 0.8rem;
            color: rgba(255,255,255,.6);
            font-weight: 700;
            margin-bottom: var(--sp-2);
            position: relative;
            z-index: 1;
        }

        .activity-type-status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: var(--r-full);
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: relative;
            z-index: 1;
        }

        .activity-type-status.ready {
            background: rgba(74,124,89,.2);
            color: var(--banana-mid);
            border: 1px solid rgba(74,124,89,.4);
        }

        .activity-type-status.coming-soon {
            background: rgba(212,160,23,.15);
            color: var(--savanna-gold);
            border: 1px solid rgba(212,160,23,.3);
        }

        @media (max-width: 768px) {
            .activity-type-grid {
                grid-template-columns: 1fr;
                gap: var(--sp-3);
            }
            
            .activity-type-card {
                padding: var(--sp-4);
            }
        }
    </style>
</div>