<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfflineContentBundle extends Model
{
    protected $fillable = [
        'content_type',
        'content_id',
        'bundle_path',
        'bundle_hash',
        'asset_count',
        'bytes',
        'metadata',
        'built_at',
    ];

    protected $casts = [
        'content_id' => 'integer',
        'asset_count' => 'integer',
        'bytes' => 'integer',
        'metadata' => 'array',
        'built_at' => 'datetime',
    ];

    public static function forContent(string $contentType, int $contentId): ?self
    {
        return static::query()
            ->where('content_type', $contentType)
            ->where('content_id', $contentId)
            ->first();
    }

    public static function upsertFromBuild(string $contentType, int $contentId, array $result): self
    {
        return static::query()->updateOrCreate(
            [
                'content_type' => $contentType,
                'content_id' => $contentId,
            ],
            [
                'bundle_path' => $result['bundle_path'],
                'bundle_hash' => $result['bundle_hash'],
                'asset_count' => $result['asset_count'],
                'bytes' => $result['bytes'],
                'built_at' => now(),
                'metadata' => [
                    'schema' => $result['schema'] ?? 'culturekids.bundle.v2',
                ],
            ]
        );
    }
}
