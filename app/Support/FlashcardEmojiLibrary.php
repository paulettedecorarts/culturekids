<?php

namespace App\Support;

use Illuminate\Support\Facades\File;
use JsonException;

/**
 * Curated emoji sets for flashcard deck authoring (grouped like a full OS emoji keyboard).
 */
class FlashcardEmojiLibrary
{
    /** @var array<string, list<string>>|null */
    protected static ?array $cache = null;

    /**
     * @return array<string, list<string>> category label => list of emoji graphemes
     *
     * @throws JsonException
     */
    public static function categories(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        $path = resource_path('data/flashcard_emojis.json');
        if (! File::exists($path)) {
            return self::$cache = [];
        }

        /** @var array<string, list<string>> $data */
        $data = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);

        return self::$cache = $data;
    }

    /**
     * @return list<string>
     */
    public static function categoryNames(): array
    {
        return array_keys(self::categories());
    }

    /**
     * @return list<string>
     */
    public static function allEmojisFlattened(): array
    {
        $out = [];
        foreach (self::categories() as $emojis) {
            foreach ($emojis as $e) {
                $out[] = $e;
            }
        }

        return array_values(array_unique($out));
    }
}
