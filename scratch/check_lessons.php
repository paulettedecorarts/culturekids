<?php
use App\Models\LessonPlan;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$lessons = LessonPlan::latest()->limit(5)->get();
foreach ($lessons as $l) {
    $lessonable = $l->lessonable;
    $title = $lessonable ? $lessonable->title : 'NULL';
    echo "ID: {$l->id}, Type: {$l->lessonable_type}, ID: {$l->lessonable_id}, Title: {$title}\n";
}
