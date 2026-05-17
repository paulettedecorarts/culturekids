<div class="teacher-library">
    <div class="header">
        <div>
            <h1 class="page-title">{{ __('Library') }}</h1>
            <div class="breadcrumb">{{ __('Content · Approved activities') }}</div>
            <p style="margin-top:12px; font-size:14px; font-weight:600; color:var(--stone); max-width:560px; line-height:1.5">
                {{ __('Browse every activity type your organisation admin approved in the Review Queue — stories, songs, games, puzzles, and more.') }}
            </p>
        </div>
    </div>

    <div class="th-filters">
        <input
            type="search"
            wire:model.live.debounce.300ms="search"
            placeholder="{{ __('Search titles, tribes, types…') }}"
            class="th-input th-input--search"
        >
        <select wire:model.live="type" class="th-select">
            <option value="">{{ __('All activity types') }}</option>
            @foreach ($typeOptions as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
        <select wire:model.live="tribe" class="th-select">
            <option value="">{{ __('All tribes') }}</option>
            @foreach ($tribeOptions as $t)
                <option value="{{ $t->id }}">{{ $t->name }}</option>
            @endforeach
        </select>
        <select wire:model.live="age" class="th-select">
            <option value="all">{{ __('All ages') }}</option>
            <option value="2-3">2–3 {{ __('yrs') }}</option>
            <option value="3-5">3–5 {{ __('yrs') }}</option>
            <option value="5-6">5–6 {{ __('yrs') }}</option>
        </select>
    </div>

    @if ($catalogItems->isEmpty())
        <div style="background:#FFFBEB; border:1px solid #FDE68A; border-radius:24px; padding:32px; text-align:center; font-weight:600; color:#92400E;">
            {{ __('No approved content matches these filters.') }}
        </div>
    @else
        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(260px, 1fr)); gap:24px;">
            @foreach ($catalogItems as $item)
                @php
                    $href = $item['view_url'] ?? null;
                    $tag = $item['type_label'];
                    $emoji = $item['tribe_emoji'] ?? '📚';
                    if ($item['content_type'] === 'song') {
                        $emoji = '🎵';
                    } elseif ($item['content_type'] !== 'story') {
                        $emoji = match ($item['content_type']) {
                            'flashcard' => '🃏',
                            'puzzle' => '🧩',
                            'drawing', 'colouring' => '🎨',
                            'language' => '🗣️',
                            'game' => '🎮',
                            'maze' => '🌀',
                            'spot_difference' => '🔍',
                            'word_search' => '🔤',
                            'culture' => '🏛️',
                            default => $emoji,
                        };
                    }
                @endphp
                @if ($href)
                    <a
                        href="{{ $href }}"
                        wire:navigate
                        style="text-decoration:none; color:inherit; background:#fff; border-radius:32px; border:1px solid var(--cream-mid); overflow:hidden; box-shadow:0 8px 32px rgba(26,18,8,.06); transition:transform .2s; display:block;"
                        onmouseover="this.style.transform='translateY(-4px)'"
                        onmouseout="this.style.transform='none'"
                    >
                @else
                    <div style="background:#fff; border-radius:32px; border:1px solid var(--cream-mid); overflow:hidden; box-shadow:0 8px 32px rgba(26,18,8,.06); display:block; opacity:.85;">
                @endif
                    <div style="aspect-ratio:4/3; background:linear-gradient(135deg,var(--cream-warm),var(--cream-mid)); display:flex; align-items:center; justify-content:center; position:relative;">
                        @if (! empty($item['cover_image_path']))
                            <img src="{{ asset('storage/'.$item['cover_image_path']) }}" alt="" style="width:100%; height:100%; object-fit:cover;">
                        @else
                            <span style="font-size:48px">{{ $emoji }}</span>
                        @endif
                        <span style="position:absolute; top:12px; left:12px; font-size:10px; font-weight:800; text-transform:uppercase; letter-spacing:.4px; background:rgba(255,255,255,.92); padding:4px 10px; border-radius:999px; color:var(--clay-red);">{{ $tag }}</span>
                    </div>
                    <div style="padding:20px 22px 24px;">
                        <p style="font-size:11px; font-weight:800; color:var(--clay-red); text-transform:uppercase; letter-spacing:.5px; margin-bottom:8px">
                            {{ $item['tribe_name'] ?? __('Tribe') }}
                        </p>
                        <h3 style="font-family:var(--font-display); font-size:18px; font-weight:800; color:var(--ink); line-height:1.25; margin-bottom:8px">{{ $item['title'] }}</h3>
                        <p style="font-size:12px; font-weight:700; color:var(--stone)">
                            @if ($item['age_min'] !== null && $item['age_max'] !== null)
                                {{ $item['age_min'] }}–{{ $item['age_max'] }} {{ __('yrs') }}
                            @elseif ($item['meta'])
                                {{ $item['meta'] }}
                            @else
                                {{ $tag }}
                            @endif
                        </p>
                    </div>
                @if ($href)
                    </a>
                @else
                    </div>
                @endif
            @endforeach
        </div>

        <div style="margin-top:32px">
            {{ $catalogItems->links('vendor.pagination.teacher') }}
        </div>
    @endif
</div>
