{{--
    Teacher hub layout does not load Tailwind; default Laravel pagination uses oversized SVGs without utility classes.
    Plain text + inline styles only.
--}}
@if ($paginator->hasPages())
    <nav class="teacher-pagination" style="margin-top:24px;" aria-label="{{ __('Pagination') }}">
        <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:center;gap:8px;font-size:13px;font-weight:700;">
            @if ($paginator->onFirstPage())
                <span style="opacity:.35;padding:8px 14px;border-radius:999px;">« {{ __('Previous') }}</span>
            @else
                <a
                    href="{{ $paginator->previousPageUrl() }}"
                    rel="prev"
                    wire:navigate
                    style="padding:8px 14px;border-radius:999px;border:2px solid var(--cream-mid);background:#fff;color:var(--ink);text-decoration:none;"
                >« {{ __('Previous') }}</a>
            @endif

            @for ($page = 1; $page <= $paginator->lastPage(); $page++)
                @if ($page == $paginator->currentPage())
                    <span style="min-width:40px;text-align:center;padding:8px 12px;border-radius:999px;background:var(--clay-red);color:#fff;">{{ $page }}</span>
                @else
                    <a
                        href="{{ $paginator->url($page) }}"
                        wire:navigate
                        style="min-width:40px;text-align:center;padding:8px 12px;border-radius:999px;border:2px solid var(--cream-mid);background:#fff;color:var(--ink);text-decoration:none;"
                    >{{ $page }}</a>
                @endif
            @endfor

            @if ($paginator->hasMorePages())
                <a
                    href="{{ $paginator->nextPageUrl() }}"
                    rel="next"
                    wire:navigate
                    style="padding:8px 14px;border-radius:999px;border:2px solid var(--cream-mid);background:#fff;color:var(--ink);text-decoration:none;"
                >{{ __('Next') }} »</a>
            @else
                <span style="opacity:.35;padding:8px 14px;border-radius:999px;">{{ __('Next') }} »</span>
            @endif
        </div>
        <p style="text-align:center;margin-top:12px;font-size:12px;font-weight:600;color:var(--stone);">
            {{ __('Showing') }}
            <span style="color:var(--ink);">{{ $paginator->firstItem() }}</span>
            {{ __('to') }}
            <span style="color:var(--ink);">{{ $paginator->lastItem() }}</span>
            {{ __('of') }}
            <span style="color:var(--ink);">{{ $paginator->total() }}</span>
            {{ __('results') }}
        </p>
    </nav>
@endif
