@php
    $hasWorkspace = $showWorkspace && $sourcePreview;
@endphp
<div class="translation-form-page">
    <div class="tf-header">
        <div style="display:flex;align-items:center;gap:12px">
            <a href="{{ route($listRoute) }}" wire:navigate class="btn btn-ghost btn-sm" style="text-decoration:none">← Translations</a>
            <div>
                <div class="sa-page-title">{{ $isCreate ? 'Add Translation' : 'Edit Translation' }}</div>
                <div class="sa-breadcrumb">
                    @if($contextLabel)
                        {{ $contextLabel }}
                    @else
                        Map translations to source content · 12 activity types
                    @endif
                </div>
            </div>
        </div>
        <div class="sa-page-actions">
            <a href="{{ route($listRoute) }}" wire:navigate class="sa-table-action">Cancel</a>
            @if($tag)
                <button type="button" class="sa-table-action sa-table-action--danger" wire:click="delete" wire:confirm="Delete this translation?">Delete</button>
            @endif
            @if($hasWorkspace)
                <x-livewire-submit-button type="button" wire:click="save" target="save" variant="md">
                    {{ $isCreate ? 'Save translation' : 'Save changes' }}
                </x-livewire-submit-button>
            @endif
        </div>
    </div>

    @if (session()->has('message'))
        <div class="tf-flash">{{ session('message') }}</div>
    @endif

    <div class="sa-table-wrap tf-selector-card">
        <div class="tf-section-label">1 · Choose content</div>
        <div class="tf-selectors">
            <div class="tf-selector">
                <label class="tf-label">Activity type</label>
                <select wire:model.live="content_type" class="tf-input" @disabled($tag)>
                    @foreach($typeOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('content_type') <div class="tf-error">{{ $message }}</div> @enderror
            </div>
            <div class="tf-selector">
                <label class="tf-label">Content item</label>
                <select wire:model.live="content_id" class="tf-input" @disabled(!$content_type || $tag)>
                    <option value="">{{ $content_type ? 'Select item' : 'Choose type first' }}</option>
                    @foreach($contentOptions as $item)
                        <option value="{{ $item->id }}">{{ $item->label }}@if($item->tribe_name) · {{ $item->tribe_name }}@endif</option>
                    @endforeach
                </select>
                @error('content_id') <div class="tf-error">{{ $message }}</div> @enderror
            </div>
            @if($subItemsRequired && !$hasWorkspace)
            <div class="tf-selector tf-selector--wide">
                <label class="tf-label">Part to translate</label>
                <select wire:model.live="sub_item_key" class="tf-input" @disabled(!$content_id)>
                    <option value="">{{ $content_id ? 'Select…' : 'Choose content first' }}</option>
                    @foreach($subItemOptions as $opt)
                        <option value="{{ $opt['key'] }}">{{ $opt['label'] }}</option>
                    @endforeach
                </select>
                @error('sub_item_key') <div class="tf-error">{{ $message }}</div> @enderror
            </div>
            @endif
        </div>
    </div>

    @if($hasWorkspace)
        <div class="tf-workspace">
            <div class="tf-workspace-col tf-workspace-col--translate">
                <div class="tf-section-label">2 · Translation</div>
                @if(!empty($fieldSchema['subtype_label']))
                    <p class="tf-workspace-intro">Fields match the {{ $fieldSchema['subtype_label'] }} editor.</p>
                @endif
                <form wire:submit.prevent="save" class="tf-translate-card">
                    @include('livewire.admin.translations._fields')
                    <div class="tf-form-footer">
                        <a href="{{ route($listRoute) }}" wire:navigate class="sa-table-action">Cancel</a>
                        <x-livewire-submit-button type="submit" target="save" variant="md">
                            {{ $isCreate ? 'Save translation' : 'Save changes' }}
                        </x-livewire-submit-button>
                    </div>
                </form>
            </div>
            <div class="tf-workspace-col tf-workspace-col--source">
                @include('livewire.admin.translations._source-preview')
            </div>
        </div>
    @elseif($content_id)
        <div class="tf-placeholder sa-table-wrap">
            @if($subItemsRequired)
                Select a panel, card, word, or list entry above (or from the list once the workspace opens).
            @else
                Loading workspace…
            @endif
        </div>
    @endif

    <style>
        .translation-form-page .tf-header { display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--sp-5);flex-wrap:wrap;gap:var(--sp-3); }
        .translation-form-page .tf-flash { background:rgba(74,124,89,.12);border:1px solid rgba(74,124,89,.35);color:var(--banana-light);padding:10px 14px;border-radius:10px;margin-bottom:var(--sp-4);font-size:12px;font-weight:700; }
        .translation-form-page .tf-selector-card { padding:var(--sp-5); margin-bottom:var(--sp-4); }
        .translation-form-page .tf-section-label { font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:var(--savanna-gold);margin-bottom:var(--sp-3); }
        .translation-form-page .tf-selectors { display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:var(--sp-4); }
        .translation-form-page .tf-selector--wide { grid-column:1/-1; }
        .translation-form-page .tf-label { display:block;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.04em;color:var(--cms-text-muted);margin-bottom:6px; }
        .translation-form-page .tf-input { width:100%;background:var(--cms-input-bg);border:1px solid var(--cms-input-border);border-radius:10px;padding:12px 14px;color:var(--cms-text);font-size:14px; }
        .translation-form-page .tf-error { font-size:11px;color:#fda4af;margin-top:6px; }
        .translation-form-page .tf-hint { font-size:11px;color:var(--cms-text-muted);margin-top:6px;line-height:1.4; }
        .translation-form-page .tf-workspace { display:grid;grid-template-columns:minmax(320px,1fr) minmax(360px,1.1fr);gap:var(--sp-5);align-items:start; }
        @media (max-width: 960px) { .translation-form-page .tf-workspace { grid-template-columns:1fr; } }
        .translation-form-page .tf-translate-card, .translation-form-page .tf-source { background:var(--cms-surface-raised);border:1px solid var(--cms-border);border-radius:20px;padding:var(--sp-5); }
        .translation-form-page .tf-workspace-intro { font-size:12px;color:var(--cms-text-muted);margin:0 0 var(--sp-4); }
        .translation-form-page .tf-subtype-badge { display:inline-block;font-size:11px;font-weight:800;padding:4px 10px;border-radius:999px;background:rgba(212,160,23,.15);color:var(--savanna-gold);margin-bottom:var(--sp-3); }
        .translation-form-page .tf-field { margin-bottom:var(--sp-4); }
        .translation-form-page .tf-note { font-size:12px;color:var(--cms-text-muted);background:rgba(255,255,255,.03);border-radius:10px;padding:12px;margin-bottom:var(--sp-4); }
        .translation-form-page .tf-hotspot-grid { display:grid;grid-template-columns:1fr 1fr;gap:var(--sp-3); }
        .translation-form-page .tf-field-actions { margin-top:var(--sp-3); }
        .translation-form-page .tf-form-footer { display:flex;justify-content:flex-end;gap:8px;margin-top:var(--sp-5);padding-top:var(--sp-4);border-top:1px solid var(--cms-border); }
        .translation-form-page .tf-source-kicker { font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:var(--cms-text-muted); }
        .translation-form-page .tf-source-title { font-size:18px;font-weight:800;color:var(--cms-text);margin-top:4px; }
        .translation-form-page .tf-source-sub { font-size:12px;color:var(--cms-text-muted);margin-top:4px; }
        .translation-form-page .tf-subnav { display:flex;flex-wrap:wrap;gap:6px;margin:var(--sp-4) 0;max-height:140px;overflow-y:auto; }
        .translation-form-page .tf-subnav-btn { font-size:11px;font-weight:700;padding:6px 10px;border-radius:999px;border:1px solid var(--cms-border);background:transparent;color:var(--cms-text-muted);cursor:pointer; }
        .translation-form-page .tf-subnav-btn.is-active { border-color:var(--savanna-gold);color:var(--savanna-gold);background:rgba(212,160,23,.12); }
        .translation-form-page .tf-preview-image-wrap { position:relative;background:rgba(0,0,0,.25);border-radius:14px;overflow:hidden;min-height:280px;display:flex;align-items:center;justify-content:center; }
        .translation-form-page .tf-preview-image { width:100%;max-height:420px;object-fit:contain;display:block; }
        .translation-form-page .tf-preview-hotspot { position:absolute;width:14px;height:14px;border-radius:50%;background:var(--sunfire);border:2px solid #fff;transform:translate(-50%,-50%);box-shadow:0 0 0 3px rgba(232,135,42,.35); }
        .translation-form-page .tf-preview-caption { font-size:13px;font-style:italic;color:var(--cms-text);margin-top:12px; }
        .translation-form-page .tf-preview-meta { font-size:11px;color:var(--cms-text-muted);margin-top:8px; }
        .translation-form-page .tf-preview-flashcard { display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-top:12px; }
        .translation-form-page .tf-fc-side { border:1px solid var(--cms-border);border-radius:14px;padding:16px;min-height:140px;position:relative; }
        .translation-form-page .tf-fc-front { background:rgba(255,255,255,.03); }
        .translation-form-page .tf-fc-back { background:rgba(74,124,89,.08); }
        .translation-form-page .tf-fc-emoji { font-size:40px;display:block;margin-bottom:8px; }
        .translation-form-page .tf-fc-label { font-size:16px;font-weight:800;color:var(--cms-text); }
        .translation-form-page .tf-fc-side-tag { position:absolute;top:8px;right:10px;font-size:9px;font-weight:800;text-transform:uppercase;color:var(--cms-text-muted); }
        .translation-form-page .tf-preview-word-row { display:flex;gap:14px;align-items:flex-start;margin-top:16px;padding:16px;border:1px solid var(--cms-border);border-radius:14px; }
        .translation-form-page .tf-preview-emoji { font-size:36px; }
        .translation-form-page .tf-preview-word-main { font-size:20px;font-weight:800;color:var(--cms-text); }
        .translation-form-page .tf-preview-row { display:flex;justify-content:space-between;gap:12px;padding:8px 0;border-bottom:1px solid var(--cms-border);font-size:13px; }
        .translation-form-page .tf-preview-proverb { font-size:16px;font-style:italic;color:var(--cms-text);margin-top:12px; }
        .translation-form-page .tf-preview-lyrics { white-space:pre-wrap;font-size:12px;color:var(--cms-text-muted);background:rgba(0,0,0,.2);padding:12px;border-radius:10px;max-height:240px;overflow:auto; }
        .translation-form-page .tf-placeholder { padding:var(--sp-8);text-align:center;color:var(--cms-text-muted);font-size:13px; }
    </style>
</div>
