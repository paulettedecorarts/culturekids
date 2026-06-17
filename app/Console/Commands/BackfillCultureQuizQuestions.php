<?php

namespace App\Console\Commands;

use App\Models\CultureActivity;
use App\Models\Tribe;
use App\Services\Seed\HeritageActivitiesSeedImporter;
use App\Support\CultureQuizGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class BackfillCultureQuizQuestions extends Command
{
    protected $signature = 'culture:backfill-quiz-questions {--dry-run : Show what would change without saving}';

    protected $description = 'Generate quiz_questions for heritage culture activities that were imported without them';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $path = base_path(HeritageActivitiesSeedImporter::JSON_PATH);
        $payload = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);
        $itemsById = collect($payload['activities'] ?? [])
            ->keyBy(fn (array $item) => (string) ($item['id'] ?? ''));

        $updated = 0;

        CultureActivity::query()
            ->where('metadata->seed_source', 'heritage_activities_seed')
            ->orderBy('id')
            ->each(function (CultureActivity $activity) use ($itemsById, $dryRun, &$updated): void {
                $seedId = (string) ($activity->metadata['seed_activity_id'] ?? '');
                $item = $itemsById->get($seedId);

                if (! is_array($item)) {
                    return;
                }

                $type = strtolower((string) ($item['activityType'] ?? ''));
                $tag = strtolower((string) ($item['tag'] ?? ''));
                $isQuiz = $type === 'quiz'
                    || str_contains($tag, 'quiz')
                    || str_contains($tag, 'graduation');

                if (! $isQuiz) {
                    return;
                }

                if (! empty($activity->quiz_questions)) {
                    return;
                }

                $tribe = Tribe::query()->find($activity->tribe_id);
                $questions = CultureQuizGenerator::fromHeritageItem($item, $tribe);

                if ($questions === []) {
                    return;
                }

                $this->line(sprintf(
                    '%s %s — %d questions',
                    $dryRun ? '[dry-run]' : '[update]',
                    $activity->title,
                    count($questions),
                ));

                if (! $dryRun) {
                    $activity->quiz_questions = $questions;
                    $activity->save();
                }

                $updated++;
            });

        $this->info($dryRun
            ? "Would update {$updated} culture activit(ies)."
            : "Updated {$updated} culture activit(ies).");

        return self::SUCCESS;
    }
}
