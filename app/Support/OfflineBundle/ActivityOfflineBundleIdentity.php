<?php

namespace App\Support\OfflineBundle;

use App\Models\Activity;
use App\Models\OrganisationContentDecision;

/**
 * Maps legacy {@see Activity} rows to offline bundle keys (content_type + content_id).
 *
 * Bundles for language, drawings, games, etc. are stored under the dedicated model id,
 * not the mirror Activity id shown in tribe activity lists.
 */
class ActivityOfflineBundleIdentity
{
    /**
     * @return array{content_type: string, content_id: int}|null
     */
    public static function resolve(Activity $activity): ?array
    {
        $metadata = is_array($activity->metadata)
            ? $activity->metadata
            : (json_decode((string) $activity->metadata, true) ?? []);

        return match ((string) $activity->type) {
            'flashcard' => self::pair(OrganisationContentDecision::TYPE_FLASHCARD, (int) $activity->id),
            'puzzle' => self::pair(OrganisationContentDecision::TYPE_PUZZLE, (int) $activity->id),
            'vocab_pack' => self::fromLegacy($metadata, 'legacy_language_activity_id', OrganisationContentDecision::TYPE_LANGUAGE),
            'drawing_kit' => self::drawingIdentity($metadata),
            'game' => self::fromLegacy($metadata, 'legacy_game_id', OrganisationContentDecision::TYPE_GAME),
            'maze' => self::fromLegacy($metadata, 'legacy_maze_id', OrganisationContentDecision::TYPE_MAZE),
            'spot_difference' => self::fromLegacy($metadata, 'legacy_spot_difference_id', OrganisationContentDecision::TYPE_SPOT_DIFFERENCE),
            'word_search' => self::fromLegacy($metadata, 'legacy_word_search_id', OrganisationContentDecision::TYPE_WORD_SEARCH),
            'culture' => self::fromLegacy($metadata, 'legacy_culture_activity_id', OrganisationContentDecision::TYPE_CULTURE),
            default => null,
        };
    }

    /**
     * @return array{content_type: string, content_id: int}|null
     */
    private static function fromLegacy(array $metadata, string $key, string $contentType): ?array
    {
        $id = (int) ($metadata[$key] ?? 0);

        return $id > 0 ? self::pair($contentType, $id) : null;
    }

    /**
     * @return array{content_type: string, content_id: int}|null
     */
    private static function drawingIdentity(array $metadata): ?array
    {
        $id = (int) ($metadata['legacy_drawing_id'] ?? 0);
        if ($id < 1) {
            return null;
        }

        $contentType = ($metadata['drawing_type'] ?? null) === 'coloring'
            ? OrganisationContentDecision::TYPE_COLOURING
            : OrganisationContentDecision::TYPE_DRAWING;

        return self::pair($contentType, $id);
    }

    /**
     * @return array{content_type: string, content_id: int}
     */
    private static function pair(string $contentType, int $contentId): array
    {
        return [
            'content_type' => $contentType,
            'content_id' => $contentId,
        ];
    }
}
