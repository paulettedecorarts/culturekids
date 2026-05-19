<div class="language-detail-page">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:var(--sp-5);flex-wrap:wrap;gap:var(--sp-3)">
        <div style="display:flex;align-items:center;gap:12px">
            <a href="{{ route('admin.languages') }}" class="btn btn-ghost btn-sm" style="text-decoration:none">← Languages</a>
            <div>
                <div class="sa-page-title">{{ $language ? 'Language Details' : 'Create Language' }}</div>
                <div class="sa-breadcrumb">{{ $language ? "Language #{$language->id}" : 'New language registry entry' }}</div>
            </div>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap">
            @if($language && ! $isEditing)
                <button type="button" class="sa-table-action sa-table-action--accent" wire:click="startEditing">Edit</button>
            @endif
            @if($isEditing)
                <x-livewire-submit-button type="button" wire:click="saveLanguage" target="saveLanguage" variant="success-sm">
                    {{ $language ? 'Save Changes' : 'Create Language' }}
                </x-livewire-submit-button>
                <button type="button" class="sa-table-action" wire:click="cancelEditing">
                    {{ $language ? 'Cancel' : 'Back' }}
                </button>
            @endif
            @if($language)
                <button type="button" class="sa-table-action sa-table-action--danger" wire:click="deleteLanguage" wire:confirm="Delete this language?" >Delete</button>
            @endif
        </div>
    </div>

    @if(session()->has('message'))
        <div style="background:rgba(74,124,89,.12);border:1px solid rgba(74,124,89,.35);color:var(--banana-light);padding:10px 14px;border-radius:10px;margin-bottom:var(--sp-4);font-size:12px;font-weight:700">
            {{ session('message') }}
        </div>
    @endif

    @if($isEditing)
        <div class="sa-table-wrap" style="padding:18px">
            <div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px">
                <div><label>Name</label><input wire:model.defer="name" type="text">@error('name')<div class="error">{{ $message }}</div>@enderror</div>
                <div><label>Native name</label><input wire:model.defer="native_name" type="text">@error('native_name')<div class="error">{{ $message }}</div>@enderror</div>
                <div><label>Code</label><input wire:model.defer="code" type="text" placeholder="lug-UG">@error('code')<div class="error">{{ $message }}</div>@enderror</div>
                <div><label>Flag emoji</label><input wire:model.defer="flag_emoji" type="text" placeholder="🇺🇬">@error('flag_emoji')<div class="error">{{ $message }}</div>@enderror</div>
                <div>
                    <label>Coverage %</label>
                    <div style="padding:10px 12px;border-radius:8px;background:var(--cms-input-bg);border:1px solid var(--cms-border);font-weight:700">
                        {{ $translation_coverage }}% <span style="font-weight:500;color:var(--cms-text-muted);font-size:11px">(from language activities)</span>
                    </div>
                </div>
                <div><label>Sort order</label><input wire:model.defer="sort_order" type="number" min="0">@error('sort_order')<div class="error">{{ $message }}</div>@enderror</div>
                <div>
                    <label>Status</label>
                    <div style="padding:10px 12px;border-radius:8px;background:var(--cms-input-bg);border:1px solid var(--cms-border);font-weight:700;text-transform:capitalize">
                        {{ $status }} <span style="font-weight:500;color:var(--cms-text-muted);font-size:11px">(auto from coverage)</span>
                    </div>
                </div>
            </div>
            <div style="margin-top:10px"><label>Notes</label><textarea wire:model.defer="notes" rows="4"></textarea></div>
            <div style="display:flex;gap:16px;margin-top:10px;flex-wrap:wrap">
                <label class="check-row"><input type="checkbox" wire:model.defer="audio_pack_available"> Audio pack available</label>
                <label class="check-row"><input type="checkbox" wire:model.defer="is_active"> Active</label>
            </div>
        </div>
    @elseif($language)
        <div class="sa-table-wrap" style="padding:20px">
            <div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;margin-bottom:10px">
                <div class="item"><span>Name</span><strong>{{ $language->name }}</strong></div>
                <div class="item"><span>Native Name</span><strong>{{ $language->native_name ?: '—' }}</strong></div>
                <div class="item"><span>Code</span><strong>{{ $language->code }}</strong></div>
                <div class="item"><span>Flag</span><strong>{{ $language->flag_emoji ?: '—' }}</strong></div>
                <div class="item"><span>Coverage</span><strong>{{ $language->translation_coverage }}%</strong></div>
                <div class="item"><span>Status</span><strong>{{ ucfirst($language->status) }}</strong></div>
                <div class="item"><span>Audio Pack</span><strong>{{ $language->audio_pack_available ? 'Yes' : 'No' }}</strong></div>
                <div class="item"><span>Active</span><strong>{{ $language->is_active ? 'Yes' : 'No' }}</strong></div>
                <div class="item"><span>Sort</span><strong>{{ $language->sort_order }}</strong></div>
            </div>
            <div class="item"><span>Notes</span><strong>{{ $language->notes ?: '—' }}</strong></div>
        </div>
    @endif

    <style>
        .language-detail-page label{font-size:11px;color: var(--cms-text-muted);display:block;margin-bottom:4px}
        .language-detail-page input:not([type="checkbox"]),.language-detail-page select,.language-detail-page textarea{width:100%;padding:9px;border-radius:8px;border:1px solid var(--cms-input-border);background:var(--cms-input-bg);color:var(--cms-text)}
        .language-detail-page select option{background:var(--cms-input-bg);color:var(--cms-text)}
        .language-detail-page .check-row{display:flex;align-items:center;gap:8px;font-size:13px;color:var(--cms-text)}
        .language-detail-page .check-row input[type="checkbox"]{width:16px;height:16px;accent-color:#D4A017}
        .language-detail-page .error{font-size:10px;color:#ff8c8c}
        .language-detail-page .item{padding:10px;border-radius:10px;background:var(--cms-surface);border:1px solid var(--cms-border)}
        .language-detail-page .item span{display:block;font-size:10px;color: var(--cms-text-muted);text-transform:uppercase;margin-bottom:4px}
        .language-detail-page .item strong{font-size:14px;color:var(--cms-text)}
        @media (max-width: 900px){.language-detail-page .sa-table-wrap > div{grid-template-columns:1fr !important}}
    </style>
</div>
