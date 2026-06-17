<?php

namespace App\Support;

use App\Models\Tribe;

/**
 * Builds playable quiz_questions for heritage culture activities (mirrors the HTML prototype).
 */
final class CultureQuizGenerator
{
    /**
     * @param  array<string, mixed>  $item  Heritage seed activity row
     * @return list<array{question: string, answer: string, options: list<string>}>
     */
    public static function fromHeritageItem(array $item, ?Tribe $tribe = null): array
    {
        $tag = strtolower((string) ($item['tag'] ?? ''));
        $isGraduation = str_contains($tag, 'grad');
        $isClanQuiz = str_contains($tag, 'clan quiz');

        $tribeName = (string) ($item['tribe'] ?? $tribe?->name ?? 'Heritage');
        $hero = (string) ($item['hero'] ?? $tribe?->hero_name ?? 'Hero');
        $heroFirst = explode(' ', trim($hero))[0] ?: 'Hero';
        $heroTitle = (string) ($item['heroTitle'] ?? 'Heritage Hero');
        $greeting = (string) ($item['greeting'] ?? $tribe?->greeting ?? '');
        $meaning = (string) ($item['greetingMeaning'] ?? '');
        $language = (string) ($item['language'] ?? '');
        $region = (string) ($item['region'] ?? $tribe?->region ?? 'Uganda');
        $regionFirst = trim(explode(',', $region)[0] ?: $region);
        $animal = (string) ($item['sacredAnimal'] ?? 'Lion');

        $clans = $tribe
            ? $tribe->clans()->orderBy('sort_order')->pluck('name')->all()
            : [];
        $clanA = $clans[0] ?? 'Kaal';
        $clanB = $clans[1] ?? 'Gora';

        $pool = [
            self::mcq("What is the {$tribeName} hero's name?", [$heroFirst, $clanA, 'Kintu', 'Kaboyo'], 0),
            self::mcq(
                $greeting !== '' ? "What does \"{$greeting}\" mean?" : "What does the {$tribeName} greeting mean?",
                array_values(array_filter([$meaning, 'Good night', 'Farewell', 'Come here'])),
                0,
            ),
            self::mcq("Which region do the {$tribeName} people live in?", [$regionFirst, 'Kampala', 'Nairobi', 'Mombasa'], 0),
            self::mcq("What language do the {$tribeName} speak?", [$language ?: 'Local language', 'Luganda', 'Kiswahili', 'French'], 0),
            self::mcq("Which is a {$tribeName} clan?", [$clanA, $clanB, 'Nkima', 'Payira'], 0),
            self::mcq("What is a sacred animal for the {$tribeName}?", [$animal, 'Elephant', 'Giraffe', 'Rhino'], 0),
            self::mcq(
                $greeting !== '' ? "Complete: \"{$greeting}\" means ___" : "What does the {$tribeName} greeting mean?",
                array_values(array_filter([$meaning, 'Goodbye', 'Sleep well', 'Thank you'])),
                0,
            ),
            self::mcq("How many clans does {$tribeName} have?", [string(max(count($clans), 5)), '2', '10', '20'], 0),
            self::mcq("Who is the {$tribeName} hero's title?", [$heroTitle, $clanA, 'Elder', 'Chief'], 0),
        ];

        if ($isClanQuiz) {
            foreach (array_slice($clans, 0, 5) as $clan) {
                $pool[] = self::mcq(
                    "Is {$clan} one of the {$tribeName} clans?",
                    ['Yes', 'No', 'Maybe', 'Not sure'],
                    0,
                );
            }
        }

        $limit = $isGraduation ? 5 : ($isClanQuiz ? 10 : 5);

        return array_slice($pool, 0, min($limit, count($pool)));
    }

    /**
     * @param  list<string>  $options
     * @return array{question: string, answer: string, options: list<string>}
     */
    private static function mcq(string $question, array $options, int $correctIndex): array
    {
        $options = array_values(array_unique(array_filter(array_map('strval', $options))));
        while (count($options) < 4) {
            $options[] = 'Option '.(count($options) + 1);
        }
        $options = array_slice($options, 0, 4);

        $correctIndex = min($correctIndex, count($options) - 1);
        $answer = $options[$correctIndex];

        shuffle($options);

        return [
            'question' => $question,
            'answer' => $answer,
            'options' => $options,
        ];
    }
}
