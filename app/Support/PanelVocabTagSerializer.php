<?php

namespace App\Support;

use App\Models\ContentTranslation;
use App\Models\PanelVocabTag;

final class PanelVocabTagSerializer
{
    /**
     * @return array<string, mixed>
     */
    public static function toArray(ContentTranslation|PanelVocabTag $tag, bool $includeId = true): array
    {
        $payload = [
            'word' => $tag->word,
            'translation' => $tag->translation,
            'phonetic' => $tag->phonetic,
            'x_position' => $tag->x_position,
            'y_position' => $tag->y_position,
            'width' => $tag->width,
            'height' => $tag->height,
        ];

        if ($includeId) {
            $payload['id'] = $tag->id;
        }

        if ($tag->metadata !== null && $tag->metadata !== []) {
            $payload['metadata'] = $tag->metadata;
        }

        return $payload;
    }
}
