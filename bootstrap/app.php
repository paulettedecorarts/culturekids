<?php

use App\Http\Middleware\EnsureTeacherLibraryAccess;
use App\Http\Middleware\LogLivewireUploadDiagnostics;
use App\Http\Middleware\LogSuperAdminActions;
use App\Http\Middleware\EnsurePortalRoleIsolation;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api/v1',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Log upload requests before web/session/throttle (route middleware runs too late if those fail).
        $middleware->prepend(LogLivewireUploadDiagnostics::class);

        // Trust all proxies in production (Coolify/nginx reverse proxy)
        $middleware->trustProxies(at: '*');

        $middleware->validateCsrfTokens(except: [
            'livewire/upload-file',
        ]);

        // Super Admin can operate the panel while the app is in maintenance mode.
        $middleware->preventRequestsDuringMaintenance(except: [
            'admin',
            'admin/*',
            'livewire/*',
            'login',
            'logout',
            'up',
        ]);

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'log.admin' => LogSuperAdminActions::class,
            'log.livewire.upload' => LogLivewireUploadDiagnostics::class,
            'portal.role' => EnsurePortalRoleIsolation::class,
            'teacher.library' => EnsureTeacherLibraryAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->reportable(function (\Throwable $e): void {
            $request = request();
            if (! $request?->is('livewire/upload-file')) {
                return;
            }

            $context = [
                'message' => $e->getMessage(),
                'class' => $e::class,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'livewire_tmp_writable' => is_writable(storage_path('app/livewire-tmp')),
            ];
            LogLivewireUploadDiagnostics::log('error', 'livewire.upload.reported', $context);
            error_log('[culturekids] livewire.upload.reported '.json_encode($context, JSON_UNESCAPED_SLASHES));
        });
    })->create();
