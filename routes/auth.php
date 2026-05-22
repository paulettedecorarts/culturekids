<?php

use App\Http\Controllers\Auth\ResendRegistrationVerificationController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/email/verify/{id}/{hash}', VerifyEmailController::class)
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

Route::middleware('guest')->group(function () {
    Volt::route('register', 'pages.auth.register')
        ->name('register');

    Volt::route('verify-email-code', 'pages.auth.verify-email-code')
        ->name('verification.enter-code');

    Route::redirect('/register/check-email', '/verify-email-code');

    Route::post('register/resend-verification', ResendRegistrationVerificationController::class)
        ->middleware('throttle:6,1')
        ->name('registration.resend-verification');

    Volt::route('login', 'pages.auth.login')
        ->name('login');

    Volt::route('forgot-password', 'pages.auth.forgot-password')
        ->name('password.request');

    Volt::route('reset-password/{token}', 'pages.auth.reset-password')
        ->name('password.reset');
});

Route::middleware('auth')->group(function () {
    Volt::route('verify-email', 'pages.auth.verify-email')
        ->name('verification.notice');

    Volt::route('confirm-password', 'pages.auth.confirm-password')
        ->name('password.confirm');
});
