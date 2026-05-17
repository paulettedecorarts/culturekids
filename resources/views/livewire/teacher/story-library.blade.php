<div class="teacher-library">
    <div class="header">
        <div>
            <h1 class="page-title">{{ __('Story Library') }}</h1>
            <div class="breadcrumb">{{ __('Content · Published stories & comics') }}</div>
            <p style="margin-top:12px; font-size:14px; font-weight:600; color:var(--stone); max-width:560px; line-height:1.5">
                {{ __('Only stories your organisation admin has approved in the Review Queue appear here. Nothing shows until they publish a comic from review (heritage or school-owned).') }}
            </p>
        </div>
    </div>

    <div class="th-filters">
        <input
            type="search"
            wire:model.live.debounce.300ms="search"
            placeholder="{{ __('Search titles…') }}"
            class="th-input th-input--search"
        >
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

    @if ($comics->isEmpty())
        <div style="background:#FFFBEB; border:1px solid #FDE68A; border-radius:24px; padding:32px; text-align:center; font-weight:600; color:#92400E;">
            {{ __('No published stories match these filters.') }}
        </div>
    @else
        <div style="display:grid; grid-template-columns:repeat(auto-fill, minmax(260px, 1fr)); gap:24px;">
            @foreach ($comics as $comic)
                <a
                    href="{{ route('teacher.stories.show', $comic->id) }}"
                    wire:navigate
                    style="text-decoration:none; color:inherit; background:#fff; border-radius:32px; border:1px solid var(--cream-mid); overflow:hidden; box-shadow:0 8px 32px rgba(26,18,8,.06); transition:transform .2s; display:block;"
                    onmouseover="this.style.transform='translateY(-4px)'"
                    onmouseout="this.style.transform='none'"
                >
                    <div style="aspect-ratio:4/3; background:linear-gradient(135deg,var(--cream-warm),var(--cream-mid)); display:flex; align-items:center; justify-content:center; position:relative;">
                        @if ($comic->cover_image_path)
                            <img src="{{ asset('storage/'.$comic->cover_image_path) }}" alt="" style="width:100%; height:100%; object-fit:cover;">
                        @else
                            <span style="font-size:48px">{{ $comic->tribe?->hero_emoji ?? '📖' }}</span>
                        @endif
                    </div>
                    <div style="padding:20px 22px 24px;">
                        <p style="font-size:11px; font-weight:800; color:var(--clay-red); text-transform:uppercase; letter-spacing:.5px; margin-bottom:8px">
                            {{ $comic->tribe?->name ?? __('Tribe') }}
                        </p>
                        <h3 style="font-family:var(--font-display); font-size:18px; font-weight:800; color:var(--ink); line-height:1.25; margin-bottom:8px">{{ $comic->title }}</h3>
                        <p style="font-size:12px; font-weight:700; color:var(--stone)">{{ $comic->age_range }} · {{ $comic->panels_count }} {{ __('panels') }}</p>
                    </div>
                </a>
            @endforeach
        </div>

        <div style="margin-top:32px">
            {{ $comics->links('vendor.pagination.teacher') }}
        </div>
    @endif
</div>
