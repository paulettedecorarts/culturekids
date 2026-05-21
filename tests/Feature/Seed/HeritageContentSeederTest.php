<?php

namespace Tests\Feature\Seed;

use App\Models\Activity;
use App\Models\ActivityFlashcardSlide;
use App\Models\Comic;
use App\Models\LanguageActivity;
use App\Models\Song;
use App\Models\Tribe;
use App\Services\Seed\HeritageContentSeedImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HeritageContentSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_consolidated_importer_loads_both_seed_files(): void
    {
        $summary = app(HeritageContentSeedImporter::class)->import();

        $this->assertSame(11, $summary['tribes']);
        $this->assertSame(1100, $summary['word_flashcards']['slides']);
        $this->assertSame(11, $summary['word_flashcards']['activities']);
        $this->assertSame(1210, $summary['heritage_activities']['total']);
        $this->assertSame(0, $summary['heritage_activities']['skipped']);

        $this->assertDatabaseCount('tribes', 11);
        $this->assertDatabaseCount('activity_flashcard_slides', 1100);

        $this->assertGreaterThan(0, LanguageActivity::count());
        $this->assertGreaterThan(0, Comic::count());
        $this->assertGreaterThan(0, Song::count());
        $this->assertGreaterThan(0, Activity::query()->where('type', 'puzzle')->count());

        $deck = Activity::query()
            ->where('type', 'flashcard')
            ->where('metadata->seed_deck_key', 'alur')
            ->first();

        $this->assertNotNull($deck);
        $this->assertSame(100, $deck->flashcardSlides()->count());

        $slide = ActivityFlashcardSlide::query()
            ->where('metadata->seed_card_id', 'word_flashcard_0001')
            ->first();

        $this->assertNotNull($slide);
        $this->assertSame('Tik', $slide->front_label);

        $story = Comic::query()->where('metadata->seed_activity_id', 'activity_0091')->first();
        $this->assertNotNull($story);

        $this->assertGreaterThan(
            1200,
            Activity::count(),
            'Mirrored activities should exist for vocab_pack, puzzle, song, maze, etc.'
        );
    }

    public function test_importer_is_idempotent(): void
    {
        $importer = app(HeritageContentSeedImporter::class);

        $importer->import();
        $second = $importer->import();

        $this->assertSame(1100, $second['word_flashcards']['slides']);
        $this->assertDatabaseCount('activity_flashcard_slides', 1100);
        $this->assertDatabaseCount('tribes', 11);
    }
}
