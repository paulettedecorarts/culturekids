<?php

namespace Tests\Unit;

use App\Support\CultureQuizGenerator;
use PHPUnit\Framework\TestCase;

class CultureQuizGeneratorTest extends TestCase
{
    public function test_generates_multiple_choice_questions_for_clan_quiz(): void
    {
        $item = [
            'tribe' => 'Alur',
            'hero' => 'Gipir',
            'heroTitle' => 'Keeper of the Sacred Beads',
            'greeting' => 'Kop Ango?',
            'greetingMeaning' => "What's the news?",
            'language' => 'Dho-Alur',
            'region' => 'West Nile, Northwestern Uganda',
            'sacredAnimal' => 'Nile Crocodile',
            'activityType' => 'Quiz',
            'tag' => 'Clan Quiz',
        ];

        $questions = CultureQuizGenerator::fromHeritageItem($item, null);

        $this->assertGreaterThanOrEqual(5, count($questions));
        $this->assertSame("What is the Alur hero's name?", $questions[0]['question']);
        $this->assertSame('Gipir', $questions[0]['answer']);
        $this->assertCount(4, $questions[0]['options']);
        $this->assertContains($questions[0]['answer'], $questions[0]['options']);
    }
}
