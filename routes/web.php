<?php

use Illuminate\Support\Facades\Route;
use Iqonic\FileManager\Http\Controllers\DashboardController;

Route::group([
    'prefix' => config('file-manager.route_prefix'),
    'middleware' => config('file-manager.middleware'),
], function () {
    
    Route::get('/', [DashboardController::class, 'index'])->name('file-manager.dashboard');
    Route::get('/trash', [DashboardController::class, 'trash'])->name('file-manager.trash');
    // Public Shared Links
    Route::get('/shared/{token}', [\Iqonic\FileManager\Http\Controllers\ShareController::class, 'show'])->name('file-manager.share.show');
    Route::post('/shared/{token}/unlock', [\Iqonic\FileManager\Http\Controllers\ShareController::class, 'unlock'])->name('share.unlock');
    Route::get('/shared/{token}/download', [\Iqonic\FileManager\Http\Controllers\ShareController::class, 'download'])->name('share.download');
    Route::get('/shared/{token}/preview', [\Iqonic\FileManager\Http\Controllers\ShareController::class, 'preview'])->name('share.preview');


    Route::get('/settings', [\Iqonic\FileManager\Http\Controllers\SettingsController::class, 'index'])->name('file-manager.settings');

});
