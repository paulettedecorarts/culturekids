<?php

namespace App\Support;

use App\Models\Activity;
use Illuminate\Support\Facades\DB;

/**
 * Reads only legacy-id JSON paths from activities.metadata — avoids loading multi-MB blobs on list APIs.
 */
final class ActivityBundleMetadataExtract
{
    /** @var list<string> */
    private const LEGACY_KEYS = [
        'legacy_maze_id',
        'legacy_game_id',
        'legacy_language_activity_id',
        'legacy_drawing_id',
        'drawing_type',
        'legacy_spot_difference_id',
        'legacy_word_search_id',
        'legacy_culture_activity_id',
    ];

    /**
     * @return array<int, \Illuminate\Contracts\Database\Query\Expression>
     */
    public static function selectExpressions(): array
    {
        return [
            DB::raw("CAST(JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.legacy_maze_id')) AS UNSIGNED) as _legacy_maze_id"),
            DB::raw("CAST(JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.legacy_game_id')) AS UNSIGNED) as _legacy_game_id"),
            DB::raw("CAST(JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.legacy_language_activity_id')) AS UNSIGNED) as _legacy_language_activity_id"),
            DB::raw("CAST(JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.legacy_drawing_id')) AS UNSIGNED) as _legacy_drawing_id"),
            DB::raw("JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.drawing_type')) as _drawing_type"),
            DB::raw("CAST(JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.legacy_spot_difference_id')) AS UNSIGNED) as _legacy_spot_difference_id"),
            DB::raw("CAST(JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.legacy_word_search_id')) AS UNSIGNED) as _legacy_word_search_id"),
            DB::raw("CAST(JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.legacy_culture_activity_id')) AS UNSIGNED) as _legacy_culture_activity_id"),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function toMetadataArray(Activity $activity): array
    {
        $attrs = $activity->getAttributes();
        $fromExtracts = array_filter([
            'legacy_maze_id' => self::intOrNull($attrs['_legacy_maze_id'] ?? null),
            'legacy_game_id' => self::intOrNull($attrs['_legacy_game_id'] ?? null),
            'legacy_language_activity_id' => self::intOrNull($attrs['_legacy_language_activity_id'] ?? null),
            'legacy_drawing_id' => self::intOrNull($attrs['_legacy_drawing_id'] ?? null),
            'drawing_type' => self::stringOrNull($attrs['_drawing_type'] ?? null),
            'legacy_spot_difference_id' => self::intOrNull($attrs['_legacy_spot_difference_id'] ?? null),
            'legacy_word_search_id' => self::intOrNull($attrs['_legacy_word_search_id'] ?? null),
            'legacy_culture_activity_id' => self::intOrNull($attrs['_legacy_culture_activity_id'] ?? null),
        ], fn ($value) => $value !== null && $value !== '');

        if ($fromExtracts !== []) {
            return $fromExtracts;
        }

        return self::pickLegacyKeys(self::rawMetadata($activity));
    }

    /**
     * Metadata safe for offline bundle manifests (never includes nested maze/grid blobs).
     *
     * @return array<string, mixed>|null
     */
    public static function slimForOfflineBundle(Activity $activity): ?array
    {
        $slim = self::toMetadataArray($activity);

        return $slim !== [] ? $slim : null;
    }

    /**
     * @param  array<string, mixed>  $metadata
     * @return array<string, mixed>
     */
    public static function pickLegacyKeys(array $metadata): array
    {
        $picked = [];
        foreach (self::LEGACY_KEYS as $key) {
            if (array_key_exists($key, $metadata) && $metadata[$key] !== null && $metadata[$key] !== '') {
                $picked[$key] = $metadata[$key];
            }
        }

        return $picked;
    }

    /**
     * @return array<string, mixed>
     */
    private static function rawMetadata(Activity $activity): array
    {
        $metadata = $activity->metadata;

        if (is_array($metadata)) {
            return $metadata;
        }

        if (is_string($metadata) && $metadata !== '') {
            return json_decode($metadata, true) ?? [];
        }

        return [];
    }

    private static function intOrNull(mixed $value): ?int
    {
        $int = (int) $value;

        return $int > 0 ? $int : null;
    }

    private static function stringOrNull(mixed $value): ?string
    {
        if (! is_string($value) || $value === '' || $value === 'null') {
            return null;
        }

        return $value;
    }
}
