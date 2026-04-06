<div>
    <div style="margin-bottom:var(--sp-5)">
        <div class="sa-page-title">Module Toggles</div>
        <div class="sa-breadcrumb">Global platform feature control — affects all organizations</div>
    </div>

    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:var(--sp-3)">
        @foreach($modules as $index => $module)
            <div class="module-toggle">
                <div class="toggle-info">
                    <div class="toggle-name">{{ $module['icon'] }} {{ $module['name'] }}</div>
                    <div class="toggle-desc">{{ $module['description'] }}</div>
                </div>
                <!-- Wire up the toggle action to Livewire instead of JS -->
                <div 
                    class="toggle-switch {{ $module['status'] ? 'on' : 'off' }}" 
                    wire:click="toggle({{ $index }})"
                ></div>
            </div>
        @endforeach
    </div>
</div>
