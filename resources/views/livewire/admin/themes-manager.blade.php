<div class="themes-mgmt-page">
    @if(! $isOrgAdminOnly)
        <div class="themes-mgmt-org-bar">
            <span class="themes-mgmt-org-label">Configuring For:</span>
            <select wire:model.live="selectedOrgId" class="themes-mgmt-org-select">
                <option value="">All Organizations</option>
                <option value="global">Global (Platform-wide)</option>
                @foreach($organisations as $org)
                    <option value="{{ $org->id }}">{{ $org->name }}</option>
                @endforeach
            </select>
        </div>
    @endif

    <header class="themes-mgmt-header">
        <div>
            <div class="sa-page-title">Theme Management</div>
            <div class="sa-breadcrumb">Visual identity & branding control</div>
        </div>
        <button type="button" wire:click="create" class="btn btn-primary btn-sm themes-mgmt-create-btn">🎨 Create Theme</button>
    </header>

    @if (session()->has('message'))
        <div class="cms-flash-success">✨ {{ session('message') }}</div>
    @endif

    @if (session()->has('error'))
        <div class="cms-flash-error">⚠️ {{ session('error') }}</div>
    @endif

    <div class="themes-mgmt-grid">
        @forelse($themes as $theme)
            <article class="theme-mgr-card">
                <div class="theme-mgr-card-swatches">
                    <div style="background:{{ $theme->colors['primary'] }}"></div>
                    <div style="background:{{ $theme->colors['secondary'] }}"></div>
                    <div style="background:{{ $theme->colors['accent'] }}"></div>
                    <div style="background:{{ $theme->colors['success'] }}"></div>
                </div>

                <div class="theme-mgr-card-body">
                    <div class="theme-mgr-card-title-row">
                        <div class="theme-mgr-card-title-wrap">
                            <h3 class="theme-mgr-card-title">
                                {{ $theme->name }}
                                @if($theme->is_default)
                                    <span class="theme-mgr-default-badge">DEFAULT</span>
                                @endif
                            </h3>
                            <p class="theme-mgr-card-meta">
                                {{ $theme->slug }}
                                @if($theme->org_id)
                                    · {{ $theme->organisation->name }}
                                @else
                                    · Global
                                @endif
                            </p>
                        </div>
                    </div>

                    @if($theme->description)
                        <p class="theme-mgr-card-desc">{{ $theme->description }}</p>
                    @endif

                    <div class="sa-table-actions theme-mgr-card-actions">
                        @if(!$theme->is_default)
                            <button type="button" wire:click="setDefault({{ $theme->id }})" class="sa-table-action sa-table-action--accent sa-table-action--grow">Set default</button>
                        @endif
                        <button type="button" wire:click="edit({{ $theme->id }})" class="sa-table-action sa-table-action--grow">Edit</button>
                        @if(!$theme->is_default)
                            <button type="button" wire:click="delete({{ $theme->id }})" wire:confirm="Delete this theme?" class="sa-table-action sa-table-action--danger">Delete</button>
                        @endif
                    </div>
                </div>
            </article>
        @empty
            <div class="themes-mgmt-empty">
                <div class="themes-mgmt-empty-icon" aria-hidden="true">🎨</div>
                <p class="themes-mgmt-empty-title">No themes created</p>
                <p class="themes-mgmt-empty-text">Create your first theme to customize the platform appearance.</p>
            </div>
        @endforelse
    </div>

    <div class="themes-mgmt-pagination">
        {{ $themes->links(data: ['scrollTo' => false]) }}
    </div>

    @if($showModal)
        <div class="cms-modal-backdrop sa-modal-backdrop theme-mgr-backdrop">
            <div class="cms-modal-panel sa-modal-panel theme-mgr-modal-panel">
                <header class="theme-mgr-modal-header">
                    <div class="theme-mgr-modal-header-text">
                        <h2 class="theme-mgr-modal-title">{{ $editing ? '🎨 Edit Theme' : '✨ Create New Theme' }}</h2>
                        <p class="theme-mgr-modal-subtitle">Design your platform's visual identity</p>
                    </div>
                    <button type="button" wire:click="$set('showModal', false)" class="theme-mgr-modal-close" aria-label="Close">×</button>
                </header>

                <div class="theme-mgr-modal-body">
                    <div class="theme-mgr-modal-columns">
                    <form id="theme-mgr-form" wire:submit.prevent="save" class="theme-mgr-form">
                        <section class="theme-mgr-section">
                            <h3 class="theme-mgr-section-title">Basic Information</h3>
                            <div class="theme-mgr-basic-grid">
                                <div class="theme-mgr-basic-primary">
                                <div class="theme-mgr-field">
                                    <label class="theme-mgr-label">Theme Name</label>
                                    <input wire:model.live="name" type="text" placeholder="Savanna Sunset" class="theme-mgr-input">
                                    @error('name') <div class="theme-mgr-error">{{ $message }}</div> @enderror
                                </div>

                                <div class="theme-mgr-field">
                                    <label class="theme-mgr-label">Slug (Auto-generated)</label>
                                    <input wire:model="slug" type="text" readonly class="theme-mgr-input theme-mgr-input--readonly theme-mgr-input--mono">
                                </div>

                                <div class="theme-mgr-field">
                                    <label class="theme-mgr-label">Description</label>
                                    <textarea wire:model="description" rows="2" placeholder="A warm, earthy theme inspired by African savannas..." class="theme-mgr-input theme-mgr-textarea"></textarea>
                                </div>
                                </div>

                                <div class="theme-mgr-basic-org">
                                    <div class="theme-mgr-field">
                                        <label class="theme-mgr-label">Organization</label>
                                        @if($isOrgAdminOnly)
                                            <input type="text" value="{{ $organisations->first()?->name ?? 'My Organization' }}" readonly class="theme-mgr-input theme-mgr-input--readonly">
                                            <p class="theme-mgr-hint">Themes are locked to your organization.</p>
                                        @else
                                            <select wire:model="org_id" class="theme-mgr-input theme-mgr-select">
                                                <option value="">Global (Platform-wide)</option>
                                                @foreach($organisations as $org)
                                                    <option value="{{ $org->id }}">{{ $org->name }}</option>
                                                @endforeach
                                            </select>
                                            <p class="theme-mgr-hint">Leave as Global for platform-wide theme, or select an organization for custom branding</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </section>

                        <section class="theme-mgr-section">
                            <h3 class="theme-mgr-section-title">Quick Presets</h3>
                            <div class="theme-mgr-preset-grid">
                                @foreach($presets as $key => $preset)
                                    <button
                                        type="button"
                                        wire:click="applyPreset('{{ $key }}')"
                                        class="theme-mgr-preset-btn"
                                    >
                                        <div class="theme-mgr-preset-swatches">
                                            <span style="background:{{ $preset['colors']['primary'] }}"></span>
                                            <span style="background:{{ $preset['colors']['secondary'] }}"></span>
                                            <span style="background:{{ $preset['colors']['accent'] }}"></span>
                                        </div>
                                        <span class="theme-mgr-preset-name">{{ $preset['name'] }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </section>

                        <section class="theme-mgr-section">
                            <h3 class="theme-mgr-section-title">Color Palette</h3>
                            <div class="theme-mgr-color-grid">
                                @foreach(['primary' => 'Primary', 'secondary' => 'Secondary', 'accent' => 'Accent', 'success' => 'Success', 'warning' => 'Warning', 'danger' => 'Danger'] as $key => $label)
                                    <div class="theme-mgr-color-field">
                                        <label class="theme-mgr-label">{{ $label }}</label>
                                        <div class="theme-mgr-color-inputs">
                                            <input wire:model.live="{{ $key }}" type="color" class="theme-mgr-color-picker">
                                            <input wire:model.live="{{ $key }}" type="text" class="theme-mgr-input theme-mgr-input--mono">
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </section>

                        <section class="theme-mgr-section">
                            <h3 class="theme-mgr-section-title">Surface & Text Colors</h3>
                            <div class="theme-mgr-color-grid">
                                @foreach(['background' => 'Background', 'surface' => 'Surface', 'text_primary' => 'Text Primary', 'text_secondary' => 'Text Secondary', 'text_muted' => 'Text Muted'] as $key => $label)
                                    <div class="theme-mgr-color-field">
                                        <label class="theme-mgr-label">{{ $label }}</label>
                                        <div class="theme-mgr-color-inputs">
                                            <input wire:model.live="{{ $key }}" type="color" class="theme-mgr-color-picker">
                                            <input wire:model.live="{{ $key }}" type="text" class="theme-mgr-input theme-mgr-input--mono">
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </section>

                    </form>

                    <aside class="theme-mgr-preview" style="background:{{ $background }}">
                        <h3 class="theme-mgr-preview-title" style="color:{{ $text_primary }}">Live Preview</h3>

                        <div class="theme-mgr-preview-card" style="background:{{ $surface }}">
                            <h4 class="theme-mgr-preview-card-title" style="color:{{ $text_primary }}">Sample Card</h4>
                            <p class="theme-mgr-preview-card-text" style="color:{{ $text_secondary }}">This is how your content will look with the selected theme colors.</p>
                            <div class="theme-mgr-preview-btns">
                                <button type="button" style="background:{{ $primary }};color:var(--cms-text)">Primary</button>
                                <button type="button" style="background:{{ $secondary }};color:var(--cms-text)">Secondary</button>
                                <button type="button" style="background:{{ $accent }};color:{{ $text_primary }}">Accent</button>
                            </div>
                        </div>

                        <div class="theme-mgr-preview-states">
                            <div class="theme-mgr-preview-state" style="background:{{ $surface }}">
                                <span style="color:{{ $text_secondary }}">Success State</span>
                                <span class="theme-mgr-preview-pill" style="background:{{ $success }};color:var(--cms-text)">ACTIVE</span>
                            </div>
                            <div class="theme-mgr-preview-state" style="background:{{ $surface }}">
                                <span style="color:{{ $text_secondary }}">Warning State</span>
                                <span class="theme-mgr-preview-pill" style="background:{{ $warning }};color:{{ $text_primary }}">PENDING</span>
                            </div>
                            <div class="theme-mgr-preview-state" style="background:{{ $surface }}">
                                <span style="color:{{ $text_secondary }}">Danger State</span>
                                <span class="theme-mgr-preview-pill" style="background:{{ $danger }};color:var(--cms-text)">ERROR</span>
                            </div>
                        </div>

                        <div class="theme-mgr-preview-card" style="background:{{ $surface }}">
                            <p style="font-size:16px;color:{{ $text_primary }};font-weight:700;margin:0 0 8px">Primary Text</p>
                            <p style="font-size:14px;color:{{ $text_secondary }};margin:0 0 8px">Secondary text for descriptions and labels.</p>
                            <p style="font-size:12px;color:{{ $text_muted }};margin:0">Muted text for timestamps and metadata.</p>
                        </div>
                    </aside>
                    </div>
                </div>

                <footer class="theme-mgr-modal-footer">
                    <button type="button" wire:click="$set('showModal', false)" class="btn btn-ghost theme-mgr-cancel-btn">Cancel</button>
                    <x-livewire-submit-button
                        type="submit"
                        form="theme-mgr-form"
                        target="save"
                        :loading="$editing ? __('Saving…') : __('Creating…')"
                    >
                        {{ $editing ? '💾 Save Changes' : '✨ Create Theme' }}
                    </x-livewire-submit-button>
                </footer>
            </div>
        </div>
    @endif

    <style>
        .themes-mgmt-page { min-width: 0; max-width: 100%; }

        .themes-mgmt-org-bar {
            display: flex;
            flex-direction: column;
            align-items: stretch;
            gap: 12px;
            background: var(--cms-surface-raised);
            border: 1px solid var(--cms-border);
            border-radius: var(--r-lg);
            padding: var(--sp-4);
            margin-bottom: var(--sp-5);
        }
        @media (min-width: 640px) {
            .themes-mgmt-org-bar {
                flex-direction: row;
                align-items: center;
                gap: 24px;
                padding: 20px 32px;
                border-radius: 20px;
            }
        }
        .themes-mgmt-org-label {
            font-size: 12px;
            font-weight: 700;
            color: var(--savanna-gold);
            text-transform: uppercase;
            flex-shrink: 0;
        }
        .themes-mgmt-org-select {
            width: 100%;
            box-sizing: border-box;
            background: var(--cms-input-bg);
            border: 1px solid var(--cms-border);
            border-radius: var(--r-full);
            padding: 10px 16px;
            color: var(--cms-text);
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
        }
        @media (min-width: 640px) {
            .themes-mgmt-org-select { flex: 1; }
        }

        .themes-mgmt-header {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            justify-content: space-between;
            gap: var(--sp-4);
            margin-bottom: var(--sp-5);
        }
        .themes-mgmt-create-btn { width: 100%; justify-content: center; }
        @media (min-width: 640px) {
            .themes-mgmt-create-btn { width: auto; }
        }

        .themes-mgmt-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: var(--sp-4);
        }
        @media (min-width: 480px) {
            .themes-mgmt-grid { grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); }
        }
        @media (min-width: 900px) {
            .themes-mgmt-grid { grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); }
        }

        .theme-mgr-card {
            background: var(--cms-surface-raised);
            border: 1px solid var(--cms-border);
            border-radius: var(--r-xl);
            overflow: hidden;
            min-width: 0;
        }
        .theme-mgr-card-swatches {
            height: 100px;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2px;
            padding: 2px;
            background: var(--cms-input-bg);
        }
        @media (min-width: 480px) {
            .theme-mgr-card-swatches { height: 120px; }
        }
        .theme-mgr-card-swatches > div { border-radius: 8px; min-height: 0; }
        .theme-mgr-card-body { padding: var(--sp-4); }
        .theme-mgr-card-title {
            font-family: var(--font-display);
            font-size: clamp(16px, 4vw, 18px);
            font-weight: 700;
            color: var(--cms-text);
            margin: 0 0 4px;
            word-break: break-word;
        }
        .theme-mgr-default-badge {
            display: inline-block;
            background: rgba(212, 160, 23, 0.2);
            color: var(--savanna-gold);
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 10px;
            font-weight: 800;
            margin-left: 6px;
            vertical-align: middle;
        }
        .theme-mgr-card-meta {
            font-size: 11px;
            color: var(--cms-text-muted);
            font-family: monospace;
            margin: 0;
            word-break: break-all;
        }
        .theme-mgr-card-desc {
            font-size: 12px;
            color: var(--cms-text-muted);
            margin: var(--sp-3) 0 0;
            line-height: 1.5;
        }
        .theme-mgr-card-actions {
            width: 100%;
            margin-top: var(--sp-3);
            padding-top: var(--sp-3);
            border-top: 1px solid var(--cms-border);
            flex-wrap: wrap;
            gap: 8px;
        }
        @media (max-width: 479px) {
            .theme-mgr-card-actions .sa-table-action {
                flex: 1 1 calc(50% - 4px);
                min-width: 0;
                justify-content: center;
            }
        }

        .themes-mgmt-empty {
            grid-column: 1 / -1;
            text-align: center;
            color: var(--cms-text-muted);
            padding: var(--sp-8) var(--sp-4);
        }
        .themes-mgmt-empty-icon { font-size: 48px; margin-bottom: var(--sp-4); }
        @media (min-width: 480px) { .themes-mgmt-empty-icon { font-size: 64px; } }
        .themes-mgmt-empty-title { font-size: 16px; font-weight: 700; margin: 0 0 8px; }
        .themes-mgmt-empty-text { font-size: 13px; margin: 0; }

        .themes-mgmt-pagination { margin-top: var(--sp-6); overflow-x: auto; }

        /* Modal */
        .themes-mgmt-page .theme-mgr-modal-panel.sa-modal-panel,
        .themes-mgmt-page .theme-mgr-modal-panel.cms-modal-panel {
            padding: 0 !important;
            margin: 0 auto !important;
            width: 100% !important;
            max-width: 1200px !important;
            display: flex !important;
            flex-direction: column !important;
            overflow: hidden !important;
        }
        .theme-mgr-backdrop { overflow-y: auto; align-items: flex-start; padding: 12px; }
        .theme-mgr-modal-panel {
            max-height: calc(100vh - 24px);
            width: 100%;
            max-width: 1200px;
            display: flex;
            flex-direction: column;
            margin: auto;
        }
        .theme-mgr-modal-header {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-start;
            justify-content: space-between;
            gap: var(--sp-3);
            padding: var(--sp-4);
            border-bottom: 1px solid var(--cms-border);
            flex-shrink: 0;
        }
        @media (min-width: 768px) {
            .theme-mgr-modal-header { padding: 32px; }
        }
        .theme-mgr-modal-title {
            font-family: var(--font-display);
            font-size: clamp(20px, 5vw, 28px);
            color: var(--cms-text);
            margin: 0 0 4px;
        }
        .theme-mgr-modal-subtitle {
            font-size: 12px;
            color: var(--cms-text-muted);
            font-weight: 700;
            margin: 0;
        }
        .theme-mgr-modal-close {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            font-size: 20px;
            cursor: pointer;
            flex-shrink: 0;
        }

        .theme-mgr-modal-body {
            flex: 1;
            min-height: 0;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .theme-mgr-modal-columns {
            display: flex;
            flex-direction: column;
            flex: 1;
            min-height: 0;
            overflow: hidden;
        }
        @media (min-width: 1024px) {
            .theme-mgr-modal-columns {
                flex-direction: row;
                align-items: stretch;
            }
        }

        .theme-mgr-form {
            flex: 1 1 auto;
            width: 100%;
            min-width: 0;
            padding: var(--sp-4);
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }
        @media (min-width: 768px) {
            .theme-mgr-form { padding: 32px; }
        }

        .theme-mgr-basic-grid {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        @media (min-width: 768px) {
            .theme-mgr-basic-grid {
                display: grid;
                grid-template-columns: 1fr minmax(220px, 320px);
                align-items: start;
                gap: 24px;
            }
        }
        .theme-mgr-basic-primary {
            display: flex;
            flex-direction: column;
            gap: 20px;
            min-width: 0;
        }
        .theme-mgr-basic-org { min-width: 0; }

        .theme-mgr-modal-footer {
            flex-shrink: 0;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: flex-end;
            gap: 12px;
            padding: var(--sp-4);
            border-top: 1px solid var(--cms-border);
            background: var(--cms-surface);
        }
        @media (max-width: 639px) {
            .theme-mgr-modal-footer {
                flex-direction: column-reverse;
                align-items: stretch;
            }
            .theme-mgr-modal-footer .lw-submit-btn {
                width: 100%;
                justify-content: center;
            }
            .theme-mgr-cancel-btn { width: 100%; text-align: center; }
        }

        .theme-mgr-section { margin-bottom: var(--sp-6); }
        .theme-mgr-section-title {
            font-size: 14px;
            font-weight: 800;
            color: var(--savanna-gold);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0 0 var(--sp-4);
        }
        .theme-mgr-fields { display: grid; gap: 20px; }
        .theme-mgr-label {
            display: block;
            font-size: 11px;
            font-weight: 800;
            color: var(--cms-text-muted);
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        .theme-mgr-input {
            width: 100%;
            box-sizing: border-box;
            background: var(--cms-input-bg);
            border: 1px solid var(--cms-border);
            border-radius: 12px;
            padding: 12px 14px;
            color: var(--cms-text);
            font-family: var(--font-admin);
            font-size: 15px;
        }
        .theme-mgr-input--readonly {
            background: var(--cms-surface);
            cursor: not-allowed;
        }
        .theme-mgr-input--mono { font-family: monospace; font-size: 13px; }
        .theme-mgr-textarea { resize: vertical; min-height: 72px; }
        .theme-mgr-select { cursor: pointer; }
        .theme-mgr-hint {
            font-size: 10px;
            color: var(--cms-text-muted);
            margin: 4px 0 0;
            line-height: 1.4;
        }
        .theme-mgr-error {
            color: var(--clay-red);
            font-size: 10px;
            margin-top: 4px;
        }

        .theme-mgr-preset-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
        }
        @media (min-width: 480px) {
            .theme-mgr-preset-grid { grid-template-columns: repeat(2, 1fr); }
        }
        .theme-mgr-preset-btn {
            border-radius: 12px;
            padding: 12px;
            text-align: left;
            cursor: pointer;
            width: 100%;
        }
        .theme-mgr-preset-swatches {
            display: flex;
            gap: 8px;
            margin-bottom: 8px;
        }
        .theme-mgr-preset-swatches span {
            width: 20px;
            height: 20px;
            border-radius: 6px;
            flex-shrink: 0;
        }
        .theme-mgr-preset-name {
            font-size: 13px;
            font-weight: 700;
            color: var(--cms-text);
        }

        .theme-mgr-color-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 16px;
        }
        @media (min-width: 480px) {
            .theme-mgr-color-grid { grid-template-columns: repeat(2, 1fr); }
        }
        .theme-mgr-color-inputs {
            display: flex;
            gap: 8px;
            align-items: center;
            min-width: 0;
        }
        .theme-mgr-color-picker {
            width: 52px;
            height: 44px;
            border: 2px solid var(--cms-border);
            border-radius: 12px;
            cursor: pointer;
            background: transparent;
            flex-shrink: 0;
            padding: 0;
        }
        @media (min-width: 480px) {
            .theme-mgr-color-picker { width: 60px; height: 48px; }
        }
        .theme-mgr-color-inputs .theme-mgr-input { flex: 1; min-width: 0; }

        /* Live preview: desktop side panel only (sibling to form, not inside it) */
        .theme-mgr-preview {
            display: none;
        }
        @media (min-width: 1024px) {
            .theme-mgr-preview {
                display: block;
                flex-shrink: 0;
                width: min(400px, 38%);
                padding: 32px;
                overflow-y: auto;
                border-left: 1px solid var(--cms-border);
                -webkit-overflow-scrolling: touch;
                align-self: stretch;
            }
        }
        .theme-mgr-preview-title {
            font-size: 14px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0 0 var(--sp-4);
        }
        .theme-mgr-preview-card {
            border-radius: 20px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        .theme-mgr-preview-card-title {
            font-family: var(--font-display);
            font-size: 18px;
            font-weight: 800;
            margin: 0 0 8px;
        }
        .theme-mgr-preview-card-text {
            font-size: 13px;
            margin: 0 0 16px;
            line-height: 1.6;
        }
        .theme-mgr-preview-btns {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .theme-mgr-preview-btns button {
            padding: 8px 16px;
            border-radius: 8px;
            border: none;
            font-weight: 700;
            font-size: 12px;
            cursor: default;
        }
        .theme-mgr-preview-states {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 20px;
        }
        .theme-mgr-preview-state {
            border-radius: 12px;
            padding: 14px 16px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            font-size: 13px;
            font-weight: 600;
        }
        .theme-mgr-preview-pill {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 800;
        }
    </style>
</div>
