<?php

use App\Support\HeritageTerms;

if (! function_exists('heritage')) {
    /**
     * User-facing label (e.g. people instead of tribe in UI).
     */
    function heritage(string $key, array $replace = []): string
    {
        return HeritageTerms::get($key, $replace);
    }
}
