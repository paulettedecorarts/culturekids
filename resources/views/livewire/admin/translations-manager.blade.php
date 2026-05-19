<div>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--sp-5);flex-wrap:wrap;gap:var(--sp-3)">
        <div>
            <div class="sa-page-title">Translations</div>
            <div class="sa-breadcrumb">Content · Panel vocabulary translations</div>
        </div>
        <div style="display:flex;gap:var(--sp-2);align-items:center">
            <button wire:click="createTag" class="btn btn-primary btn-sm" style="background:var(--clay-red); border:none; color:var(--cms-text); padding: var(--sp-2) var(--sp-4); border-radius: var(--r-full); font-weight:700; cursor:pointer;">+ Add Translation</button>
        </div>
    </div>

    @if (session()->has('message'))
        <div style="background:rgba(74,124,89,.12);border:1px solid rgba(74,124,89,.35);color:var(--banana-light);padding:10px 14px;border-radius:10px;margin-bottom:var(--sp-4);font-size:12px;font-weight:700">
            {{ session('message') }}
        </div>
    @endif

    <div class="sa-stats-row">
        <div class="sa-stat">
            <div class="sa-stat-val">{{ $stats['total'] }}</div>
            <div class="sa-stat-label">Total Tags</div>
            <div class="sa-stat-delta">Panel vocab tags</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val" style="color:var(--banana-green)">{{ $stats['coverage'] }}%</div>
            <div class="sa-stat-label">Coverage</div>
            <div class="sa-stat-delta">Has translation</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val" style="color:var(--sunfire)">{{ $stats['missing'] }}</div>
            <div class="sa-stat-label">Missing</div>
            <div class="sa-stat-delta">Needs translation</div>
        </div>
        <div class="sa-stat">
            <div class="sa-stat-val">{{ $stats['comics_covered'] }}</div>
            <div class="sa-stat-label">Comics Covered</div>
            <div class="sa-stat-delta">With vocab tags</div>
        </div>
    </div>

    <div style="display:flex; gap:var(--sp-2); margin-bottom:var(--sp-4);">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search word, translation, comic, tribe..." style="background:var(--cms-input-bg); border:1px solid var(--cms-border); border-radius:var(--r-full); padding:var(--sp-2) var(--sp-4); color:var(--cms-text); font-size:12px; outline:none; min-width:300px;">
        <select wire:model.live="statusFilter" style="background:var(--cms-input-bg); border:1px solid var(--cms-border); border-radius:var(--r-full); padding:var(--sp-2) var(--sp-4); color:var(--cms-text); font-size:12px; outline:none;">
            <option value="">All</option>
            <option value="translated">Translated</option>
            <option value="missing">Missing</option>
        </select>
    </div>

    <div class="sa-table-wrap">
        <div class="sa-table-head" style="grid-template-columns:1.4fr 1.6fr 1.6fr 1.2fr 120px">
            <span>Word</span>
            <span>Translation</span>
            <span>Context</span>
            <span>Phonetic</span>
            <span>Actions</span>
        </div>

        @forelse($tags as $tag)
            <div class="sa-table-row" style="grid-template-columns:1.4fr 1.6fr 1.6fr 1.2fr 120px">
                <span style="font-size:12px;color:var(--cms-text);font-weight:700">{{ $tag->word }}</span>
                <span style="font-size:12px;color:{{ $tag->translation ? 'var(--banana-green)' : 'var(--sunfire)' }};font-style:{{ $tag->translation ? 'normal' : 'italic' }}">
                    {{ $tag->translation ?: 'Missing' }}
                </span>
                <span style="font-size:11px;color: var(--cms-text-muted)">
                    {{ $tag->panel?->comic?->title ?? 'Unknown comic' }}
                    @if($tag->panel?->comic?->tribe)
                        · {{ $tag->panel->comic->tribe->name }}
                    @endif
                    · Panel {{ (int) ($tag->panel?->order_index ?? 0) + 1 }}
                </span>
                <span style="font-size:12px;color:var(--cms-text-muted)">{{ $tag->phonetic ?: '—' }}</span>
                <div style="display:flex;gap:6px">
                    <button wire:click="editTag({{ $tag->id }})" class="btn btn-ghost btn-sm" style="padding:3px 8px;font-size:9px">Edit</button>
                    <button wire:click="deleteTag({{ $tag->id }})" wire:confirm="Delete this translation tag?" class="btn btn-ghost btn-sm" style="padding:3px 8px;font-size:9px;color:#fecaca;border-color:rgba(196,75,43,.35)">Delete</button>
                </div>
            </div>
        @empty
            <div style="padding:22px;color:var(--cms-text-muted)">No translation tags found.</div>
        @endforelse
    </div>

    <div style="margin-top:12px">
        {{ $tags->links() }}
    </div>

    @if($showModal)
        <div class="sa-modal-backdrop" style="position:fixed;inset:0;z-index:1000;display:flex;align-items:center;justify-content:center;padding:24px">
            <div class="sa-modal-panel" style="border-radius:20px;width:min(760px,100%);padding:20px">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px">
                    <div style="font-size:16px;font-weight:800;color:var(--cms-text)">{{ $editing ? 'Edit Translation' : 'Add Translation' }}</div>
                    <button wire:click="closeModal" class="btn btn-ghost btn-sm">Close</button>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <div style="grid-column:1/-1">
                        <label style="display:block;font-size:11px;color: var(--cms-text-muted);margin-bottom:4px">Story</label>
                        <select wire:model.live="comic_id" style="width:100%;background:var(--cms-input-bg);border:1px solid var(--cms-input-border);border-radius:10px;padding:10px;color:var(--cms-text);color-scheme:inherit">
                            <option value="">Select story</option>
                            @foreach($storyOptions as $story)
                                <option value="{{ $story->id }}" style="background:var(--cms-input-bg);color:var(--cms-text)">
                                    {{ $story->title }} · {{ $story->tribe?->name ?? 'Unknown tribe' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div style="grid-column:1/-1">
                        <label style="display:block;font-size:11px;color: var(--cms-text-muted);margin-bottom:4px">Panel</label>
                        <select wire:model="panel_id" style="width:100%;background:var(--cms-input-bg);border:1px solid var(--cms-input-border);border-radius:10px;padding:10px;color:var(--cms-text);color-scheme:inherit">
                            <option value="">Select panel</option>
                            @foreach($panelOptions as $panel)
                                <option value="{{ $panel->id }}" style="background:var(--cms-input-bg);color:var(--cms-text)">
                                    {{ $panel->comic?->title ?? 'Unknown comic' }} · {{ $panel->comic?->tribe?->name ?? 'Unknown tribe' }} · Panel {{ (int) $panel->order_index + 1 }}
                                </option>
                            @endforeach
                        </select>
                        @error('panel_id') <div style="font-size:10px;color:#fda4af;margin-top:4px">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label style="display:block;font-size:11px;color: var(--cms-text-muted);margin-bottom:4px">Word</label>
                        <input wire:model="word" type="text" style="width:100%;background:var(--cms-input-bg);border:1px solid var(--cms-input-border);border-radius:10px;padding:10px;color:var(--cms-text)">
                        @error('word') <div style="font-size:10px;color:#fda4af;margin-top:4px">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label style="display:block;font-size:11px;color: var(--cms-text-muted);margin-bottom:4px">Translation</label>
                        <input wire:model="translation" type="text" style="width:100%;background:var(--cms-input-bg);border:1px solid var(--cms-input-border);border-radius:10px;padding:10px;color:var(--cms-text)">
                        @error('translation') <div style="font-size:10px;color:#fda4af;margin-top:4px">{{ $message }}</div> @enderror
                    </div>

                    <div style="grid-column:1/-1">
                        <label style="display:block;font-size:11px;color: var(--cms-text-muted);margin-bottom:4px">Phonetic</label>
                        <input wire:model="phonetic" type="text" style="width:100%;background:var(--cms-input-bg);border:1px solid var(--cms-input-border);border-radius:10px;padding:10px;color:var(--cms-text)">
                        @error('phonetic') <div style="font-size:10px;color:#fda4af;margin-top:4px">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:14px">
                    <button wire:click="closeModal" class="btn btn-ghost btn-sm">Cancel</button>
                    <x-livewire-submit-button type="button" wire:click="saveTag" target="saveTag" variant="sm" class="btn-primary" :loading="$editing ? __('Saving…') : __('Creating…')">
                        {{ $editing ? 'Save changes' : 'Create' }}
                    </x-livewire-submit-button>
                </div>
            </div>
        </div>
    @endif
</div>
