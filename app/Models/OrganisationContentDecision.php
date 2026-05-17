<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganisationContentDecision extends Model
{
    public const DECISION_APPROVED = 'approved';

    public const DECISION_REJECTED = 'rejected';

    public const TYPE_STORY = 'story';

    public const TYPE_SONG = 'song';

    public const TYPE_FLASHCARD = 'flashcard';

    public const TYPE_PUZZLE = 'puzzle';

    public const TYPE_DRAWING = 'drawing';

    public const TYPE_LANGUAGE = 'language';

    public const TYPE_GAME = 'game';

    public const TYPE_MAZE = 'maze';

    public const TYPE_SPOT_DIFFERENCE = 'spot_difference';

    public const TYPE_WORD_SEARCH = 'word_search';

    public const TYPE_CULTURE = 'culture';

    public const TYPE_COLOURING = 'colouring';

    /** @var list<string> */
    public const ALL_TYPES = [
        self::TYPE_STORY,
        self::TYPE_SONG,
        self::TYPE_FLASHCARD,
        self::TYPE_PUZZLE,
        self::TYPE_DRAWING,
        self::TYPE_LANGUAGE,
        self::TYPE_GAME,
        self::TYPE_MAZE,
        self::TYPE_SPOT_DIFFERENCE,
        self::TYPE_WORD_SEARCH,
        self::TYPE_CULTURE,
        self::TYPE_COLOURING,
    ];

    /** @var array<string, string> */
    public const TYPE_LABELS = [
        self::TYPE_STORY => 'Story',
        self::TYPE_SONG => 'Song',
        self::TYPE_FLASHCARD => 'Flashcard',
        self::TYPE_PUZZLE => 'Puzzle',
        self::TYPE_DRAWING => 'Drawing',
        self::TYPE_LANGUAGE => 'Language',
        self::TYPE_GAME => 'Game',
        self::TYPE_MAZE => 'Maze',
        self::TYPE_SPOT_DIFFERENCE => 'Spot the Difference',
        self::TYPE_WORD_SEARCH => 'Word Search',
        self::TYPE_CULTURE => 'Culture',
        self::TYPE_COLOURING => 'Colouring',
    ];

    protected $fillable = [
        'organisation_id',
        'content_type',
        'content_id',
        'decision',
        'decided_by',
    ];

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function decidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public static function labelFor(string $contentType): string
    {
        return self::TYPE_LABELS[$contentType] ?? ucfirst(str_replace('_', ' ', $contentType));
    }
}
