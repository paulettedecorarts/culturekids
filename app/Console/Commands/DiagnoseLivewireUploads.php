<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

class DiagnoseLivewireUploads extends Command
{
    protected $signature = 'livewire:diagnose-uploads';

    protected $description = 'Check PHP upload limits, storage paths, Redis, and logging for Livewire temp uploads';

    public function handle(): int
    {
        $this->info('Livewire upload diagnostics');
        $this->newLine();

        $this->table(['Setting', 'Value'], [
            ['app.url (config)', (string) config('app.url')],
            ['app.env', (string) config('app.env')],
            ['app.debug', config('app.debug') ? 'true' : 'false'],
            ['livewire disk', (string) config('livewire.temporary_file_upload.disk')],
            ['livewire directory', (string) config('livewire.temporary_file_upload.directory')],
            ['upload middleware', json_encode(config('livewire.temporary_file_upload.middleware'))],
            ['PHP upload_max_filesize', ini_get('upload_max_filesize')],
            ['PHP post_max_size', ini_get('post_max_size')],
            ['PHP upload_tmp_dir', ini_get('upload_tmp_dir') ?: '(system default)'],
            ['PHP max_file_uploads', ini_get('max_file_uploads')],
        ]);

        $tmpDir = storage_path('app/livewire-tmp');
        $this->line('storage/app/livewire-tmp writable: '.(is_writable($tmpDir) ? 'yes' : 'NO'));
        $this->line('storage/logs writable: '.(is_writable(storage_path('logs')) ? 'yes' : 'NO'));

        try {
            Storage::disk(config('livewire.temporary_file_upload.disk'))->put(
                trim(config('livewire.temporary_file_upload.directory'), '/').'/diag-test.txt',
                'ok'
            );
            $this->info('Storage disk write: OK');
        } catch (\Throwable $e) {
            $this->error('Storage disk write failed: '.$e->getMessage());
        }

        try {
            Log::channel('uploads')->info('livewire.diagnose', ['ok' => true]);
            $this->info('uploads log channel write: OK ('.storage_path('logs/uploads.log').')');
        } catch (\Throwable $e) {
            $this->error('uploads log channel failed: '.$e->getMessage());
        }

        try {
            Cache::store(config('cache.default'))->put('livewire_upload_diag', 'ok', 10);
            $this->info('Cache store ('.config('cache.default').'): OK');
        } catch (\Throwable $e) {
            $this->error('Cache store failed: '.$e->getMessage());
        }

        $route = Route::getRoutes()->getByName('livewire.upload-file');
        $this->line('Route livewire.upload-file: '.($route ? 'registered' : 'MISSING'));
        if ($route) {
            $this->line('  Route action: '.$route->getActionName());
        }

        try {
            $resolved = app(\Livewire\Features\SupportFileUploads\FileUploadController::class);
            $this->line('  Container resolves to: '.$resolved::class);
        } catch (\Throwable $e) {
            $this->error('  Container resolve failed: '.$e->getMessage());
        }

        $this->newLine();
        $this->comment('After an upload attempt, run: tail -30 storage/logs/uploads.log');

        return self::SUCCESS;
    }
}
