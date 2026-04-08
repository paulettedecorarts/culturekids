<?php

namespace App\Jobs;

use App\Models\Comic;
use App\Models\ComicPanel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;

/**
 * Process individual image files for comic panels
 * Optimizes images and creates thumbnails
 */
class ProcessComicImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 120;

    public $tries = 3;

    public $backoff = [10, 30, 60]; // Exponential backoff

    protected $comicId;

    protected $imagePath;

    protected $orderIndex;

    public function __construct($comicId, $imagePath, $orderIndex)
    {
        $this->comicId = $comicId;
        $this->imagePath = $imagePath;
        $this->orderIndex = $orderIndex;

        $this->onQueue('image-processing');
    }

    public function handle(): void
    {
        Log::info('=== IMAGE PROCESSING STARTED ===');
        Log::info("Comic ID: {$this->comicId}, Order: {$this->orderIndex}");

        try {
            $comic = Comic::findOrFail($this->comicId);
            $fullPath = Storage::disk('public')->path($this->imagePath);

            if (! file_exists($fullPath)) {
                Log::error("Image file not found: {$fullPath}");
                $this->fail(new \Exception('Image file not found'));

                return;
            }

            Log::info('Image file exists, size: '.filesize($fullPath).' bytes');

            // Optional: Optimize image (resize if too large, compress)
            $this->optimizeImage($fullPath);

            // Create panel record
            $panel = ComicPanel::create([
                'comic_id' => $this->comicId,
                'order_index' => $this->orderIndex,
                'image_path' => $this->imagePath,
            ]);

            Log::info("✓ Panel created with ID: {$panel->id}");
            Log::info('=== IMAGE PROCESSING COMPLETED ===');

        } catch (\Exception $e) {
            Log::error('Image processing failed: '.$e->getMessage());
            throw $e;
        }
    }

    protected function optimizeImage(string $path): void
    {
        try {
            // Check if image is too large (> 2MB or > 2000px width)
            $size = filesize($path);
            $imageInfo = getimagesize($path);

            if ($size > 2 * 1024 * 1024 || $imageInfo[0] > 2000) {
                Log::info('Optimizing large image...');

                // Use Intervention Image if available, otherwise skip
                if (class_exists('\Intervention\Image\Facades\Image')) {
                    $img = Image::make($path);

                    // Resize if too wide
                    if ($img->width() > 2000) {
                        $img->resize(2000, null, function ($constraint) {
                            $constraint->aspectRatio();
                            $constraint->upsize();
                        });
                    }

                    // Compress
                    $img->save($path, 85);

                    Log::info('✓ Image optimized, new size: '.filesize($path).' bytes');
                }
            }
        } catch (\Exception $e) {
            Log::warning('Image optimization failed (non-critical): '.$e->getMessage());
            // Don't fail the job if optimization fails
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("ProcessComicImage failed for comic #{$this->comicId}, order {$this->orderIndex}");
        Log::error('Error: '.$exception->getMessage());
    }
}
