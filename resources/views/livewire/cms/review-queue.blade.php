<div class="review-queue-page">
    <div class="cms-header cms-page-header">
        <div>
            <h1 class="cms-page-title">Content Review Queue</h1>
            <div class="cms-breadcrumb">Management · {{ $organization }} · Approval</div>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="cms-flash-success">
            {{ session('message') }}
        </div>
    @endif

    <div class="cms-stats-row" style="grid-template-columns:repeat(auto-fit, minmax(120px, 1fr));">
        <div class="cms-stat">
            <div class="cms-stat-val">{{ $pendingTotal }}</div>
            <div class="cms-stat-label">Total Pending</div>
        </div>
        @foreach($typeLabels as $typeKey => $typeLabel)
            <div class="cms-stat">
                <div class="cms-stat-val">{{ $countsByType[$typeKey] ?? 0 }}</div>
                <div class="cms-stat-label">{{ $typeLabel }}</div>
            </div>
        @endforeach
    </div>

    <div class="review-queue-filters">
        <input
            type="search"
            wire:model.live.debounce.300ms="search"
            placeholder="Search by title, type, {{ strtolower(heritage('people')) }}, or status…"
            class="review-queue-filters__search"
            aria-label="{{ __('Search queue') }}"
        >

        <select wire:model.live="typeFilter" class="review-queue-filters__select" aria-label="{{ __('Filter by type') }}">
            <option value="">{{ __('All types') }}</option>
            @foreach($typeLabels as $typeKey => $typeLabel)
                <option value="{{ $typeKey }}">{{ $typeLabel }}</option>
            @endforeach
        </select>

        <select wire:model.live="tribeFilter" class="review-queue-filters__select" aria-label="{{ heritage('filter_by_people') }}">
            <option value="">{{ heritage('all_peoples') }}</option>
            @foreach($tribes as $tribe)
                <option value="{{ $tribe->id }}">{{ $tribe->name }}</option>
            @endforeach
        </select>

        <select wire:model.live="statusFilter" class="review-queue-filters__select" aria-label="{{ __('Filter by status') }}">
            <option value="">{{ __('All statuses') }}</option>
            @foreach($statusOptions as $status)
                <option value="{{ $status }}">{{ ucfirst($status) }}</option>
            @endforeach
        </select>

        <select wire:model.live="sortBy" class="review-queue-filters__select" aria-label="{{ __('Sort results') }}">
            <option value="updated_desc">{{ __('Newest first') }}</option>
            <option value="updated_asc">{{ __('Oldest first') }}</option>
            <option value="title_asc">{{ __('Title A–Z') }}</option>
            <option value="title_desc">{{ __('Title Z–A') }}</option>
        </select>

        @if($this->hasActiveFilters())
            <button type="button" wire:click="clearFilters" class="review-queue-filters__clear btn btn-ghost btn-sm">
                {{ __('Clear filters') }}
            </button>
        @endif
    </div>

    <div class="review-queue-results-bar">
        <span>
            @if($this->hasActiveFilters())
                {{ __('Showing :filtered of :total results', ['filtered' => $filteredTotal, 'total' => $pendingTotal]) }}
            @else
                {{ __(':total items in queue', ['total' => $pendingTotal]) }}
            @endif
            <span wire:loading wire:target="search,typeFilter,tribeFilter,statusFilter,sortBy,clearFilters" class="review-queue-results-bar__loading">
                {{ __('Updating…') }}
            </span>
        </span>
        @if($filteredTotal > 0)
            <button
                type="button"
                class="btn btn-primary btn-sm review-queue-approve-all"
                wire:click="approveAll"
                wire:confirm="{{ __('Approve all :count items currently in this queue? This cannot be undone.', ['count' => $filteredTotal]) }}"
                wire:loading.attr="disabled"
                wire:target="approveAll"
                wire:loading.class="opacity-50 cursor-not-allowed"
            >
                <span wire:loading.remove wire:target="approveAll">{{ __('Approve all (:count)', ['count' => $filteredTotal]) }}</span>
                <span wire:loading wire:target="approveAll">{{ __('Approving all…') }}</span>
            </button>
        @endif
    </div>

    <div class="cms-asset-table">
        <div class="cms-table-header review-queue-table-grid">
            <span>{{ __('Type') }}</span>
            <span>{{ __('Title') }}</span>
            <span>{{ heritage('people') }}</span>
            <span>{{ __('Updated') }}</span>
            <span>{{ __('Status') }}</span>
            <span>{{ __('Actions') }}</span>
        </div>
        @forelse($pendingItems as $item)
            <div class="cms-table-row review-queue-table-grid" style="cursor:default;">
                <span class="review-queue-type" data-label="{{ __('Type') }}">{{ $item['type_label'] }}</span>
                <span class="review-queue-title" data-label="{{ __('Title') }}">{{ $item['title'] }}</span>
                <span class="review-queue-muted" data-label="{{ heritage('people') }}">{{ $item['tribe_name'] ?? '—' }}</span>
                <span class="review-queue-muted" data-label="{{ __('Updated') }}">{{ $item['updated_at']?->diffForHumans() }}</span>
                <span class="review-queue-status" data-label="{{ __('Status') }}">{{ ucfirst($item['status'] ?? 'published') }}</span>
                <span class="review-queue-actions" data-label="{{ __('Actions') }}">
                    <button
                        type="button"
                        class="btn btn-primary btn-sm"
                        wire:click="approve('{{ $item['content_type'] }}', {{ $item['id'] }})"
                        wire:loading.attr="disabled"
                        wire:target="approve('{{ $item['content_type'] }}', {{ $item['id'] }})"
                        wire:loading.class="opacity-50 cursor-not-allowed"
                    >
                        <span wire:loading.remove wire:target="approve('{{ $item['content_type'] }}', {{ $item['id'] }})">{{ __('Approve') }}</span>
                        <span wire:loading wire:target="approve('{{ $item['content_type'] }}', {{ $item['id'] }})">{{ __('Approving…') }}</span>
                    </button>
                    <button
                        type="button"
                        class="btn btn-ghost btn-sm"
                        wire:click="reject('{{ $item['content_type'] }}', {{ $item['id'] }})"
                        wire:loading.attr="disabled"
                        wire:target="reject('{{ $item['content_type'] }}', {{ $item['id'] }})"
                        wire:loading.class="opacity-50 cursor-not-allowed"
                    >
                        <span wire:loading.remove wire:target="reject('{{ $item['content_type'] }}', {{ $item['id'] }})">{{ __('Reject') }}</span>
                        <span wire:loading wire:target="reject('{{ $item['content_type'] }}', {{ $item['id'] }})">{{ __('Rejecting…') }}</span>
                    </button>
                </span>
            </div>
        @empty
            <div class="review-queue-empty">
                @if($this->hasActiveFilters())
                    {{ __('No items match your filters.') }}
                    <button type="button" wire:click="clearFilters" class="btn btn-ghost btn-sm" style="margin-top:12px;">
                        {{ __('Clear filters') }}
                    </button>
                @else
                    {{ __('No content awaiting approval across any activity type.') }}
                @endif
            </div>
        @endforelse
    </div>

    @if($pendingItems->hasPages())
        <div style="margin-top:var(--sp-6);">
            {{ $pendingItems->links(data: ['scrollTo' => false]) }}
        </div>
    @endif

    <style>
        .review-queue-filters {
            display: flex;
            flex-wrap: wrap;
            gap: var(--sp-3, 12px);
            margin-bottom: var(--sp-4, 16px);
            align-items: center;
        }

        .review-queue-filters__search {
            flex: 1 1 220px;
            min-width: 200px;
            padding: 10px 16px;
            border-radius: var(--r-full, 9999px);
            border: 1px solid var(--cms-input-border, #EDE0CE);
            background: var(--cms-input-bg, #fff);
            color: var(--cms-text, #1A1208);
            font-family: var(--font-admin, 'Bricolage Grotesque', sans-serif);
            font-size: 13px;
            font-weight: 600;
            outline: none;
        }

        .review-queue-filters__search:focus {
            border-color: var(--clay-red, #C44B2B);
            box-shadow: 0 0 0 3px rgba(196, 75, 43, 0.12);
        }

        .review-queue-filters__select {
            padding: 10px 14px;
            border-radius: var(--r-full, 9999px);
            border: 1px solid var(--cms-input-border, #EDE0CE);
            background: var(--cms-input-bg, #fff);
            color: var(--cms-text, #1A1208);
            font-family: var(--font-admin, 'Bricolage Grotesque', sans-serif);
            font-size: 12px;
            font-weight: 700;
            outline: none;
            min-width: 140px;
        }

        .review-queue-filters__clear {
            white-space: nowrap;
        }

        .review-queue-results-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: var(--sp-4, 16px);
            font-size: 12px;
            font-weight: 700;
            color: var(--cms-text-muted, #9C8875);
        }

        .review-queue-results-bar__loading {
            display: inline-block;
            margin-left: 8px;
            font-size: 11px;
            opacity: 0.8;
        }

        .review-queue-approve-all {
            white-space: nowrap;
            flex-shrink: 0;
        }

        .review-queue-table-grid {
            display: grid;
            grid-template-columns: 110px minmax(0, 2fr) minmax(100px, 1fr) minmax(90px, 0.9fr) 90px 180px;
            gap: 8px;
            align-items: center;
        }

        .review-queue-type {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            color: var(--cms-text-muted, #9C8875);
        }

        .review-queue-title {
            font-weight: 700;
            color: var(--cms-text, #1A1208);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .review-queue-muted {
            font-size: 12px;
            color: var(--cms-text-muted, #9C8875);
        }

        .review-queue-status {
            font-size: 11px;
            font-weight: 700;
            text-transform: capitalize;
            color: var(--cms-text-muted, #9C8875);
        }

        .review-queue-actions {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        .review-queue-empty {
            padding: 24px;
            color: var(--cms-text-muted, #9C8875);
            font-weight: 700;
            text-align: center;
        }

        .review-queue-filters select option,
        .review-queue-filters select optgroup {
            background: var(--cms-input-bg, #fff);
            color: var(--cms-text, #1A1208);
        }

        [data-cms-theme="dark"] .review-queue-filters__search,
        [data-cms-theme="dark"] .review-queue-filters__select {
            background: var(--cms-input-bg, rgba(255,255,255,.06));
            border-color: var(--cms-input-border, rgba(255,255,255,.12));
            color: var(--cms-text, #fff);
        }

        [data-cms-theme="dark"] .review-queue-title {
            color: var(--cms-text, #fff);
        }

        @media (max-width: 900px) {
            .review-queue-filters__search {
                flex: 1 1 100%;
            }
        }
    </style>
</div>
