<?php

namespace App\Support;

use App\Models\CultureActivity;

final class CultureApiSerializer
{
    /**
     * Playable culture payload for the child app and offline bundles.
     *
     * @return array<string, mixed>
     */
    public static function toArray(CultureActivity $activity): array
    {
        $activity->loadMissing('tribe:id,name,color,hero_emoji,hero_icon');

        return [
            'id' => $activity->id,
            'title' => $activity->title,
            'description' => $activity->description,
            'culture_type' => $activity->culture_type,
            'culture_type_label' => $activity->culture_type_label,
            'culture_type_icon' => $activity->culture_type_icon,
            'difficulty_level' => $activity->difficulty_level,
            'age_min' => $activity->age_min,
            'age_max' => $activity->age_max,
            'age_range' => $activity->age_range,
            'star_points' => $activity->star_points,
            'clan_name' => $activity->clan_name,
            'clan_totem' => $activity->clan_totem,
            'clan_role' => $activity->clan_role,
            'clan_emoji' => $activity->clan_emoji,
            'proverb' => $activity->proverb,
            'proverb_translation' => $activity->proverb_translation,
            'cultural_note' => $activity->cultural_note,
            'content' => $activity->content,
            'content_sections' => self::normalizeSections($activity->content_sections ?? []),
            'quiz_questions' => self::normalizeQuizQuestions($activity->quiz_questions ?? []),
            'map_data' => $activity->map_data ?? [],
            'design_elements' => $activity->design_elements ?? [],
            'cover_image_url' => self::publicUrl($activity->cover_image_path),
            'map_image_url' => self::publicUrl($activity->map_image_path),
            'tribe' => $activity->tribe ? [
                'id' => $activity->tribe->id,
                'name' => $activity->tribe->name,
                'color' => $activity->tribe->color,
                'icon' => $activity->tribe->hero_emoji ?? $activity->tribe->hero_icon,
            ] : null,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $sections
     * @return list<array<string, mixed>>
     */
    private static function normalizeSections(array $sections): array
    {
        return collect($sections)
            ->map(function ($section) {
                if (! is_array($section)) {
                    return null;
                }

                return [
                    'title' => $section['title'] ?? null,
                    'text' => $section['text'] ?? null,
                    'image_url' => self::publicUrl($section['image_path'] ?? null),
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @param  list<array<string, mixed>>  $questions
     * @return list<array<string, mixed>>
     */
    private static function normalizeQuizQuestions(array $questions): array
    {
        return collect($questions)
            ->map(function ($question) {
                if (! is_array($question)) {
                    return null;
                }

                $text = trim((string) ($question['question'] ?? ''));
                $answer = trim((string) ($question['answer'] ?? ''));

                if ($text === '' || $answer === '') {
                    return null;
                }

                return [
                    'question' => $text,
                    'answer' => $answer,
                ];
            })
            ->filter()
            ->values()
            ->all();
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
