<div style="position:absolute;z-index:200;bottom:calc(100% + 8px);left:0;background:var(--cms-input-bg);border:1px solid var(--cms-border);border-radius:12px;padding:14px;width:300px;box-shadow:0 12px 40px rgba(0,0,0,.6)">
    <div style="display:flex;gap:4px;flex-wrap:wrap;margin-bottom:10px;max-height:54px;overflow-y:auto">
        @foreach(array_keys($emojiCategories) as $cat)
            <button type="button" wire:click="$set('emojiPickerCategory', @js($cat))"
                style="padding:3px 8px;border-radius:6px;font-size:10px;font-weight:600;border:1px solid;cursor:pointer;white-space:nowrap;
                    {{ $emojiPickerCategory === $cat ? 'background:rgba(212,160,23,.3);color:#F2CB5A;border-color:rgba(212,160,23,.5)' : 'background:var(--cms-surface-raised);color:var(--cms-text-muted);border-color:var(--cms-border)' }}">
                {{ $cat }}
            </button>
        @endforeach
    </div>
    <div style="display:grid;grid-template-columns:repeat(8,1fr);gap:3px;max-height:180px;overflow-y:auto">
        @foreach($emojiCategories[$emojiPickerCategory] ?? [] as $emoji)
            <button type="button"
                wire:click="selectEmoji(@js($target), @js($emoji))"
                wire:key="ep-{{ $target }}-{{ $loop->index }}"
                style="width:30px;height:30px;border:1px solid var(--cms-border);border-radius:6px;background:var(--cms-surface-raised);cursor:pointer;font-size:16px;display:flex;align-items:center;justify-content:center"
                onmouseover="this.style.background='rgba(212,160,23,.2)'"
                onmouseout="this.style.background='rgba(255,255,255,.04)'">{{ $emoji }}</button>
        @endforeach
    </div>
    <div style="margin-top:8px;text-align:right">
        <button type="button" wire:click="$set('emojiPickerTarget', null)" style="font-size:11px;color:var(--cms-text-muted);background:none;border:none;cursor:pointer">Close</button>
    </div>
</div>