<?php

namespace App\Jobs;

use App\Services\Push\UserNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendUserNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public int $userId,
        public string $type,
        public string $title,
        public string $body,
        public array $data = [],
        public bool $respectCooldown = true
    ) {
        $this->onQueue('media-processing');
    }

    public function handle(UserNotificationService $notifications): void
    {
        $notifications->notifyUser(
            $this->userId,
            $this->type,
            $this->title,
            $this->body,
            $this->data,
            $this->respectCooldown
        );
    }
}
