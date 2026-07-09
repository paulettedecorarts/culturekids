@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;
@endphp
<div class="cms-site-module">
    <div class="lp-editor-header">
        <div>
            <h1 class="sa-page-title">Platform Homepage</h1>
            <div class="sa-breadcrumb">Super Admin · Public site · {{ $previewUrl }}</div>
        </div>
        <div class="lp-editor-actions">
            <button class="btn btn-ghost btn-sm" type="button" wire:click="discardChanges">Discard</button>
            <button class="btn btn-ghost btn-sm" type="button" wire:click="saveDraft">Save draft</button>
            <button class="btn btn-primary btn-sm" type="button" wire:click="publishLive">Publish live</button>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="lp-flash" role="status">
            {{ session('message') }}
        </div>
    @endif

    <div class="lp-site-grid">
        <div class="lp-editor-col">
            <div class="lp-editor-card">
                <div class="lp-editor-tabs" role="tablist" aria-label="Homepage sections">
                    <button type="button" role="tab" class="lp-tab-btn {{ $active_tab === 'hero' ? 'active' : '' }}" wire:click="$set('active_tab', 'hero')">Hero</button>
                    <button type="button" role="tab" class="lp-tab-btn {{ $active_tab === 'style' ? 'active' : '' }}" wire:click="$set('active_tab', 'style')">Style</button>
                    <button type="button" role="tab" class="lp-tab-btn {{ $active_tab === 'peoples' ? 'active' : '' }}" wire:click="$set('active_tab', 'peoples')">{{ heritage('people_plural') }}</button>
                    <button type="button" role="tab" class="lp-tab-btn {{ $active_tab === 'pricing' ? 'active' : '' }}" wire:click="$set('active_tab', 'pricing')">Pricing</button>
                    <button type="button" role="tab" class="lp-tab-btn {{ $active_tab === 'cta' ? 'active' : '' }}" wire:click="$set('active_tab', 'cta')">CTA</button>
                    <button type="button" role="tab" class="lp-tab-btn {{ $active_tab === 'seo' ? 'active' : '' }}" wire:click="$set('active_tab', 'seo')">SEO</button>
                </div>

                <div class="lp-editor-body">
                    @if ($active_tab === 'hero')
                        <div class="lp-field">
                            <label for="hero_headline">Headline (before highlight)</label>
                            <input id="hero_headline" type="text" wire:model.live="hero_headline" placeholder="Bring">
                        </div>
                        <div class="lp-field">
                            <label for="hero_highlight">Highlighted phrase</label>
                            <input id="hero_highlight" type="text" wire:model.live="hero_highlight" placeholder="Africa's Stories">
                        </div>
                        <div class="lp-field">
                            <label for="hero_headline_suffix">Headline (after highlight)</label>
                            <input id="hero_headline_suffix" type="text" wire:model.live="hero_headline_suffix" placeholder="to Life">
                        </div>
                        <div class="lp-field">
                            <label for="hero_subtitle">Subtitle</label>
                            <textarea id="hero_subtitle" wire:model.live="hero_subtitle" rows="4"></textarea>
                        </div>
                        <div class="lp-field">
                            <label for="hero_comic_id">Hero comic (cover / first panel)</label>
                            <select id="hero_comic_id" wire:model.live="hero_comic_id">
                                <option value="">— Auto (latest published comic) —</option>
                                @foreach ($comics as $comic)
                                    <option value="{{ $comic->id }}">{{ $comic->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="lp-field">
                            <label for="hero_image_upload">Custom hero image (overrides comic)</label>
                            <input id="hero_image_upload" type="file" wire:model="hero_image_upload" accept="image/*">
                            @if ($hero_image_path)
                                <small class="lp-field-hint">Current: {{ $hero_image_path }}</small>
                            @endif
                            @error('hero_image_upload') <div class="form-error">{{ $message }}</div> @enderror
                        </div>
                    @elseif ($active_tab === 'style')
                        <div class="lp-form-row lp-form-row--2">
                            <div class="lp-field">
                                <label for="primary_color">Primary colour</label>
                                <input id="primary_color" type="color" wire:model.live="primary_color">
                            </div>
                            <div class="lp-field">
                                <label for="secondary_color">Secondary colour</label>
                                <input id="secondary_color" type="color" wire:model.live="secondary_color">
                            </div>
                        </div>
                        <div class="lp-form-row lp-form-row--2">
                            <div class="lp-field">
                                <label for="accent_color">Accent colour</label>
                                <input id="accent_color" type="color" wire:model.live="accent_color">
                            </div>
                            <div class="lp-field">
                                <label for="hero_bg_start">Hero background (start)</label>
                                <input id="hero_bg_start" type="color" wire:model.live="hero_bg_start">
                            </div>
                        </div>
                        <div class="lp-field">
                            <label for="hero_bg_end">Hero background (end)</label>
                            <input id="hero_bg_end" type="color" wire:model.live="hero_bg_end">
                        </div>
                        <div class="lp-field">
                            <label for="font_heading">Heading font (Google Fonts)</label>
                            <input id="font_heading" type="text" wire:model.live="font_heading" placeholder="Baloo 2">
                        </div>
                        <div class="lp-field">
                            <label for="font_body">Body font</label>
                            <input id="font_body" type="text" wire:model.live="font_body" placeholder="Inter">
                        </div>
                    @elseif ($active_tab === 'peoples')
                        <div class="lp-field">
                            <label for="peoples_section_title">{{ heritage('people_plural') }} section title</label>
                            <input id="peoples_section_title" type="text" wire:model.live="peoples_section_title" placeholder="{{ heritage('explore_peoples_count', ['count' => $peoples_count]) }}">
                        </div>
                        <div class="lp-field">
                            <label for="peoples_count">Count in title</label>
                            <input id="peoples_count" type="number" min="1" max="200" wire:model.live="peoples_count">
                        </div>
                        <div class="lp-field">
                            <label>Featured on homepage</label>
                            <div class="tribe-selector">
                                @foreach ($peoples as $person)
                                    <button type="button" class="tribe-chip {{ in_array($person->id, $featured_tribe_ids, true) ? 'checked' : '' }}" wire:click="toggleFeaturedPeople({{ $person->id }})">
                                        {{ $person->hero_emoji ?: '🌍' }} {{ $person->name }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @elseif ($active_tab === 'pricing')
                        <div class="lp-field">
                            <label for="pricing_section_title">Section title</label>
                            <input id="pricing_section_title" type="text" wire:model.live="pricing_section_title" placeholder="Plans that scale with you">
                        </div>
                        <div class="lp-field">
                            <label for="pricing_section_lead">Section intro</label>
                            <textarea id="pricing_section_lead" wire:model.live="pricing_section_lead" rows="3"></textarea>
                        </div>

                        <div class="lp-packages-toolbar">
                            <span>Packages ({{ count($pricing_plans) }})</span>
                            <button type="button" class="btn btn-primary btn-sm" wire:click="addPricingPlan">+ Add package</button>
                        </div>

                        @forelse ($pricing_plans as $index => $plan)
                            <article class="lp-plan-card" wire:key="plan-{{ $plan['id'] ?? $index }}">
                                <header class="lp-plan-card__head">
                                    <span class="lp-plan-card__title">Package {{ $index + 1 }}</span>
                                    <div class="lp-plan-card__actions">
                                        <button type="button" class="btn btn-ghost btn-sm" wire:click="movePricingPlanUp('{{ $plan['id'] }}')" @disabled($index === 0) aria-label="Move up">↑</button>
                                        <button type="button" class="btn btn-ghost btn-sm" wire:click="movePricingPlanDown('{{ $plan['id'] }}')" @disabled($index === count($pricing_plans) - 1) aria-label="Move down">↓</button>
                                        <button type="button" class="btn btn-ghost btn-sm" wire:click="setFeaturedPricingPlan('{{ $plan['id'] }}')">
                                            {{ !empty($plan['is_featured']) ? '★ Featured' : 'Feature' }}
                                        </button>
                                        <button type="button" class="btn btn-ghost btn-sm" wire:click="removePricingPlan('{{ $plan['id'] }}')" wire:confirm="Remove this package?">Remove</button>
                                    </div>
                                </header>

                                <div class="lp-field">
                                    <label for="plan-name-{{ $index }}">Plan name</label>
                                    <input id="plan-name-{{ $index }}" type="text" wire:model.live="pricing_plans.{{ $index }}.name">
                                </div>

                                <div class="lp-form-row lp-form-row--2">
                                    <div class="lp-field">
                                        <label for="plan-price-{{ $index }}">Price</label>
                                        <input id="plan-price-{{ $index }}" type="text" wire:model.live="pricing_plans.{{ $index }}.price_display" placeholder="$0 or Custom">
                                    </div>
                                    <div class="lp-field">
                                        <label for="plan-suffix-{{ $index }}">Price suffix</label>
                                        <input id="plan-suffix-{{ $index }}" type="text" wire:model.live="pricing_plans.{{ $index }}.price_suffix" placeholder="/ year">
                                    </div>
                                </div>

                                <div class="lp-field">
                                    <label for="plan-note-{{ $index }}">Subtitle / note</label>
                                    <input id="plan-note-{{ $index }}" type="text" wire:model.live="pricing_plans.{{ $index }}.note">
                                </div>

                                <div class="lp-field">
                                    <label for="plan-features-{{ $index }}">Features (one per line)</label>
                                    <textarea id="plan-features-{{ $index }}" wire:model.live="pricing_plans.{{ $index }}.features_text" rows="4"></textarea>
                                </div>

                                <div class="lp-field">
                                    <label for="plan-badge-{{ $index }}">Badge (optional)</label>
                                    <input id="plan-badge-{{ $index }}" type="text" wire:model.live="pricing_plans.{{ $index }}.badge" placeholder="Most popular">
                                </div>

                                <div class="lp-field">
                                    <label class="lp-checkbox-field" for="plan-visible-{{ $index }}">
                                        <input id="plan-visible-{{ $index }}" type="checkbox" wire:model.live="pricing_plans.{{ $index }}.is_visible">
                                        <span>Show on homepage</span>
                                    </label>
                                </div>

                                <div class="lp-cta-block">
                                    <p class="lp-cta-block__label">Call to action</p>
                                    <div class="lp-field">
                                        <label for="plan-cta-label-{{ $index }}">Button label</label>
                                        <input id="plan-cta-label-{{ $index }}" type="text" wire:model.live="pricing_plans.{{ $index }}.cta_label">
                                    </div>
                                    <div class="lp-field">
                                        <label for="plan-cta-href-{{ $index }}">Button link</label>
                                        <input id="plan-cta-href-{{ $index }}" type="text" wire:model.live="pricing_plans.{{ $index }}.cta_href" placeholder="/register or https://…">
                                    </div>
                                    <div class="lp-field">
                                        <label for="plan-cta-style-{{ $index }}">Button style</label>
                                        <select id="plan-cta-style-{{ $index }}" wire:model.live="pricing_plans.{{ $index }}.cta_style">
                                            <option value="primary">Primary (filled)</option>
                                            <option value="outline">Outline</option>
                                        </select>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <p class="lp-empty-hint">No packages yet. Add your first plan above.</p>
                        @endforelse
                    @elseif ($active_tab === 'cta')
                        <div class="lp-field">
                            <label for="cta_title">CTA title</label>
                            <input id="cta_title" type="text" wire:model.live="cta_title">
                        </div>
                        <div class="lp-field">
                            <label for="cta_subtitle">CTA subtitle</label>
                            <textarea id="cta_subtitle" wire:model.live="cta_subtitle" rows="3"></textarea>
                        </div>
                    @elseif ($active_tab === 'seo')
                        <div class="lp-field">
                            <label for="seo_title">SEO title</label>
                            <input id="seo_title" type="text" wire:model.live="seo_title">
                        </div>
                        <div class="lp-field">
                            <label for="seo_description">Meta description</label>
                            <textarea id="seo_description" wire:model.live="seo_description" rows="3"></textarea>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <aside class="lp-preview-col" aria-label="Homepage preview">
            <div class="lp-preview-header">
                <span class="lp-status-dot" aria-hidden="true"></span>
                <span>Live preview · {{ $previewUrl }}</span>
            </div>
            <div class="lp-mock-browser" style="--mock-primary: {{ $primary_color }}; --mock-secondary: {{ $secondary_color }};">
                <div class="lp-mock-hero" style="background: linear-gradient(135deg, {{ $hero_bg_start }}, {{ $hero_bg_end }});">
                    <div>
                        <h1 style="font-family:{{ $font_heading }},sans-serif; color:{{ $secondary_color }};">
                            {{ $hero_headline }}
                            <span style="color:{{ $primary_color }}">{{ $hero_highlight }}</span>
                            {{ $hero_headline_suffix }}
                        </h1>
                        <p class="lp-mock-hero__subtitle">{{ Str::limit($hero_subtitle, 160) }}</p>
                    </div>
                    <div class="lp-mock-hero-visual">
                        @if ($hero_image_upload)
                            <img src="{{ $hero_image_upload->temporaryUrl() }}" alt="" style="max-width:100%; max-height:180px; object-fit:contain">
                        @elseif ($hero_image_path)
                            <img src="{{ Storage::disk('public')->url($hero_image_path) }}" alt="" style="max-width:100%; max-height:180px; object-fit:contain">
                        @else
                            <span style="font-size:40px" aria-hidden="true">🦸</span>
                        @endif
                    </div>
                </div>
                <div class="lp-mock-content">
                    <div class="lp-mock-section-label" style="color:{{ $primary_color }}">
                        {{ $peoples_section_title ?: heritage('explore_peoples_count', ['count' => $peoples_count]) }}
                    </div>
                    <div class="lp-mock-grid">
                        @foreach ($peoples->whereIn('id', $featured_tribe_ids)->take(4) as $person)
                            <div class="lp-mock-card">{{ $person->hero_emoji ?: '🌍' }} {{ $person->name }}</div>
                        @endforeach
                    </div>
                    <div class="lp-mock-section-label" style="color:{{ $primary_color }}; margin-top:28px;">
                        {{ $pricing_section_title ?: 'Pricing' }}
                    </div>
                    @if ($pricing_section_lead)
                        <p class="lp-mock-section-lead">{{ Str::limit($pricing_section_lead, 100) }}</p>
                    @endif
                    <div class="lp-mock-pricing">
                        @foreach ($previewPlans as $plan)
                            <div class="lp-mock-price {{ !empty($plan['is_featured']) ? 'lp-mock-price--featured' : '' }}">
                                @if (!empty($plan['badge']))
                                    <span class="lp-mock-price__badge">{{ $plan['badge'] }}</span>
                                @endif
                                <div class="lp-mock-price__name">{{ $plan['name'] }}</div>
                                <div class="lp-mock-price__amount">{{ $plan['price_display'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </aside>
    </div>

    <style>{!! file_get_contents(resource_path('css/landing-page-editor.css')) !!}</style>
    <style>
        .cms-site-module .lp-flash {
            margin-bottom: 16px;
            padding: 12px 16px;
            border: 1px solid #bbf7d0;
            background: #f0fdf4;
            color: #166534;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 700;
        }
        .cms-site-module .lp-field-hint {
            display: block;
            margin-top: 8px;
            font-size: 12px;
            color: var(--stone, #6b6560);
            word-break: break-all;
        }
    </style>
</div>
