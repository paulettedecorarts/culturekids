<?php

namespace App\Services;

use App\Models\OfflineContentBundle;
use Illuminate\Support\Facades\Cache;

class OfflineBundleBuildStatus
{
    public const QUEUED = 'queued';

    public const BUILDING = 'building';

    public const READY = 'ready';

    public const FAILED = 'failed';

    public const NOT_BUILT = 'not_built';

    private const TTL_SECONDS = 86400;

    public static function cacheKey(string $contentType, int $contentId): string
    {
        return "offline_bundle_build:{$contentType}:{$contentId}";
    }

    public static function markQueued(string $contentType, int $contentId): void
    {
        self::put($contentType, $contentId, [
            'status' => self::QUEUED,
            'message' => null,
            'queued_at' => now()->toIso8601String(),
        ]);
    }

    public static function markBuilding(string $contentType, int $contentId): void
    {
        $existing = self::get($contentType, $contentId) ?? [];
        self::put($contentType, $contentId, array_merge($existing, [
            'status' => self::BUILDING,
            'building_at' => now()->toIso8601String(),
        ]));
    }

    public static function markFailed(string $contentType, int $contentId, string $message): void
    {
        self::put($contentType, $contentId, [
            'status' => self::FAILED,
            'message' => \Illuminate\Support\Str::limit($message, 200),
            'failed_at' => now()->toIso8601String(),
        ]);
    }

    public static function clear(string $contentType, int $contentId): void
    {
        Cache::forget(self::cacheKey($contentType, $contentId));
    }

    /**
     * @return array{status: string, label: string, message: ?string, queued_at: ?string, built_at: ?string}|null
     */
    public static function get(string $contentType, int $contentId): ?array
    {
        $cached = Cache::get(self::cacheKey($contentType, $contentId));

        return is_array($cached) ? $cached : null;
    }

    /**
     * @return array{status: string, label: string, message: ?string, ready: bool, built_at: ?string}
     */
    public static function resolve(
        string $contentType,
        int $contentId,
        ?string $bundlePath,
        ?string $bundleHash,
        ?OfflineContentBundle $bundle = null,
    ): array {
        $ready = $bundlePath !== null && $bundlePath !== '';
        $builtAt = $bundle?->built_at?->toIso8601String();
        $cached = self::get($contentType, $contentId);

        if ($cached !== null) {
            $cachedStatus = (string) ($cached['status'] ?? '');

            if ($cachedStatus === self::FAILED) {
                return [
                    'status' => self::FAILED,
                    'label' => __('Failed'),
                    'message' => $cached['message'] ?? null,
                    'ready' => false,
                    'built_at' => $builtAt,
                ];
            }

            if (in_array($cachedStatus, [self::QUEUED, self::BUILDING], true)) {
                if ($ready) {
                    self::clear($contentType, $contentId);

                    return [
                        'status' => self::READY,
                        'label' => __('Ready'),
                        'message' => null,
                        'ready' => true,
                        'built_at' => $builtAt,
                    ];
                }

                return [
                    'status' => $cachedStatus,
                    'label' => $cachedStatus === self::BUILDING ? __('Building…') : __('Queued…'),
                    'message' => null,
                    'ready' => false,
                    'built_at' => $builtAt,
                ];
            }
        }

        if ($ready) {
            return [
                'status' => self::READY,
                'label' => __('Ready'),
                'message' => null,
                'ready' => true,
                'built_at' => $builtAt,
            ];
        }

        return [
            'status' => self::NOT_BUILT,
            'label' => __('Not built'),
            'message' => null,
            'ready' => false,
            'built_at' => null,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private static function put(string $contentType, int $contentId, array $payload): void
    {
        Cache::put(
            self::cacheKey($contentType, $contentId),
            array_merge($payload, ['updated_at' => now()->toIso8601String()]),
            self::TTL_SECONDS
        );
    }
}
