<?php

namespace App\Support;

use App\Models\WordSearch;

final class WordSearchApiSerializer
{
    /**
     * Playable word search payload for the child app.
     * word_positions are omitted so answers are not exposed to the client.
     *
     * @return array<string, mixed>
     */
    public static function toArray(WordSearch $item): array
    {
        $words = collect($item->words ?? [])
            ->map(function ($entry) {
                if (is_string($entry)) {
                    $word = strtoupper(trim($entry));

                    return $word !== '' ? ['word' => $word] : null;
                }

                if (! is_array($entry)) {
                    return null;
                }

                $word = strtoupper(trim((string) ($entry['word'] ?? '')));
                if ($word === '') {
                    return null;
                }

                return [
                    'word' => $word,
                    'translation' => $entry['translation'] ?? null,
                    'hint' => $entry['hint'] ?? null,
                ];
            })
            ->filter()
            ->values()
            ->all();

        return [
            'id' => $item->id,
            'title' => $item->title,
            'grid' => $item->grid ?? [],
            'grid_size' => $item->grid_size,
            'words' => $words,
            'allow_diagonal' => (bool) $item->allow_diagonal,
            'allow_reverse' => (bool) $item->allow_reverse,
            'cultural_note' => $item->cultural_note,
            'difficulty_level' => $item->difficulty_level,
        ];
    }
}
