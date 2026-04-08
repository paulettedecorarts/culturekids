<?php

namespace App\Services;

use App\Models\ComicPanel;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\PdfToImage\Pdf;

/**
 * Turns one PDF on the public disk into sequential ComicPanel image rows.
 */
class ComicPdfExtractor
{
    /**
     * @return int Number of panels created
     */
    public static function extractPages(int $comicId, string $relativePdfPath, int $startOrder): int
    {
        $disk = Storage::disk('public');
        $fullPath = $disk->path($relativePdfPath);

        if (! is_file($fullPath)) {
            throw new \RuntimeException("PDF not found: {$relativePdfPath}");
        }

        if (extension_loaded('imagick')) {
            try {
                return self::extractWithImagick($comicId, $relativePdfPath, $fullPath, $startOrder);
            } catch (\Throwable $e) {
                Log::warning('ComicPdfExtractor Imagick failed: '.$e->getMessage());
            }
        }

        if (class_exists(Pdf::class)) {
            try {
                return self::extractWithSpatie($comicId, $relativePdfPath, $fullPath, $startOrder);
            } catch (\Throwable $e) {
                Log::warning('ComicPdfExtractor Spatie failed: '.$e->getMessage());
            }
        }

        ComicPanel::create([
            'comic_id' => $comicId,
            'order_index' => $startOrder,
            'image_path' => $relativePdfPath,
        ]);

        return 1;
    }

    private static function extractWithImagick(
        int $comicId,
        string $relativePdfPath,
        string $fullPath,
        int $startOrder
    ): int {
        self::configureGhostscriptForWindows();

        $imagick = new \Imagick;
        if (PHP_OS_FAMILY === 'Windows' && ($gs = self::findWindowsGhostscript())) {
            try {
                $imagick->setOption('gs:executable', $gs);
            } catch (\Throwable) {
            }
        }
        $imagick->setResolution(150, 150);
        $imagick->readImage($fullPath);

        $disk = Storage::disk('public');
        $pageIndex = 0;

        foreach ($imagick as $page) {
            $page->setImageFormat('jpg');
            $page->setImageCompressionQuality(85);

            $filename = 'comics/panels/'.uniqid('', true).'_p'.($pageIndex + 1).'.jpg';
            $outputPath = $disk->path($filename);
            $dir = dirname($outputPath);
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $page->writeImage($outputPath);

            ComicPanel::create([
                'comic_id' => $comicId,
                'order_index' => $startOrder + $pageIndex,
                'image_path' => $filename,
            ]);
            $pageIndex++;
        }

        $imagick->clear();
        $imagick->destroy();

        $disk->delete($relativePdfPath);

        return $pageIndex;
    }

    private static function extractWithSpatie(
        int $comicId,
        string $relativePdfPath,
        string $fullPath,
        int $startOrder
    ): int {
        $pdf = new Pdf($fullPath);
        $pageCount = $pdf->getNumberOfPages();
        $disk = Storage::disk('public');

        for ($page = 1; $page <= $pageCount; $page++) {
            $filename = 'comics/panels/'.uniqid('', true).'_p'.$page.'.jpg';
            $outputPath = $disk->path($filename);
            $dir = dirname($outputPath);
            if (! is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $pdf->setPage($page)
                ->setOutputFormat('jpg')
                ->setCompressionQuality(85)
                ->saveImage($outputPath);

            ComicPanel::create([
                'comic_id' => $comicId,
                'order_index' => $startOrder + ($page - 1),
                'image_path' => $filename,
            ]);
        }

        $disk->delete($relativePdfPath);

        return $pageCount;
    }

    private static function configureGhostscriptForWindows(): void
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            return;
        }

        $gs = self::findWindowsGhostscript();
        if ($gs === null) {
            return;
        }

        putenv("MAGICK_GHOSTSCRIPT_PATH={$gs}");
        putenv("MAGICK_GHOSTSCRIPT_EXECUTABLE={$gs}");
        $_ENV['MAGICK_GHOSTSCRIPT_PATH'] = $gs;
        $_SERVER['MAGICK_GHOSTSCRIPT_PATH'] = $gs;
    }

    private static function findWindowsGhostscript(): ?string
    {
        $candidates = array_merge(
            glob('C:\\Program Files\\gs\\gs*\\bin\\gswin64c.exe') ?: [],
            glob('C:\\Program Files\\gs\\gs*\\bin\\gswin32c.exe') ?: [],
            glob('C:\\Program Files (x86)\\gs\\gs*\\bin\\gswin64c.exe') ?: [],
            glob('C:\\Program Files (x86)\\gs\\gs*\\bin\\gswin32c.exe') ?: []
        );

        if ($candidates === []) {
            return null;
        }

        rsort($candidates);

        return $candidates[0] ?? null;
    }
}
