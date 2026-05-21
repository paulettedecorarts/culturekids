<?php

namespace App\Services\Seed;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Consolidated importer for both Heritage seed files:
 * - seed/activities.seed.json (1,210 activities → domain models + activities mirror)
 * - seed/wordFlashcards.seed.json (1,100 cards → 11 flashcard decks + slides)
 */
class HeritageContentSeedImporter
{
    /**
     * @return array<string, mixed>
     */
    public function import(?Command $command = null): array
    {
        $command?->info('═══ Heritage Content Seed ═══');
        $command?->info('Step 1/3: Tribes & clans (from activities.seed.json)');

        $tribeMap = app(HeritageTribeUpserter::class)->upsertFromActivitiesJson();
        $command?->info('  '.count($tribeMap).' tribes ready.');

        $command?->info('Step 2/3: Word flashcards (wordFlashcards.seed.json)');
        $flashcardStats = app(WordFlashcardSeedImporter::class)->import($command, $tribeMap);

        $command?->info('Step 3/3: Heritage activities (activities.seed.json)');
        $activityStats = DB::transaction(
            fn () => app(HeritageActivitiesSeedImporter::class)->import($tribeMap, $command)
        );

        return [
            'tribes' => count($tribeMap),
            'word_flashcards' => $flashcardStats,
            'heritage_activities' => $activityStats,
        ];
    }
}
