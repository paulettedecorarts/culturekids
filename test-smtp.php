<?php

echo "Testing SMTP connection to Mailtrap...\n\n";

$host = 'sandbox.smtp.mailtrap.io';
$port = 2525;
$username = 'e21f6a3ad8b86d';
$password = 'cc91843552d19a';

echo "Host: $host\n";
echo "Port: $port\n\n";

// Test basic socket connection
echo "1. Testing socket connection...\n";
$startTime = microtime(true);
$socket = @fsockopen($host, $port, $errno, $errstr, 10);
$endTime = microtime(true);

if ($socket) {
    echo "✓ Socket connected in " . round(($endTime - $startTime) * 1000) . "ms\n";
    
    // Read server greeting
    $response = fgets($socket, 512);
    echo "Server greeting: " . trim($response) . "\n\n";
    
    // Try EHLO
    echo "2. Sending EHLO...\n";
    fwrite($socket, "EHLO localhost\r\n");
    $response = '';
    while ($line = fgets($socket, 512)) {
        $response .= $line;
        if (substr($line, 3, 1) == ' ') break;
    }
    echo "EHLO response:\n" . $response . "\n";
    
    // Try AUTH LOGIN
    echo "3. Testing AUTH LOGIN...\n";
    fwrite($socket, "AUTH LOGIN\r\n");
    $response = fgets($socket, 512);
    echo "AUTH response: " . trim($response) . "\n";
    
    if (strpos($response, '334') !== false) {
        // Send username
        fwrite($socket, base64_encode($username) . "\r\n");
        $response = fgets($socket, 512);
        echo "Username response: " . trim($response) . "\n";
        
        // Send password
        fwrite($socket, base64_encode($password) . "\r\n");
        $response = fgets($socket, 512);
        echo "Password response: " . trim($response) . "\n";
        
        if (strpos($response, '235') !== false) {
            echo "\n✓ Authentication successful!\n";
        } else {
            echo "\n✗ Authentication failed!\n";
        }
    }
    
    fwrite($socket, "QUIT\r\n");
    fclose($socket);
} else {
    echo "✗ Socket connection failed: $errstr ($errno)\n";
    echo "Connection time: " . round(($endTime - $startTime) * 1000) . "ms\n";
}

echo "\n4. Testing with stream_socket_client (what Laravel uses)...\n";
$startTime = microtime(true);
$context = stream_context_create([
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
    ]
]);

$stream = @stream_socket_client(
    "tcp://$host:$port",
    $errno,
    $errstr,
    10,
    STREAM_CLIENT_CONNECT,
    $context
);
$endTime = microtime(true);

if ($stream) {
    echo "✓ Stream connected in " . round(($endTime - $startTime) * 1000) . "ms\n";
    stream_set_timeout($stream, 10);
    $response = fgets($stream);
    echo "Server greeting: " . trim($response) . "\n";
    fclose($stream);
} else {
    echo "✗ Stream connection failed: $errstr ($errno)\n";
    echo "Connection time: " . round(($endTime - $startTime) * 1000) . "ms\n";
}

echo "\nDone.\n";
