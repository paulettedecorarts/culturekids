<?php

namespace App\Http\Livewire;

use App\Http\Middleware\LogLivewireUploadDiagnostics;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportFileUploads\FileUploadConfiguration;
use Livewire\Features\SupportFileUploads\FileUploadController as BaseFileUploadController;

class FileUploadController extends BaseFileUploadController
{
    public function handle()
    {
        try {
            return parent::handle();
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            LogLivewireUploadDiagnostics::log('error', 'livewire.upload.controller.exception', [
                'message' => $e->getMessage(),
                'class' => $e::class,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            if (config('app.debug')) {
                return new JsonResponse([
                    'message' => $e->getMessage(),
                    'exception' => $e::class,
                ], 500);
            }

            throw $e;
        }
    }

    public function validateAndStore($files, $disk)
    {
        Validator::make(
            ['files' => $files],
            [
                'files' => 'required|array|min:1',
                'files.*' => FileUploadConfiguration::rules(),
            ]
        )->validate();

        $fileHashPaths = collect($files)->map(function (UploadedFile $file) use ($disk) {
            $filename = self::safeHashFilename($file);

            return $file->storeAs('/'.FileUploadConfiguration::path(), $filename, [
                'disk' => $disk,
            ]);
        });

        return $fileHashPaths->map(function ($path) {
            return str_replace(FileUploadConfiguration::path('/'), '', $path);
        });
    }

    /**
     * Livewire embeds the original filename in the temp name; very long names exceed the 255-byte filesystem limit.
     */
    public static function safeHashFilename(UploadedFile $file): string
    {
        $original = $file->getClientOriginalName();
        if (strlen($original) > 120) {
            $ext = $file->getClientOriginalExtension();
            $base = pathinfo($original, PATHINFO_FILENAME);
            $base = substr($base, 0, 80);
            $original = $ext !== '' ? "{$base}.{$ext}" : $base;
        }

        $hash = str()->random(30);
        $meta = str('-meta'.base64_encode($original).'-')->replace('/', '_');
        $extension = $file->getClientOriginalExtension();
        $suffix = $extension !== '' && $extension !== '0' ? '.'.$extension : '';

        $name = $hash.$meta.$suffix;

        if (strlen($name) > 200) {
            $meta = '-meta'.substr(base64_encode($original), 0, 40).'-';
            $name = $hash.$meta.$suffix;
        }

        return $name;
    }
}
