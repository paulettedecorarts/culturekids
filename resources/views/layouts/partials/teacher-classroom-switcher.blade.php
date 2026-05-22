<div class="th-classroom-switcher-inner">
    @if ($teacherClassrooms->isEmpty())
        <div class="th-classroom-switcher__empty">
            {{ __('No class assigned yet. Ask your organisation admin to assign you as a teacher in Classrooms.') }}
        </div>
        <div
            class="th-classroom-switcher__compact"
            title="{{ __('No class assigned') }}"
            aria-hidden="true"
        >🏫</div>
    @elseif ($teacherClassrooms->count() === 1)
        <div class="th-classroom-switcher__label">{{ __('Your class') }}</div>
        <div class="th-classroom-switcher__name">{{ $teacherClassrooms->first()->name }}</div>
        <div
            class="th-classroom-switcher__compact"
            title="{{ $teacherClassrooms->first()->name }}"
            aria-hidden="true"
        >🏫</div>
    @else
        <form method="POST" action="{{ route('teacher.classroom.switch') }}">
            @csrf
            <label for="teacher-class-switch" class="th-classroom-switcher__label">{{ __('Active class') }}</label>
            <select
                id="teacher-class-switch"
                name="classroom_id"
                class="th-classroom-switcher__select"
                onchange="this.form.submit()"
            >
                @foreach ($teacherClassrooms as $room)
                    <option value="{{ $room->id }}" @selected((int) $teacherActiveClassroomId === (int) $room->id)>
                        {{ $room->name }}
                    </option>
                @endforeach
            </select>
        </form>
        @php
            $activeName = $teacherClassrooms->firstWhere('id', $teacherActiveClassroomId)?->name ?? $teacherClassrooms->first()->name;
        @endphp
        <div
            class="th-classroom-switcher__compact"
            title="{{ $activeName }}"
            aria-hidden="true"
        >🏫</div>
    @endif
</div>
