<?php

namespace App\Services\Seed;

use Illuminate\Support\Facades\File;

/**
 * Copies files from seed/assets/ into storage/app/public/heritage-seed/
 * and returns the public-disk relative path for DB columns.
 */
class HeritageSeedAssetPublisher
{
    public const SEED_ASSETS_ROOT = 'seed/assets';

    public const PUBLIC_PREFIX = 'heritage-seed';

    public function seedAssetPath(string $relativePath): string
    {
        return base_path(self::SEED_ASSETS_ROOT.'/'.ltrim($relativePath, '/'));
    }

    public function exists(?string $relativePath): bool
    {
        if ($relativePath === null || $relativePath === '') {
            return false;
        }

        return File::isFile($this->seedAssetPath($relativePath));
    }

    /**
     * Publish a seed asset file to the public disk if it exists.
     */
    public function publish(?string $relativePath): ?string
    {
        if ($relativePath === null || $relativePath === '') {
            return null;
        }

        if (str_starts_with($relativePath, 'http://') || str_starts_with($relativePath, 'https://')) {
            return $relativePath;
        }

        $source = $this->seedAssetPath($relativePath);

        if (! File::isFile($source)) {
            return null;
        }

        $destination = self::PUBLIC_PREFIX.'/'.ltrim($relativePath, '/');
        $destinationFull = storage_path('app/public/'.$destination);

        File::ensureDirectoryExists(dirname($destinationFull));

        if (! File::exists($destinationFull) || File::lastModified($source) > File::lastModified($destinationFull)) {
            File::copy($source, $destinationFull);
        }

        return $destination;
    }

    /**
     * @param  array<string, string|null>  $assets
     * @return array<string, string>
     */
    public function publishMany(array $assets): array
    {
        $published = [];

        foreach ($assets as $key => $relativePath) {
            if (! is_string($relativePath) || $relativePath === '') {
                continue;
            }

            $path = $this->publish($relativePath);

            if ($path !== null) {
                $published[$key] = $path;
            }
        }

        return $published;
    }
}
