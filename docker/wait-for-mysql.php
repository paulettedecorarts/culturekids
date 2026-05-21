<?php

$host = getenv('DB_HOST') ?: 'mysql';
$port = getenv('DB_PORT') ?: '3306';
$database = getenv('DB_DATABASE') ?: 'paulette';
$user = getenv('DB_USERNAME') ?: 'paulette';
$password = getenv('DB_PASSWORD');

if ($password === false || $password === '') {
    fwrite(STDERR, "DB_PASSWORD is not set\n");
    exit(1);
}

$dsn = sprintf('mysql:host=%s;port=%s;dbname=%s', $host, $port, $database);
$maxAttempts = (int) (getenv('DB_WAIT_ATTEMPTS') ?: 90);
$sleepSeconds = (int) (getenv('DB_WAIT_SLEEP') ?: 2);

echo "Waiting for MySQL at {$host}:{$port} (database: {$database}, user: {$user})...\n";

for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
    try {
        $pdo = new PDO($dsn, $user, $password, [
            PDO::ATTR_TIMEOUT => 5,
        ]);
        $pdo->query('SELECT 1');
        echo "      ✅ MySQL is ready.\n";
        exit(0);
    } catch (Throwable $e) {
        if ($attempt % 5 === 0) {
            echo "      … attempt {$attempt}/{$maxAttempts}: {$e->getMessage()}\n";
        }
        sleep($sleepSeconds);
    }
}

echo "      ❌ MySQL not ready after {$maxAttempts} attempts.\n";
exit(1);
