<?php

use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\AgeProfileController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChildProfileController;
use App\Http\Controllers\Api\ComicController;
use App\Http\Controllers\Api\LanguageRegistryController;
use App\Http\Controllers\Api\OrganisationModuleAdminController;
use App\Http\Controllers\Api\ProgressController;
use App\Http\Controllers\Api\PushDeviceController;
use App\Http\Controllers\Api\ReadingProgressController;
use App\Http\Controllers\Api\SongController;
use App\Http\Controllers\Api\TribeCatalogController;
use Illuminate\Support\Facades\Route;

// Public Mobile Auth Routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Protected Mobile API Routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::get('/auth/user', [AuthController::class, 'me']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    
    // Content
    Route::get('/age-profiles', [AgeProfileController::class, 'index']);
    Route::get('/languages', [LanguageRegistryController::class, 'index']);
    Route::get('/tribes', [TribeCatalogController::class, 'index']);
    
    // Activities
    Route::get('/tribes/{tribeId}/activities', [ActivityController::class, 'getTribeActivities']);
    Route::get('/activities/{id}', [ActivityController::class, 'show']);
    
    // Comics/Stories
    Route::get('/comics', [ComicController::class, 'index']);
    Route::get('/comics/{id}', [ComicController::class, 'show']);
    Route::post('/comics/{id}/complete', [ComicController::class, 'complete']);
    Route::get('/tribes/{tribeId}/comics', [ComicController::class, 'getByTribe']);
    
    // Songs
    Route::get('/songs', [SongController::class, 'index']);
    Route::get('/songs/{id}', [SongController::class, 'show']);
    Route::get('/tribes/{tribeId}/songs', [SongController::class, 'getByTribe']);
    
    // Child Profiles
    Route::get('/child-profiles', [ChildProfileController::class, 'index']);
    Route::post('/child-profiles', [ChildProfileController::class, 'store']);
    Route::get('/child-profiles/{id}', [ChildProfileController::class, 'show']);
    Route::put('/child-profiles/{id}', [ChildProfileController::class, 'update']);
    Route::delete('/child-profiles/{id}', [ChildProfileController::class, 'destroy']);
    
    // Progress & Sync
    Route::post('/progress/events', [ProgressController::class, 'recordEvents']);
    Route::get('/progress/child/{childId}', [ProgressController::class, 'getChildProgress']);
    Route::post('/sync', [ProgressController::class, 'sync']);
    
    // Reading Progress
    Route::post('/reading-progress', [ReadingProgressController::class, 'updateProgress']);
    Route::get('/reading-progress/{comicId}', [ReadingProgressController::class, 'getProgress']);
    Route::get('/reading-progress', [ReadingProgressController::class, 'getAllProgress']);
    
    // Push Notifications
    Route::get('/push/devices', [PushDeviceController::class, 'index']);
    Route::post('/push/devices/register', [PushDeviceController::class, 'register']);
    Route::post('/push/devices/unregister', [PushDeviceController::class, 'unregister']);
});

Route::middleware(['auth:sanctum', 'role:super_admin'])->prefix('admin')->group(function () {
    Route::put('organisations/{organisation}/modules', [OrganisationModuleAdminController::class, 'update']);
});
