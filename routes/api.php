<?php

use App\Http\Controllers\Api\AgeProfileController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LanguageRegistryController;
use App\Http\Controllers\Api\OrganisationModuleAdminController;
use App\Http\Controllers\Api\PushDeviceController;
use App\Http\Controllers\Api\TribeCatalogController;
use Illuminate\Support\Facades\Route;

// Public Mobile Auth Routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Protected Mobile API Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/age-profiles', [AgeProfileController::class, 'index']);
    Route::get('/languages', [LanguageRegistryController::class, 'index']);
    Route::get('/tribes', [TribeCatalogController::class, 'index']);
    Route::get('/push/devices', [PushDeviceController::class, 'index']);
    Route::post('/push/devices/register', [PushDeviceController::class, 'register']);
    Route::post('/push/devices/unregister', [PushDeviceController::class, 'unregister']);
});

Route::middleware(['auth:sanctum', 'role:super_admin'])->prefix('admin')->group(function () {
    Route::put('organisations/{organisation}/modules', [OrganisationModuleAdminController::class, 'update']);
});
