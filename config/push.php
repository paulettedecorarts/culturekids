<?php

return [
    'provider' => env('PUSH_PROVIDER', 'log'), // log|fcm

    'fcm' => [
        'url' => env('PUSH_FCM_URL', 'https://fcm.googleapis.com/fcm/send'),
        'server_key' => env('PUSH_FCM_SERVER_KEY'),
    ],
];
