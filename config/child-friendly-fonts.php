<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default homepage font keys (stored in platform_landing_settings)
    |--------------------------------------------------------------------------
    */
    'defaults' => [
        'heading' => 'baloo_2',
        'body' => 'nunito',
    ],

    /*
    |--------------------------------------------------------------------------
    | Curated Google Fonts for children's content
    |--------------------------------------------------------------------------
    |
    | key       — stable identifier saved in settings
    | label     — shown in the homepage editor
    | family    — CSS font-family name
    | google    — Google Fonts CSS2 family slug
    | roles     — heading, body, or both
    | fallback  — generic fallback stack suffix
    | weights   — requested font weights for Google Fonts
    */
    'fonts' => [
        'baloo_2' => [
            'label' => 'Baloo 2',
            'family' => 'Baloo 2',
            'google' => 'Baloo+2',
            'roles' => ['heading', 'body'],
            'fallback' => 'cursive',
            'weights' => '400;600;700;800',
        ],
        'fredoka' => [
            'label' => 'Fredoka',
            'family' => 'Fredoka',
            'google' => 'Fredoka',
            'roles' => ['heading', 'body'],
            'fallback' => 'sans-serif',
            'weights' => '400;500;600;700',
        ],
        'bubblegum_sans' => [
            'label' => 'Bubblegum Sans',
            'family' => 'Bubblegum Sans',
            'google' => 'Bubblegum+Sans',
            'roles' => ['heading'],
            'fallback' => 'cursive',
            'weights' => '400',
        ],
        'chewy' => [
            'label' => 'Chewy',
            'family' => 'Chewy',
            'google' => 'Chewy',
            'roles' => ['heading'],
            'fallback' => 'cursive',
            'weights' => '400',
        ],
        'comic_neue' => [
            'label' => 'Comic Neue',
            'family' => 'Comic Neue',
            'google' => 'Comic+Neue',
            'roles' => ['heading', 'body'],
            'fallback' => 'cursive',
            'weights' => '400;700',
        ],
        'luckiest_guy' => [
            'label' => 'Luckiest Guy',
            'family' => 'Luckiest Guy',
            'google' => 'Luckiest+Guy',
            'roles' => ['heading'],
            'fallback' => 'cursive',
            'weights' => '400',
        ],
        'nunito' => [
            'label' => 'Nunito',
            'family' => 'Nunito',
            'google' => 'Nunito',
            'roles' => ['body', 'heading'],
            'fallback' => 'sans-serif',
            'weights' => '400;600;700;800',
        ],
        'quicksand' => [
            'label' => 'Quicksand',
            'family' => 'Quicksand',
            'google' => 'Quicksand',
            'roles' => ['body', 'heading'],
            'fallback' => 'sans-serif',
            'weights' => '400;500;600;700',
        ],
        'atkinson_hyperlegible' => [
            'label' => 'Atkinson Hyperlegible',
            'family' => 'Atkinson Hyperlegible',
            'google' => 'Atkinson+Hyperlegible',
            'roles' => ['body'],
            'fallback' => 'sans-serif',
            'weights' => '400;700',
        ],
        'lexend' => [
            'label' => 'Lexend',
            'family' => 'Lexend',
            'google' => 'Lexend',
            'roles' => ['body'],
            'fallback' => 'sans-serif',
            'weights' => '400;600;700',
        ],
        'comfortaa' => [
            'label' => 'Comfortaa',
            'family' => 'Comfortaa',
            'google' => 'Comfortaa',
            'roles' => ['body', 'heading'],
            'fallback' => 'sans-serif',
            'weights' => '400;600;700',
        ],
        'sniglet' => [
            'label' => 'Sniglet',
            'family' => 'Sniglet',
            'google' => 'Sniglet',
            'roles' => ['heading'],
            'fallback' => 'cursive',
            'weights' => '400;800',
        ],
    ],

];
