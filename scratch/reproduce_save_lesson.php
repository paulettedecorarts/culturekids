<?php
use App\Models\User;
use App\Models\Classroom;
use App\Models\Activity;
use App\Models\LessonPlan;
use App\Support\TeacherPrintScope;
use Carbon\Carbon;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Setup data
$classroom = Classroom::whereNotNull('teacher_id')->first();
$user = User::find($classroom->teacher_id);
$activity = TeacherPrintScope::activitiesQueryFor($user)->first();

if (!$activity) {
    echo "No activity found for user\n";
    exit;
}

echo "Testing saveLesson logic for Activity ID: {$activity->id}\n";

// Emulate saveLesson logic
$lessonable = null;
$content_kind = 'activity';
$selected_activity_id = $activity->id;
$form_scheduled_on = Carbon::today()->toDateString();
$form_notes = 'Test note';

if ($content_kind === 'comic') {
    // ...
} else {
    $activity_fetched = Activity::query()->findOrFail((int) $selected_activity_id);
    if (! TeacherPrintScope::activitiesQueryFor($user)->whereKey($activity_fetched->id)->exists()) {
        echo "SCOPE CHECK FAILED\n";
        exit;
    }
    $lessonable = $activity_fetched;
}

$maxOrder = (int) LessonPlan::query()
    ->where('classroom_id', $classroom->id)
    ->whereDate('scheduled_on', $form_scheduled_on)
    ->max('sort_order');

$lesson = LessonPlan::query()->create([
    'classroom_id' => $classroom->id,
    'lessonable_id' => $lessonable->getKey(),
    'lessonable_type' => $lessonable::class,
    'scheduled_on' => $form_scheduled_on,
    'status' => 'planned',
    'sort_order' => $maxOrder + 1,
    'notes' => $form_notes,
    'created_by' => $user->id,
]);

echo "Successfully created lesson ID: {$lesson->id}\n";
