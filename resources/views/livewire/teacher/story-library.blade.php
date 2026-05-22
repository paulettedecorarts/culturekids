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
        <div class="th-alert th-alert--warn" style="padding:32px; text-align:center;">
            {{ __('No approved content matches these filters.') }}
        </div>
    @else
        <div class="th-library-grid">
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
                    <a href="{{ $href }}" wire:navigate class="th-library-card">
                @else
                    <div class="th-library-card th-library-card--static">
                @endif
                    <div class="th-library-card__media">
                        @if (! empty($item['cover_image_path']))
                            <img src="{{ asset('storage/'.$item['cover_image_path']) }}" alt="">
                        @else
                            <span class="th-library-card__emoji">{{ $emoji }}</span>
                        @endif
                        <span class="th-library-card__type">{{ $tag }}</span>
                    </div>
                    <div class="th-library-card__body">
                        <p class="th-library-card__tribe">{{ $item['tribe_name'] ?? heritage('people') }}</p>
                        <h3 class="th-library-card__title">{{ $item['title'] }}</h3>
                        <p class="th-library-card__meta">
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

        <div class="th-library-pagination">
            {{ $catalogItems->links('vendor.pagination.teacher') }}
        </div>
    @endif
</div>
