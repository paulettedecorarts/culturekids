<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogLivewireUploadDiagnostics
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->is('livewire/upload-file')) {
            return $next($request);
        }

        $filesMeta = [];
        foreach ($_FILES as $fieldName => $meta) {
            $filesMeta[$fieldName] = [
                'name' => $meta['name'] ?? null,
                'type' => $meta['type'] ?? null,
                'size' => $meta['size'] ?? null,
                'error' => $meta['error'] ?? null,
            ];
        }

        Log::info('livewire.upload.request', [
            'content_length' => $request->server('CONTENT_LENGTH'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'max_file_uploads' => ini_get('max_file_uploads'),
            'files_meta' => $filesMeta,
            'user_id' => auth()->id(),
            'ip' => $request->ip(),
        ]);

        $response = $next($request);

        if ($response->getStatusCode() >= 400) {
            Log::warning('livewire.upload.response.error', [
                'status' => $response->getStatusCode(),
                'content_length' => $request->server('CONTENT_LENGTH'),
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size'),
                'files_meta' => $filesMeta,
                'response_content' => method_exists($response, 'getContent') ? $response->getContent() : null,
                'user_id' => auth()->id(),
            ]);
        }

        return $response;
    }
}
