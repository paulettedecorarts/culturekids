<?php

namespace App\Support\OfflineBundle;

class OfflineBundleAssetCollector
{
    /** @var list<string> */
    private array $paths = [];

    /**
     * @param  list<string>  $explicit
     * @return list<string> unique storage-relative paths
     */
    public function collect(mixed $data, array $explicit = []): array
    {
        $this->paths = [];

        foreach ($explicit as $path) {
            $this->addPath($path);
        }

        $this->walk($data);

        return array_values(array_unique($this->paths));
    }

    private function walk(mixed $value): void
    {
        if (! is_array($value)) {
            return;
        }

        foreach ($value as $key => $child) {
            if ($key === 'piece_paths' && is_array($child)) {
                foreach ($child as $piecePath) {
                    if (is_string($piecePath)) {
                        $this->addPath($piecePath);
                    }
                }
                continue;
            }

            if (is_string($key) && $this->keySuggestsAsset($key) && is_string($child)) {
                $this->addPath($child);
            } elseif (is_array($child)) {
                $this->walk($child);
            }
        }
    }

    private function keySuggestsAsset(string $key): bool
    {
        return str_ends_with($key, '_path')
            || str_ends_with($key, '_url')
            || in_array($key, ['image', 'audio', 'video', 'cover', 'template', 'file', 'source_image'], true);
    }

    private function addPath(string $path): void
    {
        $path = trim($path);
        if ($path === '' || str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return;
        }

        $path = ltrim($path, '/');
        if (str_contains($path, '..') || ! $this->looksLikeStoragePath($path)) {
            return;
        }

        $this->paths[] = $path;
    }

    private function looksLikeStoragePath(string $path): bool
    {
        if (! preg_match('/^[a-zA-Z0-9._\-\/]+$/', $path)) {
            return false;
        }

        // Require a file extension so folder paths (e.g. culture/maps) are never zipped.
        return preg_match('/\.[a-zA-Z0-9]{2,5}$/', basename($path)) === 1;
    }
}
