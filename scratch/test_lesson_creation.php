<?php
use App\Models\User;
use App\Models\Classroom;
use App\Models\Activity;
use App\Models\LessonPlan;
use App\Models\Organisation;
use Carbon\Carbon;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Find a classroom and its teacher
$classroom = Classroom::whereNotNull('teacher_id')->first();
if (!$classroom) {
    echo "No classroom with teacher found\n";
    exit;
}

$user = User::find($classroom->teacher_id);
if (!$user) {
    echo "Teacher not found for classroom {$classroom->id}\n";
    exit;
}

$activity = App\Support\TeacherPrintScope::activitiesQueryFor($user)->first();
if (!$activity) {
    echo "No scoped activity found for user {$user->id}\n";
    exit;
}

echo "Using User ID: {$user->id}\n";
echo "Using Classroom ID: {$classroom->id}\n";
echo "Using Activity ID: {$activity->id}\n";

// Check scope
$scopeMatch = App\Support\TeacherPrintScope::activitiesQueryFor($user)->whereKey($activity->id)->exists();
echo "Scope Check for Activity: " . ($scopeMatch ? "PASS" : "FAIL") . "\n";

try {
    $lesson = LessonPlan::create([
        'classroom_id' => $classroom->id,
        'lessonable_id' => $activity->id,
        'lessonable_type' => Activity::class,
        'scheduled_on' => Carbon::today()->toDateString(),
        'status' => 'planned',
        'sort_order' => 1,
        'created_by' => $user->id,
    ]);
    echo "Successfully created lesson plan ID: {$lesson->id}\n";
} catch (\Exception $e) {
    echo "Failed to create lesson plan: " . $e->getMessage() . "\n";
}
