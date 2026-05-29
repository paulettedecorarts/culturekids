<?php

namespace Tests\Unit;

use App\Services\JigsawPuzzleGenerator;
use PHPUnit\Framework\TestCase;

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
}
