<?php

namespace App\Support;

class HeritageTerms
{
    public static function get(string $key, array $replace = []): string
    {
        $text = (string) config("heritage.{$key}", $key);

        foreach ($replace as $name => $value) {
            $text = str_replace(':'.$name, (string) $value, $text);
        }

        return $text;
    }
}
