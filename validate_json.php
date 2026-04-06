<?php
$content = file_get_contents('database/data/heritage_data.json');
$result = json_decode($content);
if ($result === null) {
    echo "ERROR: " . json_last_error_msg();
} else {
    echo "SUCCESS: Valid JSON";
}
