<?php

use App\Models\OrganisationContentDecision;

return [

    /*
    |--------------------------------------------------------------------------
    | Canonical organisation modules (feature flags)
    |--------------------------------------------------------------------------
    | Keys match OrganisationContentDecision types where applicable.
    | Add new modules here when shipping a new activity — not via admin forms.
    */

    'definitions' => [
        [
            'key' => 'stories',
            'name' => 'Stories',
            'description' => 'Panel stories and comic reader',
            'icon' => '📖',
            'sort_order' => 10,
        ],
        [
            'key' => 'songs',
            'name' => 'Songs & Audio',
            'description' => 'Music and pronunciation',
            'icon' => '🎵',
            'sort_order' => 20,
        ],
        [
            'key' => 'flashcards',
            'name' => 'Flashcards',
            'description' => 'Vocabulary and language cards',
            'icon' => '🃏',
            'sort_order' => 30,
        ],
        [
            'key' => 'puzzles',
            'name' => 'Puzzles',
            'description' => 'Printable and interactive puzzles',
            'icon' => '🧩',
            'sort_order' => 40,
        ],
        [
            'key' => 'drawings',
            'name' => 'Drawings',
            'description' => 'Drawing kits and creative templates',
            'icon' => '🖍️',
            'sort_order' => 50,
        ],
        [
            'key' => 'colouring',
            'name' => 'Colouring',
            'description' => 'Colouring pages and templates',
            'icon' => '🎨',
            'sort_order' => 60,
        ],
        [
            'key' => 'language_activities',
            'name' => 'Language Activities',
            'description' => 'Word trace, audio match, and language games',
            'icon' => '📝',
            'sort_order' => 70,
        ],
        [
            'key' => 'games',
            'name' => 'Games',
            'description' => 'Interactive quiz and game activities',
            'icon' => '🎮',
            'sort_order' => 80,
        ],
        [
            'key' => 'mazes',
            'name' => 'Mazes',
            'description' => 'Maze path activities',
            'icon' => '🌀',
            'sort_order' => 90,
        ],
        [
            'key' => 'spot_difference',
            'name' => 'Spot the Difference',
            'description' => 'Visual comparison activities',
            'icon' => '🔍',
            'sort_order' => 100,
        ],
        [
            'key' => 'word_searches',
            'name' => 'Word Searches',
            'description' => 'Word search puzzles',
            'icon' => '🔤',
            'sort_order' => 110,
        ],
        [
            'key' => 'culture_activities',
            'name' => 'Culture Activities',
            'description' => 'Heritage and cultural learning',
            'icon' => '🏺',
            'sort_order' => 120,
        ],
        [
            'key' => 'offline_bundles',
            'name' => 'Offline Bundles',
            'description' => '.ckb download system',
            'icon' => '📦',
            'sort_order' => 200,
        ],
        [
            'key' => 'theme_engine',
            'name' => 'Theme Engine',
            'description' => 'Organisation branding override',
            'icon' => '🎨',
            'sort_order' => 210,
        ],
        [
            'key' => 'kiosk_mode',
            'name' => 'Kiosk Mode',
            'description' => 'Classroom tablets',
            'icon' => '🖥️',
            'sort_order' => 220,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Org review content_type → module key
    |--------------------------------------------------------------------------
    */

    'content_types' => [
        OrganisationContentDecision::TYPE_STORY => 'stories',
        OrganisationContentDecision::TYPE_SONG => 'songs',
        OrganisationContentDecision::TYPE_FLASHCARD => 'flashcards',
        OrganisationContentDecision::TYPE_PUZZLE => 'puzzles',
        OrganisationContentDecision::TYPE_DRAWING => 'drawings',
        OrganisationContentDecision::TYPE_COLOURING => 'colouring',
        OrganisationContentDecision::TYPE_LANGUAGE => 'language_activities',
        OrganisationContentDecision::TYPE_GAME => 'games',
        OrganisationContentDecision::TYPE_MAZE => 'mazes',
        OrganisationContentDecision::TYPE_SPOT_DIFFERENCE => 'spot_difference',
        OrganisationContentDecision::TYPE_WORD_SEARCH => 'word_searches',
        OrganisationContentDecision::TYPE_CULTURE => 'culture_activities',
    ],

    /*
    |--------------------------------------------------------------------------
    | activities.type → module key
    |--------------------------------------------------------------------------
    */

    'activity_types' => [
        'story' => 'stories',
        'song' => 'songs',
        'flashcard' => 'flashcards',
        'puzzle' => 'puzzles',
        'drawing_kit' => 'drawings',
        'vocab_pack' => 'language_activities',
        'game' => 'games',
        'maze' => 'mazes',
        'spot_difference' => 'spot_difference',
        'word_search' => 'word_searches',
        'culture' => 'culture_activities',
    ],

    /*
    |--------------------------------------------------------------------------
    | age_profiles.content_access_rules.modules → organisation module key
    |--------------------------------------------------------------------------
    */

    'age_profile_modules' => [
        'stories' => 'stories',
        'songs' => 'songs',
        'flashcard' => 'flashcards',
        'vocab_pack' => 'language_activities',
        'puzzle' => 'puzzles',
        'worksheet' => 'puzzles',
        'game' => 'games',
    ],

    /*
    |--------------------------------------------------------------------------
    | age_profiles.content_access_rules.modules → activities.type (where applicable)
    |--------------------------------------------------------------------------
    | Stories use the comics API, not the activities table — leave stories => [].
    */

    'age_profile_activity_types' => [
        'stories' => [],
        'songs' => ['song'],
        'flashcard' => ['flashcard'],
        'vocab_pack' => ['vocab_pack'],
        'puzzle' => ['puzzle'],
        'worksheet' => ['puzzle'],
        'game' => ['game'],
    ],

];
