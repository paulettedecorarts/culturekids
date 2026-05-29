<?php

namespace Tests\Unit;

use App\Services\JigsawPuzzleGenerator;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class JigsawPuzzleGeneratorTest extends TestCase
{
    public function test_grid_dimensions_factor_pairs(): void
    {
        $g = new JigsawPuzzleGenerator;

        $this->assertSame([3, 4], $g->gridDimensions(12));
        $this->assertSame([10, 10], $g->gridDimensions(100));
        $this->assertSame([1, 7], $g->gridDimensions(7));
    }

    public function test_grid_dimensions_follows_portrait_and_landscape_from_image_size(): void
    {
        $g = new JigsawPuzzleGenerator;

        $this->assertSame([4, 3], $g->gridDimensions(12, null, 600, 800));
        $this->assertSame([3, 4], $g->gridDimensions(12, null, 800, 600));
    }

    public function test_grid_dimensions_follows_explicit_orientation(): void
    {
        $g = new JigsawPuzzleGenerator;

        $this->assertSame([4, 3], $g->gridDimensions(12, JigsawPuzzleGenerator::ORIENTATION_PORTRAIT));
        $this->assertSame([3, 4], $g->gridDimensions(12, JigsawPuzzleGenerator::ORIENTATION_LANDSCAPE));
        $this->assertSame([3, 4], $g->gridDimensions(12, JigsawPuzzleGenerator::ORIENTATION_SQUARE));
        $this->assertSame([10, 10], $g->gridDimensions(100, JigsawPuzzleGenerator::ORIENTATION_SQUARE));
    }

    public function test_tile_bounds_cover_full_image_without_gaps(): void
    {
        $g = new JigsawPuzzleGenerator;
        $ref = new \ReflectionClass($g);
        $method = $ref->getMethod('tileBounds');
        $method->setAccessible(true);

        $srcW = 1000;
        $srcH = 800;
        $rows = 4;
        $cols = 5;
        for ($r = 0; $r < $rows; $r++) {
            $rowWidth = 0;
            for ($c = 0; $c < $cols; $c++) {
                [$x, $y, $w, $h] = $method->invoke($g, $srcW, $srcH, $rows, $cols, $r, $c);
                $rowWidth += $w;
                if ($r === 0 && $c === 0) {
                    $this->assertSame(0, $x);
                    $this->assertSame(0, $y);
                }
            }
            $this->assertSame($srcW, $rowWidth);
        }

        for ($c = 0; $c < $cols; $c++) {
            $colHeight = 0;
            for ($r = 0; $r < $rows; $r++) {
                [, , , $h] = $method->invoke($g, $srcW, $srcH, $rows, $cols, $r, $c);
                $colHeight += $h;
            }
            $this->assertSame($srcH, $colHeight);
        }
    }

    public function test_validate_grid_and_infer_orientation(): void
    {
        $this->assertSame([4, 3], JigsawPuzzleGenerator::validateGrid(4, 3));
        $this->assertSame(JigsawPuzzleGenerator::ORIENTATION_PORTRAIT, JigsawPuzzleGenerator::inferOrientationFromGrid(4, 3));
        $this->assertSame(JigsawPuzzleGenerator::ORIENTATION_LANDSCAPE, JigsawPuzzleGenerator::inferOrientationFromGrid(3, 4));

        $this->expectException(RuntimeException::class);
        JigsawPuzzleGenerator::validateGrid(12, 1);
    }
}
