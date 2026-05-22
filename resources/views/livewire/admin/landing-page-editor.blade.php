@php use Illuminate\Support\Facades\Storage; @endphp
<div class="cms-site-module">
    <div class="cms-header">
        <div>
            <h1 class="cms-page-title">Platform Homepage</h1>
            <div class="cms-breadcrumb">Super Admin · Public site · {{ $previewUrl }}</div>
        </div>
        <div style="margin-left:auto; display:flex; gap:var(--sp-3)">
            <button class="btn btn-ghost btn-sm" type="button" wire:click="discardChanges">🔄 Discard</button>
            <button class="btn btn-ghost btn-sm" type="button" wire:click="saveDraft">💾 Save draft</button>
            <button class="btn btn-primary btn-sm" type="button" wire:click="publishLive">🚀 Publish live</button>
        </div>
    </div>

    @if (session()->has('message'))
        <div style="margin-bottom:12px; padding:10px 14px; border:1px solid #DCFCE7; background:#F0FDF4; color:#166534; border-radius:10px; font-size:12px; font-weight:700;">
            {{ session('message') }}
        </div>
    @endif

    <div class="site-grid">
        <div class="editor-col">
            <div class="editor-card">
                <div class="editor-tabs">
                    <button type="button" class="tab-btn {{ $active_tab === 'hero' ? 'active' : '' }}" wire:click="$set('active_tab', 'hero')">🦸 Hero</button>
                    <button type="button" class="tab-btn {{ $active_tab === 'style' ? 'active' : '' }}" wire:click="$set('active_tab', 'style')">🎨 Colours &amp; fonts</button>
                    <button type="button" class="tab-btn {{ $active_tab === 'peoples' ? 'active' : '' }}" wire:click="$set('active_tab', 'peoples')">🌍 {{ heritage('people_plural') }}</button>
                    <button type="button" class="tab-btn {{ $active_tab === 'cta' ? 'active' : '' }}" wire:click="$set('active_tab', 'cta')">📣 CTA</button>
                    <button type="button" class="tab-btn {{ $active_tab === 'seo' ? 'active' : '' }}" wire:click="$set('active_tab', 'seo')">🔍 SEO</button>
                </div>

                <div class="editor-body">
                    @if ($active_tab === 'hero')
                        <div class="form-group">
                            <label>Headline (before highlight)</label>
                            <input type="text" wire:model.live="hero_headline" placeholder="Bring">
                        </div>
                        <div class="form-group">
                            <label>Highlighted phrase</label>
                            <input type="text" wire:model.live="hero_highlight" placeholder="Africa's Stories">
                        </div>
                        <div class="form-group">
                            <label>Headline (after highlight)</label>
                            <input type="text" wire:model.live="hero_headline_suffix" placeholder="to Life">
                        </div>
                        <div class="form-group">
                            <label>Subtitle</label>
                            <textarea wire:model.live="hero_subtitle" rows="4"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Hero comic (cover / first panel)</label>
                            <select wire:model.live="hero_comic_id">
                                <option value="">— Auto (latest published comic) —</option>
                                @foreach ($comics as $comic)
                                    <option value="{{ $comic->id }}">{{ $comic->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Custom hero image (overrides comic)</label>
                            <input type="file" wire:model="hero_image_upload" accept="image/*">
                            @if ($hero_image_path)
                                <small style="display:block; margin-top:6px; color:var(--stone)">Current: {{ $hero_image_path }}</small>
                            @endif
                            @error('hero_image_upload') <div class="form-error">{{ $message }}</div> @enderror
                        </div>
                    @elseif ($active_tab === 'style')
                        <div class="form-group">
                            <label>Primary colour</label>
                            <input type="color" wire:model.live="primary_color">
                        </div>
                        <div class="form-group">
                            <label>Secondary colour</label>
                            <input type="color" wire:model.live="secondary_color">
                        </div>
                        <div class="form-group">
                            <label>Accent colour</label>
                            <input type="color" wire:model.live="accent_color">
                        </div>
                        <div class="form-group">
                            <label>Hero background (start)</label>
                            <input type="color" wire:model.live="hero_bg_start">
                        </div>
                        <div class="form-group">
                            <label>Hero background (end)</label>
                            <input type="color" wire:model.live="hero_bg_end">
                        </div>
                        <div class="form-group">
                            <label>Heading font (Google Fonts name)</label>
                            <input type="text" wire:model.live="font_heading" placeholder="Baloo 2">
                        </div>
                        <div class="form-group">
                            <label>Body font</label>
                            <input type="text" wire:model.live="font_body" placeholder="Inter">
                        </div>
                    @elseif ($active_tab === 'peoples')
                        <div class="form-group">
                            <label>{{ heritage('people_plural') }} section title (optional)</label>
                            <input type="text" wire:model.live="peoples_section_title" placeholder="{{ heritage('explore_peoples_count', ['count' => $peoples_count]) }}">
                        </div>
                        <div class="form-group">
                            <label>Count shown in title</label>
                            <input type="number" min="1" max="200" wire:model.live="peoples_count">
                        </div>
                        <div class="form-group">
                            <label>Featured {{ strtolower(heritage('ugandan_peoples')) }} on homepage</label>
                            <div class="tribe-selector">
                                @foreach ($peoples as $person)
                                    <button type="button" class="tribe-chip {{ in_array($person->id, $featured_tribe_ids, true) ? 'checked' : '' }}" wire:click="toggleFeaturedPeople({{ $person->id }})">
                                        {{ $person->hero_emoji ?: '🌍' }} {{ $person->name }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @elseif ($active_tab === 'cta')
                        <div class="form-group">
                            <label>CTA title</label>
                            <input type="text" wire:model.live="cta_title">
                        </div>
                        <div class="form-group">
                            <label>CTA subtitle</label>
                            <textarea wire:model.live="cta_subtitle" rows="3"></textarea>
                        </div>
                    @else
                        <div class="form-group">
                            <label>SEO title</label>
                            <input type="text" wire:model.live="seo_title">
                        </div>
                        <div class="form-group">
                            <label>Meta description</label>
                            <textarea wire:model.live="seo_description" rows="3"></textarea>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="preview-col">
            <div class="preview-header">
                <div class="status-dot"></div>
                <span>LIVE PREVIEW · {{ $previewUrl }}</span>
            </div>
            <div class="mock-browser" style="--mock-primary: {{ $primary_color }}; --mock-secondary: {{ $secondary_color }};">
                <div class="mock-hero" style="background: linear-gradient(135deg, {{ $hero_bg_start }}, {{ $hero_bg_end }}); color: {{ $secondary_color }}; text-align:left; display:grid; grid-template-columns:1fr 1fr; gap:24px; align-items:center;">
                    <div>
                        <h1 style="font-family:{{ $font_heading }},sans-serif; color:{{ $secondary_color }};">
                            {{ $hero_headline }} <span style="color:{{ $primary_color }}">{{ $hero_highlight }}</span> {{ $hero_headline_suffix }}
                        </h1>
                        <p style="color:var(--ink-light); max-width:none; margin:0 0 16px;">{{ $hero_subtitle }}</p>
                        <div class="mock-btn" style="background:{{ $primary_color }}">Start Free Trial</div>
                    </div>
                    <div style="min-height:160px; border-radius:20px; background:rgba(255,255,255,.6); display:flex; align-items:center; justify-content:center; overflow:hidden;">
                        @if ($hero_image_upload)
                            <img src="{{ $hero_image_upload->temporaryUrl() }}" alt="" style="max-width:100%; max-height:200px; object-fit:contain">
                        @elseif ($hero_image_path)
                            <img src="{{ Storage::disk('public')->url($hero_image_path) }}" alt="" style="max-width:100%; max-height:200px; object-fit:contain">
                        @else
                            <span style="font-size:48px">🦸</span>
                        @endif
                    </div>
                </div>
                <div class="mock-content">
                    <div class="mock-section-label" style="color:{{ $primary_color }}">{{ $peoples_section_title ?: heritage('explore_peoples_count', ['count' => $peoples_count]) }}</div>
                    <div class="mock-grid">
                        @foreach ($peoples->whereIn('id', $featured_tribe_ids)->take(4) as $person)
                            <div class="mock-card">{{ $person->hero_emoji ?: '🌍' }} {{ $person->name }}</div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .site-grid { display: grid; grid-template-columns: 420px 1fr; gap: 40px; }
        .editor-col { height: calc(100vh - 200px); }
        .editor-card { background: #fff; border: 1px solid var(--cream-mid); border-radius: var(--r-xl); height: 100%; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 8px 32px rgba(26,18,8,.05); }
        .editor-tabs { display: flex; flex-wrap: wrap; background: var(--cream-warm); border-bottom: 1px solid var(--cream-mid); padding: 8px; gap: 4px; }
        .tab-btn { flex: 1; min-width: 72px; padding: 10px; border: none; background: transparent; border-radius: 12px; font-size: 11px; font-weight: 800; color: var(--stone); cursor: pointer; }
        .tab-btn.active { background: #fff; color: var(--clay-red); box-shadow: 0 4px 12px rgba(26,18,8,.08); }
        .editor-body { padding: 32px; flex: 1; overflow-y: auto; }
        .form-group { margin-bottom: 24px; }
        .form-group label { display: block; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; color: var(--stone); margin-bottom: 8px; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 12px 18px; border-radius: 16px; border: 2px solid var(--cream-warm); background: #FDFBFA; font-family: var(--font-admin); font-size: 14px; }
        .tribe-selector { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 8px; }
        .tribe-chip { padding: 8px 16px; border-radius: 99px; background: var(--cream-warm); font-size: 12px; font-weight: 700; color: var(--stone); cursor: pointer; border: none; }
        .tribe-chip.checked { background: var(--clay-red); color: #fff; }
        .preview-col { display: flex; flex-direction: column; gap: 16px; }
        .preview-header { display: flex; align-items: center; gap: 12px; padding: 0 12px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; color: var(--stone); }
        .status-dot { width: 8px; height: 8px; background: var(--banana-green); border-radius: 50%; }
        .mock-browser { flex: 1; border: 4px solid var(--cream-mid); border-radius: 40px; background: #fff; overflow: hidden; box-shadow: 0 24px 64px rgba(26,18,8,.15); }
        .mock-hero { padding: 48px 40px; }
        .mock-hero h1 { font-size: 28px; font-weight: 800; line-height: 1.1; margin-bottom: 12px; }
        .mock-content { padding: 40px; }
        .mock-section-label { font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 16px; }
        .mock-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .mock-card { padding: 20px; border-radius: 20px; text-align: center; background: var(--cream); font-weight: 800; font-size: 13px; border: 2px solid var(--cream-mid); }
        .mock-btn { display: inline-block; color: #fff; padding: 10px 24px; border-radius: 99px; font-size: 11px; font-weight: 800; }
        @media (max-width: 1100px) { .site-grid { grid-template-columns: 1fr; } }
    </style>
</div>
