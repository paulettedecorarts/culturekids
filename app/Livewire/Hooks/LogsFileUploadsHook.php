<?php

namespace App\Livewire\Hooks;

use Illuminate\Support\Facades\Log;
use Livewire\ComponentHook;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class LogsFileUploadsHook extends ComponentHook
{
    public function skip(): bool
    {
        return ! in_array(WithFileUploads::class, class_uses_recursive($this->component));
    }

    public function callUpdate($propertyName, $fullPath, $newValue): void
    {
        if (! $this->looksLikeUploadProperty($propertyName, $newValue)) {
            return;
        }

        $payload = [
            'event' => 'livewire.hook.update',
            'component' => $this->component::class,
            'property' => $propertyName,
            'path' => $fullPath,
            'user_id' => auth()->id(),
        ];

        if ($newValue instanceof TemporaryUploadedFile) {
            $payload['file'] = [
                'name' => $newValue->getClientOriginalName(),
                'mime' => $newValue->getMimeType(),
                'size' => $newValue->getSize(),
            ];
        } elseif (is_array($newValue)) {
            $payload['file_count'] = count($newValue);
        } else {
            $payload['value_type'] = get_debug_type($newValue);
        }

        Log::channel('uploads')->info('livewire.hook.update', $payload);
    }

    public function callException($e, $stopPropagation): void
    {
        if (! in_array(WithFileUploads::class, class_uses_recursive($this->component))) {
            return;
        }

        Log::channel('uploads')->error('livewire.component.exception', [
            'component' => $this->component::class,
            'message' => $e->getMessage(),
            'class' => $e::class,
            'user_id' => auth()->id(),
        ]);
    }

    protected function looksLikeUploadProperty(string $propertyName, mixed $value): bool
    {
        if ($value instanceof TemporaryUploadedFile || (is_array($value) && $this->arrayHasUpload($value))) {
            return true;
        }

        return (bool) preg_match('/(_file|_image|_upload|Files?|files|logo|audio|video|media|cover_image|panel_files|puzzle_image|replacement_image)$/i', $propertyName);
    }

    protected function arrayHasUpload(array $value): bool
    {
        foreach ($value as $item) {
            if ($item instanceof TemporaryUploadedFile) {
                return true;
            }
            if (is_array($item) && $this->arrayHasUpload($item)) {
                return true;
            }
        }

        return false;
    }
}
