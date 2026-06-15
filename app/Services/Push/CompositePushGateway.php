<?php

namespace App\Services\Push;

/**
 * Routes Expo tokens to Expo Push API and native FCM tokens to FCM.
 */
class CompositePushGateway implements PushGateway
{
    public function __construct(
        private readonly ExpoPushGateway $expo,
        private readonly FcmPushGateway $fcm,
    ) {}

    public function send(array $tokens, string $title, string $body, array $data = []): void
    {
        $expoTokens = [];
        $fcmTokens = [];

        foreach ($tokens as $token) {
            if (str_starts_with($token, 'ExponentPushToken[')) {
                $expoTokens[] = $token;
            } else {
                $fcmTokens[] = $token;
            }
        }

        if ($expoTokens !== []) {
            $this->expo->send($expoTokens, $title, $body, $data);
        }

        if ($fcmTokens !== []) {
            $this->fcm->send($fcmTokens, $title, $body, $data);
        }
    }
}
