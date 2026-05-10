<?php

use App\Http\Controllers\Admin\ImpersonationController;
use App\Livewire\Admin\ActivitiesManager;
use App\Livewire\Admin\ActivityDetailPage;
use App\Livewire\Admin\ActivityTypeSelector;
use App\Livewire\Admin\AgeCategories;
use App\Livewire\Admin\AgeProfileDetailPage;
use App\Livewire\Admin\AnalyticsManager;
use App\Livewire\Admin\AssetsManager;
use App\Livewire\Admin\AuditLogs;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\ImpersonateUser;
use App\Livewire\Admin\LanguageDetailPage;
use App\Livewire\Admin\LanguagesManager;
use App\Livewire\Admin\ModuleRegistry;
use App\Livewire\Admin\ModuleToggles;
use App\Livewire\Admin\OrganizationCreate;
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
use App\Livewire\CMS\ApprovedContent;
use App\Livewire\CMS\OfflineBundles;
use App\Livewire\CMS\Organizations;
use App\Livewire\CMS\OrgClassroomsManager;
use App\Livewire\CMS\OrgPeopleManager;
use App\Livewire\CMS\Puzzles\PuzzleEditor;
use App\Livewire\CMS\Puzzles\PuzzleShow;
use App\Livewire\CMS\Puzzles\PuzzlesIndex;
use App\Livewire\CMS\ReviewQueue;
use App\Livewire\CMS\SongPreview;
use App\Livewire\CMS\StoryPreview;
use App\Livewire\Teacher\MainDashboard;
use App\Livewire\Teacher\MyClass;
use App\Livewire\Teacher\PrintCenter;
use App\Livewire\Teacher\Reports;
use App\Livewire\Teacher\StoryLibrary;
use App\Livewire\Teacher\TeacherStoryReader;
use App\Livewire\Teacher\TribesExplorer;
use App\Livewire\Teacher\Worksheets;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

// Super Admin Routes
Route::middleware(['auth', 'verified', 'role:super_admin', 'log.admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('dashboard', Dashboard::class)->name('dashboard');
    Route::get('users', UserManagement::class)->name('users');
    Route::get('users/create', UserForm::class)->name('users.create');
    Route::get('users/{user}', UserDetail::class)->name('users.detail');
    Route::get('users/{user}/edit', UserForm::class)->name('users.edit');
    Route::get('organizations', OrganizationsManager::class)->name('organizations');
    Route::get('organizations/create', OrganizationCreate::class)->name('organizations.create');
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
    Route::get('songs/activities/create', \App\Livewire\CMS\Songs\SongEditor::class)->name('songs.activities.create');
    Route::get('songs/activities/{id}/edit', \App\Livewire\CMS\Songs\SongEditor::class)->name('songs.activities.edit');
    Route::get('songs/activities/{id}', \App\Livewire\CMS\Songs\SongShow::class)->name('songs.activities.show');
    Route::get('songs/activities/{id}/preview', \App\Livewire\CMS\Songs\SongActivityPreview::class)->name('songs.activities.preview');
    
    // Drawing activities
    Route::get('drawings', \App\Livewire\CMS\Drawings\DrawingManager::class)->name('drawings');
    Route::get('drawings/create', \App\Livewire\CMS\Drawings\DrawingEditor::class)->name('drawings.create');
    Route::get('drawings/{id}/edit', \App\Livewire\CMS\Drawings\DrawingEditor::class)->name('drawings.edit');
    Route::get('drawings/{id}', \App\Livewire\CMS\Drawings\DrawingShow::class)->name('drawings.show');
    Route::get('drawings/{id}/play', \App\Livewire\Student\DrawingPlayer::class)->name('drawings.play');

    Route::get('language-activities', \App\Livewire\CMS\Languages\LanguageActivityManager::class)->name('language-activities');
    Route::get('language-activities/create', \App\Livewire\CMS\Languages\LanguageActivityEditor::class)->name('language-activities.create');
    Route::get('language-activities/{id}/edit', \App\Livewire\CMS\Languages\LanguageActivityEditor::class)->name('language-activities.edit');
    Route::get('language-activities/{id}', \App\Livewire\CMS\Languages\LanguageActivityShow::class)->name('language-activities.show');

    Route::get('games', \App\Livewire\CMS\Games\GameManager::class)->name('games');
    Route::get('games/create', \App\Livewire\CMS\Games\GameEditor::class)->name('games.create');
    Route::get('games/{id}/edit', \App\Livewire\CMS\Games\GameEditor::class)->name('games.edit');
    Route::get('games/{id}', \App\Livewire\CMS\Games\GameShow::class)->name('games.show');

    Route::get('mazes', \App\Livewire\CMS\Mazes\MazeManager::class)->name('mazes');
    Route::get('mazes/create', \App\Livewire\CMS\Mazes\MazeEditor::class)->name('mazes.create');
    Route::get('mazes/{id}/edit', \App\Livewire\CMS\Mazes\MazeEditor::class)->name('mazes.edit');
    Route::get('mazes/{id}', \App\Livewire\CMS\Mazes\MazeShow::class)->name('mazes.show');

    Route::get('spot-differences', \App\Livewire\CMS\SpotDifferences\SpotDifferenceManager::class)->name('spot-differences');
    Route::get('spot-differences/create', \App\Livewire\CMS\SpotDifferences\SpotDifferenceEditor::class)->name('spot-differences.create');
    Route::get('spot-differences/{id}/edit', \App\Livewire\CMS\SpotDifferences\SpotDifferenceEditor::class)->name('spot-differences.edit');
    Route::get('spot-differences/{id}', \App\Livewire\CMS\SpotDifferences\SpotDifferenceShow::class)->name('spot-differences.show');
    
    Route::get('puzzles/create', PuzzleEditor::class)->name('puzzles.create');
    Route::get('puzzles/{id}/edit', PuzzleEditor::class)->name('puzzles.edit');
    Route::get('puzzles/{id}', PuzzleShow::class)->name('puzzles.show');
    Route::get('puzzles', PuzzlesIndex::class)->name('puzzles');

    Route::get('activities', ActivitiesManager::class)->name('activities');
    Route::get('activities/types', ActivityTypeSelector::class)->name('activities.types');
    Route::get('activities/create', ActivityDetailPage::class)->name('activities.create');
    Route::get('activities/{id}', ActivityDetailPage::class)->name('activities.detail');
    Route::get('/modules-registry', ModuleRegistry::class)->name('modules-registry');
    Route::get('/age-categories', AgeCategories::class)->name('age-categories');
    Route::get('/age-categories/create', AgeProfileDetailPage::class)->name('age-categories.create');
    Route::get('/age-categories/{id}', AgeProfileDetailPage::class)->name('age-categories.detail');
    Route::get('languages', LanguagesManager::class)->name('languages');
    Route::get('languages/create', LanguageDetailPage::class)->name('languages.create');
    Route::get('languages/{id}', LanguageDetailPage::class)->name('languages.detail');

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
// Super Admin must impersonate a cms_editor user to access these routes.
Route::middleware(['auth', 'verified', 'role:cms_editor', 'portal.role:cms_editor'])->prefix('cms/editor')->name('cms.editor.')->group(function () {
    Route::get('/dashboard', App\Livewire\CMS\Dashboard::class)->name('dashboard');

    // Reuse mature admin content modules in editor portal context.
    Route::get('/tribes', TribeManager::class)->name('tribes');
    Route::get('/tribes/create', TribeForm::class)->name('tribes.create');
    Route::get('/tribes/{tribe}', TribeDetail::class)->name('tribes.detail');
    Route::get('/tribes/{tribe}/edit', TribeForm::class)->name('tribes.edit');

    Route::get('/story-packs', StoriesManager::class)->name('story-packs');
    Route::get('/story-packs/create', StoryForm::class)->name('story-packs.create');
    Route::get('/story-packs/{id}/edit', StoryForm::class)->name('story-packs.edit');
    Route::get('/story-packs/{id}/panels', PanelEditor::class)->name('story-packs.panels');
    Route::get('/story-packs/{id}', StoryDetail::class)->name('story-packs.detail');
    Route::get('/assets', AssetsManager::class)->name('assets');
    Route::get('/translations', TranslationsManager::class)->name('translations');

    Route::get('/songs', SongsManager::class)->name('songs');
    Route::get('/songs/create', SongDetailPage::class)->name('songs.create');
    Route::get('/songs/{id}', SongDetailPage::class)->name('songs.detail');
    Route::get('/songs/activities/create', \App\Livewire\CMS\Songs\SongEditor::class)->name('songs.activities.create');
    Route::get('/songs/activities/{id}/edit', \App\Livewire\CMS\Songs\SongEditor::class)->name('songs.activities.edit');
    Route::get('/songs/activities/{id}', \App\Livewire\CMS\Songs\SongShow::class)->name('songs.activities.show');

    Route::get('/drawings', \App\Livewire\CMS\Drawings\DrawingManager::class)->name('drawings');
    Route::get('/drawings/create', \App\Livewire\CMS\Drawings\DrawingEditor::class)->name('drawings.create');
    Route::get('/drawings/{id}/edit', \App\Livewire\CMS\Drawings\DrawingEditor::class)->name('drawings.edit');
    Route::get('/drawings/{id}', \App\Livewire\CMS\Drawings\DrawingShow::class)->name('drawings.show');
    Route::get('/drawings/{id}/play', \App\Livewire\Student\DrawingPlayer::class)->name('drawings.play');

    Route::get('/language-activities', \App\Livewire\CMS\Languages\LanguageActivityManager::class)->name('language-activities');
    Route::get('/language-activities/create', \App\Livewire\CMS\Languages\LanguageActivityEditor::class)->name('language-activities.create');
    Route::get('/language-activities/{id}/edit', \App\Livewire\CMS\Languages\LanguageActivityEditor::class)->name('language-activities.edit');
    Route::get('/language-activities/{id}', \App\Livewire\CMS\Languages\LanguageActivityShow::class)->name('language-activities.show');

    Route::get('/games', \App\Livewire\CMS\Games\GameManager::class)->name('games');
    Route::get('/games/create', \App\Livewire\CMS\Games\GameEditor::class)->name('games.create');
    Route::get('/games/{id}/edit', \App\Livewire\CMS\Games\GameEditor::class)->name('games.edit');
    Route::get('/games/{id}', \App\Livewire\CMS\Games\GameShow::class)->name('games.show');

    Route::get('/mazes', \App\Livewire\CMS\Mazes\MazeManager::class)->name('mazes');
    Route::get('/mazes/create', \App\Livewire\CMS\Mazes\MazeEditor::class)->name('mazes.create');
    Route::get('/mazes/{id}/edit', \App\Livewire\CMS\Mazes\MazeEditor::class)->name('mazes.edit');
    Route::get('/mazes/{id}', \App\Livewire\CMS\Mazes\MazeShow::class)->name('mazes.show');

    Route::get('/spot-differences', \App\Livewire\CMS\SpotDifferences\SpotDifferenceManager::class)->name('spot-differences');
    Route::get('/spot-differences/create', \App\Livewire\CMS\SpotDifferences\SpotDifferenceEditor::class)->name('spot-differences.create');
    Route::get('/spot-differences/{id}/edit', \App\Livewire\CMS\SpotDifferences\SpotDifferenceEditor::class)->name('spot-differences.edit');
    Route::get('/spot-differences/{id}', \App\Livewire\CMS\SpotDifferences\SpotDifferenceShow::class)->name('spot-differences.show');
    Route::get('/songs/activities/{id}/preview', \App\Livewire\CMS\Songs\SongActivityPreview::class)->name('songs.activities.preview');

    Route::get('/flashcards', ActivitiesManager::class)->name('flashcards');
    Route::get('/offline-bundles', OfflineBundles::class)->name('offline-bundles');

    Route::get('/puzzles/create', PuzzleEditor::class)->name('puzzles.create');
    Route::get('/puzzles/{id}/edit', PuzzleEditor::class)->name('puzzles.edit');
    Route::get('/puzzles/{id}', PuzzleShow::class)->name('puzzles.show');
    Route::get('/puzzles', PuzzlesIndex::class)->name('puzzles');

    Route::get('/activities', ActivitiesManager::class)->name('activities');
    Route::get('/activities/types', ActivityTypeSelector::class)->name('activities.types');
    Route::get('/activities/create', ActivityDetailPage::class)->name('activities.create');
    Route::get('/activities/{id}', ActivityDetailPage::class)->name('activities.detail');
    
    Route::get('/people', App\Livewire\CMS\EditorPeopleManager::class)->name('people');
});

// CMS Organisational Admin (Management & Site)
// Super Admin must impersonate an org_admin user to access these routes.
Route::middleware(['auth', 'verified', 'role:org_admin', 'portal.role:org_admin'])->prefix('cms/admin')->name('cms.admin.')->group(function () {
    Route::get('/dashboard', AdminDashboard::class)->name('dashboard');
    Route::get('/review', ReviewQueue::class)->name('review');
    Route::get('/approved-content', ApprovedContent::class)->name('approved-content');
    Route::get('/approved-content/stories/{id}', StoryPreview::class)->name('approved-content.stories.show');
    Route::get('/approved-content/songs/{id}', SongPreview::class)->name('approved-content.songs.show');
    // Reuse mature admin theme management with org-admin scoping.
    Route::get('/themes', ThemesManager::class)->name('themes');
    Route::get('/organizations', Organizations::class)->name('organizations');
    Route::get('/people', OrgPeopleManager::class)->name('people');
    Route::get('/classrooms', OrgClassroomsManager::class)->name('classrooms');
    Route::get('/analytics', Analytics::class)->name('analytics');
});

// Teacher Hub (Classroom Context)
// Super Admin must impersonate a teacher user to access these routes.
Route::middleware(['auth', 'verified', 'role:teacher', 'portal.role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/dashboard', MainDashboard::class)->name('dashboard');
    Route::get('/lessons', App\Livewire\Teacher\Dashboard::class)->name('lessons');
    Route::get('/my-class', MyClass::class)->name('my-class');
    Route::get('/my-class/{id}', App\Livewire\Teacher\ChildDetail::class)->name('child-detail');
    Route::get('/reports', Reports::class)->name('reports');

    // Content Modules
    Route::get('/library', StoryLibrary::class)->name('library');
    Route::get('/stories/{id}', TeacherStoryReader::class)->name('stories.show');
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
