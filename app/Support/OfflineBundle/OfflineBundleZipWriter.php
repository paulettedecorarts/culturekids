<?php

namespace App\Support\OfflineBundle;

use Illuminate\Support\Facades\Storage;
use ZipArchive;

class OfflineBundleZipWriter
{
    private ZipArchive $zip;

    /** @var array<string, string> storage path => zip entry */
    private array $pathMap = [];

    private int $assetCount = 0;

    public function __construct(
        private readonly string $tempZipPath,
    ) {
        if (! class_exists(ZipArchive::class)) {
            throw new \RuntimeException('ZipArchive extension is required to build offline bundles.');
        }

        $this->zip = new ZipArchive;
        if ($this->zip->open($this->tempZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Could not create offline bundle archive.');
        }
    }

    /**
     * @param  list<string>  $storagePaths
     * @return array<string, string> original path => bundle-relative path
     */
    public function addStorageAssets(array $storagePaths, string $prefix = 'assets'): array
    {
        $disk = Storage::disk('public');
        $added = [];

        foreach ($storagePaths as $storagePath) {
            if (isset($this->pathMap[$storagePath]) || ! $disk->exists($storagePath)) {
                continue;
            }

            $entry = $prefix.'/'.basename($storagePath);
            $counter = 0;
            while ($this->zipEntryExists($entry)) {
                $counter++;
                $entry = $prefix.'/'.pathinfo($storagePath, PATHINFO_FILENAME).'-'.$counter.'.'.pathinfo($storagePath, PATHINFO_EXTENSION);
            }

            $this->zip->addFile($disk->path($storagePath), $entry);
            $this->pathMap[$storagePath] = $entry;
            $added[$storagePath] = $entry;
            $this->assetCount++;
        }

        return $added;
    }

    public function addManifest(array $manifest): void
    {
        $this->zip->addFromString(
            'manifest.json',
            json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    public function close(): int
    {
        $this->zip->close();

        return $this->assetCount;
    }

    public function assetCount(): int
    {
        return $this->assetCount;
    }

    private function zipEntryExists(string $entry): bool
    {
        return $this->zip->locateName($entry) !== false;
    }
}
