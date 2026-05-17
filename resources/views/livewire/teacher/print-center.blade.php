<div class="teacher-print-center">
    <div class="header">
        <div>
            <h1 class="page-title">{{ __('Print Center') }}</h1>
            <div class="breadcrumb">{{ __('Content · Stories & printable activities') }}</div>
            <p style="margin-top:12px; font-size:14px; font-weight:600; color:var(--stone); max-width:640px; line-height:1.5">
                {{ __('Open a story for browser printing, or download PDFs for activities when a file is attached in metadata.') }}
            </p>
        </div>
    </div>

    <div class="th-filters">
        <input
            type="search"
            wire:model.live.debounce.300ms="search"
            placeholder="{{ __('Search…') }}"
            class="th-input th-input--search"
        >
        <select wire:model.live="tribe" class="th-select">
            <option value="">{{ __('All tribes') }}</option>
            @foreach ($tribeOptions as $t)
                <option value="{{ $t->id }}">{{ $t->name }}</option>
            @endforeach
        </select>
        <select wire:model.live="kind" class="th-select">
            <option value="all">{{ __('Stories & activities') }}</option>
            <option value="comics">{{ __('Stories / comics only') }}</option>
            <option value="activities">{{ __('Activities only') }}</option>
        </select>
    </div>

    @if ($kind === 'all' || $kind === 'comics')
        <div style="margin-bottom:36px">
            <h3 style="font-size:13px; font-weight:800; text-transform:uppercase; letter-spacing:1px; color:var(--stone); margin-bottom:16px">{{ __('Stories & comics') }}</h3>
            @if ($comics->isEmpty())
                <div style="background:#FFFBEB; border:1px solid #FDE68A; border-radius:20px; padding:24px; font-weight:600; color:#92400E;">
                    {{ __('No matching stories.') }}
                </div>
            @else
                <div style="display:flex; flex-direction:column; gap:8px">
                    @foreach ($comics as $comic)
                        <div style="display:flex; align-items:center; gap:20px; padding:20px 22px; background:#fff; border:1px solid var(--cream-mid); border-radius:24px; box-shadow:0 4px 20px rgba(26,18,8,.04); flex-wrap:wrap">
                            <div style="width:52px; height:52px; border-radius:14px; background:linear-gradient(135deg,var(--cream-warm),var(--cream-mid)); display:flex; align-items:center; justify-content:center; font-size:24px; flex-shrink:0">
                                {{ $comic->tribe?->hero_emoji ?? '📖' }}
                            </div>
                            <div style="flex:1; min-width:200px">
                                <div style="font-weight:800; font-size:16px; color:var(--ink)">{{ $comic->title }}</div>
                                <div style="font-size:12px; font-weight:700; color:var(--stone); margin-top:4px">
                                    {{ $comic->tribe?->name ?? __('Tribe') }} · {{ $comic->age_range }} · {{ $comic->panels_count }} {{ __('panels') }}
                                </div>
                            </div>
                            <div style="display:flex; gap:8px; flex-wrap:wrap">
                                <a
                                    href="{{ route('teacher.stories.show', $comic->id) }}"
                                    wire:navigate
                                    class="btn btn-outline btn-sm"
                                    style="text-decoration:none; border-radius:12px; padding:10px 16px; font-size:12px"
                                >{{ __('Read') }}</a>
                                <a
                                    href="{{ route('teacher.stories.show', $comic->id) }}?print=1"
                                    target="_blank"
                                    rel="noopener"
                                    class="btn btn-primary btn-sm"
                                    style="text-decoration:none; border-radius:12px; padding:10px 16px; font-size:12px"
                                >{{ __('Print') }}</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    @if ($kind === 'all' || $kind === 'activities')
        <div>
            <h3 style="font-size:13px; font-weight:800; text-transform:uppercase; letter-spacing:1px; color:var(--stone); margin-bottom:16px">{{ __('Activities') }}</h3>
            @if ($activities->isEmpty())
                <div style="background:#FFFBEB; border:1px solid #FDE68A; border-radius:20px; padding:24px; font-weight:600; color:#92400E;">
                    {{ __('No matching activities.') }}
                </div>
            @else
                <div style="display:flex; flex-direction:column; gap:8px">
                    @foreach ($activities as $activity)
                        @php($fileUrl = $activity->printableAssetUrl())
                        <div style="display:flex; align-items:center; gap:20px; padding:20px 22px; background:#fff; border:1px solid var(--cream-mid); border-radius:24px; box-shadow:0 4px 20px rgba(26,18,8,.04); flex-wrap:wrap">
                            <div style="width:52px; height:52px; border-radius:14px; background:linear-gradient(135deg,#E8F4FC,#D4E8F7); display:flex; align-items:center; justify-content:center; font-size:24px; flex-shrink:0">🖨</div>
                            <div style="flex:1; min-width:200px">
                                <div style="font-weight:800; font-size:16px; color:var(--ink)">{{ $activity->title }}</div>
                                <div style="font-size:12px; font-weight:700; color:var(--stone); margin-top:4px">
                                    {{ $activity->tribe?->name ?? __('Tribe') }}
                                    <span style="text-transform:capitalize"> · {{ str_replace('_', ' ', $activity->type) }}</span>
                                    @if ($activity->age_range)
                                        · {{ $activity->age_range }}
                                    @endif
                                </div>
                            </div>
                            <div style="display:flex; gap:8px; flex-wrap:wrap">
                                @if ($fileUrl)
                                    <a
                                        href="{{ $fileUrl }}"
                                        target="_blank"
                                        rel="noopener"
                                        class="btn btn-primary btn-sm"
                                        style="text-decoration:none; border-radius:12px; padding:10px 16px; font-size:12px"
                                    >{{ __('Download PDF') }}</a>
                                @else
                                    <span style="font-size:12px; font-weight:700; color:var(--stone); padding:10px 8px">{{ __('No PDF in metadata') }}</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif
</div>
