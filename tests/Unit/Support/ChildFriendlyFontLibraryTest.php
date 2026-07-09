<?php

namespace Tests\Unit\Support;

use App\Support\ChildFriendlyFontLibrary;
use Tests\TestCase;

class ChildFriendlyFontLibraryTest extends TestCase
{
  public function test_it_resolves_legacy_family_names_to_keys(): void
    {
        $fonts = app(ChildFriendlyFontLibrary::class);

        $this->assertSame('baloo_2', $fonts->resolveKey('Baloo 2', 'heading'));
        $this->assertSame('nunito', $fonts->resolveKey('Inter', 'body'));
    }

    public function test_it_builds_google_fonts_stylesheet_url(): void
    {
        $fonts = app(ChildFriendlyFontLibrary::class);

        $url = $fonts->googleFontsStylesheetUrl(['baloo_2', 'nunito']);

        $this->assertStringStartsWith('https://fonts.googleapis.com/css2?', $url);
        $this->assertStringContainsString('family=Baloo+2:wght@', $url);
        $this->assertStringContainsString('family=Nunito:wght@', $url);
    }

    public function test_it_returns_role_specific_font_options(): void
    {
        $fonts = app(ChildFriendlyFontLibrary::class);

        $headingFonts = $fonts->forRole('heading');
        $bodyFonts = $fonts->forRole('body');

        $this->assertArrayHasKey('baloo_2', $headingFonts);
        $this->assertArrayHasKey('nunito', $bodyFonts);
        $this->assertArrayNotHasKey('luckiest_guy', $bodyFonts);
    }
}
