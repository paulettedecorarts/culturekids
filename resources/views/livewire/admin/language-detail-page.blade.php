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
                <button type="button" class="btn btn-sm" wire:click="startEditing" style="background:rgba(212,160,23,.2);color:#F2CB5A;border:1px solid rgba(212,160,23,.4)">Edit</button>
            @endif
            @if($isEditing)
                <button type="button" class="btn btn-sm" wire:click="saveLanguage" style="background:rgba(74,124,89,.25);color:#B8D9C6;border:1px solid rgba(74,124,89,.4)">
                    {{ $language ? 'Save Changes' : 'Create Language' }}
                </button>
                <button type="button" class="btn btn-sm" wire:click="cancelEditing" style="background:rgba(255,255,255,.08);color:#fff;border:1px solid rgba(255,255,255,.2)">
                    {{ $language ? 'Cancel' : 'Back' }}
                </button>
            @endif
            @if($language)
                <button type="button" class="btn btn-sm" wire:click="deleteLanguage" wire:confirm="Delete this language?" style="background:rgba(196,75,43,.2);color:#E06444;border:1px solid rgba(196,75,43,.35)">Delete</button>
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
                <div><label>Coverage %</label><input wire:model.defer="translation_coverage" type="number" min="0" max="100">@error('translation_coverage')<div class="error">{{ $message }}</div>@enderror</div>
                <div><label>Sort order</label><input wire:model.defer="sort_order" type="number" min="0">@error('sort_order')<div class="error">{{ $message }}</div>@enderror</div>
                <div><label>Status</label><select wire:model.defer="status"><option value="verified">Verified</option><option value="partial">Partial</option><option value="pending">Pending</option></select></div>
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
        .language-detail-page label{font-size:11px;color:rgba(255,255,255,.65);display:block;margin-bottom:4px}
        .language-detail-page input:not([type="checkbox"]),.language-detail-page select,.language-detail-page textarea{width:100%;padding:9px;border-radius:8px;border:1px solid rgba(255,255,255,.12);background:#1a2744;color:#fff}
        .language-detail-page select option{background:#1a2744;color:#fff}
        .language-detail-page .check-row{display:flex;align-items:center;gap:8px;font-size:13px;color:#fff}
        .language-detail-page .check-row input[type="checkbox"]{width:16px;height:16px;accent-color:#D4A017}
        .language-detail-page .error{font-size:10px;color:#ff8c8c}
        .language-detail-page .item{padding:10px;border-radius:10px;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.08)}
        .language-detail-page .item span{display:block;font-size:10px;color:rgba(255,255,255,.45);text-transform:uppercase;margin-bottom:4px}
        .language-detail-page .item strong{font-size:14px;color:#fff}
        @media (max-width: 900px){.language-detail-page .sa-table-wrap > div{grid-template-columns:1fr !important}}
    </style>
</div>
