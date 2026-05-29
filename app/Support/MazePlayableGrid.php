<?php

namespace App\Support;

/**
 * Ensures start/end markers sit on walkable cells (0 = path, 1 = wall).
 */
final class MazePlayableGrid
{
    public const CELL_PATH = 0;

    public const CELL_WALL = 1;

    /**
     * @param  array<int, array<int, int>>  $grid
     * @param  array{row?: int, col?: int}|null  $start
     * @param  array{row?: int, col?: int}|null  $end
     * @return array<int, array<int, int>>
     */
    public static function normalize(array $grid, ?array $start, ?array $end): array
    {
        $normalized = [];
        foreach ($grid as $r => $row) {
            $normalized[$r] = is_array($row) ? array_values($row) : [];
        }

        foreach ([$start, $end] as $position) {
            if (! is_array($position)) {
                continue;
            }
            $row = (int) ($position['row'] ?? -1);
            $col = (int) ($position['col'] ?? -1);
            if (isset($normalized[$row][$col])) {
                $normalized[$row][$col] = self::CELL_PATH;
            }
        }

        return $normalized;
    }

    /**
     * @param  array{row?: int, col?: int}  $start
     * @param  array{row?: int, col?: int}  $end
     * @param  array<int, array<int, int>>  $grid
     */
    public static function applyMarkersToGrid(array &$grid, array $start, array $end): void
    {
        foreach ([$start, $end] as $position) {
            $row = (int) ($position['row'] ?? -1);
            $col = (int) ($position['col'] ?? -1);
            if (isset($grid[$row][$col])) {
                $grid[$row][$col] = self::CELL_PATH;
            }
        }
    }
}
