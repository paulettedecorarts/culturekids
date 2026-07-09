@props([
    'target',
    'loading' => null,
    'type' => 'submit',
    'outline' => false,
])

@php
    $loadingLabel = $loading ?? __('Please wait…');
    $classes = 'btn-primary guest-submit-btn'.($outline ? ' btn-primary--outline' : '');
@endphp

<button
    type="{{ $type }}"
    {{ $attributes->class([$classes]) }}
    wire:loading.attr="disabled"
    wire:loading.class="guest-submit-btn--loading"
    wire:target="{{ $target }}"
>
    <span wire:loading.remove.delay wire:target="{{ $target }}">{{ $slot }}</span>
    <span class="guest-submit-btn__loading" wire:loading.flex.delay wire:target="{{ $target }}">
        <svg class="guest-submit-btn__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
            <circle cx="12" cy="12" r="10" stroke-width="3" stroke-dasharray="32" stroke-dashoffset="8" opacity="0.25"/>
            <path d="M12 2a10 10 0 0 1 10 10" stroke-width="3" stroke-linecap="round"/>
        </svg>
        {{ $loadingLabel }}
    </span>
</button>
