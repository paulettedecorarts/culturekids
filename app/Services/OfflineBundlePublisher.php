<?php

namespace App\Services;

use App\Jobs\BuildOfflineBundle;
use App\Models\OrganisationContentDecision;

class OfflineBundlePublisher
{
    public static function queue(string $contentType, int $contentId): void
    {
        if (! in_array($contentType, OrganisationContentDecision::ALL_TYPES, true)) {
            return;
        }

        OfflineBundleBuildStatus::markQueued($contentType, $contentId);
        BuildOfflineBundle::dispatch($contentType, $contentId);
    }

    /**
     * @param  iterable<int, array{content_type: string, content_id: int}>  $items
     */
    public static function queueMany(iterable $items): int
    {
        $count = 0;
        foreach ($items as $item) {
            $type = (string) ($item['content_type'] ?? '');
            $id = (int) ($item['content_id'] ?? 0);
            if ($type === '' || $id < 1) {
                continue;
            }
            self::queue($type, $id);
            $count++;
        }

        return $count;
    }
}
