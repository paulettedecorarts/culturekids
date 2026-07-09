<?php

namespace Tests\Unit\Services\Seed;

use App\Services\Seed\HeritageSeedAssetPublisher;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class HeritageSeedAssetPublisherTest extends TestCase
{
    public function test_publish_copies_seed_asset_to_public_storage(): void
    {
        $relative = '_test/test-tribe.jpg';
        $source = base_path(HeritageSeedAssetPublisher::SEED_ASSETS_ROOT.'/'.$relative);
        $binary = base64_decode('/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjL/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAn/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwCwAA//2Q==', true);

        File::ensureDirectoryExists(dirname($source));
        File::put($source, $binary);

        $publisher = app(HeritageSeedAssetPublisher::class);
        $published = $publisher->publish($relative);

        $this->assertSame('heritage-seed/'.$relative, $published);
        $this->assertTrue(File::exists(storage_path('app/public/heritage-seed/'.$relative)));

        File::delete($source);
        File::deleteDirectory(base_path('seed/assets/_test'));
        File::delete(storage_path('app/public/heritage-seed/_test/test-tribe.jpg'));
        File::deleteDirectory(storage_path('app/public/heritage-seed/_test'));
    }

    public function test_publish_returns_null_when_asset_missing(): void
    {
        $publisher = app(HeritageSeedAssetPublisher::class);

        $this->assertNull($publisher->publish('missing/file.mp3'));
    }
}
