<?php

namespace App\Services\Push;

use App\Jobs\DispatchPushNotification;
use App\Models\PushDeviceToken;
use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Support\Arr;

class UserNotificationService
{
    /** @var array<string, int> Cooldown in hours per notification type. */
    private const COOLDOWN_HOURS = [
        UserNotification::TYPE_LOGIN_ALERT => 24,
        UserNotification::TYPE_DOWNLOAD_REMINDER => 72,
        UserNotification::TYPE_RECOMMENDATION => 168,
        UserNotification::TYPE_CONTENT_PUBLISHED => 1,
    ];

    /**
     * @param  array<string, mixed>  $data
     */
    public function notifyUser(
        User|int $user,
        string $type,
        string $title,
        string $body,
        array $data = [],
        bool $respectCooldown = true
    ): ?UserNotification {
        $user = $user instanceof User ? $user : User::findOrFail($user);

        if ($respectCooldown && $this->isInCooldown($user->id, $type)) {
            return null;
        }

        $screen = $this->screenForType($type, $data);
        $payload = array_filter([
            'type' => $type,
            'screen' => $screen,
            ...$data,
        ], fn ($value) => $value !== null && $value !== '');

        $notification = UserNotification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'data' => $payload,
            'push_sent' => false,
        ]);

        $tokens = PushDeviceToken::query()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->pluck('token')
            ->values()
            ->all();

        if ($tokens !== []) {
            DispatchPushNotification::dispatch(
                tokens: $tokens,
                title: $title,
                body: $body,
                data: array_merge($payload, [
                    'notification_id' => (string) $notification->id,
                ])
            );

            $notification->forceFill(['push_sent' => true])->save();
        }

        return $notification;
    }

    /**
     * @param  iterable<int, User|int>  $users
     * @param  array<string, mixed>  $data
     */
    public function notifyUsers(
        iterable $users,
        string $type,
        string $title,
        string $body,
        array $data = [],
        bool $respectCooldown = true
    ): int {
        $sent = 0;

        foreach ($users as $user) {
            if ($this->notifyUser($user, $type, $title, $body, $data, $respectCooldown)) {
                $sent++;
            }
        }

        return $sent;
    }

    private function isInCooldown(int $userId, string $type): bool
    {
        $hours = self::COOLDOWN_HOURS[$type] ?? 0;
        if ($hours <= 0) {
            return false;
        }

        return UserNotification::query()
            ->where('user_id', $userId)
            ->where('type', $type)
            ->where('created_at', '>=', now()->subHours($hours))
            ->exists();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function screenForType(string $type, array $data): string
    {
        if (! empty($data['screen']) && is_string($data['screen'])) {
            return $data['screen'];
        }

        $tribeId = Arr::get($data, 'tribe_id');

        return match ($type) {
            UserNotification::TYPE_LOGIN_ALERT => '/(parent)/(tabs)/dashboard',
            UserNotification::TYPE_DOWNLOAD_REMINDER => $tribeId
                ? "/(parent)/pack/{$tribeId}"
                : '/(parent)/(tabs)/pack-store',
            UserNotification::TYPE_RECOMMENDATION => $tribeId
                ? "/(parent)/pack/{$tribeId}"
                : '/(parent)/(tabs)/pack-store',
            UserNotification::TYPE_CONTENT_PUBLISHED => '/(parent)/(tabs)/pack-store',
            default => '/(parent)/notifications',
        };
    }
}
