<?php

namespace App\Support;

use App\Models\Classroom;
use App\Models\User;
use Illuminate\Support\Collection;

class TeacherActiveClassroom
{
    public const SESSION_KEY = 'teacher_active_classroom_id';

    /**
     * @return Collection<int, Classroom>
     */
    public static function teachingClassroomsFor(User $teacher): Collection
    {
        if (! $teacher->organisation_id) {
            return collect();
        }

        return Classroom::query()
            ->where('organisation_id', $teacher->organisation_id)
            ->where('teacher_id', $teacher->id)
            ->orderBy('name')
            ->get();
    }

    public static function activeClassroom(User $teacher): ?Classroom
    {
        $classrooms = self::teachingClassroomsFor($teacher);
        if ($classrooms->isEmpty()) {
            session()->forget(self::SESSION_KEY);

            return null;
        }

        $requestedId = (int) session(self::SESSION_KEY, 0);
        if ($requestedId && $classrooms->contains('id', $requestedId)) {
            return $classrooms->firstWhere('id', $requestedId);
        }

        $first = $classrooms->first();
        session([self::SESSION_KEY => $first->id]);

        return $first;
    }

    public static function setActiveClassroomId(User $teacher, int $classroomId): void
    {
        $allowed = self::teachingClassroomsFor($teacher)->contains('id', $classroomId);
        if ($allowed) {
            session([self::SESSION_KEY => $classroomId]);
        }
    }
}
