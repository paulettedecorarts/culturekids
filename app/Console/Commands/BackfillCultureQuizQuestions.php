<?php

namespace App\Console\Commands;

use App\Models\CultureActivity;
use App\Support\CultureQuizGenerator;
use Illuminate\Console\Command;

class BackfillCultureQuizQuestions extends Command
{
    protected $signature = 'culture:backfill-quiz-questions {--dry-run : Show what would change without saving}';

    protected $description = 'Generate quiz_questions for heritage culture activities that were imported without them';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $updated = 0;

        CultureActivity::query()
            ->with(['tribe.clans'])
            ->where('metadata->seed_source', 'heritage_activities_seed')
            ->orderBy('id')
            ->each(function (CultureActivity $activity) use ($dryRun, &$updated): void {
                $meta = is_array($activity->metadata) ? $activity->metadata : [];
                $type = strtolower((string) ($meta['seed_activity_type'] ?? ''));
                $tag = strtolower((string) ($meta['tag'] ?? ''));
                $isQuiz = $type === 'quiz'
                    || str_contains($tag, 'quiz')
                    || str_contains($tag, 'graduation');

                if (! $isQuiz || ! empty($activity->quiz_questions)) {
                    return;
                }

                $item = CultureQuizGenerator::heritageItemFromActivity($activity);
                $questions = CultureQuizGenerator::fromHeritageItem($item, $activity->tribe);

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
                    $activity->saveQuietly();
                }

                $updated++;
            });

        $this->info($dryRun
            ? "Would update {$updated} culture activit(ies)."
            : "Updated {$updated} culture activit(ies).");

        return self::SUCCESS;
    }
}
