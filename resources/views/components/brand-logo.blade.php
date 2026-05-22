@props([
    /** full | mark | compact */
    'variant' => 'full',
])

@php
    $logoUrl = asset(config('brand.logo'));
    $markUrl = asset(config('brand.favicon_32'));
    $alt = config('brand.name');
    $src = $variant === 'mark' ? $markUrl : $logoUrl;
    $classes = match ($variant) {
        'mark' => 'brand-logo brand-logo--mark',
        'compact' => 'brand-logo brand-logo--compact',
        default => 'brand-logo brand-logo--full',
    };
@endphp

<img
    src="{{ $src }}"
    alt="{{ $alt }}"
    {{ $attributes->class([$classes]) }}
    decoding="async"
/>
