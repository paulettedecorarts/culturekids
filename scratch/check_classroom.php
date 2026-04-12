<?php
use App\Models\Classroom;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$c = Classroom::find(1);
if ($c) {
    echo "ID 1: {$c->name}\n";
} else {
    echo "ID 1 not found\n";
}
