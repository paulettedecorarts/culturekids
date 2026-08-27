<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Support\EmojiSkinToneFixer;
use Illuminate\Support\Facades\DB;

$targets = [
    ['table' => 'activity_flashcard_slides', 'column' => 'emoji'],
    ['table' => 'language_activity_words', 'column' => 'emoji'],
    ['table' => 'age_profiles', 'column' => 'icon_emoji'],
    ['table' => 'modules', 'column' => 'icon'],
];

foreach ($targets as $t) {
    $rows = DB::table($t['table'])->select('id', $t['column'])->get();
    $updated = 0;
    foreach ($rows as $row) {
        $original = $row->{$t['column']};
        $fixed = EmojiSkinToneFixer::fix($original);
        if ($fixed !== $original) {
            DB::table($t['table'])->where('id', $row->id)->update([$t['column'] => $fixed]);
            $updated++;
        }
    }
    echo "{$t['table']}.{$t['column']}: updated {$updated} of {$rows->count()} rows\n";
}

echo "Done.\n";
