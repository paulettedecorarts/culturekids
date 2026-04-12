<?php
use App\Models\User;
use App\Models\Organisation;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = User::find(11);
if ($user && $user->organisation) {
    $org = $user->organisation;
    echo "User 11 Org: {$org->name} (ID: {$org->id})\n";
    echo "Allowed Tribe IDs: " . json_encode($org->restrictedTribeIds()) . "\n";
} else {
    echo "User 11 has no org\n";
}
