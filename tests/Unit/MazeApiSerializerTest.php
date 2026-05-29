<?php

namespace Tests\Unit;

use App\Models\Maze;
use App\Support\MazeApiSerializer;
use Tests\TestCase;

class MazeApiSerializerTest extends TestCase
{
    public function test_to_array_includes_playable_grid_and_positions(): void
    {
        $maze = new Maze([
            'id' => 7,
            'title' => 'Test Maze',
            'maze_type' => 'standard',
            'difficulty_level' => 'easy',
            'grid' => [[0, 1], [0, 0]],
            'grid_rows' => 2,
            'grid_cols' => 2,
            'start_position' => ['row' => 0, 'col' => 0],
            'end_position' => ['row' => 1, 'col' => 1],
            'collectibles' => [['row' => 0, 'col' => 1, 'emoji' => '💎', 'required' => true]],
            'hero_character' => '🦁',
        ]);

        $payload = MazeApiSerializer::toArray($maze);

        $this->assertSame(7, $payload['id']);
        $this->assertSame('standard', $payload['maze_type']);
        $this->assertSame([[0, 1], [0, 0]], $payload['grid']);
        $this->assertSame(['row' => 0, 'col' => 0], $payload['start_position']);
        $this->assertSame(['row' => 1, 'col' => 1], $payload['end_position']);
        $this->assertCount(1, $payload['collectibles']);
        $this->assertSame('🦁', $payload['hero_character']);
    }
}
