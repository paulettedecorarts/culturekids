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
    
    // New Platform section items
    Route::get('themes', \App\Livewire\Admin\ThemesManager::class)->name('themes');
    Route::get('analytics', \App\Livewire\Admin\AnalyticsManager::class)->name('analytics');
});

// CMS Editor (Content Production)
Route::middleware(['auth', 'verified', 'role:cms_editor|super_admin'])->prefix('cms/editor')->name('cms.editor.')->group(function () {
    Route::get('/dashboard', \App\Livewire\CMS\Dashboard::class)->name('dashboard');
    Route::get('/tribes', \App\Livewire\CMS\TribeDirectory::class)->name('tribes');
    Route::get('/story-packs', \App\Livewire\CMS\StoryPacks::class)->name('story-packs');
    Route::get('/assets', \App\Livewire\CMS\Assets::class)->name('assets');
    Route::get('/translations', \App\Livewire\CMS\Translations::class)->name('translations');
    Route::get('/songs', \App\Livewire\CMS\Songs::class)->name('songs');
    Route::get('/activities', \App\Livewire\CMS\Activities::class)->name('activities');
});

// CMS Organisational Admin (Management & Site)
Route::middleware(['auth', 'verified', 'role:org_admin|super_admin'])->prefix('cms/admin')->name('cms.admin.')->group(function () {
    Route::get('/dashboard', \App\Livewire\CMS\AdminDashboard::class)->name('dashboard');
    Route::get('/site', \App\Livewire\CMS\Site::class)->name('site');
    Route::get('/themes', \App\Livewire\CMS\Themes::class)->name('themes');
    Route::get('/organizations', \App\Livewire\CMS\Organizations::class)->name('organizations');
    Route::get('/analytics', \App\Livewire\CMS\Analytics::class)->name('analytics');
});

// Teacher Hub (Classroom Context)
Route::middleware(['auth', 'verified', 'role:teacher|super_admin'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/dashboard', \App\Livewire\Teacher\Dashboard::class)->name('dashboard');
    Route::get('/my-class', \App\Livewire\Teacher\MyClass::class)->name('class');
    Route::get('/resources', \App\Livewire\Teacher\Resources::class)->name('resources');
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
