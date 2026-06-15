<?php

namespace App\Jobs;

use App\Models\AuditLog;
use App\Models\Comic;
use App\Models\PushDeviceToken;
use App\Models\Song;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\Push\UserNotificationService;
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

    public function handle(UserNotificationService $notifications): void
    {
        $versionKey = $this->orgId
            ? "api:org:{$this->orgId}:content_version"
            : 'api:global:content_version';
        if (! Cache::has($versionKey)) {
            Cache::put($versionKey, 1, now()->addYears(5));
        } else {
            Cache::increment($versionKey);
        }

        $payload = ['org_id' => $this->orgId];
        $title = 'New content available';
        $body = 'Fresh approved content is now available offline.';

        if ($this->comicId) {
            $comic = Comic::find($this->comicId);
            $payload['comic_id'] = $this->comicId;
            $payload['comic_title'] = $comic?->title;
            if ($comic?->title) {
                $title = 'New story published';
                $body = $comic->title.' is now available.';
            }
            if ($comic?->tribe_id) {
                $payload['tribe_id'] = (string) $comic->tribe_id;
            }
        }

        if ($this->songId) {
            $song = Song::find($this->songId);
            $payload['song_id'] = $this->songId;
            $payload['song_title'] = $song?->title;
            if ($song?->title) {
                $title = 'New song published';
                $body = $song->title.' is now available.';
            }
            if ($song?->tribe_id) {
                $payload['tribe_id'] = (string) $song->tribe_id;
            }
        }

        $userIds = PushDeviceToken::query()
            ->where('is_active', true)
            ->when($this->orgId, fn ($query) => $query->where('organisation_id', $this->orgId))
            ->distinct()
            ->pluck('user_id');

        $users = User::query()
            ->role('parent')
            ->whereIn('id', $userIds)
            ->get();

        $notifyData = array_filter([
            'org_id' => $this->orgId !== null ? (string) $this->orgId : null,
            'comic_id' => isset($payload['comic_id']) ? (string) $payload['comic_id'] : null,
            'song_id' => isset($payload['song_id']) ? (string) $payload['song_id'] : null,
            'tribe_id' => $payload['tribe_id'] ?? null,
        ], fn ($value) => $value !== null);

        $sent = $notifications->notifyUsers(
            $users,
            UserNotification::TYPE_CONTENT_PUBLISHED,
            $title,
            $body,
            $notifyData
        );

        AuditLog::record('PUBLISH_SIDE_EFFECTS', 'publish/side-effects', $payload + ['notifications_sent' => $sent]);
        Log::info('Published content side-effects applied', $payload + ['notifications_sent' => $sent]);
    }
}
