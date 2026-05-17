<div>
    <div style="margin-bottom:var(--sp-5)">
        <div class="sa-page-title">Module Toggles</div>
        <div class="sa-breadcrumb">Global platform feature control — affects all organizations</div>
    </div>

    @if (session()->has('message'))
        <div style="background:rgba(74,124,89,0.1); border:1px solid rgba(74,124,89,0.3); color:var(--banana-light); padding:12px 20px; border-radius:12px; margin-bottom:var(--sp-6); font-size:12px; font-weight:700">
            ✨ {{ session('message') }}
        </div>
    @endif

    @if(count($modules) > 0)
        <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:var(--sp-3)">
            @foreach($modules as $module)
                <div class="module-toggle">
                    <div class="toggle-info">
                        <div class="toggle-name">{{ $module->icon }} {{ $module->name }}</div>
                        <div class="toggle-desc">{{ $module->description }}</div>
                    </div>
                    <div 
                        class="toggle-switch {{ $module->is_enabled ? 'on' : 'off' }}" 
                        wire:click="toggle({{ $module->id }})"
                    ></div>
                </div>
            @endforeach
        </div>
    @else
        <div style="text-align:center;padding:var(--sp-12);color:var(--cms-text-muted)">
            <div style="font-size:48px;margin-bottom:var(--sp-4)">🧩</div>
            <div style="font-size:16px;font-weight:700;margin-bottom:var(--sp-2)">No modules configured</div>
            <div style="font-size:13px;margin-bottom:var(--sp-4)">Create modules in the Module Registry first.</div>
            <a href="{{ route('admin.modules-registry') }}" style="display:inline-block;background:var(--clay-red);color:var(--cms-text);padding:12px 24px;border-radius:14px;text-decoration:none;font-weight:700;font-size:13px">
                Go to Module Registry →
            </a>
        </div>
    @endif
</div>
