<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\Seed\HeritageContentSeedImporter;
use Illuminate\Database\Seeder;

/**
 * Master seed — run everything with:
 *
 *   php artisan db:seed
 *
 * Includes:
 * - Roles & permissions
 * - Organisation modules (15)
 * - Super admin login (admin@culturekids.app / password)
 * - Heritage Heroes content from seed/activities.seed.json (1,210 items)
 * - Word flashcards from seed/wordFlashcards.seed.json (1,100 cards → 11 decks)
 * - Comic → activities sync (idempotent)
 *
 * Age profiles & languages are created by migrations on fresh migrate.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command?->info('═══ Culture Kids — full database seed ═══');

        $this->command?->info('→ Roles & permissions');
        $this->call(RoleSeeder::class);

        $this->command?->info('→ Organisation modules');
        $this->call(ModuleSeeder::class);

        $this->command?->info('→ Platform admin user');
        $superAdmin = User::firstOrCreate(
            ['email' => 'admin@culturekids.app'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password'),
            ]
        );
        $superAdmin->assignRole('super_admin');
        $this->command?->info('  admin@culturekids.app / password');

        $this->command?->info('→ Heritage content (activities + word flashcards JSON)');
        $summary = app(HeritageContentSeedImporter::class)->import($this->command);

        $hf = $summary['heritage_activities'];
        $wf = $summary['word_flashcards'];

        $this->command?->info('→ Comic / activities mirror sync');
        $this->call(SyncComicsToActivitiesSeeder::class);

        $this->command?->info('');
        $this->command?->info('═══ Seed complete ═══');
        $this->command?->info("Tribes: {$summary['tribes']}");
        $this->command?->info("Word flashcard decks: {$wf['activities']} ({$wf['slides']} slides)");
        $this->command?->info(sprintf(
            'Heritage activities: %d (language %d, puzzle %d, story %d, culture %d, song %d, maze %d, spot %d, wordsearch %d, drawing %d, game %d, skipped %d)',
            $hf['total'],
            $hf['language'],
            $hf['puzzle'],
            $hf['story'],
            $hf['culture'],
            $hf['song'],
            $hf['maze'],
            $hf['spot_difference'],
            $hf['word_search'],
            $hf['drawing'],
            $hf['game'],
            $hf['skipped']
        ));
    }
}
