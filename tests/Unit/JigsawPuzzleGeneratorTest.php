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

    public function test_grid_dimensions_follows_portrait_and_landscape(): void
    {
        $g = new JigsawPuzzleGenerator;

        // Portrait (taller): more rows than cols → 4×3 for 12 pieces
        $this->assertSame([4, 3], $g->gridDimensions(12, 600, 800));
        // Landscape (wider): more cols than rows → 3×4
        $this->assertSame([3, 4], $g->gridDimensions(12, 800, 600));
    }
}
