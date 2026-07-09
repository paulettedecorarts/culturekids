@props([
    'target',
    'loading' => null,
    'type' => 'submit',
])

@php
    $loadingLabel = $loading ?? __('Please wait…');
@endphp

<button
    type="{{ $type }}"
    {{ $attributes->class(['fh-submit-btn']) }}
    wire:loading.attr="disabled"
    wire:loading.class="fh-submit-btn--loading"
    wire:target="{{ $target }}"
>
    <span class="fh-submit-btn__label" wire:loading.remove wire:target="{{ $target }}">{{ $slot }}</span>
    <span class="fh-submit-btn__loading" wire:loading.flex wire:target="{{ $target }}">
        <svg class="fh-submit-btn__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
            <circle cx="12" cy="12" r="10" stroke-width="3" stroke-dasharray="32" stroke-dashoffset="8" opacity="0.25"/>
            <path d="M12 2a10 10 0 0 1 10 10" stroke-width="3" stroke-linecap="round"/>
        </svg>
        {{ $loadingLabel }}
    </span>
</button>
