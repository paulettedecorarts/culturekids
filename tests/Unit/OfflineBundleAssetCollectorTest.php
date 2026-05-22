<?php

namespace Tests\Unit;

use App\Support\OfflineBundle\OfflineBundleAssetCollector;
use PHPUnit\Framework\TestCase;

class OfflineBundleAssetCollectorTest extends TestCase
{
    public function test_collects_explicit_and_nested_paths(): void
    {
        $collector = new OfflineBundleAssetCollector;

        $paths = $collector->collect([
            'cover_image_path' => 'covers/hero.png',
            'metadata' => [
                'print_path' => 'prints/sheet.pdf',
                'ignored_url' => 'https://cdn.example.com/x.png',
            ],
        ], ['panels/1.jpg']);

        $this->assertContains('covers/hero.png', $paths);
        $this->assertContains('prints/sheet.pdf', $paths);
        $this->assertContains('panels/1.jpg', $paths);
        $this->assertNotContains('https://cdn.example.com/x.png', $paths);
    }

    public function test_ignores_plain_text_and_emoji_values(): void
    {
        $collector = new OfflineBundleAssetCollector;

        $paths = $collector->collect([
            'title' => 'Mission: The Culture Guardian',
            'description' => '👨‍👩‍👧',
            'image_path' => 'activities/card.png',
            'slides' => [
                ['caption' => '👨‍👩‍👧', 'image_path' => 'slides/one.jpg'],
            ],
        ]);

        $this->assertSame(['activities/card.png', 'slides/one.jpg'], $paths);
    }

    public function test_ignores_directory_paths_without_file_extension(): void
    {
        $collector = new OfflineBundleAssetCollector;

        $paths = $collector->collect([
            'map_image_path' => 'culture/maps',
            'cover_image_path' => 'culture/covers/hero.png',
        ]);

        $this->assertSame(['culture/covers/hero.png'], $paths);
    }
}
