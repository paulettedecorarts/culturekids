<?php

namespace App\Support;

use App\Models\LanguageActivity;

final class LanguageActivityApiSerializer
{
    /**
     * @return array<string, mixed>
     */
    public static function toArray(LanguageActivity $activity): array
    {
        $activity->loadMissing('words');

        return [
            'id' => $activity->id,
            'activity_type' => $activity->activity_type,
            'activity_type_label' => $activity->activity_type_label,
            'language_code' => $activity->language_code,
            'difficulty_level' => $activity->difficulty_level,
            'full_sentence' => $activity->full_sentence,
            'sentence_translation' => $activity->sentence_translation,
            'cultural_note' => $activity->cultural_note,
            'audio_path' => self::publicUrl($activity->audio_path),
            'words' => $activity->words->map(fn ($word) => [
                'id' => $word->id,
                'order' => $word->order_index,
                'word' => $word->word,
                'translation' => $word->translation,
                'phonetic' => $word->phonetic,
                'emoji' => $word->emoji,
                'image_path' => self::publicUrl($word->image_path),
                'audio_path' => self::publicUrl($word->audio_path),
                'trace_path' => self::publicUrl($word->trace_path),
                'is_correct_answer' => (bool) $word->is_correct_answer,
                'is_fixed' => (bool) $word->is_fixed,
            ])->values()->all(),
        ];
    }

    private static function publicUrl(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset('storage/'.$path);
    }
}
