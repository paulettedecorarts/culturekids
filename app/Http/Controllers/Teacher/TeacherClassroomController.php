<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Support\TeacherActiveClassroom;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TeacherClassroomController extends Controller
{
    public function switch(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'classroom_id' => ['required', 'integer', 'min:1'],
        ]);

        $user = $request->user();
        if ($user) {
            TeacherActiveClassroom::setActiveClassroomId($user, (int) $validated['classroom_id']);
        }

        return redirect()->back();
    }
}
