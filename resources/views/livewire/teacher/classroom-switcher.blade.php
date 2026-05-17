<div class="th-classroom-switcher-inner">
    @if ($classrooms->isEmpty())
        <div class="th-classroom-switcher__empty">
            {{ __('No class assigned yet. Ask your organisation admin to assign you as a teacher in Classrooms.') }}
        </div>
        <div
            class="th-classroom-switcher__compact"
            title="{{ __('No class assigned') }}"
            aria-hidden="true"
        >🏫</div>
    @elseif ($classrooms->count() === 1)
        <div class="th-classroom-switcher__label">{{ __('Your class') }}</div>
        <div class="th-classroom-switcher__name">{{ $classrooms->first()->name }}</div>
        <div
            class="th-classroom-switcher__compact"
            title="{{ $classrooms->first()->name }}"
            aria-hidden="true"
        >🏫</div>
    @else
        <label for="teacher-class-switch" class="th-classroom-switcher__label">{{ __('Active class') }}</label>
        <select
            id="teacher-class-switch"
            wire:model.live="activeId"
            class="th-classroom-switcher__select"
        >
            @foreach ($classrooms as $room)
                <option value="{{ $room->id }}">{{ $room->name }}</option>
            @endforeach
        </select>
        @php
            $activeName = $classrooms->firstWhere('id', $activeId)?->name ?? $classrooms->first()->name;
        @endphp
        <div
            class="th-classroom-switcher__compact"
            title="{{ $activeName }}"
            aria-hidden="true"
        >🏫</div>
    @endif
</div>
