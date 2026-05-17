<div>
    <div class="cms-header">
        <div>
            <h1 class="cms-page-title">Classrooms</h1>
            <div class="cms-breadcrumb">Rosters · Your organisation</div>
        </div>
        <div style="display:flex; align-items:center; gap:12px; flex-wrap:wrap; justify-content:flex-end">
            @if ($organization)
                <button type="button" class="btn btn-primary btn-sm" wire:click="openCreateModal">Add classroom</button>
            @endif
            <a href="{{ route('cms.admin.people') }}" wire:navigate class="btn btn-ghost btn-sm" style="text-decoration:none">Teachers &amp; children</a>
            <a href="{{ route('cms.admin.organizations') }}" wire:navigate class="btn btn-ghost btn-sm" style="text-decoration:none">Organization profile</a>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="cms-flash-success">
            {{ session('message') }}
        </div>
    @endif

    @if (! $organization)
        <div style="padding:24px; background:var(--cms-surface); border:1px solid var(--cms-border); border-radius:var(--r-xl); color:var(--cms-text-muted); font-weight:600">
            Your account is not linked to an organisation. Contact support.
        </div>
    @else
        @if ($teachers->isEmpty() && $classrooms->isEmpty())
            <div style="margin-bottom:16px; padding:12px 16px; border:1px solid #FDE68A; background:#FFFBEB; border-radius:12px; font-size:13px; color:#92400E; font-weight:600">
                Invite at least one <strong>teacher</strong> under <a href="{{ route('cms.admin.people') }}" wire:navigate style="color:#92400E; text-decoration:underline">Teachers &amp; children</a> before assigning classes.
            </div>
        @endif

        <div class="cms-asset-table">
            <div class="cms-table-header" style="grid-template-columns:1fr 1fr minmax(80px, auto) auto;">
                <span>Class</span>
                <span>Teacher</span>
                <span style="text-align:center">Children</span>
                <span style="text-align:right">Actions</span>
            </div>
            @forelse($classrooms as $room)
                <div class="cms-table-row" style="grid-template-columns:1fr 1fr minmax(80px, auto) auto; cursor:default; align-items:start">
                    <div>
                        <div class="cms-asset-name">{{ $room->name }}</div>
                        @if ($room->description)
                            <div class="cms-asset-sub" style="white-space:pre-wrap; margin-top:4px">{{ \Illuminate\Support\Str::limit($room->description, 120) }}</div>
                        @endif
                    </div>
                    <div style="font-size:14px; font-weight:600; color:var(--cms-text-muted)">
                        {{ $room->teacher?->name ?? '—' }}
                    </div>
                    <div style="text-align:center; font-size:14px; font-weight:700; color:var(--cms-text)">
                        {{ $room->children->count() }}
                    </div>
                    <div style="display:flex; flex-wrap:wrap; gap:8px; justify-content:flex-end">
                        <button type="button" class="btn btn-ghost btn-sm" wire:click="openEditModal({{ $room->id }})">Edit</button>
                        <button type="button" class="btn btn-ghost btn-sm" wire:click="openStudentsModal({{ $room->id }})">Children</button>
                        <button
                            type="button"
                            class="btn btn-ghost btn-sm"
                            style="color:var(--clay-red)"
                            wire:click="deleteClassroom({{ $room->id }})"
                            wire:confirm="{{ __('Remove this class? Class rosters will be cleared.') }}"
                        >Remove</button>
                    </div>
                </div>
            @empty
                <div style="padding:32px; text-align:center; color:var(--cms-text-muted); font-weight:600">No classrooms yet. Create one to assign a teacher and add children.</div>
            @endforelse
        </div>
    @endif

    @if ($organization && $showClassModal)
        <div
            wire:click="closeClassModal"
            class="cms-modal-backdrop" style="position:fixed; inset:0; backdrop-filter:blur(6px); z-index:1000; display:flex; align-items:center; justify-content:center; padding:24px;"
            role="presentation"
        >
            <div
                onclick="event.stopPropagation()"
                class="cms-modal-panel" style="max-width:560px; border-radius:var(--r-xl); border:1px solid var(--cms-border); box-shadow:0 24px 64px rgba(26,18,8,.18); max-height:90vh; display:flex; flex-direction:column; overflow:hidden;"
                role="dialog"
                aria-modal="true"
                aria-labelledby="class-modal-title"
            >
                <div style="padding:var(--sp-6); border-bottom:1px solid var(--cms-border); display:flex; align-items:flex-start; justify-content:space-between; gap:var(--sp-4); flex-shrink:0">
                    <div>
                        <h2 id="class-modal-title" style="font-family:var(--font-display); font-size:22px; font-weight:800; color:var(--cms-text); margin-bottom:4px">
                            {{ $editingClassroomId ? __('Edit classroom') : __('New classroom') }}
                        </h2>
                        <div style="font-size:12px; color:var(--cms-text-muted); font-weight:600">
                            {{ __('Classes belong to :org. You can assign a teacher and add children to the roster next.', ['org' => $organization->name]) }}
                        </div>
                    </div>
                    <button
                        type="button"
                        wire:click="closeClassModal"
                        style="flex-shrink:0; width:40px; height:40px; border-radius:12px; border:1px solid var(--cms-border); background:var(--cms-surface); color:var(--cms-text); font-size:20px; line-height:1; cursor:pointer; font-weight:700"
                    >×</button>
                </div>
                <form wire:submit.prevent="saveClassroom" style="padding:var(--sp-6); overflow-y:auto; display:flex; flex-direction:column; gap:var(--sp-4)">
                    <div>
                        <label style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; color:var(--cms-text-muted); margin-bottom:6px">{{ __('Name') }}</label>
                        <input type="text" wire:model="formName" autocomplete="off" style="width:100%; padding:10px 12px; border-radius:10px; border:1px solid var(--cms-border); font-family:var(--font-admin); font-size:15px">
                        @error('formName') <div style="color:var(--clay-red); font-size:12px; margin-top:6px; font-weight:600">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; color:var(--cms-text-muted); margin-bottom:6px">{{ __('Description') }} <span style="font-weight:500">({{ __('optional') }})</span></label>
                        <textarea wire:model="formDescription" rows="3" style="width:100%; padding:10px 12px; border-radius:10px; border:1px solid var(--cms-border); font-family:var(--font-admin); font-size:15px; resize:vertical"></textarea>
                        @error('formDescription') <div style="color:var(--clay-red); font-size:12px; margin-top:6px; font-weight:600">{{ $message }}</div> @enderror
                    </div>
                    <div>
                        <label style="display:block; font-size:11px; font-weight:700; text-transform:uppercase; color:var(--cms-text-muted); margin-bottom:6px">{{ __('Class teacher') }}</label>
                        <select
                            wire:model.live="formTeacherId"
                            style="width:100%; padding:10px 12px; border-radius:10px; border:1px solid var(--cms-border); font-family:var(--font-admin); font-size:15px; background:var(--cms-surface)"
                        >
                            <option value="">{{ __('— Not assigned —') }}</option>
                            @foreach($teachers as $t)
                                <option value="{{ $t->id }}">{{ $t->name }}</option>
                            @endforeach
                        </select>
                        @error('formTeacherId') <div style="color:var(--clay-red); font-size:12px; margin-top:6px; font-weight:600">{{ $message }}</div> @enderror
                        @if($teachers->isEmpty())
                            <div style="margin-top:8px; font-size:12px; color:var(--cms-text-muted); font-weight:600">{{ __('No teachers in your organisation yet.') }}</div>
                        @endif
                    </div>
                    <div style="display:flex; justify-content:flex-end; gap:12px; margin-top:var(--sp-2); padding-top:var(--sp-4); border-top:1px solid var(--cms-border)">
                        <button type="button" class="btn btn-ghost btn-sm" wire:click="closeClassModal">{{ __('Cancel') }}</button>
                        <button type="submit" class="btn btn-primary btn-sm" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="saveClassroom">{{ $editingClassroomId ? __('Save') : __('Create') }}</span>
                            <span wire:loading wire:target="saveClassroom">{{ __('Saving…') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if ($organization && $showStudentsModal && $studentsClassroomId)
        @php($room = $classrooms->firstWhere('id', $studentsClassroomId))
        <div
            wire:click="closeStudentsModal"
            class="cms-modal-backdrop" style="position:fixed; inset:0; backdrop-filter:blur(6px); z-index:1000; display:flex; align-items:center; justify-content:center; padding:24px;"
            role="presentation"
        >
            <div
                onclick="event.stopPropagation()"
                class="cms-modal-panel" style="max-width:520px; border-radius:var(--r-xl); border:1px solid var(--cms-border); box-shadow:0 24px 64px rgba(26,18,8,.18); max-height:90vh; display:flex; flex-direction:column; overflow:hidden;"
                role="dialog"
                aria-modal="true"
                aria-labelledby="students-modal-title"
            >
                <div style="padding:var(--sp-6); border-bottom:1px solid var(--cms-border); display:flex; align-items:flex-start; justify-content:space-between; gap:var(--sp-4); flex-shrink:0">
                    <div>
                        <h2 id="students-modal-title" style="font-family:var(--font-display); font-size:22px; font-weight:800; color:var(--cms-text); margin-bottom:4px">{{ __('Children in class') }}</h2>
                        <div style="font-size:12px; color:var(--cms-text-muted); font-weight:600">
                            {{ $room?->name ?? '' }}
                        </div>
                    </div>
                    <button
                        type="button"
                        wire:click="closeStudentsModal"
                        style="flex-shrink:0; width:40px; height:40px; border-radius:12px; border:1px solid var(--cms-border); background:var(--cms-surface); color:var(--cms-text); font-size:20px; line-height:1; cursor:pointer; font-weight:700"
                    >×</button>
                </div>
                <div style="padding:var(--sp-6); overflow-y:auto; display:flex; flex-direction:column; gap:var(--sp-3)">
                    @if ($orgChildren->isEmpty())
                        <div style="font-size:13px; color:var(--cms-text-muted); font-weight:600">
                            {{ __('No child accounts in your organisation yet. Invite them from') }}
                            <a href="{{ route('cms.admin.people') }}" wire:navigate style="color:var(--cms-text); text-decoration:underline">{{ __('Teachers & children') }}</a>.
                        </div>
                    @else
                        <p style="font-size:13px; color:var(--cms-text-muted); font-weight:600; margin:0">{{ __('Select which children are in this class.') }}</p>
                        <div style="display:flex; flex-direction:column; gap:10px; max-height:min(50vh, 360px); overflow-y:auto; padding-right:4px">
                            @foreach($orgChildren as $stu)
                                <label style="display:flex; align-items:center; gap:12px; padding:10px 12px; border-radius:10px; border:1px solid var(--cms-border); cursor:pointer; font-size:14px; font-weight:600; color:var(--cms-text)">
                                    <input type="checkbox" wire:model="selectedStudentIds" value="{{ $stu->id }}" style="width:18px; height:18px">
                                    <span>{{ $stu->name }} <span style="font-weight:500; color:var(--cms-text-muted)">({{ $stu->email }})</span></span>
                                </label>
                            @endforeach
                        </div>
                        @error('selectedStudentIds') <div style="color:var(--clay-red); font-size:12px; font-weight:600">{{ $message }}</div> @enderror
                        @error('selectedStudentIds.*') <div style="color:var(--clay-red); font-size:12px; font-weight:600">{{ $message }}</div> @enderror
                    @endif
                </div>
                <div style="padding:var(--sp-6); padding-top:0; display:flex; justify-content:flex-end; gap:12px; border-top:1px solid var(--cms-border); margin-top:auto; flex-shrink:0">
                    <button type="button" class="btn btn-ghost btn-sm" wire:click="closeStudentsModal">{{ __('Cancel') }}</button>
                    <button
                        type="button"
                        class="btn btn-primary btn-sm"
                        wire:click="saveStudents"
                        wire:loading.attr="disabled"
                        @if($orgChildren->isEmpty()) disabled @endif
                    >
                        <span wire:loading.remove wire:target="saveStudents">{{ __('Save roster') }}</span>
                        <span wire:loading wire:target="saveStudents">{{ __('Saving…') }}</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
