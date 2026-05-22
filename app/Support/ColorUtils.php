<?php

namespace App\Support;

class ColorUtils
{
    /**
     * Lighten or darken a hex colour. Positive $percent lightens toward white; negative darkens toward black.
     */
    public static function adjust(string $hex, float $percent): string
    {
        $hex = ltrim(trim($hex), '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        if (strlen($hex) !== 6 || ! ctype_xdigit($hex)) {
            return '#'.strtoupper($hex ?: '000000');
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        $factor = $percent >= 0
            ? $percent / 100
            : abs($percent) / 100;

        if ($percent >= 0) {
            $r = (int) round($r + (255 - $r) * $factor);
            $g = (int) round($g + (255 - $g) * $factor);
            $b = (int) round($b + (255 - $b) * $factor);
        } else {
            $r = (int) round($r * (1 - $factor));
            $g = (int) round($g * (1 - $factor));
            $b = (int) round($b * (1 - $factor));
        }

        return sprintf('#%02X%02X%02X', min(255, max(0, $r)), min(255, max(0, $g)), min(255, max(0, $b)));
    }

    public static function lighten(string $hex, float $percent): string
    {
        return self::adjust($hex, abs($percent));
    }

    public static function darken(string $hex, float $percent): string
    {
        return self::adjust($hex, -abs($percent));
    }

    /**
     * Mix two hex colours. $weight is the proportion of $hex2 (0–100).
     */
    public static function mix(string $hex1, string $hex2, float $weight = 50): string
    {
        $hex1 = ltrim(trim($hex1), '#');
        $hex2 = ltrim(trim($hex2), '#');

        if (strlen($hex1) === 3) {
            $hex1 = $hex1[0].$hex1[0].$hex1[1].$hex1[1].$hex1[2].$hex1[2];
        }
        if (strlen($hex2) === 3) {
            $hex2 = $hex2[0].$hex2[0].$hex2[1].$hex2[1].$hex2[2].$hex2[2];
        }

        $w = max(0, min(100, $weight)) / 100;
        $r1 = hexdec(substr($hex1, 0, 2));
        $g1 = hexdec(substr($hex1, 2, 2));
        $b1 = hexdec(substr($hex1, 4, 2));
        $r2 = hexdec(substr($hex2, 0, 2));
        $g2 = hexdec(substr($hex2, 2, 2));
        $b2 = hexdec(substr($hex2, 4, 2));

        $r = (int) round($r1 * (1 - $w) + $r2 * $w);
        $g = (int) round($g1 * (1 - $w) + $g2 * $w);
        $b = (int) round($b1 * (1 - $w) + $b2 * $w);

        return sprintf('#%02X%02X%02X', $r, $g, $b);
    }
}
