@props([
    'target',
    'loading' => null,
    'type' => 'submit',
    'variant' => 'primary',
])

@php
    $loadingLabel = $loading ?? __('Saving…');
    $variantClass = match ($variant) {
        'block' => 'lw-submit-btn lw-submit-btn--block btn btn-primary',
        'md' => 'lw-submit-btn lw-submit-btn--md btn btn-primary',
        'success-sm' => 'lw-submit-btn lw-submit-btn--success-sm btn btn-sm',
        'sm' => 'lw-submit-btn lw-submit-btn--sm btn btn-sm',
        default => 'lw-submit-btn lw-submit-btn--primary btn btn-primary',
    };
@endphp

<button
    type="{{ $type }}"
    {{ $attributes->class([$variantClass]) }}
    wire:loading.attr="disabled"
    wire:loading.class="opacity-70"
    wire:target="{{ $target }}"
>
    <span wire:loading.remove.delay wire:target="{{ $target }}">{{ $slot }}</span>
    <span class="lw-submit-btn__loading" wire:loading.flex.delay wire:target="{{ $target }}">
        <svg class="lw-submit-btn__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
            <circle cx="12" cy="12" r="10" stroke-width="3" stroke-dasharray="32" stroke-dashoffset="8" opacity="0.25"/>
            <path d="M12 2a10 10 0 0 1 10 10" stroke-width="3" stroke-linecap="round"/>
        </svg>
        {{ $loadingLabel }}
    </span>
</button>
