<?php

/**
 * Build seed/activities.seed.json from seed/activities.seed.js
 * and attach tribe icon paths when seed/assets/tribes/{id}.jpg exists.
 *
 * Run: php scripts/build_activities_seed_json.php
 */

declare(strict_types=1);

$base = dirname(__DIR__);
$script = $base.'/scripts/build_activities_seed_json.mjs';

if (! is_file($base.'/seed/activities.seed.js')) {
    fwrite(STDERR, "activities.seed.js not found\n");
    exit(1);
}

chdir($base);
passthru('node '.escapeshellarg($script), $code);

exit($code);
