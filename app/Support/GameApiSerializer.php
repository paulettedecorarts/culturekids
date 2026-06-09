<?php

namespace App\Support;

use App\Models\Game;
use App\Models\GameQuestion;

final class GameApiSerializer
{
    /**
     * Playable game payload for the child app and offline bundles.
     *
     * @return array<string, mixed>
     */
    public static function toArray(Game $game): array
    {
        $game->loadMissing(['tribe:id,name,color,hero_emoji,hero_icon', 'questions']);

        return [
            'id' => $game->id,
            'title' => $game->title,
            'description' => $game->description,
            'game_type' => $game->game_type,
            'game_type_label' => $game->game_type_label,
            'game_type_icon' => $game->game_type_icon,
            'difficulty_level' => $game->difficulty_level,
            'age_min' => $game->age_min,
            'age_max' => $game->age_max,
            'age_range' => $game->age_range,
            'star_points' => $game->star_points,
            'time_limit_seconds' => $game->time_limit_seconds,
            'lives' => $game->lives,
            'shuffle_questions' => (bool) $game->shuffle_questions,
            'questions_per_round' => $game->questions_per_round,
            'cultural_note' => $game->cultural_note,
            'language_code' => $game->language_code,
            'cover_image_url' => self::publicUrl($game->cover_image_path),
            'background_music_url' => self::publicUrl($game->background_music_path),
            'questions' => $game->questions->map(fn (GameQuestion $q) => self::questionToArray($q))->values()->all(),
            'tribe' => $game->tribe ? [
                'id' => $game->tribe->id,
                'name' => $game->tribe->name,
                'color' => $game->tribe->color,
                'icon' => $game->tribe->hero_emoji ?? $game->tribe->hero_icon,
            ] : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function questionToArray(GameQuestion $question): array
    {
        return [
            'id' => $question->id,
            'order' => $question->order_index,
            'question_text' => $question->question_text,
            'question_emoji' => $question->question_emoji,
            'question_image_url' => self::publicUrl($question->question_image_path),
            'question_audio_url' => self::publicUrl($question->question_audio_path),
            'options' => self::normalizeOptions($question->options ?? []),
            'match_text' => $question->match_text,
            'match_emoji' => $question->match_emoji,
            'match_image_url' => self::publicUrl($question->match_image_path),
            'correct_answer' => $question->correct_answer,
            'hint' => $question->hint,
            'points' => $question->points ?? 10,
            'beat_pattern' => $question->beat_pattern ?? [],
        ];
    }

    /**
     * @param  list<mixed>  $options
     * @return list<array<string, mixed>>
     */
    private static function normalizeOptions(array $options): array
    {
        return collect($options)
            ->map(function ($option) {
                if (! is_array($option)) {
                    return null;
                }

                $text = trim((string) ($option['text'] ?? ''));

                return [
                    'text' => $text !== '' ? $text : null,
                    'emoji' => $option['emoji'] ?? null,
                    'image_url' => self::publicUrl($option['image_path'] ?? null),
                    'audio_url' => self::publicUrl($option['audio_path'] ?? null),
                    'is_correct' => (bool) ($option['is_correct'] ?? false),
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

        return asset('storage/'.ltrim($path, '/'));
    }
}
