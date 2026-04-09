<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AgeProfileController;
use App\Http\Controllers\Api\AuthController;

// Public Mobile Auth Routes
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Protected Mobile API Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/age-profiles', [AgeProfileController::class, 'index']);
    
    // Future mobile API routes will go here (get tribes, progress, etc.)
});
