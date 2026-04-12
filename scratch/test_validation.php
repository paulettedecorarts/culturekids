<?php
use Illuminate\Support\Facades\Validator;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$data = [
    'content_kind' => 'activity',
    'selected_comic_id' => null,
    'selected_activity_id' => null,
    'form_scheduled_on' => '2026-04-12',
];

$rules = [
    'content_kind' => 'required|in:comic,activity',
    'selected_comic_id' => 'required_if:content_kind,comic|nullable|exists:comics,id',
    'selected_activity_id' => 'required_if:content_kind,activity|nullable|exists:activities,id',
    'form_scheduled_on' => 'required|date',
];

$validator = Validator::make($data, $rules);

if ($validator->fails()) {
    echo "Validation FAILED\n";
    print_r($validator->errors()->toArray());
} else {
    echo "Validation PASSED\n";
}
