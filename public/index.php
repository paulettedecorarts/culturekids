<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Coolify/Docker only show stdout/stderr — log upload hits before Laravel boots.
$__uri = $_SERVER['REQUEST_URI'] ?? '';
if (str_contains($__uri, 'livewire/upload-file')) {
    $cl = $_SERVER['CONTENT_LENGTH'] ?? '?';
    $method = $_SERVER['REQUEST_METHOD'] ?? '?';
    file_put_contents(
        'php://stderr',
        "[culturekids] index.php {$method} {$__uri} content_length={$cl}\n",
        FILE_APPEND
    );
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
