<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Super Admin Routes
Route::middleware(['auth', 'verified', 'role:super_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('dashboard', \App\Livewire\Admin\Dashboard::class)->name('dashboard');
    Route::get('users', \App\Livewire\Admin\UserManagement::class)->name('users');
    Route::get('organizations', \App\Livewire\Admin\OrganizationsManager::class)->name('organizations');
    Route::get('modules', \App\Livewire\Admin\ModuleToggles::class)->name('modules');
    Route::get('permissions', \App\Livewire\Admin\PermissionsManager::class)->name('permissions');
});

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';

Route::get('/health', function () {
    $dbStatus = 'Disconnected';
    $redisStatus = 'Disconnected';
    
    try {
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        $dbStatus = 'Connected';
    } catch (\Exception $e) {
        $dbStatus = 'Error: ' . $e->getMessage();
    }

    try {
        $redis = \Illuminate\Support\Facades\Redis::connection();
        $redis->ping();
        $redisStatus = 'Connected';
    } catch (\Exception $e) {
        $redisStatus = 'Error: ' . $e->getMessage();
    }

    return view('health', [
        'db' => $dbStatus,
        'redis' => $redisStatus,
        'php' => PHP_VERSION,
        'env' => config('app.env'),
    ]);
});
