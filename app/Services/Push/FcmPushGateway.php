<?php

namespace App\Services\Push;

use Illuminate\Support\Facades\Http;

class FcmPushGateway implements PushGateway
{
    public function send(array $tokens, string $title, string $body, array $data = []): void
    {
        $serverKey = (string) config('push.fcm.server_key');
        if ($serverKey === '') {
            return;
        }

        $url = (string) config('push.fcm.url', 'https://fcm.googleapis.com/fcm/send');

        foreach ($tokens as $token) {
            Http::timeout(10)
                ->withHeaders([
                    'Authorization' => 'key='.$serverKey,
                    'Content-Type' => 'application/json',
                ])
                ->post($url, [
                    'to' => $token,
                    'notification' => [
                        'title' => $title,
                        'body' => $body,
                    ],
                    'data' => $data,
                    'priority' => 'high',
                ])
                ->throw();
        }
    }
}
