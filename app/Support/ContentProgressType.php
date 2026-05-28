<?php

namespace App\Support;

use App\Services\OrganisationModuleResolver;
use InvalidArgumentException;

class ContentProgressType
{
    public const STORY = 'story';

    public const SONG = 'song';

    public const FLASHCARD = 'flashcard';

    public const PUZZLE = 'puzzle';

    public const MAZE = 'maze';

    public const SPOT_DIFFERENCE = 'spot_difference';

    public const WORD_SEARCH = 'word_search';

    public const DRAWING_KIT = 'drawing_kit';

    public const VOCAB_PACK = 'vocab_pack';

    public const CULTURE = 'culture';

    public const GAME = 'game';

    public const COLOURING = 'colouring';

    /** @var list<string> */
    public const ALL = [
        self::STORY,
        self::SONG,
        self::FLASHCARD,
        self::PUZZLE,
        self::MAZE,
        self::SPOT_DIFFERENCE,
        self::WORD_SEARCH,
        self::DRAWING_KIT,
        self::VOCAB_PACK,
        self::CULTURE,
        self::GAME,
        self::COLOURING,
    ];

    public static function assertValid(string $type): void
    {
        if (! in_array($type, self::ALL, true)) {
            throw new InvalidArgumentException("Unsupported content type [{$type}]");
        }
    }

    public static function moduleKey(string $type): ?string
    {
        self::assertValid($type);

        $activityMap = OrganisationModuleResolver::activityTypeToModuleKey();
        if (isset($activityMap[$type])) {
            return $activityMap[$type];
        }

        $contentMap = OrganisationModuleResolver::contentTypeToModuleKey();

        return $contentMap[$type] ?? null;
    }

    public static function usesActivityTable(string $type): bool
    {
        return ! in_array($type, [self::STORY, self::SONG], true);
    }
}
