<?php

namespace Database\Seeders;

use App\Services\Seed\HeritageContentSeedImporter;
use Illuminate\Database\Seeder;

/**
 * Heritage-only seed (also runs from DatabaseSeeder).
 *
 *   php artisan db:seed --class=HeritageContentSeeder
 */
class HeritageContentSeeder extends Seeder
{
    public function run(): void
    {
        $summary = app(HeritageContentSeedImporter::class)->import($this->command);

        $hf = $summary['heritage_activities'];
        $wf = $summary['word_flashcards'];

        $this->command?->info("Tribes: {$summary['tribes']}");
        $this->command?->info("Word flashcard decks: {$wf['activities']} ({$wf['slides']} slides)");
        $this->command?->info("Heritage activities: {$hf['total']} (skipped {$hf['skipped']})");
    }
}
