<div class="tribes-explorer">
    <div class="header">
        <div>
            <h1 class="page-title">{{ __('Tribes explorer') }}</h1>
            <div class="breadcrumb">{{ __('Content · Heritage by tribe') }}</div>
            <p style="margin-top:12px; font-size:14px; font-weight:600; color:var(--stone); max-width:560px; line-height:1.5">
                {{ __('Tribes listed here match content your org admin approved from the Review Queue. Counts show how many approved items you have per activity type.') }}
            </p>
        </div>
    </div>

    <div class="th-filters">
        <input
            type="search"
            wire:model.live.debounce.300ms="search"
            placeholder="{{ __('Search tribes, languages, regions…') }}"
            class="th-input th-input--search"
            style="max-width:400px;"
        >
        <select wire:model.live="type" class="th-select">
            <option value="">{{ __('All activity types') }}</option>
            @foreach ($typeOptions as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
        <div style="display:flex; flex-wrap:wrap; gap:8px; align-items:center">
            <button
                type="button"
                wire:click="selectRegion('')"
                class="btn btn-sm {{ $region === '' ? 'btn-primary' : 'btn-outline' }}"
                style="border-radius:999px; padding:8px 14px; font-size:12px"
            >{{ __('All regions') }}</button>
            @foreach ($regions as $r)
                <button
                    type="button"
                    wire:click="selectRegion(@js($r))"
                    class="btn btn-sm {{ $region === $r ? 'btn-primary' : 'btn-outline' }}"
                    style="border-radius:999px; padding:8px 14px; font-size:12px"
                >{{ $r }}</button>
            @endforeach
        </div>
    </div>

    @if ($tribes->isEmpty())
        <div style="background:#FFFBEB; border:1px solid #FDE68A; border-radius:24px; padding:32px; text-align:center; font-weight:600; color:#92400E;">
            {{ __('No tribes match these filters.') }}
        </div>
    @else
        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(220px, 1fr)); gap:22px;">
            @foreach ($tribes as $tribe)
                @php
                    $typeCounts = $countsByTribe[$tribe->id] ?? [];
                    $totalItems = collect($typeCounts)->sum('count');
                @endphp
                <div style="background:#fff; border-radius:24px; border:1px solid var(--cream-mid); overflow:hidden; box-shadow:0 8px 24px rgba(26,18,8,.05);">
                    <div style="height:88px; display:flex; align-items:center; justify-content:center; font-size:36px; background:linear-gradient(135deg, {{ $tribe->color ? $tribe->color.'33' : 'var(--cream-mid)' }}, var(--cream-warm));">
                        {{ $tribe->hero_emoji ?: '🌍' }}
                    </div>
                    <div style="padding:16px 18px 20px;">
                        <div style="font-family:var(--font-display); font-size:17px; font-weight:800; color:var(--ink); margin-bottom:4px">{{ $tribe->name }}</div>
                        <div style="font-size:11px; font-weight:700; color:var(--stone); margin-bottom:12px">
                            @if ($tribe->region)
                                {{ $tribe->region }}
                            @endif
                            @if ($tribe->hero_name)
                                @if ($tribe->region) · @endif{{ $tribe->hero_name }}
                            @endif
                        </div>
                        @if ($typeCounts !== [])
                            <div style="display:flex; flex-wrap:wrap; gap:6px; margin-bottom:12px">
                                @foreach ($typeCounts as $row)
                                    <span style="display:inline-block; font-size:10px; font-weight:800; background:var(--cream-warm); padding:4px 10px; border-radius:999px; color:var(--ink-light);">
                                        {{ $row['count'] }} {{ $row['label'] }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <span style="display:inline-block; font-size:10px; font-weight:800; background:var(--cream-warm); padding:4px 10px; border-radius:999px; color:var(--ink-light); margin-bottom:12px">
                                {{ $totalItems }} {{ __('approved items') }}
                            </span>
                        @endif
                        <a
                            href="{{ route('teacher.library', array_filter(['tribe' => $tribe->id, 'type' => $type ?: null])) }}"
                            wire:navigate
                            class="btn btn-primary btn-sm"
                            style="width:100%; display:block; text-align:center; text-decoration:none; border-radius:12px; padding:10px; font-size:12px; margin-top:4px"
                        >{{ __('Open Library') }}</a>
                    </div>
                </div>
            @endforeach
        </div>

        <div style="margin-top:28px">
            {{ $tribes->links('vendor.pagination.teacher') }}
        </div>
    @endif
</div>
