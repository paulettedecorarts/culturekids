<?php

namespace App\Support;

/**
 * Content-production activity links for super-admin and CMS editor sidebars.
 */
final class CmsProductionActivityNav
{
    /** @return list<array{route: string, label: string, icon: string}> */
    public static function items(): array
    {
        return [
            ['route' => 'drawings', 'label' => 'Drawings', 'icon' => '🖍'],
            ['route' => 'colouring', 'label' => 'Colouring', 'icon' => '🎨'],
            ['route' => 'games', 'label' => 'Games', 'icon' => '🎮'],
            ['route' => 'culture-activities', 'label' => 'Culture', 'icon' => '🏺'],
            ['route' => 'language-activities', 'label' => 'Language', 'icon' => '📝'],
            ['route' => 'mazes', 'label' => 'Mazes', 'icon' => '🌀'],
            ['route' => 'spot-differences', 'label' => 'Spot the Difference', 'icon' => '🔍'],
            ['route' => 'word-searches', 'label' => 'Word Searches', 'icon' => '🔤'],
        ];
    }
}
