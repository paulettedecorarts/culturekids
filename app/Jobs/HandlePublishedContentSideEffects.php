<?php

namespace App\Jobs;

use App\Models\AuditLog;
use App\Models\Comic;
use App\Models\PushDeviceToken;
use App\Models\Song;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class HandlePublishedContentSideEffects implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public ?int $orgId = null,
        public ?int $comicId = null,
        public ?int $songId = null
    ) {
        $this->onQueue('media-processing');
    }

    public function handle(): void
    {
        // API cache invalidation strategy: bump org content version
        // so clients can detect stale data and refresh.
        $versionKey = $this->orgId
            ? "api:org:{$this->orgId}:content_version"
            : 'api:global:content_version';
        if (! Cache::has($versionKey)) {
            Cache::put($versionKey, 1, now()->addYears(5));
        } else {
            Cache::increment($versionKey);
        }

        // Notification hook point (e.g., Firebase push). We log metadata now
        // so downstream notifier wiring can consume a clear event trail.
        $payload = ['org_id' => $this->orgId];
        if ($this->comicId) {
            $comic = Comic::find($this->comicId);
            $payload['comic_id'] = $this->comicId;
            $payload['comic_title'] = $comic?->title;
        }
        if ($this->songId) {
            $song = Song::find($this->songId);
            $payload['song_id'] = $this->songId;
            $payload['song_title'] = $song?->title;
        }

        $tokens = PushDeviceToken::query()
            ->where('is_active', true)
            ->when($this->orgId, fn ($query) => $query->where('organisation_id', $this->orgId))
            ->pluck('token')
            ->values()
            ->all();

        if ($tokens !== []) {
            $title = 'New content available';
            $body = 'Fresh approved content is now available offline.';

            if (! empty($payload['comic_title'])) {
                $title = 'New story published';
                $body = $payload['comic_title'].' is now available.';
            } elseif (! empty($payload['song_title'])) {
                $title = 'New song published';
                $body = $payload['song_title'].' is now available.';
            }

            DispatchPushNotification::dispatch(
                tokens: $tokens,
                title: $title,
                body: $body,
                data: [
                    'org_id' => $this->orgId !== null ? (string) $this->orgId : null,
                    'comic_id' => isset($payload['comic_id']) ? (string) $payload['comic_id'] : null,
                    'song_id' => isset($payload['song_id']) ? (string) $payload['song_id'] : null,
                    'type' => 'content_published',
                ]
            );
        }

        AuditLog::record('PUBLISH_SIDE_EFFECTS', 'publish/side-effects', $payload);
        Log::info('Published content side-effects applied', $payload);
    }
}
