<div>
    <div class="header" style="margin-bottom:28px">
        <div>
            <h1 class="page-title" style="font-size:26px">{{ $comic->title }}</h1>
            <div class="breadcrumb">
                {{ $comic->tribe?->name ?? __('Tribe') }} · {{ $comic->age_range }} · {{ $comic->panels->count() }} {{ __('panels') }}
            </div>
        </div>
        <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center">
            <a href="{{ route('teacher.library') }}" wire:navigate class="btn btn-outline btn-sm" style="text-decoration:none">{{ __('← Story Library') }}</a>
            <button type="button" class="btn btn-primary btn-sm" style="border-radius:12px" onclick="window.print()">{{ __('Print') }}</button>
        </div>
    </div>

    @if ($comic->description)
        <div style="background:#fff; border:1px solid var(--cream-mid); border-radius:24px; padding:20px 24px; margin-bottom:24px; font-size:14px; color:var(--ink-light); line-height:1.6">
            {{ $comic->description }}
        </div>
    @endif

    <div style="display:grid; gap:24px;">
        @foreach ($comic->panels as $panel)
            <div style="background:#fff; border:1px solid var(--cream-mid); border-radius:24px; padding:24px;">
                <div style="font-size:11px; font-weight:800; color:var(--stone); text-transform:uppercase; letter-spacing:1px; margin-bottom:12px">{{ __('Panel') }} {{ $panel->order_index + 1 }}</div>
                @if ($panel->image_path)
                    <img src="{{ asset('storage/'.$panel->image_path) }}" alt="" style="max-width:100%; border-radius:14px; border:1px solid var(--cream-mid); margin-bottom:12px;">
                @endif
                @if ($panel->caption)
                    <div style="margin-bottom:12px; color:var(--ink); font-size:15px; line-height:1.5">{{ $panel->caption }}</div>
                @endif
                @if ($panel->audio_url)
                    <audio controls style="width:100%; max-width:480px;">
                        <source src="{{ asset('storage/'.$panel->audio_url) }}">
                    </audio>
                @endif
            </div>
        @endforeach
    </div>

    @if (request()->boolean('print'))
        <script>
            window.addEventListener('load', function () {
                setTimeout(function () { window.print(); }, 300);
            });
        </script>
    @endif
</div>
