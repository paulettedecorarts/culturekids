<?php

namespace App\Console\Commands;

use App\Models\Activity;
use App\Models\Comic;
use App\Models\ComicPanel;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateStoriesToComics extends Command
{
    protected $signature = 'stories:migrate';

    protected $description = 'Migrate stories from activities table to comics structure';

    public function handle()
    {
        $this->info('Starting migration of stories from activities to comics...');

        $stories = Activity::where('type', 'story')->get();

        if ($stories->isEmpty()) {
            $this->warn('No stories found in activities table.');

            return;
        }

        $this->info("Found {$stories->count()} stories to migrate.");

        $bar = $this->output->createProgressBar($stories->count());
        $bar->start();

        $migrated = 0;
        $errors = 0;

        foreach ($stories as $story) {
            try {
                DB::transaction(function () use ($story, &$migrated) {
                    // Parse age range (e.g., "2-3" to age_min=2, age_max=3)
                    $ageRange = explode('-', $story->age_range ?? '3-4');
                    $ageMin = (int) ($ageRange[0] ?? 3);
                    $ageMax = (int) ($ageRange[1] ?? 4);

                    // Create comic
                    $comic = Comic::create([
                        'tribe_id' => $story->tribe_id,
                        'title' => $story->title,
                        'description' => $story->description,
                        'age_min' => $ageMin,
                        'age_max' => $ageMax,
                        'status' => $story->is_published ? 'published' : 'draft',
                        'cover_image_path' => $story->metadata['cover'] ?? null,
                        'star_points' => $story->star_points ?? 10,
                        'created_at' => $story->created_at,
                        'updated_at' => $story->updated_at,
                    ]);

                    // Migrate panels
                    if (isset($story->metadata['panels']) && is_array($story->metadata['panels'])) {
                        foreach ($story->metadata['panels'] as $index => $panelPath) {
                            ComicPanel::create([
                                'comic_id' => $comic->id,
                                'order_index' => $index,
                                'image_path' => $panelPath,
                                'audio_url' => null, // Old system didn't have per-panel audio
                            ]);
                        }
                    }

                    $migrated++;
                });
            } catch (\Exception $e) {
                $errors++;
                $this->error("\nError migrating story '{$story->title}': ".$e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info('Migration complete!');
        $this->info("✅ Successfully migrated: {$migrated} stories");

        if ($errors > 0) {
            $this->warn("⚠️  Errors: {$errors} stories");
        }

        $this->newLine();
        $this->comment('Note: Original stories in activities table are preserved.');
        $this->comment('You can delete them manually if migration was successful.');

        return 0;
    }
}
