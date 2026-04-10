<?php

namespace App\Livewire\CMS;

use App\Models\AuditLog;
use App\Models\Classroom;
use App\Models\Organisation;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.cms')]
class OrgClassroomsManager extends Component
{
    public bool $showClassModal = false;

    public ?int $editingClassroomId = null;

    public string $formName = '';

    public string $formDescription = '';

    /** @var int|string|null */
    public $formTeacherId = null;

    public bool $showStudentsModal = false;

    public ?int $studentsClassroomId = null;

    /** @var array<int|string> */
    public array $selectedStudentIds = [];

    public function openCreateModal(): void
    {
        $this->editingClassroomId = null;
        $this->formName = '';
        $this->formDescription = '';
        $this->formTeacherId = null;
        $this->resetValidation();
        $this->showClassModal = true;
    }

    public function openEditModal(int $classroomId): void
    {
        $classroom = $this->findOrgClassroom($classroomId);
        if (! $classroom) {
            return;
        }

        $this->editingClassroomId = $classroom->id;
        $this->formName = $classroom->name;
        $this->formDescription = (string) ($classroom->description ?? '');
        $this->formTeacherId = $classroom->teacher_id;
        $this->resetValidation();
        $this->showClassModal = true;
    }

    public function closeClassModal(): void
    {
        $this->showClassModal = false;
        $this->editingClassroomId = null;
        $this->resetValidation();
    }

    public function saveClassroom(): void
    {
        $org = auth()->user()?->organisation;
        if (! $org instanceof Organisation) {
            abort(403);
        }

        if ($this->formTeacherId === '' || $this->formTeacherId === null) {
            $this->formTeacherId = null;
        } else {
            $this->formTeacherId = (int) $this->formTeacherId;
        }

        $teacherRule = ['nullable', 'integer', 'exists:users,id'];

        $this->validate([
            'formName' => ['required', 'string', 'max:160'],
            'formDescription' => ['nullable', 'string', 'max:2000'],
            'formTeacherId' => $teacherRule,
        ], [], [
            'formName' => 'name',
            'formDescription' => 'description',
            'formTeacherId' => 'teacher',
        ]);

        if ($this->formTeacherId !== null) {
            if (! $this->teacherBelongsToOrg($org->id, $this->formTeacherId)) {
                $this->addError('formTeacherId', __('Choose a teacher from your organisation.'));

                return;
            }
        }

        if ($this->editingClassroomId) {
            $classroom = $this->findOrgClassroom($this->editingClassroomId);
            if (! $classroom) {
                return;
            }
            $classroom->update([
                'name' => $this->formName,
                'description' => $this->formDescription !== '' ? $this->formDescription : null,
                'teacher_id' => $this->formTeacherId,
            ]);
            AuditLog::record('CLASSROOM_UPDATE', "organisations/{$org->id}/classrooms/{$classroom->id}", [
                'name' => $classroom->name,
            ]);
            session()->flash('message', __('Class updated.'));
        } else {
            $classroom = Classroom::create([
                'organisation_id' => $org->id,
                'name' => $this->formName,
                'description' => $this->formDescription !== '' ? $this->formDescription : null,
                'teacher_id' => $this->formTeacherId,
            ]);
            AuditLog::record('CLASSROOM_CREATE', "organisations/{$org->id}/classrooms/{$classroom->id}", [
                'name' => $classroom->name,
            ]);
            session()->flash('message', __('Class created.'));
        }

        $this->closeClassModal();
    }

    public function deleteClassroom(int $classroomId): void
    {
        $org = auth()->user()?->organisation;
        if (! $org) {
            abort(403);
        }

        $classroom = $this->findOrgClassroom($classroomId);
        if (! $classroom) {
            return;
        }

        AuditLog::record('CLASSROOM_DELETE', "organisations/{$org->id}/classrooms/{$classroom->id}", [
            'name' => $classroom->name,
        ]);

        $classroom->delete();

        if ($this->studentsClassroomId === $classroomId) {
            $this->closeStudentsModal();
        }

        session()->flash('message', __('Class removed.'));
    }

    public function openStudentsModal(int $classroomId): void
    {
        $classroom = $this->findOrgClassroom($classroomId);
        if (! $classroom) {
            return;
        }

        $this->studentsClassroomId = $classroom->id;
        $this->selectedStudentIds = $classroom->children->pluck('id')->map(fn ($id) => (int) $id)->values()->all();
        $this->resetValidation();
        $this->showStudentsModal = true;
    }

    public function closeStudentsModal(): void
    {
        $this->showStudentsModal = false;
        $this->studentsClassroomId = null;
        $this->selectedStudentIds = [];
        $this->resetValidation();
    }

    public function saveStudents(): void
    {
        $org = auth()->user()?->organisation;
        if (! $org) {
            abort(403);
        }

        $classroom = $this->studentsClassroomId
            ? $this->findOrgClassroom($this->studentsClassroomId)
            : null;

        if (! $classroom) {
            return;
        }

        $this->validate([
            'selectedStudentIds' => ['array'],
            'selectedStudentIds.*' => ['integer', 'exists:users,id'],
        ]);

        $ids = array_map('intval', $this->selectedStudentIds);
        $allowed = User::query()
            ->where('organisation_id', $org->id)
            ->whereHas('roles', fn ($q) => $q->where('name', 'child'))
            ->whereIn('id', $ids)
            ->pluck('id')
            ->all();

        $classroom->children()->sync($allowed);

        AuditLog::record('CLASSROOM_SYNC_STUDENTS', "organisations/{$org->id}/classrooms/{$classroom->id}", [
            'child_count' => count($allowed),
        ]);

        $this->closeStudentsModal();
        session()->flash('message', __('Class roster updated.'));
    }

    protected function findOrgClassroom(int $id): ?Classroom
    {
        $orgId = auth()->user()?->organisation_id;
        if (! $orgId) {
            return null;
        }

        return Classroom::query()
            ->where('organisation_id', $orgId)
            ->whereKey($id)
            ->first();
    }

    protected function teacherBelongsToOrg(int $organisationId, int $teacherUserId): bool
    {
        return User::query()
            ->where('organisation_id', $organisationId)
            ->whereKey($teacherUserId)
            ->whereHas('roles', fn ($q) => $q->where('name', 'teacher'))
            ->exists();
    }

    public function render()
    {
        $org = auth()->user()?->organisation;

        $classrooms = collect();
        $teachers = collect();
        $orgChildren = collect();

        if ($org) {
            $classrooms = Classroom::query()
                ->where('organisation_id', $org->id)
                ->with(['teacher', 'children'])
                ->orderBy('name')
                ->get();

            $teachers = User::query()
                ->where('organisation_id', $org->id)
                ->whereHas('roles', fn ($q) => $q->where('name', 'teacher'))
                ->orderBy('name')
                ->get();

            $orgChildren = User::query()
                ->where('organisation_id', $org->id)
                ->whereHas('roles', fn ($q) => $q->where('name', 'child'))
                ->orderBy('name')
                ->get();
        }

        return view('livewire.cms.org-classrooms-manager', [
            'organization' => $org,
            'classrooms' => $classrooms,
            'teachers' => $teachers,
            'orgChildren' => $orgChildren,
        ]);
    }
}
