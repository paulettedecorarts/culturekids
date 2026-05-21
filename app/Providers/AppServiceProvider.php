<?php

namespace App\Providers;

use App\Models\LanguageActivity;
use App\Models\LanguageActivityWord;
use App\Models\ContentTranslation;
use App\Models\PanelVocabTag;
use App\Observers\TranslationCoverageObserver;
use App\Services\Push\FcmPushGateway;
use App\Services\Push\LogPushGateway;
use App\Services\Push\PushGateway;
use Carbon\CarbonImmutable;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Livewire\Livewire;

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
        $this->configurePagination();
        $this->configureBladeLayouts();
        $this->configureLivewire();
        $this->ensureLivewireTemporaryUploadDirectoryExists();
        $this->registerTranslationCoverageObservers();
    }

    protected function registerTranslationCoverageObservers(): void
    {
        $observer = TranslationCoverageObserver::class;

        LanguageActivity::observe($observer);
        LanguageActivityWord::observe($observer);
        ContentTranslation::observe($observer);
        PanelVocabTag::observe($observer);
    }

    /**
     * Livewire stores temp uploads on the configured disk (default: local → storage/app/private/livewire-tmp).
     */
    protected function ensureLivewireTemporaryUploadDirectoryExists(): void
    {
        try {
            $disk = config('livewire.temporary_file_upload.disk') ?: config('filesystems.default');
            $dir = trim((string) (config('livewire.temporary_file_upload.directory') ?: ''), '/');
            if ($dir !== '') {
                Storage::disk($disk)->makeDirectory($dir);
            }
            Storage::disk('livewire-tmp')->makeDirectory('livewire-tmp');
            @chmod(storage_path('app/livewire-tmp'), 0775);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::channel('uploads')->error('livewire.tmp_dir.failed', [
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configurePagination(): void
    {
        Paginator::defaultView('vendor.pagination.portal');
        Paginator::defaultSimpleView('vendor.pagination.portal');
    }

    /**
     * Map resources/views/layouts/* to <x-layouts::…> (Flux / Livewire starter convention).
     */
    protected function configureBladeLayouts(): void
    {
        Blade::anonymousComponentPath(resource_path('views/layouts'), 'layouts');
        Blade::anonymousComponentPath(resource_path('views/pages'), 'pages');
    }

    protected function configureLivewire(): void
    {
        Livewire::componentHook(\App\Livewire\Hooks\AbsolutePaginationPath::class);
        Livewire::componentHook(\App\Livewire\Hooks\LogsFileUploadsHook::class);
    }

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
