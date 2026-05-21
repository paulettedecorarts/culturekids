<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Livewire\Features\SupportFileUploads\FileUploadConfiguration;

class CleanLivewireTempUploads extends Command
{
    protected $signature = 'livewire:clean-temp-uploads';

    protected $description = 'Delete Livewire temporary uploads older than 24 hours';

    public function handle(): int
    {
        if (FileUploadConfiguration::isUsingS3()) {
            $this->warn('S3 temp uploads use bucket lifecycle rules; skipping local cleanup.');

            return self::SUCCESS;
        }

        $storage = FileUploadConfiguration::storage();
        $cutoff = now()->subDay()->timestamp;
        $deleted = 0;

        foreach ($storage->allFiles(FileUploadConfiguration::path()) as $filePathname) {
            if (! $storage->exists($filePathname)) {
                continue;
            }

            if ($cutoff > $storage->lastModified($filePathname)) {
                $storage->delete($filePathname);
                $deleted++;
            }
        }

        $this->info("Deleted {$deleted} expired temporary upload(s).");

        return self::SUCCESS;
    }
}
