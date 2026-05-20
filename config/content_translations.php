<?php

use App\Models\Activity;
use App\Models\Comic;
use App\Models\CultureActivity;
use App\Models\Drawing;
use App\Models\Game;
use App\Models\LanguageActivity;
use App\Models\Maze;
use App\Models\OrganisationContentDecision;
use App\Models\Song;
use App\Models\SpotDifference;
use App\Models\WordSearch;

return [

    /*
    |--------------------------------------------------------------------------
    | Translatable content types (12 activity modules)
    |--------------------------------------------------------------------------
    */

    'types' => [
        OrganisationContentDecision::TYPE_STORY => [
            'label' => 'Story',
            'model' => Comic::class,
            'title_column' => 'title',
            'sub_items' => 'panels',
            'has_hotspot' => true,
        ],
        OrganisationContentDecision::TYPE_SONG => [
            'label' => 'Song',
            'model' => Song::class,
            'title_column' => 'title',
            'sub_items' => 'lyric_segments',
        ],
        OrganisationContentDecision::TYPE_FLASHCARD => [
            'label' => 'Flashcard',
            'model' => Activity::class,
            'title_column' => 'title',
            'sub_items' => 'flashcard_slides',
            'query' => ['type' => 'flashcard'],
        ],
        OrganisationContentDecision::TYPE_PUZZLE => [
            'label' => 'Puzzle',
            'model' => Activity::class,
            'title_column' => 'title',
            'sub_items' => 'content_fields',
            'query' => ['type' => 'puzzle'],
        ],
        OrganisationContentDecision::TYPE_DRAWING => [
            'label' => 'Drawing',
            'model' => Drawing::class,
            'title_column' => 'title',
            'sub_items' => 'drawing_fields',
            'query_scope' => 'drawing_exclude_coloring',
        ],
        OrganisationContentDecision::TYPE_COLOURING => [
            'label' => 'Colouring',
            'model' => Drawing::class,
            'title_column' => 'title',
            'sub_items' => 'drawing_fields',
            'query_scope' => 'drawing_coloring_only',
        ],
        OrganisationContentDecision::TYPE_LANGUAGE => [
            'label' => 'Language activity',
            'model' => LanguageActivity::class,
            'title_column' => 'title',
            'sub_items' => 'language_words',
        ],
        OrganisationContentDecision::TYPE_GAME => [
            'label' => 'Game',
            'model' => Game::class,
            'title_column' => 'title',
            'sub_items' => 'game_questions',
        ],
        OrganisationContentDecision::TYPE_MAZE => [
            'label' => 'Maze',
            'model' => Maze::class,
            'title_column' => 'title',
            'sub_items' => 'maze_items',
        ],
        OrganisationContentDecision::TYPE_SPOT_DIFFERENCE => [
            'label' => 'Spot the difference',
            'model' => SpotDifference::class,
            'title_column' => 'title',
            'sub_items' => 'spot_zones',
        ],
        OrganisationContentDecision::TYPE_WORD_SEARCH => [
            'label' => 'Word search',
            'model' => WordSearch::class,
            'title_column' => 'title',
            'sub_items' => 'word_search_words',
        ],
        OrganisationContentDecision::TYPE_CULTURE => [
            'label' => 'Culture activity',
            'model' => CultureActivity::class,
            'title_column' => 'title',
            'sub_items' => 'culture_fields',
        ],
    ],

];
