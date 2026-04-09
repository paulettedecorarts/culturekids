<?php

use App\Http\Controllers\Admin\ImpersonationController;
use App\Livewire\Admin\ActivitiesManager;
use App\Livewire\Admin\ActivityDetailPage;
use App\Livewire\Admin\AgeCategories;
use App\Livewire\Admin\AgeProfileDetailPage;
use App\Livewire\Admin\AnalyticsManager;
use App\Livewire\Admin\AssetsManager;
use App\Livewire\Admin\AuditLogs;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\ImpersonateUser;
use App\Livewire\Admin\LanguagesManager;
use App\Livewire\Admin\ModuleRegistry;
use App\Livewire\Admin\ModuleToggles;
use App\Livewire\Admin\OrganizationDetail;
use App\Livewire\Admin\OrganizationsManager;
use App\Livewire\Admin\PanelEditor;
use App\Livewire\Admin\PermissionsManager;
use App\Livewire\Admin\SongDetailPage;
use App\Livewire\Admin\SongsManager;
use App\Livewire\Admin\StoriesManager;
use App\Livewire\Admin\StoryDetail;
use App\Livewire\Admin\StoryForm;
use App\Livewire\Admin\StoryPacksManager;
use App\Livewire\Admin\ThemesManager;
use App\Livewire\Admin\TranslationsManager;
use App\Livewire\Admin\TribeDetail;
use App\Livewire\Admin\TribeForm;
use App\Livewire\Admin\TribeManager;
use App\Livewire\Admin\UserDetail;
use App\Livewire\Admin\UserForm;
use App\Livewire\Admin\UserManagement;
use App\Livewire\CMS\Activities;
use App\Livewire\CMS\AdminDashboard;
use App\Livewire\CMS\Analytics;
use App\Livewire\CMS\Assets;
use App\Livewire\CMS\Organizations;
use App\Livewire\CMS\Site;
use App\Livewire\CMS\Songs;
use App\Livewire\CMS\StoryPacks;
use App\Livewire\CMS\Themes;
use App\Livewire\CMS\Translations;
use App\Livewire\CMS\TribeDirectory;
use App\Livewire\Teacher\MainDashboard;
use App\Livewire\Teacher\MyClass;
use App\Livewire\Teacher\PrintCenter;
use App\Livewire\Teacher\Reports;
use App\Livewire\Teacher\StoryLibrary;
use App\Livewire\Teacher\TribesExplorer;
use App\Livewire\Teacher\Worksheets;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Super Admin Routes
Route::middleware(['auth', 'verified', 'role:super_admin', 'log.admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('dashboard', Dashboard::class)->name('dashboard');
    Route::get('users', UserManagement::class)->name('users');
    Route::get('users/create', UserForm::class)->name('users.create');
    Route::get('users/{user}', UserDetail::class)->name('users.detail');
    Route::get('users/{user}/edit', UserForm::class)->name('users.edit');
    Route::get('organizations', OrganizationsManager::class)->name('organizations');
    Route::get('organizations/{organization}', OrganizationDetail::class)->name('organizations.detail');
    Route::get('modules', ModuleToggles::class)->name('modules');
    Route::get('permissions', PermissionsManager::class)->name('permissions');

    // Content Management
    Route::get('stories', StoriesManager::class)->name('stories');
    Route::get('stories/create', StoryForm::class)->name('stories.create');
    Route::get('stories/{id}/edit', StoryForm::class)->name('stories.edit');
    Route::get('stories/{id}/panels', PanelEditor::class)->name('stories.panels');
    Route::get('stories/{id}', StoryDetail::class)->name('stories.detail');
    Route::get('tribe-registry', TribeManager::class)->name('tribe-registry'); // Now 'Tribe Directory'
    Route::get('story-packs', StoryPacksManager::class)->name('story-packs');
    Route::get('assets', AssetsManager::class)->name('assets');
    Route::get('translations', TranslationsManager::class)->name('translations');

    // Activities section items
    Route::get('songs', SongsManager::class)->name('songs');
    Route::get('songs/create', SongDetailPage::class)->name('songs.create');
    Route::get('songs/{id}', SongDetailPage::class)->name('songs.detail');
    Route::get('activities', ActivitiesManager::class)->name('activities');
    Route::get('activities/create', ActivityDetailPage::class)->name('activities.create');
    Route::get('activities/{id}', ActivityDetailPage::class)->name('activities.detail');
    Route::get('/modules-registry', ModuleRegistry::class)->name('modules-registry');
    Route::get('/age-categories', AgeCategories::class)->name('age-categories');
    Route::get('/age-categories/create', AgeProfileDetailPage::class)->name('age-categories.create');
    Route::get('/age-categories/{id}', AgeProfileDetailPage::class)->name('age-categories.detail');
    Route::get('languages', LanguagesManager::class)->name('languages');

    // New Platform section items
    Route::get('themes', ThemesManager::class)->name('themes');
    Route::get('tribes', TribeManager::class)->name('tribes');
    Route::get('tribes/create', TribeForm::class)->name('tribes.create');
    Route::get('tribes/{tribe}', TribeDetail::class)->name('tribes.detail');
    Route::get('tribes/{tribe}/edit', TribeForm::class)->name('tribes.edit');
    Route::get('analytics', AnalyticsManager::class)->name('analytics');

    // Logs section
    Route::get('audit-logs', AuditLogs::class)->name('audit-logs');
    Route::get('impersonate', ImpersonateUser::class)->name('impersonate');
});

// Stop Impersonation - accessible by anyone currently impersonating
Route::middleware(['auth', 'verified'])->post('admin/stop-impersonation', [ImpersonationController::class, 'stop'])->name('admin.stop-impersonation');

// CMS Editor (Content Production)
Route::middleware(['auth', 'verified', 'role:cms_editor|super_admin'])->prefix('cms/editor')->name('cms.editor.')->group(function () {
    Route::get('/dashboard', App\Livewire\CMS\Dashboard::class)->name('dashboard');
    Route::get('/tribes', TribeDirectory::class)->name('tribes');
    Route::get('/story-packs', StoryPacks::class)->name('story-packs');
    Route::get('/assets', Assets::class)->name('assets');
    Route::get('/translations', Translations::class)->name('translations');
    Route::get('/songs', Songs::class)->name('songs');
    Route::get('/activities', Activities::class)->name('activities');
});

// CMS Organisational Admin (Management & Site)
Route::middleware(['auth', 'verified', 'role:org_admin|super_admin'])->prefix('cms/admin')->name('cms.admin.')->group(function () {
    Route::get('/dashboard', AdminDashboard::class)->name('dashboard');
    Route::get('/site', Site::class)->name('site');
    Route::get('/themes', Themes::class)->name('themes');
    Route::get('/organizations', Organizations::class)->name('organizations');
    Route::get('/analytics', Analytics::class)->name('analytics');
});

// Teacher Hub (Classroom Context)
Route::middleware(['auth', 'verified', 'role:teacher|super_admin'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/dashboard', MainDashboard::class)->name('dashboard');
    Route::get('/lessons', App\Livewire\Teacher\Dashboard::class)->name('lessons');
    Route::get('/my-class', MyClass::class)->name('class');
    Route::get('/reports', Reports::class)->name('reports');

    // Content Modules
    Route::get('/library', StoryLibrary::class)->name('library');
    Route::get('/tribes', TribesExplorer::class)->name('tribes');
    Route::get('/print-center', PrintCenter::class)->name('print-center');
    Route::get('/worksheets', Worksheets::class)->name('worksheets');
});

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__.'/auth.php';

Route::get('/health', function () {
    $dbStatus = 'Disconnected';
    $redisStatus = 'Disconnected';

    try {
        DB::connection()->getPdo();
        $dbStatus = 'Connected';
    } catch (Exception $e) {
        $dbStatus = 'Error: '.$e->getMessage();
    }

    try {
        $redis = Redis::connection();
        $redis->ping();
        $redisStatus = 'Connected';
    } catch (Exception $e) {
        $redisStatus = 'Error: '.$e->getMessage();
    }

    return view('health', [
        'db' => $dbStatus,
        'redis' => $redisStatus,
        'php' => PHP_VERSION,
        'env' => config('app.env'),
    ]);
});
