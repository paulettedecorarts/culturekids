<?php

namespace App\Support;

/**
 * Server-side mirror of the mobile apple grade module (accuracy / speed / precision).
 */
final class AppleGradeScoring
{
    private const WEIGHT_ACCURACY = 0.45;

    private const WEIGHT_SPEED = 0.30;

    private const WEIGHT_PRECISION = 0.25;

    private const THRESHOLD_GOLD = 0.88;

    private const THRESHOLD_SILVER = 0.68;

    /** @var array<string, float> */
    private const GRADE_MULTIPLIERS = [
        'gold' => 1.0,
        'silver' => 0.75,
        'bronze' => 0.5,
    ];

    /**
     * @param  array<string, mixed>|null  $input
     * @return array{
     *   grade: string,
     *   stars_earned: int,
     *   max_stars: int,
     *   accuracy: float,
     *   speed: float,
     *   precision: float,
     *   performance: float,
     *   metadata: array<string, mixed>
     * }
     */
    public static function compute(?array $input, int $maxStars): array
    {
        $input = $input ?? [];
        $cappedMax = max(1, $maxStars);

        $accuracy = self::resolveAccuracy($input);
        $speed = self::resolveSpeed($input);
        $precision = self::resolvePrecision($input);

        $performance = ($accuracy * self::WEIGHT_ACCURACY)
            + ($speed * self::WEIGHT_SPEED)
            + ($precision * self::WEIGHT_PRECISION);

        $grade = self::gradeFromPerformance($performance);
        $multiplier = self::GRADE_MULTIPLIERS[$grade] ?? 0.5;
        $starsEarned = max(1, min($cappedMax, (int) round($cappedMax * $multiplier)));

        $metadata = [
            'apple_grade' => $grade,
            'apple_performance' => round($performance, 3),
            'apple_accuracy' => round($accuracy, 3),
            'apple_speed' => round($speed, 3),
            'apple_precision' => round($precision, 3),
            'apple_max_stars' => $cappedMax,
            'apple_input' => $input,
        ];

        return [
            'grade' => $grade,
            'stars_earned' => $starsEarned,
            'max_stars' => $cappedMax,
            'accuracy' => round($accuracy, 3),
            'speed' => round($speed, 3),
            'precision' => round($precision, 3),
            'performance' => round($performance, 3),
            'metadata' => $metadata,
        ];
    }

    /**
     * @param  array<string, mixed>  $input
     */
    private static function resolveAccuracy(array $input): float
    {
        if (isset($input['accuracy']) && is_numeric($input['accuracy'])) {
            return self::clamp01((float) $input['accuracy']);
        }

        $correct = $input['correctCount'] ?? $input['correct_count'] ?? null;
        $attempts = $input['attemptCount'] ?? $input['attempt_count'] ?? null;
        if (is_numeric($correct) && is_numeric($attempts) && (int) $attempts > 0) {
            $ratio = self::clamp01((float) $correct / (float) $attempts);
            $avgMatch = $input['avgMatchScore'] ?? $input['avg_match_score'] ?? null;
            if (is_numeric($avgMatch) && ($input['subType'] ?? $input['sub_type'] ?? null) === 'speak_back') {
                return self::clamp01($ratio * 0.55 + (float) $avgMatch * 0.45);
            }

            return $ratio;
        }

        $score = $input['score'] ?? null;
        $maxScore = $input['maxScore'] ?? $input['max_score'] ?? null;
        if (is_numeric($score) && is_numeric($maxScore) && (float) $maxScore > 0) {
            return self::clamp01((float) $score / (float) $maxScore);
        }

        return 1.0;
    }

    /**
     * @param  array<string, mixed>  $input
     */
    private static function resolvePrecision(array $input): float
    {
        if (isset($input['precision']) && is_numeric($input['precision'])) {
            return self::clamp01((float) $input['precision']);
        }

        $mistakes = $input['mistakeCount'] ?? $input['mistake_count'] ?? null;
        $attempts = $input['attemptCount'] ?? $input['attempt_count'] ?? null;
        if (is_numeric($mistakes) && is_numeric($attempts) && (int) $attempts > 0) {
            return self::clamp01(1 - ((float) $mistakes / (float) $attempts));
        }

        return 1.0;
    }

    /**
     * @param  array<string, mixed>  $input
     */
    private static function resolveSpeed(array $input): float
    {
        if (isset($input['speed']) && is_numeric($input['speed'])) {
            return self::clamp01((float) $input['speed']);
        }

        $remaining = $input['timeRemainingRatio'] ?? $input['time_remaining_ratio'] ?? null;
        if (is_numeric($remaining)) {
            return self::clamp01((float) $remaining);
        }

        $duration = $input['durationMs'] ?? $input['duration_ms'] ?? null;
        $par = $input['parDurationMs'] ?? $input['par_duration_ms'] ?? null;
        if (is_numeric($duration) && is_numeric($par) && (float) $par > 0) {
            return self::clamp01(min((float) $par / max((float) $duration, 1), 1));
        }

        return 0.85;
    }

    private static function gradeFromPerformance(float $performance): string
    {
        if ($performance >= self::THRESHOLD_GOLD) {
            return 'gold';
        }
        if ($performance >= self::THRESHOLD_SILVER) {
            return 'silver';
        }

        return 'bronze';
    }

    private static function clamp01(float $value): float
    {
        return max(0.0, min(1.0, $value));
    }
}
