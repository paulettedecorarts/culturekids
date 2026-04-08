<?php

namespace App\Jobs;

use App\Models\Comic;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Batch job to process multiple comic files
 * Handles large-scale uploads with progress tracking
 */
class ProcessComicBatch implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600; // 10 minutes per batch

    public $tries = 2;

    public $maxExceptions = 3;

    protected $comicId;

    protected $files;

    protected $startOrder;

    /**
     * @param  int  $comicId
     * @param  array  $files  Array of ['path' => string, 'type' => 'pdf|image']
     */
    public function __construct($comicId, array $files, int $startOrder = 0)
    {
        $this->comicId = $comicId;
        $this->files = $files;
        $this->startOrder = $startOrder;

        // Use dedicated queue for media processing
        $this->onQueue('media-processing');
    }

    public function handle(): void
    {
        if ($this->batch()->cancelled()) {
            Log::warning("Batch cancelled for comic #{$this->comicId}");

            return;
        }

        Log::info('=== BATCH PROCESSING STARTED ===');
        Log::info("Comic ID: {$this->comicId}");
        Log::info('Files to process: '.count($this->files));

        $comic = Comic::find($this->comicId);
        if (! $comic) {
            Log::error("Comic #{$this->comicId} not found");

            return;
        }

        if ($this->batch()->cancelled()) {
            Log::warning('Batch cancelled before processing');

            return;
        }

        $items = [];
        foreach ($this->files as $fileInfo) {
            $items[] = [
                'path' => $fileInfo['path'],
                'is_pdf' => ($fileInfo['type'] ?? '') === 'pdf',
            ];
        }

        ProcessComicStoryMedia::dispatch($this->comicId, $items, $this->startOrder, null);

        Log::info('=== BATCH PROCESSING QUEUED (ProcessComicStoryMedia) ===');
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("ProcessComicBatch failed for comic #{$this->comicId}");
        Log::error('Error: '.$exception->getMessage());

        // Notify admin or update comic status
        $comic = Comic::find($this->comicId);
        if ($comic) {
            $comic->update([
                'metadata' => array_merge($comic->metadata ?? [], [
                    'processing_failed' => true,
                    'error' => $exception->getMessage(),
                    'failed_at' => now()->toIso8601String(),
                ]),
            ]);
        }
    }
}
