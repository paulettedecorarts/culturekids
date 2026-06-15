<?php

return [
    'provider' => env('PUSH_PROVIDER', 'expo'), // log|expo|fcm|composite

    'fcm' => [
        'url' => env('PUSH_FCM_URL', 'https://fcm.googleapis.com/fcm/send'),
        'server_key' => env('PUSH_FCM_SERVER_KEY'),
    ],
];
