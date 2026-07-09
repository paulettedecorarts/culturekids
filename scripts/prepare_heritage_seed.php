<?php

/**
 * Prepare heritage seed assets and JSON for db:seed.
 *
 * Run: php scripts/prepare_heritage_seed.php
 */

declare(strict_types=1);

$base = dirname(__DIR__);

require $base.'/vendor/autoload.php';

$steps = [
    'extract_heritage_tribe_images.php',
    'build_activities_seed_json.php',
];

foreach ($steps as $step) {
    $script = $base.'/scripts/'.$step;
    echo "→ {$step}\n";
    passthru(PHP_BINARY.' '.escapeshellarg($script), $code);

    if ($code !== 0) {
        exit($code);
    }
}

echo "Heritage seed files are ready.\n";
