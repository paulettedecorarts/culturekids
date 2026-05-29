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
    public const ORIENTATION_PORTRAIT = 'portrait';

    public const ORIENTATION_LANDSCAPE = 'landscape';

    public const ORIENTATION_SQUARE = 'square';

    public const ORIENTATION_CHOICES = [
        self::ORIENTATION_PORTRAIT,
        self::ORIENTATION_LANDSCAPE,
        self::ORIENTATION_SQUARE,
    ];

    protected const MAX_SOURCE_EDGE = 1200;

    /** Lower = faster writes (3 is a good balance for puzzle tiles). */
    protected const PNG_COMPRESSION = 3;

    public static function normalizeOrientation(?string $orientation): ?string
    {
        if ($orientation === null || $orientation === '') {
            return null;
        }

        $value = strtolower(trim($orientation));

        return in_array($value, self::ORIENTATION_CHOICES, true) ? $value : null;
    }

    public static function inferOrientationFromGrid(int $rows, int $cols): string
    {
        if ($rows > $cols) {
            return self::ORIENTATION_PORTRAIT;
        }
        if ($cols > $rows) {
            return self::ORIENTATION_LANDSCAPE;
        }

        return self::ORIENTATION_SQUARE;
    }

    /**
     * @return array{0: int, 1: int} validated rows, cols
     */
    public static function validateGrid(int $rows, int $cols): array
    {
        $rows = max(1, $rows);
        $cols = max(1, $cols);
        $pieces = $rows * $cols;

        if ($pieces < 4 || $pieces > 400) {
            throw new RuntimeException('Grid must produce between 4 and 400 tiles (currently '.$pieces.').');
        }

        return [$rows, $cols];
    }

    /**
     * Loads the image at the given public-disk path, normalizes to source.png, slices into a grid.
     *
     * @return array{rows: int, cols: int, piece_paths: list<string>, width: int, height: int, source_path: string, orientation: string, pieces: int}
     */
    public function generateFromStoredFile(
        string $relativeUploadPath,
        int $activityId,
        int $rows,
        int $cols
    ): array {
        if (! extension_loaded('gd')) {
            throw new RuntimeException('PHP GD extension is required to generate puzzle pieces.');
        }

        [$rows, $cols] = self::validateGrid($rows, $cols);
        $pieceCount = $rows * $cols;

        @set_time_limit(max(120, min(600, $pieceCount * 3)));
        @ini_set('memory_limit', '512M');

        $disk = Storage::disk('public');
        if (! $disk->exists($relativeUploadPath)) {
            throw new RuntimeException('Uploaded image not found: '.$relativeUploadPath);
        }

        $absoluteUpload = $disk->path($relativeUploadPath);
        $image = $this->loadImage($absoluteUpload);
        if ($image === false) {
            throw new RuntimeException('Could not read image file.');
        }

        $srcW = imagesx($image);
        $srcH = imagesy($image);
        [$image, $srcW, $srcH] = $this->maybeDownscale($image, $srcW, $srcH);

        $orientation = self::inferOrientationFromGrid($rows, $cols);

        $baseDir = 'jigsaw-puzzles/'.$activityId;
        $canonicalSource = $baseDir.'/source.png';
        $piecesRoot = $baseDir.'/pieces';

        $disk->makeDirectory($baseDir);

        $rewriteSource = $relativeUploadPath !== $canonicalSource;
        if ($rewriteSource) {
            imagealphablending($image, false);
            imagesavealpha($image, true);
            imagepng($image, $disk->path($canonicalSource), self::PNG_COMPRESSION);
            imagedestroy($image);
            $image = $this->loadImage($disk->path($canonicalSource));
            if ($image === false) {
                throw new RuntimeException('Could not reload normalized source image.');
            }
            $srcW = imagesx($image);
            $srcH = imagesy($image);
        }

        // Fresh folder per run so URLs change (avoids CDN/browser serving stale 001.png, etc.).
        $this->wipePiecesRoot($disk, $piecesRoot);
        $piecesDir = $piecesRoot.'/'.now()->format('YmdHis');
        $disk->makeDirectory($piecesDir);

        $piecePaths = [];
        $index = 0;
        for ($r = 0; $r < $rows; $r++) {
            for ($c = 0; $c < $cols; $c++) {
                $index++;
                [$x, $y, $w, $h] = $this->tileBounds($srcW, $srcH, $rows, $cols, $r, $c);

                if ($w < 1 || $h < 1) {
                    imagedestroy($image);
                    throw new RuntimeException('Invalid tile bounds for grid '.$rows.'×'.$cols.' on '.$srcW.'×'.$srcH.' image.');
                }

                $tile = imagecreatetruecolor($w, $h);
                if ($tile === false) {
                    imagedestroy($image);
                    throw new RuntimeException('Could not create tile image.');
                }
                imagealphablending($tile, false);
                imagesavealpha($tile, true);
                imagecopy($tile, $image, 0, 0, $x, $y, $w, $h);

                $name = $piecesDir.'/'.sprintf('%03d.png', $index);
                imagepng($tile, $disk->path($name), self::PNG_COMPRESSION);
                imagedestroy($tile);
                $piecePaths[] = $name;
            }
        }

        imagedestroy($image);

        if (count($piecePaths) !== $pieceCount) {
            throw new RuntimeException('Tile count mismatch after generation.');
        }

        if ($rewriteSource && $relativeUploadPath !== $canonicalSource && $disk->exists($relativeUploadPath)) {
            $disk->delete($relativeUploadPath);
        }

        return [
            'rows' => $rows,
            'cols' => $cols,
            'pieces' => $pieceCount,
            'piece_paths' => $piecePaths,
            'width' => $srcW,
            'height' => $srcH,
            'source_path' => $canonicalSource,
            'orientation' => $orientation,
        ];
    }

    public function inferOrientationFromDimensions(int $width, int $height): string
    {
        if ($width > $height * 1.05) {
            return self::ORIENTATION_LANDSCAPE;
        }
        if ($height > $width * 1.05) {
            return self::ORIENTATION_PORTRAIT;
        }

        return self::ORIENTATION_SQUARE;
    }

    /**
     * Pick rows × cols === $n.
     * Portrait → more rows than cols; landscape → more cols than rows; square → closest to N×N.
     * Without explicit orientation, uses image dimensions when available.
     *
     * @return array{0: int, 1: int} rows, cols
     */
    public function gridDimensions(
        int $n,
        ?string $orientation = null,
        ?int $width = null,
        ?int $height = null
    ): array {
        if ($n < 1) {
            return [1, 1];
        }

        $orientation = self::normalizeOrientation($orientation);
        if ($orientation === null && $width !== null && $height !== null && $width > 0 && $height > 0) {
            $orientation = $this->inferOrientationFromDimensions($width, $height);
        }

        if ($orientation !== null) {
            return $this->gridDimensionsForOrientation($n, $orientation);
        }

        return $this->defaultGridDimensions($n);
    }

    /**
     * @return array{0: int, 1: int}
     */
    public function gridDimensionsForOrientation(int $n, string $orientation): array
    {
        $orientation = self::normalizeOrientation($orientation) ?? self::ORIENTATION_SQUARE;
        $pairs = $this->factorPairs($n);

        if ($orientation === self::ORIENTATION_PORTRAIT) {
            return $this->pickPair($pairs, fn (int $rows, int $cols): bool => $rows >= $cols, fn (int $rows, int $cols): float => $rows / max(1, $cols));
        }

        if ($orientation === self::ORIENTATION_LANDSCAPE) {
            return $this->pickPair($pairs, fn (int $rows, int $cols): bool => $cols >= $rows, fn (int $rows, int $cols): float => $cols / max(1, $rows));
        }

        return $this->pickSquarePair($pairs);
    }

    /**
     * @param  list<array{0: int, 1: int}>  $pairs
     * @return array{0: int, 1: int}
     */
    protected function pickPair(array $pairs, callable $filter, callable $score): array
    {
        $eligible = array_values(array_filter($pairs, fn (array $pair): bool => $filter($pair[0], $pair[1])));
        if ($eligible === []) {
            usort($pairs, fn (array $a, array $b): int => $score($b[0], $b[1]) <=> $score($a[0], $a[1]));

            return $pairs[0];
        }

        usort($eligible, function (array $a, array $b) use ($score): int {
            $scoreB = $score($b[0], $b[1]);
            $scoreA = $score($a[0], $a[1]);

            return $scoreB <=> $scoreA;
        });

        return $eligible[0];
    }

    /**
     * @param  list<array{0: int, 1: int}>  $pairs
     * @return array{0: int, 1: int}
     */
    protected function pickSquarePair(array $pairs): array
    {
        $best = $pairs[0];
        $bestDiff = PHP_FLOAT_MAX;
        foreach ($pairs as [$rows, $cols]) {
            $diff = abs($rows - $cols);
            if ($diff < $bestDiff) {
                $bestDiff = $diff;
                $best = [$rows, $cols];
            }
        }

        return $best;
    }

    /**
     * @return list<array{0: int, 1: int}>
     */
    protected function factorPairs(int $n): array
    {
        $pairs = [];
        $sqrt = (int) floor(sqrt($n));
        for ($r = 1; $r <= $sqrt; $r++) {
            if ($n % $r === 0) {
                $cols = (int) ($n / $r);
                $pairs[] = [(int) $r, $cols];
                if ($r !== $cols) {
                    $pairs[] = [$cols, $r];
                }
            }
        }

        return $pairs;
    }

    /**
     * @return array{0: int, 1: int}
     */
    public function defaultGridDimensions(int $n): array
    {
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
    /**
     * Evenly divide the source image so every pixel is covered exactly once (no gaps/overlaps).
     *
     * @return array{0: int, 1: int, 2: int, 3: int}
     */
    protected function tileBounds(int $srcW, int $srcH, int $rows, int $cols, int $row, int $col): array
    {
        $x = (int) round($col * $srcW / $cols);
        $y = (int) round($row * $srcH / $rows);
        $x2 = (int) round(($col + 1) * $srcW / $cols);
        $y2 = (int) round(($row + 1) * $srcH / $rows);

        return [$x, $y, max(1, $x2 - $x), max(1, $y2 - $y)];
    }

    protected function wipePiecesRoot($disk, string $piecesRoot): void
    {
        if ($disk->exists($piecesRoot)) {
            $disk->deleteDirectory($piecesRoot);
        }
    }

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
