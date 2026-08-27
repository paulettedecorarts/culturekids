<?php

namespace App\Support;

class EmojiSkinToneFixer
{
    private const MED_DARK = "\u{1F3FE}"; // 🏾

    // Single-codepoint person/hand emoji that support a skin-tone modifier.
    // Deliberately excludes anything already joined with ZWJ (families,
    // professions, couples) — those need per-component handling, not a
    // blanket append, so this only touches simple standalone emoji.
    private const BASES = [
        "\u{1F476}","\u{1F9D2}","\u{1F466}","\u{1F467}","\u{1F9D1}","\u{1F471}",
        "\u{1F468}","\u{1F469}","\u{1F9D3}","\u{1F474}","\u{1F475}",
        "\u{1F91A}","\u{1F590}","\u{270B}","\u{1F596}","\u{1F44C}","\u{1F90C}",
        "\u{1F90F}","\u{270C}","\u{1F91E}","\u{1F919}","\u{1F918}","\u{1F919}",
        "\u{1F448}","\u{1F449}","\u{1F446}","\u{1F595}","\u{1F447}","\u{261D}",
        "\u{1F44D}","\u{1F44E}","\u{270A}","\u{1F44A}","\u{1F91B}","\u{1F91C}",
        "\u{1F44F}","\u{1F64C}","\u{1F450}","\u{1F932}","\u{1F64F}","\u{270D}",
        "\u{1F485}","\u{1F933}","\u{1F4AA}","\u{1F9B5}","\u{1F9B6}","\u{1F442}",
        "\u{1F443}","\u{1F930}","\u{1F931}","\u{1F47C}","\u{1F385}","\u{1F57A}",
        "\u{1F483}","\u{1F6C0}","\u{1F6CC}",
    ];

    public static function fix(?string $text): ?string
    {
        if ($text === null || $text === '') {
            return $text;
        }

        // Skip anything that already has a ZWJ (complex sequence) or already
        // has a skin-tone modifier, to avoid double-applying or corrupting it.
        if (str_contains($text, "\u{200D}") || preg_match('/[\x{1F3FB}-\x{1F3FF}]/u', $text)) {
            return $text;
        }

        foreach (self::BASES as $base) {
            if ($text === $base) {
                return $base . self::MED_DARK;
            }
            // handle base + variation selector (FE0F) too
            if ($text === $base . "\u{FE0F}") {
                return $base . self::MED_DARK;
            }
        }

        return $text;
    }
}
