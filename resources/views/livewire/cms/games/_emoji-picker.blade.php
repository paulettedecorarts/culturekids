<div style="position:absolute;z-index:200;bottom:calc(100% + 8px);left:0;background:#1a2744;border:1px solid rgba(255,255,255,.15);border-radius:12px;padding:14px;width:300px;box-shadow:0 12px 40px rgba(0,0,0,.6)">
    <div style="display:flex;gap:4px;flex-wrap:wrap;margin-bottom:10px;max-height:54px;overflow-y:auto">
        @foreach(array_keys($emojiCategories) as $cat)
            <button type="button" wire:click="$set('emojiPickerCategory', @js($cat))"
                style="padding:3px 8px;border-radius:6px;font-size:10px;font-weight:600;border:1px solid;cursor:pointer;white-space:nowrap;
                    {{ $emojiPickerCategory === $cat ? 'background:rgba(212,160,23,.3);color:#F2CB5A;border-color:rgba(212,160,23,.5)' : 'background:rgba(255,255,255,.05);color:rgba(255,255,255,.6);border-color:rgba(255,255,255,.1)' }}">
                {{ $cat }}
            </button>
        @endforeach
    </div>
    <div style="display:grid;grid-template-columns:repeat(8,1fr);gap:3px;max-height:180px;overflow-y:auto">
        @foreach($emojiCategories[$emojiPickerCategory] ?? [] as $emoji)
            <button type="button"
                wire:click="selectEmoji(@js($target), @js($emoji))"
                wire:key="ep-{{ $target }}-{{ $loop->index }}"
                style="width:30px;height:30px;border:1px solid rgba(255,255,255,.08);border-radius:6px;background:rgba(255,255,255,.04);cursor:pointer;font-size:16px;display:flex;align-items:center;justify-content:center"
                onmouseover="this.style.background='rgba(212,160,23,.2)'"
                onmouseout="this.style.background='rgba(255,255,255,.04)'">{{ $emoji }}</button>
        @endforeach
    </div>
    <div style="margin-top:8px;text-align:right">
        <button type="button" wire:click="$set('emojiPickerTarget', null)" style="font-size:11px;color:rgba(255,255,255,.5);background:none;border:none;cursor:pointer">Close</button>
    </div>
</div>