<?php

namespace App\Support;

use App\Models\OrganisationContentDecision;

/**
 * Org-admin sidebar: published library links per content type (mirrors super-admin content nav).
 */
final class CmsAdminContentNav
{
    /** @return list<array{route: string, slug: string, type: string, label: string, icon: string}> */
    public static function items(): array
    {
        return [
            ['route' => 'stories', 'slug' => 'stories', 'type' => OrganisationContentDecision::TYPE_STORY, 'label' => 'Stories', 'icon' => '📖'],
            ['route' => 'songs', 'slug' => 'songs', 'type' => OrganisationContentDecision::TYPE_SONG, 'label' => 'Songs', 'icon' => '🎵'],
            ['route' => 'flashcards', 'slug' => 'flashcards', 'type' => OrganisationContentDecision::TYPE_FLASHCARD, 'label' => 'Flashcards', 'icon' => '🃏'],
            ['route' => 'puzzles', 'slug' => 'puzzles', 'type' => OrganisationContentDecision::TYPE_PUZZLE, 'label' => 'Puzzles', 'icon' => '🧩'],
            ['route' => 'drawings', 'slug' => 'drawings', 'type' => OrganisationContentDecision::TYPE_DRAWING, 'label' => 'Drawings', 'icon' => '🖍'],
            ['route' => 'language-activities', 'slug' => 'language-activities', 'type' => OrganisationContentDecision::TYPE_LANGUAGE, 'label' => 'Language', 'icon' => '📝'],
            ['route' => 'games', 'slug' => 'games', 'type' => OrganisationContentDecision::TYPE_GAME, 'label' => 'Games', 'icon' => '🎮'],
            ['route' => 'mazes', 'slug' => 'mazes', 'type' => OrganisationContentDecision::TYPE_MAZE, 'label' => 'Mazes', 'icon' => '🌀'],
            ['route' => 'spot-differences', 'slug' => 'spot-differences', 'type' => OrganisationContentDecision::TYPE_SPOT_DIFFERENCE, 'label' => 'Spot the Difference', 'icon' => '🔍'],
            ['route' => 'word-searches', 'slug' => 'word-searches', 'type' => OrganisationContentDecision::TYPE_WORD_SEARCH, 'label' => 'Word Searches', 'icon' => '🔤'],
            ['route' => 'culture-activities', 'slug' => 'culture-activities', 'type' => OrganisationContentDecision::TYPE_CULTURE, 'label' => 'Culture', 'icon' => '🏺'],
            ['route' => 'colouring', 'slug' => 'colouring', 'type' => OrganisationContentDecision::TYPE_COLOURING, 'label' => 'Colouring', 'icon' => '🎨'],
        ];
    }

    public static function labelForType(?string $type): string
    {
        if ($type === null || $type === '') {
            return 'Approved Content';
        }

        return OrganisationContentDecision::labelFor($type);
    }
}
