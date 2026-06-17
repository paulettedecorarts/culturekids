<?php

namespace App\Support;

use App\Models\CultureActivity;

final class CultureInteractionMode
{
    public const STORY = 'story';

    public const QUIZ = 'quiz';

    public const MAP = 'map';

    public const DESIGN = 'design';

    public const PROFILE = 'profile';

    public const HISTORY = 'history';

    public static function for(CultureActivity $activity): string
    {
        $hasQuiz = ! empty($activity->quiz_questions);
        $tag = strtolower((string) ($activity->metadata['tag'] ?? ''));
        $seedType = strtolower((string) ($activity->metadata['seed_activity_type'] ?? ''));
        $quizFocused = $hasQuiz && (
            $seedType === 'quiz'
            || str_contains($tag, 'quiz')
            || str_contains($tag, 'graduation')
        );

        return match ($activity->culture_type) {
            'clan_map' => self::MAP,
            'clan_design' => self::DESIGN,
            'clan_profile' => $quizFocused ? self::QUIZ : self::PROFILE,
            'clan_history' => $quizFocused ? self::QUIZ : self::HISTORY,
            'clan_story' => $quizFocused ? self::QUIZ : self::STORY,
            default => $hasQuiz ? self::QUIZ : self::STORY,
        };
    }
}
