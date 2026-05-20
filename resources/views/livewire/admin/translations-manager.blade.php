<div>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--sp-5);flex-wrap:wrap;gap:var(--sp-3)">
        <div>
            <div class="sa-page-title">Translations</div>
            <div class="sa-breadcrumb">Content · Vocabulary translations across all activity types</div>
        </div>
        <div style="display:flex;gap:var(--sp-2);align-items:center">
            <a href="{{ route($createRoute) }}" wire:navigate class="btn btn-primary btn-sm" style="background:var(--clay-red); border:none; color:var(--cms-text); padding: var(--sp-2) var(--sp-4); border-radius: var(--r-full); font-weight:700; cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;">+ Add Translation</a>
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
            <div class="sa-stat-delta">All activity types</div>
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
            <div class="sa-stat-label">Stories</div>
            <div class="sa-stat-delta">With story panel tags</div>
        </div>
    </div>

    <div class="cms-toolbar-flex" style="display:flex; gap:var(--sp-2); margin-bottom:var(--sp-4);">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search word, translation, content title…" style="background:var(--cms-input-bg); border:1px solid var(--cms-border); border-radius:var(--r-full); padding:var(--sp-2) var(--sp-4); color:var(--cms-text); font-size:12px; outline:none; min-width:0; flex:1;">
        <select wire:model.live="typeFilter" style="background:var(--cms-input-bg); border:1px solid var(--cms-border); border-radius:var(--r-full); padding:var(--sp-2) var(--sp-4); color:var(--cms-text); font-size:12px; outline:none;">
            <option value="">All types</option>
            @foreach($typeOptions as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
        <select wire:model.live="statusFilter" style="background:var(--cms-input-bg); border:1px solid var(--cms-border); border-radius:var(--r-full); padding:var(--sp-2) var(--sp-4); color:var(--cms-text); font-size:12px; outline:none;">
            <option value="">All statuses</option>
            <option value="translated">Translated</option>
            <option value="missing">Missing</option>
        </select>
    </div>

    <div class="sa-table-wrap">
        <div class="sa-table-head" style="grid-template-columns:1.4fr 1.6fr 1.6fr 1.2fr minmax(180px, auto)">
            <span>Word</span>
            <span>Translation</span>
            <span>Context</span>
            <span>Phonetic</span>
            <span>Actions</span>
        </div>

        @forelse($tags as $tag)
            <div class="sa-table-row" style="grid-template-columns:1.4fr 1.6fr 1.6fr 1.2fr minmax(180px, auto)">
                <span style="font-size:12px;color:var(--cms-text);font-weight:700">{{ $tag->word }}</span>
                <span style="font-size:12px;color:{{ $tag->translation ? 'var(--banana-green)' : 'var(--sunfire)' }};font-style:{{ $tag->translation ? 'normal' : 'italic' }}">
                    {{ $tag->translation ?: 'Missing' }}
                </span>
                <span style="font-size:11px;color: var(--cms-text-muted)">
                    {{ $tag->context_label ?? $tag->typeLabel() }}
                </span>
                <span style="font-size:12px;color:var(--cms-text-muted)">{{ $tag->phonetic ?: '—' }}</span>
                <div style="display:flex;gap:6px">
                    <div class="sa-table-actions">
                        <a href="{{ route($editRouteName, ['id' => $tag->id]) }}" wire:navigate class="sa-table-action">Edit</a>
                        <button type="button" wire:click="deleteTag({{ $tag->id }})" wire:confirm="Delete this translation tag?" class="sa-table-action sa-table-action--danger">Delete</button>
                    </div>
                </div>
            </div>
        @empty
            <div style="padding:22px;color:var(--cms-text-muted)">No translation tags found.</div>
        @endforelse
    </div>

    <div style="margin-top:12px">
        {{ $tags->links() }}
    </div>
</div>
