<div class="cms-site-module">
    <div class="cms-header">
        <div>
            <h1 class="cms-page-title">Website Management</h1>
            <div class="cms-breadcrumb">Platform · Public Web · Front-end</div>
        </div>
        <div style="margin-left:auto; display:flex; gap:var(--sp-3)">
            <button class="btn btn-ghost btn-sm" wire:click="discardChanges">🔄 Discard Changes</button>
            <button class="btn btn-primary btn-sm" wire:click="publishLive">🚀 Publish Live</button>
        </div>
    </div>
    @if (session()->has('message'))
        <div style="margin-bottom:12px; padding:10px 14px; border:1px solid #DCFCE7; background:#F0FDF4; color:#166534; border-radius:10px; font-size:12px; font-weight:700;">
            {{ session('message') }}
        </div>
    @endif

    <div class="site-grid">
        <!-- Editor Column -->
        <div class="editor-col">
            <div class="editor-card">
                <div class="editor-tabs">
                    <button class="tab-btn {{ $active_tab == 'branding' ? 'active' : '' }}" wire:click="$set('active_tab', 'branding')">🎨 Branding</button>
                    <button class="tab-btn {{ $active_tab == 'content' ? 'active' : '' }}" wire:click="$set('active_tab', 'content')">📝 Story</button>
                    <button class="tab-btn {{ $active_tab == 'contact' ? 'active' : '' }}" wire:click="$set('active_tab', 'contact')">📞 Contact</button>
                    <button class="tab-btn {{ $active_tab == 'seo' ? 'active' : '' }}" wire:click="$set('active_tab', 'seo')">🔍 SEO</button>
                </div>

                <div class="editor-body">
                    @if($active_tab == 'branding')
                        <div class="form-group">
                            <label>Hero Headline (EN)</label>
                            <input type="text" wire:model.live="hero_headline" placeholder="Main slogan...">
                        </div>
                        <div class="form-group">
                            <label>Hero Subheadline (EN)</label>
                            <textarea wire:model.live="hero_subheadline" rows="3" placeholder="Supporting text..."></textarea>
                        </div>
                        <div class="form-group">
                            <label>Primary Site Logo</label>
                            <input type="file" wire:model="logo_upload" accept="image/*">
                            @if($logo_path)
                                <small style="display:block; margin-top:6px; color:var(--stone)">Current logo: <code>{{ $logo_path }}</code></small>
                            @endif
                        </div>
                    @elseif($active_tab == 'content')
                        <div class="form-group">
                            <label>Organization Mission & About</label>
                            <textarea wire:model.live="mission_text" rows="6" placeholder="Your organization's story..."></textarea>
                        </div>
                        <div class="form-group">
                            <label>Featured Tribes (displayed on home)</label>
                            <div class="tribe-selector">
                                @foreach($tribes as $tribe)
                                    <button type="button" class="tribe-chip {{ in_array($tribe->id, $featured_tribe_ids, true) ? 'checked' : '' }}" wire:click="toggleFeaturedTribe({{ $tribe->id }})">
                                        {{ $tribe->hero_emoji ?: '🌍' }} {{ $tribe->name }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @elseif($active_tab == 'contact')
                        <div class="form-group">
                            <label>Public Support Email</label>
                            <input type="email" wire:model.live="contact_email">
                        </div>
                        <div class="form-group">
                            <label>Contact Phone</label>
                            <input type="text" wire:model.live="contact_phone">
                        </div>
                        <div class="form-group">
                            <label>Physical Location</label>
                            <input type="text" wire:model.live="location_text">
                        </div>
                    @elseif($active_tab == 'seo')
                        <div class="form-group">
                            <label>SEO Title Tag</label>
                            <input type="text" wire:model.live="seo_title">
                        </div>
                        <div class="form-group">
                            <label>Meta Description</label>
                            <textarea rows="3" wire:model.live="seo_description"></textarea>
                        </div>
                    @endif
                    <div style="display:flex; justify-content:flex-end; gap:8px;">
                        <button class="btn btn-ghost btn-sm" type="button" wire:click="save">💾 Save Draft</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Preview Column -->
        <div class="preview-col">
            <div class="preview-header">
                <div class="status-dot"></div>
                <span>LIVE PREVIEW: culturekids.app/{{ strtolower(str_replace(' ', '-', $organization)) }}</span>
            </div>
            
            <div class="mock-browser">
                <!-- Branding Mock -->
                <div class="mock-nav">
                    <div class="nav-logo">🎨 {{ $organization }}</div>
                    <div class="nav-links">
                        <span>Stories</span>
                        <span>Songs</span>
                        <div class="mock-btn">Join Now</div>
                    </div>
                </div>

                <div class="mock-hero">
                    <h1>{{ $hero_headline }}</h1>
                    <p>{{ $hero_subheadline }}</p>
                    <div class="mock-actions">
                        <div class="mock-btn primary">Get Started</div>
                        <div class="mock-btn secondary">Watch Video</div>
                    </div>
                </div>

                <div class="mock-content">
                    <div class="mock-section-label">Our Mission</div>
                    <p>{{ $mission_text }}</p>
                    
                    <div class="mock-section-label">Explore Our Tribes</div>
                    <div class="mock-grid">
                        @forelse($featuredTribes->take(4) as $tribe)
                            <div class="mock-card">{{ $tribe->hero_emoji ?: '🌍' }} {{ $tribe->name }}</div>
                        @empty
                            <div class="mock-card">🌍 Featured tribe</div>
                        @endforelse
                    </div>
                </div>

                <div class="mock-footer">
                    <div>© 2026 {{ $organization }}</div>
                    <div>{{ $contact_email }}</div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .site-grid { display: grid; grid-template-columns: 420px 1fr; gap: 40px; }
        
        /* Editor Styles */
        .editor-col { height: calc(100vh - 200px); }
        .editor-card { background: #fff; border: 1px solid var(--cream-mid); border-radius: var(--r-xl); height: 100%; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 8px 32px rgba(26,18,8,.05); }
        
        .editor-tabs { display: flex; background: var(--cream-warm); border-bottom: 1px solid var(--cream-mid); padding: 8px; gap: 4px; }
        .tab-btn { flex: 1; padding: 10px; border: none; background: transparent; border-radius: 12px; font-size: 12px; font-weight: 800; color: var(--stone); cursor: pointer; transition: all 0.2s; }
        .tab-btn:hover { background: rgba(26,18,8,.04); }
        .tab-btn.active { background: #fff; color: var(--clay-red); box-shadow: 0 4px 12px rgba(26,18,8,.08); }

        .editor-body { padding: 32px; flex: 1; overflow-y: auto; }
        .form-group { margin-bottom: 24px; }
        .form-group label { display: block; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; color: var(--stone); margin-bottom: 8px; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 12px 18px; border-radius: 16px; border: 2px solid var(--cream-warm); background: #FDFBFA; font-family: var(--font-admin); font-size: 14px; color: var(--ink); outline: none; transition: all 0.2s; }
        .form-group input:focus, .form-group textarea:focus { border-color: var(--sunfire); background: #fff; box-shadow: 0 0 0 4px var(--sunfire-pale); }

        .upload-dropzone { border: 3px dashed var(--cream-mid); border-radius: var(--r-xl); padding: 24px; text-align: center; background: #FDFBFA; cursor: pointer; }
        .upload-dropzone span { font-size: 32px; display: block; margin-bottom: 8px; }
        .upload-dropzone div { font-weight: 800; font-size: 13px; color: var(--ink); }
        .upload-dropzone small { font-size: 11px; color: var(--stone); }

        .tribe-selector { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 8px; }
        .tribe-chip { padding: 8px 16px; border-radius: 99px; background: var(--cream-warm); font-size: 12px; font-weight: 700; color: var(--stone); cursor: pointer; transition: all 0.2s; }
        .tribe-chip.checked { background: var(--clay-red); color: var(--cms-text); }

        /* Preview Styles */
        .preview-col { display: flex; flex-direction: column; gap: 16px; }
        .preview-header { display: flex; align-items: center; gap: 12px; padding: 0 12px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; color: var(--stone); }
        .status-dot { width: 8px; height: 8px; background: var(--banana-green); border-radius: 50%; box-shadow: 0 0 12px var(--banana-mid); }
        
        .mock-browser { flex: 1; border: 4px solid var(--cream-mid); border-radius: 40px; background: #fff; overflow: hidden; box-shadow: 0 24px 64px rgba(26,18,8,.15); position: relative; }
        
        .mock-nav { padding: 24px 40px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--cream-mid); }
        .nav-logo { font-family: var(--font-display); font-size: 18px; font-weight: 800; color: var(--clay-red); }
        .nav-links { display: flex; align-items: center; gap: 24px; font-size: 11px; font-weight: 800; color: var(--stone); }
        .mock-btn { background: var(--clay-red); color: var(--cms-text); padding: 10px 24px; border-radius: 99px; font-size: 11px; }

        .mock-hero { padding: 80px 40px; text-align: center; background: linear-gradient(130deg, var(--indigo-night), var(--sky-dusk)); color: var(--cms-text); position: relative; overflow: hidden; }
        .mock-hero::before { content: ''; position: absolute; inset: 0; background-image: radial-gradient(circle at 20% 20%, rgba(232,135,42,.2), transparent 50%); }
        .mock-hero h1 { font-family: var(--font-display); font-size: 38px; font-weight: 800; line-height: 1.1; margin-bottom: 16px; position: relative; }
        .mock-hero p { font-size: 14px; color: var(--cms-text-muted); max-width: 420px; margin: 0 auto 24px; position: relative; }
        .mock-actions { display: flex; justify-content: center; gap: 12px; position: relative; }
        .mock-btn.secondary { background: var(--cms-surface-raised); border: 1px solid var(--cms-border); }

        .mock-content { padding: 60px 40px; }
        .mock-section-label { font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 2px; color: var(--clay-red); margin-bottom: 16px; }
        .mock-content p { font-size: 14px; line-height: 1.7; color: var(--ink-light); margin-bottom: 40px; }
        .mock-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .mock-card { padding: 24px; border-radius: 20px; text-align: center; background: var(--cream); font-weight: 800; color: var(--ink); font-size: 14px; border: 2px solid var(--cream-mid); }

        .mock-footer { padding: 40px; background: var(--cream-warm); border-top: 1px solid var(--cream-mid); display: flex; justify-content: space-between; font-size: 11px; font-weight: 700; color: var(--stone); }
    </style>
</div>
