<?php

namespace App\Providers;

use App\Services\Push\FcmPushGateway;
use App\Services\Push\LogPushGateway;
use App\Services\Push\PushGateway;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PushGateway::class, function () {
            return config('push.provider') === 'fcm'
                ? new FcmPushGateway
                : new LogPushGateway;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS in production (fixes mixed content errors behind reverse proxy)
        if (config('app.env') === 'production') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        $this->configureDefaults();
        $this->ensureLivewireTemporaryUploadDirectoryExists();
    }

    /**
     * Livewire stores temp uploads on the configured disk (default: local → storage/app/private/livewire-tmp).
     */
    protected function ensureLivewireTemporaryUploadDirectoryExists(): void
    {
        try {
            $disk = config('livewire.temporary_file_upload.disk') ?: config('filesystems.default');
            $dir = trim((string) (config('livewire.temporary_file_upload.directory') ?: 'livewire-tmp'), '/');
            Storage::disk($disk)->makeDirectory($dir);
        } catch (\Throwable) {
            //
        }
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
