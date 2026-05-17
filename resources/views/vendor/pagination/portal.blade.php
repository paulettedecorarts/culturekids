{{--
    Shared pagination for CMS, Super Admin, and Teacher portals (no Tailwind).
    Uses Livewire wire:click (not href navigation) to avoid broken relative URLs.
--}}
@php
    if (! isset($scrollTo)) {
        $scrollTo = false;
    }

    $scrollIntoViewJsSnippet = ($scrollTo !== false)
        ? <<<JS
           (\$el.closest('{$scrollTo}') || document.querySelector('{$scrollTo}')).scrollIntoView()
        JS
        : '';
@endphp

@if ($paginator->hasPages())
    <nav class="portal-pagination" aria-label="{{ __('Pagination') }}">
        <div class="portal-pagination__controls">
            @if ($paginator->onFirstPage())
                <span class="portal-pagination__btn portal-pagination__btn--disabled" aria-disabled="true">« {{ __('Previous') }}</span>
            @else
                <button
                    type="button"
                    wire:click="previousPage('{{ $paginator->getPageName() }}')"
                    @if ($scrollIntoViewJsSnippet !== '') x-on:click="{{ $scrollIntoViewJsSnippet }}" @endif
                    wire:loading.attr="disabled"
                    class="portal-pagination__btn"
                    rel="prev"
                >« {{ __('Previous') }}</button>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="portal-pagination__ellipsis" aria-hidden="true">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        <span wire:key="paginator-{{ $paginator->getPageName() }}-page-{{ $page }}">
                            @if ($page == $paginator->currentPage())
                                <span class="portal-pagination__page portal-pagination__page--active" aria-current="page">{{ $page }}</span>
                            @else
                                <button
                                    type="button"
                                    wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')"
                                    @if ($scrollIntoViewJsSnippet !== '') x-on:click="{{ $scrollIntoViewJsSnippet }}" @endif
                                    wire:loading.attr="disabled"
                                    class="portal-pagination__page"
                                    aria-label="{{ __('Go to page :page', ['page' => $page]) }}"
                                >{{ $page }}</button>
                            @endif
                        </span>
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <button
                    type="button"
                    wire:click="nextPage('{{ $paginator->getPageName() }}')"
                    @if ($scrollIntoViewJsSnippet !== '') x-on:click="{{ $scrollIntoViewJsSnippet }}" @endif
                    wire:loading.attr="disabled"
                    class="portal-pagination__btn"
                    rel="next"
                >{{ __('Next') }} »</button>
            @else
                <span class="portal-pagination__btn portal-pagination__btn--disabled" aria-disabled="true">{{ __('Next') }} »</span>
            @endif
        </div>

        @if ($paginator->total() > 0)
            <p class="portal-pagination__summary">
                {{ __('Showing') }}
                <strong>{{ $paginator->firstItem() }}</strong>
                {{ __('to') }}
                <strong>{{ $paginator->lastItem() }}</strong>
                {{ __('of') }}
                <strong>{{ $paginator->total() }}</strong>
                {{ __('results') }}
            </p>
        @endif
    </nav>

    <style>
        .portal-pagination {
            margin-top: var(--sp-6, 24px);
            padding-top: var(--sp-4, 16px);
            border-top: 1px solid var(--cms-border, var(--sa-border, var(--cream-mid, #EDE0CE)));
        }

        .portal-pagination__controls {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .portal-pagination__btn,
        .portal-pagination__page {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 40px;
            padding: 8px 14px;
            border-radius: var(--r-full, 9999px);
            font-family: var(--font-admin, 'Bricolage Grotesque', sans-serif);
            font-size: 12px;
            font-weight: 800;
            text-decoration: none;
            cursor: pointer;
            transition: background 0.2s, border-color 0.2s, color 0.2s, opacity 0.2s;
            border: 2px solid var(--cms-border, var(--sa-border, var(--cream-mid, #EDE0CE)));
            background: var(--cms-surface, var(--sa-surface, #fff));
            color: var(--cms-text, var(--sa-text, var(--ink, #1A1208)));
        }

        .portal-pagination__page {
            min-width: 40px;
            padding: 8px 12px;
        }

        .portal-pagination__btn:hover,
        .portal-pagination__page:hover {
            border-color: var(--clay-red, #C44B2B);
            background: var(--cms-surface-hover, var(--sa-surface-hover, var(--cream-warm, #F5EDE0)));
            color: var(--cms-text, var(--sa-text, var(--ink, #1A1208)));
        }

        .portal-pagination__page--active {
            background: var(--clay-red, #C44B2B);
            border-color: var(--clay-red, #C44B2B);
            color: #fff;
            cursor: default;
        }

        .portal-pagination__btn--disabled {
            opacity: 0.35;
            cursor: not-allowed;
            pointer-events: none;
        }

        .portal-pagination__ellipsis {
            min-width: 28px;
            text-align: center;
            font-size: 14px;
            font-weight: 800;
            color: var(--cms-text-muted, var(--sa-text-muted, var(--stone, #9C8875)));
            padding: 0 4px;
        }

        .portal-pagination__summary {
            margin-top: 12px;
            text-align: center;
            font-size: 12px;
            font-weight: 600;
            color: var(--cms-text-muted, var(--sa-text-muted, var(--stone, #9C8875)));
        }

        .portal-pagination__summary strong {
            color: var(--cms-text, var(--sa-text, var(--ink, #1A1208)));
            font-weight: 800;
        }

        [data-cms-theme="dark"] .portal-pagination__btn,
        [data-cms-theme="dark"] .portal-pagination__page,
        [data-sa-theme="dark"] .portal-pagination__btn,
        [data-sa-theme="dark"] .portal-pagination__page {
            background: var(--cms-surface-raised, var(--sa-surface-raised, rgba(255,255,255,.06)));
            border-color: var(--cms-border, var(--sa-border, rgba(255,255,255,.12)));
            color: var(--cms-text, var(--sa-text, #fff));
        }

        [data-cms-theme="dark"] .portal-pagination__btn:hover,
        [data-cms-theme="dark"] .portal-pagination__page:hover,
        [data-sa-theme="dark"] .portal-pagination__btn:hover,
        [data-sa-theme="dark"] .portal-pagination__page:hover {
            background: var(--cms-surface-hover, var(--sa-surface-hover, rgba(255,255,255,.1)));
            border-color: var(--savanna-gold, #D4A017);
        }

        [data-cms-theme="dark"] .portal-pagination__page--active,
        [data-sa-theme="dark"] .portal-pagination__page--active {
            background: var(--clay-red, #C44B2B);
            border-color: var(--clay-red, #C44B2B);
            color: #fff;
        }

        [data-cms-theme="dark"] .portal-pagination__summary strong,
        [data-sa-theme="dark"] .portal-pagination__summary strong {
            color: var(--cms-text, var(--sa-text, #fff));
        }
    </style>
@endif
