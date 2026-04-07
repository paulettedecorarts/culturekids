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
    
    // Content Management
    Route::get('stories', \App\Livewire\Admin\StoriesManager::class)->name('stories');
    Route::get('tribe-registry', \App\Livewire\Admin\TribeManager::class)->name('tribe-registry'); // Now 'Tribe Directory'
    Route::get('story-packs', \App\Livewire\Admin\StoryPacksManager::class)->name('story-packs');
    Route::get('assets', \App\Livewire\Admin\AssetsManager::class)->name('assets');
    Route::get('translations', \App\Livewire\Admin\TranslationsManager::class)->name('translations');
    
    // Activities section items
    Route::get('songs', \App\Livewire\Admin\SongsManager::class)->name('songs');
    Route::get('activities', \App\Livewire\Admin\ActivitiesManager::class)->name('activities');
    Route::get('/modules-registry', \App\Livewire\Admin\ModuleRegistry::class)->name('modules-registry');
    Route::get('/age-categories', App\Livewire\Admin\AgeCategories::class)->name('age-categories');
    Route::get('languages', \App\Livewire\Admin\LanguagesManager::class)->name('languages');
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
