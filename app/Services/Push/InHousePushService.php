<?php

namespace App\Services\Push;

class InHousePushService
{
    public function __construct(private readonly PushGateway $gateway) {}

    /**
     * @param  array<int, string>  $tokens
     * @param  array<string, mixed>  $data
     */
    public function send(array $tokens, string $title, string $body, array $data = []): void
    {
        $tokens = array_values(array_unique(array_filter($tokens)));
        if ($tokens === []) {
            return;
        }

        $this->gateway->send($tokens, $title, $body, $data);
    }
}
