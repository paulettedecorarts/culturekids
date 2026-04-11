<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use RuntimeException;

/**
 * Splits a source image into a rectangular grid of PNG tiles for jigsaw-style play.
 * Uses PHP GD (ext-gd). Tiles are rectangular (classic grid), not irregular die-cut shapes.
 */
class JigsawPuzzleGenerator
{
    protected const MAX_SOURCE_EDGE = 1400;

    /**
     * Loads the image at the given public-disk path, normalizes to source.png, slices into N tiles.
     *
     * @return array{rows: int, cols: int, piece_paths: list<string>, width: int, height: int, source_path: string}
     */
    public function generateFromStoredFile(string $relativeUploadPath, int $activityId, int $pieceCount): array
    {
        if (! extension_loaded('gd')) {
            throw new RuntimeException('PHP GD extension is required to generate puzzle pieces.');
        }
        if ($pieceCount < 4 || $pieceCount > 400) {
            throw new RuntimeException('Piece count must be between 4 and 400.');
        }

        $disk = Storage::disk('public');
        if (! $disk->exists($relativeUploadPath)) {
            throw new RuntimeException('Uploaded image not found: '.$relativeUploadPath);
        }

        [$rows, $cols] = $this->gridDimensions($pieceCount);
        if ($rows * $cols !== $pieceCount) {
            throw new RuntimeException('Invalid piece count: '.$pieceCount);
        }

        $absoluteUpload = $disk->path($relativeUploadPath);
        $image = $this->loadImage($absoluteUpload);
        if ($image === false) {
            throw new RuntimeException('Could not read image file.');
        }

        $srcW = imagesx($image);
        $srcH = imagesy($image);
        [$image, $srcW, $srcH] = $this->maybeDownscale($image, $srcW, $srcH);

        $baseDir = 'jigsaw-puzzles/'.$activityId;
        $disk->makeDirectory($baseDir);
        $disk->deleteDirectory($baseDir.'/pieces');

        $canonicalSource = $baseDir.'/source.png';
        imagealphablending($image, false);
        imagesavealpha($image, true);
        imagepng($image, $disk->path($canonicalSource), 6);

        $pieceW = (int) floor($srcW / $cols);
        $pieceH = (int) floor($srcH / $rows);

        $piecesDir = $baseDir.'/pieces';
        $disk->makeDirectory($piecesDir);

        $piecePaths = [];
        $index = 0;
        for ($r = 0; $r < $rows; $r++) {
            for ($c = 0; $c < $cols; $c++) {
                $index++;
                $x = $c * $pieceW;
                $y = $r * $pieceH;
                $w = ($c === $cols - 1) ? $srcW - $x : $pieceW;
                $h = ($r === $rows - 1) ? $srcH - $y : $pieceH;

                $tile = imagecreatetruecolor($w, $h);
                if ($tile === false) {
                    imagedestroy($image);
                    throw new RuntimeException('Could not create tile image.');
                }
                imagealphablending($tile, false);
                imagesavealpha($tile, true);
                $transparent = imagecolorallocatealpha($tile, 0, 0, 0, 127);
                imagefill($tile, 0, 0, $transparent);
                imagecopy($tile, $image, 0, 0, $x, $y, $w, $h);

                $name = $piecesDir.'/'.sprintf('%03d.png', $index);
                imagepng($tile, $disk->path($name), 6);
                imagedestroy($tile);
                $piecePaths[] = $name;
            }
        }

        imagedestroy($image);

        if ($relativeUploadPath !== $canonicalSource && $disk->exists($relativeUploadPath)) {
            $disk->delete($relativeUploadPath);
        }

        return [
            'rows' => $rows,
            'cols' => $cols,
            'piece_paths' => $piecePaths,
            'width' => $srcW,
            'height' => $srcH,
            'source_path' => $canonicalSource,
        ];
    }

    /**
     * @return array{0: int, 1: int} rows, cols where rows * cols === $n
     */
    public function gridDimensions(int $n): array
    {
        if ($n < 1) {
            return [1, 1];
        }
        $sqrt = (int) floor(sqrt($n));
        for ($r = $sqrt; $r >= 1; $r--) {
            if ($n % $r === 0) {
                return [(int) $r, (int) ($n / $r)];
            }
        }

        return [1, $n];
    }

    /**
     * @return resource|GdImage|false
     */
    protected function loadImage(string $absolutePath)
    {
        $binary = file_get_contents($absolutePath);
        if ($binary === false || $binary === '') {
            return false;
        }

        return @imagecreatefromstring($binary);
    }

    /**
     * @param  resource|GdImage  $image
     * @return array{0: resource|GdImage, 1: int, 2: int}
     */
    protected function maybeDownscale($image, int $srcW, int $srcH): array
    {
        $max = max($srcW, $srcH);
        if ($max <= self::MAX_SOURCE_EDGE) {
            return [$image, $srcW, $srcH];
        }

        $scale = self::MAX_SOURCE_EDGE / $max;
        $newW = max(1, (int) round($srcW * $scale));
        $newH = max(1, (int) round($srcH * $scale));
        $resized = imagescale($image, $newW, $newH, IMG_BILINEAR_FIXED);
        imagedestroy($image);
        if ($resized === false) {
            throw new RuntimeException('Could not resize source image.');
        }

        return [$resized, $newW, $newH];
    }
}
