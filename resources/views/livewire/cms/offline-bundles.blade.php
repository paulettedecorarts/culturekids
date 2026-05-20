<div
    class="offline-bundles-page"
    @if($this->shouldPoll) wire:poll.3s @endif
>
    <div class="cms-header cms-page-header">
        <div>
            <h1 class="cms-page-title">Offline bundles</h1>
            <div class="cms-breadcrumb">Published content · .ckb packages for all 12 activity types</div>
        </div>
        <div class="cms-page-actions offline-bundles-actions">
            <button
                type="button"
                wire:click="bulkRebuildMissing"
                wire:loading.attr="disabled"
                wire:target="bulkRebuildMissing,bulkRebuildAll"
                class="btn btn-ghost btn-sm"
                @disabled($this->summary['not_built'] + $this->summary['failed'] === 0)
            >
                <span wire:loading.remove wire:target="bulkRebuildMissing">{{ __('Build missing') }}</span>
                <span wire:loading wire:target="bulkRebuildMissing">{{ __('Queuing…') }}</span>
            </button>
            <button
                type="button"
                wire:click="bulkRebuildAll"
                wire:loading.attr="disabled"
                wire:target="bulkRebuildMissing,bulkRebuildAll"
                class="btn btn-primary btn-sm"
                @disabled($this->summary['total'] === 0)
            >
                <span wire:loading.remove wire:target="bulkRebuildAll">{{ __('Build all (filtered)') }}</span>
                <span wire:loading wire:target="bulkRebuildAll">{{ __('Queuing…') }}</span>
            </button>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="cms-flash-success" style="margin-bottom:var(--sp-4)">
            {{ session('message') }}
        </div>
    @endif

    <div class="offline-bundles-summary" wire:loading.class="opacity-60" wire:target="bulkRebuildMissing,bulkRebuildAll">
        <div class="offline-bundles-stat">
            <span class="offline-bundles-stat-val">{{ $this->summary['total'] }}</span>
            <span class="offline-bundles-stat-label">{{ __('Total') }}</span>
        </div>
        <div class="offline-bundles-stat">
            <span class="offline-bundles-stat-val ob-stat-ready">{{ $this->summary['ready'] }}</span>
            <span class="offline-bundles-stat-label">{{ __('Ready') }}</span>
        </div>
        <div class="offline-bundles-stat">
            <span class="offline-bundles-stat-val ob-stat-progress">{{ $this->summary['in_progress'] }}</span>
            <span class="offline-bundles-stat-label">{{ __('In progress') }}</span>
        </div>
        <div class="offline-bundles-stat">
            <span class="offline-bundles-stat-val ob-stat-failed">{{ $this->summary['failed'] }}</span>
            <span class="offline-bundles-stat-label">{{ __('Failed') }}</span>
        </div>
        <div class="offline-bundles-stat">
            <span class="offline-bundles-stat-val">{{ $this->summary['not_built'] }}</span>
            <span class="offline-bundles-stat-label">{{ __('Not built') }}</span>
        </div>
        @if($this->shouldPoll)
            <div class="offline-bundles-poll-hint" aria-live="polite">
                <span class="offline-bundles-poll-dot" aria-hidden="true"></span>
                {{ __('Updating statuses…') }}
            </div>
        @endif
    </div>

    <div class="cms-filters offline-bundles-filters">
        <input
            type="search"
            wire:model.live.debounce.300ms="search"
            class="cms-filter-input"
            placeholder="{{ __('Search by title or tribe…') }}"
            aria-label="{{ __('Search bundles') }}"
        >
        <select wire:model.live="typeFilter" class="cms-filter-input" aria-label="{{ __('Filter by type') }}">
            <option value="">{{ __('All types') }}</option>
            @foreach($typeLabels as $typeKey => $typeLabel)
                <option value="{{ $typeKey }}">{{ $typeLabel }}</option>
            @endforeach
        </select>
    </div>

    <div class="sa-table-wrap offline-bundles-table">
        <div class="sa-table-head offline-bundles-head" style="grid-template-columns:120px 2fr 1fr minmax(120px, 1.2fr) 120px">
            <span>{{ __('Type') }}</span>
            <span>{{ __('Title') }}</span>
            <span>{{ __('Tribe') }}</span>
            <span>{{ __('Status') }}</span>
            <span>{{ __('Actions') }}</span>
        </div>

        @forelse ($this->items as $item)
            <div
                class="sa-table-row offline-bundles-row"
                wire:key="bundle-{{ $item['content_type'] }}-{{ $item['content_id'] }}"
                style="grid-template-columns:120px 2fr 1fr minmax(120px, 1.2fr) 120px;align-items:center"
            >
                <span style="font-size:11px;font-weight:800;text-transform:uppercase;color:var(--cms-text-muted)">{{ $item['type_label'] }}</span>
                <div>
                    <div style="font-weight:700;color:var(--cms-text);font-size:14px">{{ $item['title'] }}</div>
                    <div style="font-size:10px;color:var(--cms-text-muted);font-family:monospace">#{{ $item['content_id'] }}</div>
                </div>
                <span style="font-size:12px;color:var(--cms-text-muted)">{{ $item['tribe_name'] ?? '—' }}</span>
                <div class="offline-bundles-status-cell">
                    <span class="ob-status-pill {{ $statusClasses[$item['status']] ?? 'ob-status--pending' }}">
                        @if(in_array($item['status'], ['queued', 'building'], true))
                            <span class="ob-status-spinner" aria-hidden="true"></span>
                        @endif
                        {{ $item['status_label'] }}
                    </span>
                    @if($item['ready'] && $item['bundle_hash'])
                        <div class="ob-status-meta">{{ \Illuminate\Support\Str::limit($item['bundle_hash'], 12) }}</div>
                    @endif
                    @if($item['status'] === 'failed' && $item['status_message'])
                        <div class="ob-status-error" title="{{ $item['status_message'] }}">{{ \Illuminate\Support\Str::limit($item['status_message'], 48) }}</div>
                    @endif
                    @if($item['built_at'] && $item['ready'])
                        <div class="ob-status-meta">{{ __('Built :time', ['time' => \Carbon\Carbon::parse($item['built_at'])->diffForHumans()]) }}</div>
                    @endif
                </div>
                <div>
                    <button
                        type="button"
                        wire:click="rebuild('{{ $item['content_type'] }}', {{ $item['content_id'] }})"
                        wire:loading.attr="disabled"
                        wire:target="rebuild('{{ $item['content_type'] }}', {{ $item['content_id'] }})"
                        class="btn btn-sm sa-table-action"
                        style="background:rgba(212,160,23,.18);color:#F2CB5A;border:1px solid rgba(212,160,23,.3)"
                    >
                        <span wire:loading.remove wire:target="rebuild('{{ $item['content_type'] }}', {{ $item['content_id'] }})">{{ __('Rebuild') }}</span>
                        <span wire:loading wire:target="rebuild('{{ $item['content_type'] }}', {{ $item['content_id'] }})">{{ __('Queuing…') }}</span>
                    </button>
                </div>
            </div>
        @empty
            <div class="cms-empty-state">
                <p>{{ __('No published content matches your filters.') }}</p>
            </div>
        @endforelse
    </div>

    <style>
        .offline-bundles-summary {
            display: flex;
            flex-wrap: wrap;
            gap: var(--sp-3);
            align-items: center;
            margin-bottom: var(--sp-5);
            padding: var(--sp-4);
            background: var(--cms-surface);
            border: 1px solid var(--cms-border);
            border-radius: var(--r-lg);
        }
        .offline-bundles-stat {
            display: flex;
            flex-direction: column;
            min-width: 72px;
        }
        .offline-bundles-stat-val {
            font-size: 22px;
            font-weight: 800;
            color: var(--cms-text);
            line-height: 1.1;
        }
        .offline-bundles-stat-label {
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--cms-text-muted);
            margin-top: 2px;
        }
        .ob-stat-ready { color: var(--banana-mid); }
        .ob-stat-progress { color: var(--savanna-gold); }
        .ob-stat-failed { color: var(--clay-red-light); }
        .offline-bundles-poll-hint {
            margin-left: auto;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            font-weight: 700;
            color: var(--cms-text-muted);
        }
        .offline-bundles-poll-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--savanna-gold);
            animation: ob-pulse 1.2s ease-in-out infinite;
        }
        @keyframes ob-pulse {
            0%, 100% { opacity: 0.35; transform: scale(0.9); }
            50% { opacity: 1; transform: scale(1.1); }
        }
        .ob-status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 10px;
            border-radius: var(--r-full);
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        .ob-status--ready {
            background: rgba(74, 124, 89, 0.15);
            color: var(--banana-mid);
            border: 1px solid rgba(74, 124, 89, 0.35);
        }
        .ob-status--queued,
        .ob-status--building {
            background: rgba(212, 160, 23, 0.15);
            color: var(--savanna-gold);
            border: 1px solid rgba(212, 160, 23, 0.35);
        }
        .ob-status--failed {
            background: rgba(196, 75, 43, 0.12);
            color: var(--clay-red-light);
            border: 1px solid rgba(196, 75, 43, 0.35);
        }
        .ob-status--pending {
            background: var(--cms-surface-raised);
            color: var(--cms-text-muted);
            border: 1px solid var(--cms-border);
        }
        .ob-status-spinner {
            width: 10px;
            height: 10px;
            border: 2px solid currentColor;
            border-right-color: transparent;
            border-radius: 50%;
            animation: ob-spin 0.7s linear infinite;
        }
        @keyframes ob-spin { to { transform: rotate(360deg); } }
        .ob-status-meta {
            font-family: monospace;
            font-size: 9px;
            color: var(--cms-text-muted);
            margin-top: 4px;
        }
        .ob-status-error {
            font-size: 10px;
            color: var(--clay-red-light);
            margin-top: 4px;
            line-height: 1.35;
            font-weight: 600;
        }
        @media (max-width: 767px) {
            .offline-bundles-actions {
                width: 100%;
                flex-direction: column;
            }
            .offline-bundles-actions .btn {
                width: 100%;
                justify-content: center;
            }
            .offline-bundles-poll-hint {
                margin-left: 0;
                width: 100%;
            }
        }
    </style>
</div>
