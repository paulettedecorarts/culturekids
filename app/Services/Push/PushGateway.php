<?php

namespace App\Services\Push;

interface PushGateway
{
    /**
     * @param  array<int, string>  $tokens
     * @param  array<string, mixed>  $data
     */
    public function send(array $tokens, string $title, string $body, array $data = []): void;
}
