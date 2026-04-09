<?php

namespace App\Services\Push;

use Illuminate\Support\Facades\Log;

class LogPushGateway implements PushGateway
{
    public function send(array $tokens, string $title, string $body, array $data = []): void
    {
        Log::info('Push notification (log provider)', [
            'tokens_count' => count($tokens),
            'title' => $title,
            'body' => $body,
            'data' => $data,
        ]);
    }
}
