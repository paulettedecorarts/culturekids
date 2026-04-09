<?php

namespace App\Jobs;

use App\Services\Push\InHousePushService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DispatchPushNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @param  array<int, string>  $tokens
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public array $tokens,
        public string $title,
        public string $body,
        public array $data = []
    ) {
        $this->onQueue('media-processing');
    }

    public function handle(InHousePushService $push): void
    {
        try {
            $push->send($this->tokens, $this->title, $this->body, $this->data);
        } catch (\Throwable $e) {
            Log::error('DispatchPushNotification failed', [
                'error' => $e->getMessage(),
                'title' => $this->title,
            ]);
            throw $e;
        }
    }
}
