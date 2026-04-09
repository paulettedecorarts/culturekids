<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class AudioUploadService
{
    /**
     * Store an uploaded audio file on the public disk.
     *
     * This intentionally does not enforce mime/extension restrictions to support
     * broad audio ingestion workflows. Environment/web-server limits still apply.
     */
    public function store(
        UploadedFile $file,
        string $directory,
        ?string $oldPath = null,
        array $context = []
    ): string {
        $context = $this->normalizeContext($context);

        Log::info('audio.upload.started', array_merge($context, [
            'directory' => $directory,
            'original_name' => $file->getClientOriginalName(),
            'client_mime' => $file->getClientMimeType(),
            'detected_mime' => $file->getMimeType(),
            'size_bytes' => $file->getSize(),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
        ]));

        try {
            if ($oldPath) {
                Storage::disk('public')->delete($oldPath);
            }

            $path = $file->store($directory, 'public');

            Log::info('audio.upload.succeeded', array_merge($context, [
                'path' => $path,
            ]));

            return $path;
        } catch (Throwable $e) {
            Log::error('audio.upload.failed', array_merge($context, [
                'error' => $e->getMessage(),
                'exception' => $e::class,
            ]));

            throw $e;
        }
    }

    public function delete(?string $path, array $context = []): void
    {
        if (! $path) {
            return;
        }

        Storage::disk('public')->delete($path);

        Log::info('audio.delete.completed', array_merge($this->normalizeContext($context), [
            'path' => $path,
        ]));
    }

    protected function normalizeContext(array $context): array
    {
        return [
            'feature' => $context['feature'] ?? 'unknown',
            'entity' => $context['entity'] ?? null,
            'entity_id' => $context['entity_id'] ?? null,
            'user_id' => auth()->id(),
        ];
    }
}
