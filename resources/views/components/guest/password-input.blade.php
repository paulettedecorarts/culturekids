@props([
    'id',
    'label',
    'error' => null,
    'autocomplete' => 'current-password',
    'autofocus' => false,
])

<div class="input-group">
    <div @class(['password-field__label-row' => isset($labelExtra)])>
        <label class="input-label" for="{{ $id }}" @if(isset($labelExtra)) style="margin-bottom:0" @endif>
            {{ $label }}
        </label>
        @isset($labelExtra)
            {{ $labelExtra }}
        @endisset
    </div>

    <div class="password-field" x-data="{ show: false }">
        <input
            {{ $attributes->class(['form-input', 'password-field__input']) }}
            :type="show ? 'text' : 'password'"
            id="{{ $id }}"
            name="{{ $id }}"
            autocomplete="{{ $autocomplete }}"
            @if($autofocus) autofocus @endif
        />
        <button
            type="button"
            class="password-field__toggle"
            tabindex="-1"
            @click="show = !show"
            :aria-label="show ? '{{ __('Hide password') }}' : '{{ __('Show password') }}'"
            :title="show ? '{{ __('Hide password') }}' : '{{ __('Show password') }}'"
        >
            <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                <circle cx="12" cy="12" r="3"/>
            </svg>
            <svg x-show="show" x-cloak xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/>
                <path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/>
                <path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/>
                <line x1="2" x2="22" y1="2" y2="22"/>
            </svg>
        </button>
    </div>

    @if ($error)
        @error($error)
            <div class="input-error">{{ $message }}</div>
        @enderror
    @endif
</div>
