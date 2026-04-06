<?php

use App\Livewire\Admin\TribeManager;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    
    // Super Admin Routes
    Route::prefix('admin')->name('admin.')->group(function() {
        Route::get('tribes', TribeManager::class)->name('tribes');
    });
});

Route::get('/health', function () {
    $dbStatus = 'Disconnected';
    $redisStatus = 'Disconnected';
    
    try {
        DB::connection()->getPdo();
        $dbStatus = 'Connected';
    } catch (\Exception $e) {
        $dbStatus = 'Error: ' . $e->getMessage();
    }

    try {
        // Attempt Redis check with a short timeout
        $redis = Illuminate\Support\Facades\Redis::connection();
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


require __DIR__.'/settings.php';

