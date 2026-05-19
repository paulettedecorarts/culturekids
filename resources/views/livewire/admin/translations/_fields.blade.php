@php
    $fields = $fieldSchema['fields'] ?? [];
@endphp

<div class="tf-fields">
    @if(!empty($fieldSchema['subtype_label']))
        <div class="tf-subtype-badge">{{ $fieldSchema['subtype_label'] }}</div>
    @endif

    @foreach($fields as $field)
        @if(($field['key'] ?? '') === '_note')
            <div class="tf-note">{{ $field['hint'] }}</div>
            @continue
        @endif

        @php $key = $field['key']; @endphp

        @if(in_array($key, ['x_position', 'y_position', 'width', 'height'], true))
            @continue
        @endif

        <div class="tf-field">
            <label class="tf-label">{{ $field['label'] }}</label>
            <input
                wire:model="{{ $key }}"
                type="text"
                class="tf-input"
                placeholder="{{ $field['placeholder'] ?? '' }}"
            >
            @error($key) <div class="tf-error">{{ $message }}</div> @enderror
            @if(!empty($field['hint']))
                <div class="tf-hint">{{ $field['hint'] }}</div>
            @endif
        </div>
    @endforeach

    @if($content_type === 'story')
        <div class="tf-field-group">
            <div class="tf-label" style="margin-bottom:8px">Hotspot on panel</div>
            <div class="tf-hotspot-grid">
                <div>
                    <label class="tf-label">X %</label>
                    <input wire:model="x_position" type="number" min="0" max="100" class="tf-input">
                    @error('x_position') <div class="tf-error">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="tf-label">Y %</label>
                    <input wire:model="y_position" type="number" min="0" max="100" class="tf-input">
                    @error('y_position') <div class="tf-error">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="tf-label">Width</label>
                    <input wire:model="width" type="number" min="1" class="tf-input">
                    @error('width') <div class="tf-error">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="tf-label">Height</label>
                    <input wire:model="height" type="number" min="1" class="tf-input">
                    @error('height') <div class="tf-error">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="tf-hint">Position matches the panel editor vocab hotspots.</div>
        </div>
    @endif

    <div class="tf-field-actions">
        <button type="button" class="sa-table-action" wire:click="syncFromSource">Load from source</button>
    </div>
</div>
