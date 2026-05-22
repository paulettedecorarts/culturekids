@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand :name="config('brand.name')" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center overflow-hidden rounded-md bg-black">
            <x-brand-logo variant="mark" class="!w-8 !h-8 !rounded-md" />
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand :name="config('brand.name')" {{ $attributes }}>
        <x-slot name="logo" class="flex aspect-square size-8 items-center justify-center overflow-hidden rounded-md bg-black">
            <x-brand-logo variant="mark" class="!w-8 !h-8 !rounded-md" />
        </x-slot>
    </flux:brand>
@endif
