<?php

namespace App\Jobs;

use App\Models\ParentDownloadedPack;
use App\Models\PushDeviceToken;
use App\Models\Tribe;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\Push\UserNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendEngagementNotificationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct()
    {
        $this->onQueue('media-processing');
    }

    public function handle(UserNotificationService $notifications): void
    {
        $parentIds = PushDeviceToken::query()
            ->where('is_active', true)
            ->distinct()
            ->pluck('user_id');

        if ($parentIds->isEmpty()) {
            return;
        }

        $parents = User::query()
            ->role('parent')
            ->whereIn('id', $parentIds)
            ->withCount('childProfiles')
            ->get();

        $allTribeIds = Tribe::query()->pluck('id')->all();
        $downloadReminders = 0;
        $recommendations = 0;

        foreach ($parents as $parent) {
            $downloadedIds = ParentDownloadedPack::query()
                ->where('user_id', $parent->id)
                ->pluck('tribe_id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $missingTribeIds = array_values(array_diff($allTribeIds, $downloadedIds));

            if ($missingTribeIds === []) {
                continue;
            }

            $nextTribe = Tribe::query()->find($missingTribeIds[0]);
            if (! $nextTribe) {
                continue;
            }

            if ($parent->child_profiles_count > 0 && count($downloadedIds) === 0) {
                $sent = $notifications->notifyUser(
                    $parent,
                    UserNotification::TYPE_DOWNLOAD_REMINDER,
                    'Download your first pack',
                    "Get {$nextTribe->name} offline so {$parent->name}'s family can learn anywhere.",
                    [
                        'tribe_id' => (string) $nextTribe->id,
                        'tribe_name' => $nextTribe->name,
                    ]
                );

                if ($sent) {
                    $downloadReminders++;
                }

                continue;
            }

            if (count($downloadedIds) > 0 && count($missingTribeIds) > 0) {
                $sent = $notifications->notifyUser(
                    $parent,
                    UserNotification::TYPE_RECOMMENDATION,
                    'New tribe to explore',
                    "We think you'll love {$nextTribe->name}. Download it for more stories and songs.",
                    [
                        'tribe_id' => (string) $nextTribe->id,
                        'tribe_name' => $nextTribe->name,
                    ]
                );

                if ($sent) {
                    $recommendations++;
                }
            }
        }

        Log::info('Engagement notifications dispatched', [
            'download_reminders' => $downloadReminders,
            'recommendations' => $recommendations,
        ]);
    }
}
