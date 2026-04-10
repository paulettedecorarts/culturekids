<div style="margin-bottom:20px; padding:0 4px">
    @if ($classrooms->isEmpty())
        <div style="font-size:11px; font-weight:600; color:rgba(255,255,255,.45); line-height:1.45">
            {{ __('No class assigned yet. Ask your organisation admin to assign you as a teacher in Classrooms.') }}
        </div>
    @elseif ($classrooms->count() === 1)
        <div style="font-size:10px; font-weight:800; color:rgba(255,255,255,.35); text-transform:uppercase; letter-spacing:1px; margin-bottom:6px">{{ __('Your class') }}</div>
        <div style="font-size:13px; font-weight:800; color:#fff">{{ $classrooms->first()->name }}</div>
    @else
        <label for="teacher-class-switch" style="display:block; font-size:10px; font-weight:800; color:rgba(255,255,255,.35); text-transform:uppercase; letter-spacing:1px; margin-bottom:8px">{{ __('Active class') }}</label>
        <select
            id="teacher-class-switch"
            wire:model.live="activeId"
            style="width:100%; padding:10px 12px; border-radius:12px; border:1px solid rgba(255,255,255,.2); background:rgba(255,255,255,.08); color:#fff; font-size:13px; font-weight:700; font-family:var(--font-admin); cursor:pointer"
        >
            @foreach ($classrooms as $room)
                <option value="{{ $room->id }}">{{ $room->name }}</option>
            @endforeach
        </select>
    @endif
</div>
