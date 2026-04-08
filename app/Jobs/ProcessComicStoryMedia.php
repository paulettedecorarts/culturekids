<?php

namespace App\Jobs;

use App\Models\Comic;
use App\Models\ComicPanel;
use App\Models\ComicProcessingStatus;
use App\Services\ComicPdfExtractor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Processes uploaded story panel files in strict order: images become one panel each;
 * PDFs expand to N panels. Avoids parallel PDF jobs clobbering order_index.
 *
 * @param  array<int, array{path: string, is_pdf: bool}>  $items  Stored paths on the public disk
 */
class ProcessComicStoryMedia implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600;

    public $tries = 2;

    public function __construct(
        public int $comicId,
        public array $items,
        public int $startOrder = 0,
        public ?int $processingStatusId = null
    ) {}

    public function handle(): void
    {
        $comic = Comic::find($this->comicId);
        if (! $comic) {
            // Often caused by the job running before the creating transaction commits.
            if ($this->attempts() < 10) {
                $this->release(3);

                return;
            }
            Log::error('ProcessComicStoryMedia: comic not found after retries', ['comic_id' => $this->comicId]);
            if ($this->processingStatusId) {
                $s = ComicProcessingStatus::find($this->processingStatusId);
                $s?->markAsFailed('Story record was not available (transaction ordering).');
            }

            return;
        }

        $status = $this->processingStatusId
            ? ComicProcessingStatus::find($this->processingStatusId)
            : null;

        $order = $this->startOrder;

        try {
            if ($status) {
                $status->markAsProcessing('Starting');
            }

            foreach ($this->items as $item) {
                $path = $item['path'];
                $isPdf = $item['is_pdf'];

                if ($status) {
                    $status->markAsProcessing(basename($path));
                }

                if (! $isPdf) {
                    ComicPanel::create([
                        'comic_id' => $this->comicId,
                        'order_index' => $order,
                        'image_path' => $path,
                    ]);
                    $order++;
                } else {
                    $order += ComicPdfExtractor::extractPages($this->comicId, $path, $order);
                }

                if ($status) {
                    $status->incrementProcessed();
                }
            }
        } catch (\Throwable $e) {
            Log::error('ProcessComicStoryMedia failed: '.$e->getMessage(), [
                'comic_id' => $this->comicId,
                'trace' => $e->getTraceAsString(),
            ]);

            if ($status) {
                $status->markAsFailed($e->getMessage());
            }

            throw $e;
        }
    }

    public function failed(?\Throwable $exception): void
    {
        if ($this->processingStatusId) {
            $status = ComicProcessingStatus::find($this->processingStatusId);
            if ($status && $status->status !== ComicProcessingStatus::STATUS_COMPLETED) {
                $status->markAsFailed($exception?->getMessage() ?? 'Job failed');
            }
        }
    }
}
