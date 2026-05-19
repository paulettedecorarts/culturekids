<div class="translation-form-page">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--sp-5);flex-wrap:wrap;gap:var(--sp-3)">
        <div style="display:flex;align-items:center;gap:12px">
            <a href="{{ route($listRoute) }}" wire:navigate class="btn btn-ghost btn-sm" style="text-decoration:none">← Translations</a>
            <div>
                <div class="sa-page-title">{{ $isCreate ? 'Add Translation' : 'Edit Translation' }}</div>
                <div class="sa-breadcrumb">
                    @if($contextLabel)
                        {{ $contextLabel }}
                    @else
                        Vocabulary translation · any of the 12 content activity types
                    @endif
                </div>
            </div>
        </div>
        <div class="sa-page-actions">
            <a href="{{ route($listRoute) }}" wire:navigate class="sa-table-action">Cancel</a>
            @if($tag)
                <button type="button" class="sa-table-action sa-table-action--danger" wire:click="delete" wire:confirm="Delete this translation?">Delete</button>
            @endif
            <x-livewire-submit-button type="button" wire:click="save" target="save" variant="md">
                {{ $isCreate ? 'Create translation' : 'Save changes' }}
            </x-livewire-submit-button>
        </div>
    </div>

    <form wire:submit.prevent="save" class="sa-table-wrap" style="padding:var(--sp-5)">
        <div style="font-size:13px;font-weight:800;color:var(--cms-text);margin-bottom:var(--sp-4)">Content</div>
        <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:var(--sp-4);margin-bottom:var(--sp-5)">
            <div style="grid-column:1/-1">
                <label class="tf-label">Activity type</label>
                <select wire:model.live="content_type" class="tf-input" @disabled($tag)>
                    @foreach($typeOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('content_type') <div class="tf-error">{{ $message }}</div> @enderror
            </div>

            <div style="grid-column:1/-1">
                <label class="tf-label">Content item</label>
                <select wire:model.live="content_id" class="tf-input" @disabled(!$content_type)>
                    <option value="">{{ $content_type ? 'Select item' : 'Choose type first' }}</option>
                    @foreach($contentOptions as $item)
                        <option value="{{ $item->id }}">
                            {{ $item->label }}@if($item->tribe_name) · {{ $item->tribe_name }}@endif
                        </option>
                    @endforeach
                </select>
                @error('content_id') <div class="tf-error">{{ $message }}</div> @enderror
            </div>

            @if($subItemsRequired)
            <div style="grid-column:1/-1">
                <label class="tf-label">
                    @if($content_type === 'story')
                        Panel
                    @elseif($content_type === 'flashcard')
                        Flashcard
                    @elseif($content_type === 'language')
                        Vocab word
                    @elseif($content_type === 'word_search')
                        Word in puzzle
                    @else
                        Detail
                    @endif
                </label>
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

        <div style="font-size:13px;font-weight:800;color:var(--cms-text);margin-bottom:var(--sp-4);padding-top:var(--sp-2);border-top:1px solid var(--cms-border)">Vocabulary</div>
        <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:var(--sp-4)">
            <div>
                <label class="tf-label">Word</label>
                <input wire:model="word" type="text" class="tf-input" placeholder="e.g. PIJ">
                @error('word') <div class="tf-error">{{ $message }}</div> @enderror
                <div class="tf-hint">Source-language or primary label.</div>
            </div>

            <div>
                <label class="tf-label">Translation</label>
                <input wire:model="translation" type="text" class="tf-input" placeholder="e.g. Water">
                @error('translation') <div class="tf-error">{{ $message }}</div> @enderror
                <div class="tf-hint">English gloss or meaning. Leave empty if pending.</div>
            </div>

            <div style="grid-column:1/-1">
                <label class="tf-label">Phonetic</label>
                <input wire:model="phonetic" type="text" class="tf-input" placeholder="e.g. pee-j">
                @error('phonetic') <div class="tf-error">{{ $message }}</div> @enderror
                <div class="tf-hint">Pronunciation guide (or hint for word searches).</div>
            </div>
        </div>

        <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:var(--sp-5);padding-top:var(--sp-4);border-top:1px solid var(--cms-border)">
            <a href="{{ route($listRoute) }}" wire:navigate class="sa-table-action">Cancel</a>
            <x-livewire-submit-button type="submit" target="save" variant="md">
                {{ $isCreate ? 'Create translation' : 'Save changes' }}
            </x-livewire-submit-button>
        </div>
    </form>

    <style>
        .translation-form-page .tf-label { display:block;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.04em;color:var(--cms-text-muted);margin-bottom:6px; }
        .translation-form-page .tf-input { width:100%;background:var(--cms-input-bg);border:1px solid var(--cms-input-border);border-radius:10px;padding:12px 14px;color:var(--cms-text);font-size:14px;font-family:var(--font-admin); }
        .translation-form-page .tf-input:disabled { opacity:.55;cursor:not-allowed; }
        .translation-form-page .tf-error { font-size:11px;color:#fda4af;margin-top:6px; }
        .translation-form-page .tf-hint { font-size:11px;color:var(--cms-text-muted);margin-top:6px;line-height:1.4; }
    </style>
</div>
