<?php
use App\Models\Activity;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$a = Activity::where('title', 'like', '%weather%')->first();
if ($a) {
    echo "ID: {$a->id}, Title: {$a->title}, Type: {$a->type}, Tribe: {$a->tribe_id}\n";
} else {
    echo "Activity 'weather' not found\n";
}
