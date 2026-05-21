<?php

namespace App\Livewire\Concerns;

use Illuminate\Support\Facades\Log;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Throwable;

/**
 * Helpers for logging permanent storage after save.
 * Temporary wire:model uploads are logged by App\Livewire\Hooks\LogsFileUploadsHook.
 */
trait LogsFileUploads
{
    protected function logUploadEvent(string $event, string $field, mixed $value = null, ?Throwable $exception = null): void
    {
        $payload = [
            'event' => $event,
            'component' => static::class,
            'field' => $field,
            'user_id' => auth()->id(),
            'edit_mode' => method_exists($this, 'isFormEditMode') ? $this->isFormEditMode() : null,
            'php' => [
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size'),
            ],
        ];

        if ($value instanceof TemporaryUploadedFile) {
            $payload['file'] = [
                'name' => $value->getClientOriginalName(),
                'mime' => $value->getMimeType(),
                'size' => $value->getSize(),
                'extension' => $value->getClientOriginalExtension(),
            ];
        } elseif (is_array($value)) {
            $payload['file_count'] = count($value);
            $payload['files'] = collect($value)
                ->map(fn ($item) => $item instanceof TemporaryUploadedFile ? [
                    'name' => $item->getClientOriginalName(),
                    'size' => $item->getSize(),
                ] : ['type' => get_debug_type($item)])
                ->take(10)
                ->all();
        } elseif ($value !== null) {
            $payload['value_type'] = get_debug_type($value);
        }

        if ($exception !== null) {
            $payload['error'] = $exception->getMessage();
            Log::channel('uploads')->error($event, $payload);

            return;
        }

        Log::channel('uploads')->info($event, $payload);
    }

    protected function logUploadStore(string $field, string $storedPath, ?string $disk = 'public'): void
    {
        Log::channel('uploads')->info('livewire.upload.stored', [
            'component' => static::class,
            'field' => $field,
            'disk' => $disk,
            'path' => $storedPath,
            'user_id' => auth()->id(),
        ]);
    }

}