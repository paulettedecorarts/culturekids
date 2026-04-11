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
}
