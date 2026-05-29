<?php

namespace Tests\Unit;

use App\Models\Maze;
use App\Support\MazeApiSerializer;
use PHPUnit\Framework\TestCase;

class MazeLegacySyncTest extends TestCase
{
    public function test_api_payload_stays_bounded(): void
    {
        $maze = new Maze([
            'id' => 42,
            'title' => 'Test',
            'maze_type' => 'standard',
            'grid_rows' => 20,
            'grid_cols' => 20,
            'grid' => array_fill(0, 20, array_fill(0, 20, 0)),
            'start_position' => ['row' => 0, 'col' => 1],
            'end_position' => ['row' => 19, 'col' => 18],
        ]);

        $encoded = json_encode(MazeApiSerializer::toArray($maze));

        $this->assertIsString($encoded);
        $this->assertLessThan(50_000, strlen($encoded));
    }

    public function test_mirror_metadata_shape_excludes_nested_maze_key(): void
    {
        $metadata = [
            'source' => 'maze_mirror',
            'legacy_maze_id' => 5,
            'maze_type' => 'standard',
            'grid_rows' => 10,
            'grid_cols' => 10,
        ];

        $encoded = json_encode($metadata);

        $this->assertIsString($encoded);
        $this->assertLessThan(500, strlen($encoded));
        $this->assertStringNotContainsString('"maze"', $encoded);
    }
}
