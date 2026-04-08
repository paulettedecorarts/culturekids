<?php

namespace App\Jobs;

use App\Models\Comic;
use App\Models\ComicProcessingStatus;
use App\Services\ComicPdfExtractor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * @deprecated Prefer ProcessComicStoryMedia for ordered multi-file uploads. Kept for any legacy dispatch.
 */
class ProcessComicPDF implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;

    public $tries = 3;

    public function __construct(
        protected int $comicId,
        protected string $pdfPath,
        protected int $startOrder = 0
    ) {}

    public function handle(): void
    {
        if (! Comic::whereKey($this->comicId)->exists()) {
            return;
        }

        $status = ComicProcessingStatus::where('comic_id', $this->comicId)
            ->where('status', '!=', ComicProcessingStatus::STATUS_COMPLETED)
            ->latest()
            ->first();

        try {
            if ($status) {
                $status->markAsProcessing(basename($this->pdfPath));
            }

            ComicPdfExtractor::extractPages($this->comicId, $this->pdfPath, $this->startOrder);

            if ($status) {
                $status->incrementProcessed();
            }
        } catch (\Throwable $e) {
            Log::error('ProcessComicPDF failed: '.$e->getMessage(), ['comic_id' => $this->comicId]);
            if ($status) {
                $status->markAsFailed($e->getMessage());
            }
            throw $e;
        }
    }

    public function failed(?\Throwable $exception): void
    {
        Log::error('ProcessComicPDF job failed for comic #'.$this->comicId.': '.($exception?->getMessage() ?? ''));
    }
}
