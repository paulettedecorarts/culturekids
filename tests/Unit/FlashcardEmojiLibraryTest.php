<?php

namespace Tests\Unit;

use App\Support\FlashcardEmojiLibrary;
use Tests\TestCase;

class FlashcardEmojiLibraryTest extends TestCase
{
    public function test_bundled_json_loads_with_categories_and_unique_flatten(): void
    {
        $cats = FlashcardEmojiLibrary::categories();
        $this->assertNotEmpty($cats);
        $this->assertIsArray($cats['Smileys & emotion'] ?? null);

        $flat = FlashcardEmojiLibrary::allEmojisFlattened();
        $this->assertGreaterThan(500, count($flat));
        $this->assertSame(count($flat), count(array_unique($flat)));
    }
}
