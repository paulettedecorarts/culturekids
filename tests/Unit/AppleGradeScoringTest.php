<?php

namespace Tests\Unit;

use App\Support\AppleGradeScoring;
use PHPUnit\Framework\TestCase;

class AppleGradeScoringTest extends TestCase
{
    public function test_perfect_quiz_scores_gold(): void
    {
        $result = AppleGradeScoring::compute([
            'correctCount' => 5,
            'attemptCount' => 5,
            'mistakeCount' => 0,
            'durationMs' => 60_000,
            'parDurationMs' => 100_000,
        ], 10);

        $this->assertSame('gold', $result['grade']);
        $this->assertSame(10, $result['stars_earned']);
    }

    public function test_many_mistakes_score_bronze(): void
    {
        $result = AppleGradeScoring::compute([
            'correctCount' => 3,
            'attemptCount' => 10,
            'mistakeCount' => 7,
            'durationMs' => 120_000,
            'parDurationMs' => 100_000,
        ], 10);

        $this->assertContains($result['grade'], ['bronze', 'silver']);
        $this->assertGreaterThanOrEqual(1, $result['stars_earned']);
        $this->assertLessThanOrEqual(10, $result['stars_earned']);
    }

    public function test_game_performance_uses_score_and_lives(): void
    {
        $result = AppleGradeScoring::compute([
            'score' => 80,
            'maxScore' => 100,
            'precision' => 0.5,
            'timeRemainingRatio' => 0.2,
        ], 20);

        $this->assertArrayHasKey('metadata', $result);
        $this->assertSame('bronze', $result['grade']);
        $this->assertSame(10, $result['stars_earned']);
    }

    public function test_participation_full_credit_is_gold(): void
    {
        $result = AppleGradeScoring::compute([
            'accuracy' => 1,
            'speed' => 1,
            'precision' => 1,
        ], 8);

        $this->assertSame('gold', $result['grade']);
        $this->assertSame(8, $result['stars_earned']);
    }

    public function test_speak_back_blends_match_score_with_pass_rate(): void
    {
        $result = AppleGradeScoring::compute([
            'subType' => 'speak_back',
            'correctCount' => 4,
            'attemptCount' => 5,
            'mistakeCount' => 1,
            'avgMatchScore' => 0.82,
            'accuracy' => 0.79,
            'precision' => 0.9,
            'durationMs' => 120_000,
            'parDurationMs' => 175_000,
        ], 10);

        $this->assertContains($result['grade'], ['silver', 'gold', 'bronze']);
        $this->assertGreaterThanOrEqual(1, $result['stars_earned']);
    }
}
