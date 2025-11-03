<?php

use App\Http\Controllers\tinyUrlController;
use App\Http\Controllers\SetupController;
use Illuminate\Support\Facades\Route;

// Setup routes (not protected by middleware)
Route::get('/setup', [SetupController::class, 'index'])->name('setup.index');
Route::post('/setup/configure', [SetupController::class, 'configure'])->name('setup.configure');
Route::post('/setup/test-connections', [SetupController::class, 'testConnections'])->name('setup.test-connections');
Route::get('/setup/status', [SetupController::class, 'status'])->name('setup.status');
Route::get('/setup/config', [SetupController::class, 'showConfig'])->name('setup.config');

// Main application routes
Route::get('/', function () {
    return view('index');
});

// URL shortening and redirect routes
Route::post('/tiny-url', [tinyUrlController::class, 'store'])->name('tiny-url.store');
Route::get('/{token}', [tinyUrlController::class, 'show'])->name('tiny-url.show');

// API routes for AJAX and advanced functionality
Route::prefix('api')->group(function () {
    Route::post('/shorten', [tinyUrlController::class, 'store'])->name('api.tiny-url.store');
    Route::get('/stats', [tinyUrlController::class, 'index'])->name('api.stats');
    Route::get('/storage-info', [tinyUrlController::class, 'storageInfo'])->name('api.storage-info');

    // URL management routes
    Route::get('/urls/{token}', [tinyUrlController::class, 'edit'])->name('api.urls.show');
    Route::put('/urls/{token}', [tinyUrlController::class, 'update'])->name('api.urls.update');
    Route::delete('/urls/{token}', [tinyUrlController::class, 'destroy'])->name('api.urls.delete');
    Route::post('/urls/{token}/extend', [tinyUrlController::class, 'extend'])->name('api.urls.extend');
});
