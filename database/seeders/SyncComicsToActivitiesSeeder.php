<?php

namespace Database\Seeders;

use App\Models\Comic;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Backfills the activities table with entries for all existing comics.
 *
 * This seeder is idempotent — safe to run multiple times.
 * It uses upsert logic: existing activity rows are updated, new ones are inserted.
 *
 * Run once during production deployment after the Comic::booted() sync was added.
 * Future comics sync automatically via the Comic model's saved/deleted events.
 */
class SyncComicsToActivitiesSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Syncing comics to activities table...');

        $total   = Comic::count();
        $synced  = 0;
        $skipped = 0;

        Comic::with('tribe')
            ->orderBy('id')
            ->chunk(100, function ($comics) use (&$synced, &$skipped) {
                foreach ($comics as $comic) {
                    try {
                        $this->syncComic($comic);
                        $synced++;
                    } catch (\Throwable $e) {
                        Log::warning('SyncComicsToActivitiesSeeder: failed to sync comic', [
                            'comic_id' => $comic->id,
                            'title'    => $comic->title,
                            'error'    => $e->getMessage(),
                        ]);
                        $skipped++;
                    }
                }
            });

        $this->command->info("Done. Synced: {$synced}, Skipped: {$skipped}, Total: {$total}");
    }

    protected function syncComic(Comic $comic): void
    {
        $metadata = array_merge($comic->metadata ?? [], [
            'source'   => 'comic_mirror',
            'comic_id' => $comic->id,
        ]);

        // Check if an activity entry already exists for this comic
        $existing = DB::table('activities')
            ->where('type', 'story')
            ->where(function ($q) use ($comic): void {
                $q->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.comic_id')) = ?", [$comic->id])
                  ->orWhere(function ($f) use ($comic): void {
                      $f->where('tribe_id', $comic->tribe_id)
                        ->where('title', $comic->title);
                  });
            })
            ->orderByDesc('id')
            ->first();

        $payload = [
            'tribe_id'     => $comic->tribe_id,
            'type'         => 'story',
            'title'        => $comic->title,
            'description'  => $comic->description,
            'age_range'    => "{$comic->age_min}-{$comic->age_max}",
            'star_points'  => $comic->star_points,
            'metadata'     => json_encode($metadata),
            'is_published' => $comic->status === 'published',
            'updated_at'   => now(),
        ];

        if ($existing) {
            DB::table('activities')
                ->where('id', $existing->id)
                ->update($payload);
        } else {
            $payload['created_at'] = now();
            DB::table('activities')->insert($payload);
        }
    }
}
