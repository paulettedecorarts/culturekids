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

        $filesMeta = $this->filesMeta();

        self::log('info', 'livewire.upload.request', [
            'content_length' => $request->server('CONTENT_LENGTH'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'max_file_uploads' => ini_get('max_file_uploads'),
            'files_meta' => $filesMeta,
            'has_valid_signature' => $request->hasValidSignature(),
            'user_id' => auth()->id(),
            'ip' => $request->ip(),
        ]);

        try {
            $response = $next($request);
        } catch (\Throwable $e) {
            self::log('error', 'livewire.upload.exception', [
                'message' => $e->getMessage(),
                'class' => $e::class,
                'files_meta' => $filesMeta,
                'user_id' => auth()->id(),
            ]);

            throw $e;
        }

        if ($response->getStatusCode() >= 400) {
            self::log('error', 'livewire.upload.response.error', [
                'status' => $response->getStatusCode(),
                'content_length' => $request->server('CONTENT_LENGTH'),
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size'),
                'files_meta' => $filesMeta,
                'has_valid_signature' => $request->hasValidSignature(),
                'response_content' => method_exists($response, 'getContent') ? $response->getContent() : null,
                'user_id' => auth()->id(),
            ]);
        }

        return $response;
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public static function log(string $level, string $message, array $context = []): void
    {
        try {
            Log::channel('uploads')->{$level}($message, $context);
        } catch (\Throwable $e) {
            error_log(sprintf(
                '[culturekids uploads] %s %s (log channel failed: %s)',
                $message,
                json_encode($context, JSON_UNESCAPED_SLASHES) ?: '{}',
                $e->getMessage()
            ));
            report($e);
        }
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    protected function filesMeta(): array
    {
        $filesMeta = [];
        foreach ($_FILES as $fieldName => $meta) {
            $filesMeta[$fieldName] = [
                'name' => $meta['name'] ?? null,
                'type' => $meta['type'] ?? null,
                'size' => $meta['size'] ?? null,
                'error' => $meta['error'] ?? null,
            ];
        }

        return $filesMeta;
    }
}
