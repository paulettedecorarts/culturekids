<?php

namespace App\Services\Push;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExpoPushGateway implements PushGateway
{
    public function send(array $tokens, string $title, string $body, array $data = []): void
    {
        $expoTokens = array_values(array_filter($tokens, fn (string $t) => str_starts_with($t, 'ExponentPushToken[')));

        if ($expoTokens === []) {
            return;
        }

        $messages = array_map(fn (string $token) => [
            'to' => $token,
            'title' => $title,
            'body' => $body,
            'data' => $data,
            'sound' => 'default',
            'priority' => 'high',
        ], $expoTokens);

        $response = Http::timeout(15)
            ->acceptJson()
            ->post('https://exp.host/--/api/v2/push/send', $messages);

        if ($response->failed()) {
            Log::warning('Expo push send failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return;
        }

        $payload = $response->json('data', []);
        foreach ($payload as $ticket) {
            if (($ticket['status'] ?? null) === 'error') {
                Log::warning('Expo push ticket error', $ticket);
            }
        }
    }
}
