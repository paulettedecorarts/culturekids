<?php
/**
 * Intent: The user requested to copy the JS file to a JSON file.
 * This script safely reads the Javascript array from docs/heritage_data.js,
 * fixes the unquoted object keys (like converting `id: ` to `"id": `) so it becomes valid JSON,
 * and saves it directly to database/data/heritage_data.json.
 */

$jsFile = __DIR__ . '/../docs/heritage_data.js';
$jsonFile = __DIR__ . '/database/data/heritage_data.json';

// Ensure data directory exists
if (!is_dir(dirname($jsonFile))) {
    mkdir(dirname($jsonFile), 0777, true);
}

// Read the Javascript file
$content = file_get_contents($jsFile);

// Extract just the TRIBES array (between 'const TRIBES = [' and '];')
$startPos = strpos($content, 'const TRIBES = [');
$startPos = strpos($content, '[', $startPos);
$endPos = strpos($content, '];', $startPos); // Find the closing bracket of the array
$arrayContent = substr($content, $startPos, $endPos - $startPos + 1);

// Regex replacement to convert JS object keys into valid JSON keys
// Specifically targeting unquoted alphanumeric keys followed by a colon. e.g.  title: -> "title":
$jsonContent = preg_replace('/([{,]\s*)([a-zA-Z0-9_]+)\s*:/', '$1"$2":', $arrayContent);

// Optional formatting cleanup: trailing commas inside objects/arrays are invalid in JSON
$jsonContent = preg_replace('/,\s*}/', '}', $jsonContent);
$jsonContent = preg_replace('/,\s*]/', ']', $jsonContent);

// Save the valid JSON string to the new file
file_put_contents($jsonFile, $jsonContent);

echo "Successfully converted heritage_data.js into a valid JSON file at: {$jsonFile}\n";
