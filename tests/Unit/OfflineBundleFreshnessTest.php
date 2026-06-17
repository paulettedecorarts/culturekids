<?php

namespace Tests\Unit;

use App\Models\Activity;
use App\Models\OfflineContentBundle;
use App\Models\OrganisationContentDecision;
use App\Models\Tribe;
use App\Services\OfflineBundleFreshness;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OfflineBundleFreshnessTest extends TestCase
{
    use RefreshDatabase;

    private function createPuzzleActivity(array $puzzleMeta): Activity
    {
        $tribe = Tribe::create([
            'name' => 'Test Tribe',
            'hero_name' => 'Hero',
            'region' => 'Test',
        ]);

        return Activity::create([
            'tribe_id' => $tribe->id,
            'type' => 'puzzle',
            'title' => 'Test Puzzle',
            'is_published' => true,
            'star_points' => 10,
            'metadata' => ['puzzle' => $puzzleMeta],
        ]);
    }

    private function seedBundle(Activity $activity, int $assetCount, ?Carbon $builtAt = null): string
    {
        $path = 'bundles/global/puzzle-'.$activity->id.'.ckb';
        Storage::disk('public')->put($path, 'ckb');

        OfflineContentBundle::query()->create([
            'content_type' => OrganisationContentDecision::TYPE_PUZZLE,
            'content_id' => $activity->id,
            'bundle_path' => $path,
            'bundle_hash' => 'abc',
            'asset_count' => $assetCount,
            'bytes' => 1000,
            'built_at' => $builtAt ?? now(),
        ]);

        return $path;
    }

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_puzzle_needs_rebuild_when_asset_count_is_below_tile_count(): void
    {
        $activity = $this->createPuzzleActivity([
            'generating' => false,
            'source_image' => 'jigsaw-puzzles/1/source.png',
            'piece_paths' => [
                'jigsaw-puzzles/1/pieces/1700000000/001.png',
                'jigsaw-puzzles/1/pieces/1700000000/002.png',
            ],
            'generated_at' => now()->toIso8601String(),
        ]);

        $this->seedBundle($activity, assetCount: 1);

        $this->assertTrue(app(OfflineBundleFreshness::class)->puzzleNeedsRebuild($activity));
    }

    public function test_puzzle_skips_rebuild_while_generating(): void
    {
        $activity = $this->createPuzzleActivity([
            'generating' => true,
            'piece_paths' => [],
        ]);

        $this->assertFalse(app(OfflineBundleFreshness::class)->puzzleNeedsRebuild($activity));
    }

    public function test_puzzle_needs_rebuild_when_built_before_generated_at(): void
    {
        $generatedAt = Carbon::parse('2026-06-17T12:00:00Z');

        $activity = $this->createPuzzleActivity([
            'generating' => false,
            'source_image' => 'jigsaw-puzzles/1/source.png',
            'piece_paths' => [
                'jigsaw-puzzles/1/pieces/1700000000/001.png',
            ],
            'generated_at' => $generatedAt->toIso8601String(),
        ]);

        $this->seedBundle($activity, assetCount: 2, builtAt: $generatedAt->copy()->subHour());

        $this->assertTrue(app(OfflineBundleFreshness::class)->puzzleNeedsRebuild($activity));
    }

    public function test_puzzle_does_not_need_rebuild_when_complete_and_current(): void
    {
        $generatedAt = Carbon::parse('2026-06-17T12:00:00Z');

        $activity = $this->createPuzzleActivity([
            'generating' => false,
            'source_image' => 'jigsaw-puzzles/1/source.png',
            'piece_paths' => [
                'jigsaw-puzzles/1/pieces/1700000000/001.png',
            ],
            'generated_at' => $generatedAt->toIso8601String(),
        ]);

        $this->seedBundle($activity, assetCount: 2, builtAt: $generatedAt->copy()->addMinute());

        $this->assertFalse(app(OfflineBundleFreshness::class)->puzzleNeedsRebuild($activity));
    }
}