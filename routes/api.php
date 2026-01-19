<?php

use Illuminate\Support\Facades\Route;
use Iqonic\FileManager\Http\Controllers\FileController;
use Iqonic\FileManager\Http\Controllers\UploadController;
use Iqonic\FileManager\Http\Controllers\TrashController;
use Iqonic\FileManager\Http\Controllers\ShareController;
use Iqonic\FileManager\Http\Controllers\StatsController;

Route::group([
    'prefix' => config('file-manager.route_prefix') . '/api',
    // Allow either session auth (for dashboard) or santum token (for headless)
    'middleware' => ['web'], 
], function () {
    // Auth Routes (Public)
    Route::post('/auth/token', [\Iqonic\FileManager\Http\Controllers\Api\AuthApiController::class, 'issueToken']);
    
    // Protected Routes
    Route::group(['middleware' => ['auth:sanctum,web']], function () {
        Route::post('/auth/logout', [\Iqonic\FileManager\Http\Controllers\Api\AuthApiController::class, 'logout']);
        Route::get('/user', [\Iqonic\FileManager\Http\Controllers\Api\AuthApiController::class, 'user']);
    
    // Files
    Route::get('/files', [FileController::class, 'index']);
    Route::patch('/files/{file}', [FileController::class, 'update']);
    Route::delete('/files/{file}', [FileController::class, 'destroy']);
    Route::post('/files/{file}/restore', [FileController::class, 'restore']);
    Route::get('/files/{file}/download', [FileController::class, 'download']);
    Route::get('/files/{file}/preview', [FileController::class, 'preview'])->name('file-manager.preview')->withTrashed();
    
    // Bulk Operations
    Route::post('/files/bulk-delete', [FileController::class, 'bulkDestroy']);
    Route::post('/files/bulk-move', [FileController::class, 'bulkUpdate']);
    Route::post('/files/bulk-download', [FileController::class, 'bulkDownload']);
    Route::post('/files/bulk-sync-s3', [FileController::class, 'bulkSyncS3']);
    
    // Upload
    Route::post('/files/upload', [UploadController::class, 'upload']);

    
    // Folders
    Route::get('/folders/tree', [FileController::class, 'folderTree']);
    Route::post('/folders', [FileController::class, 'createFolder']);
    Route::get('/folders/{folder}/download', [FileController::class, 'downloadFolder']);

    // Shares
    Route::post('/files/{file}/share', [ShareController::class, 'store']);
    
    // Trash 
    Route::get('/trash', [TrashController::class, 'index'])->name('file-manager.trash.index');
    Route::delete('/trash/empty', [TrashController::class, 'empty'])->name('file-manager.trash.empty');
    Route::post('/trash/bulk-restore', [TrashController::class, 'bulkRestore']);
    Route::post('/trash/bulk-destroy', [TrashController::class, 'bulkDestroy']);
    Route::post('/trash/{id}/restore', [TrashController::class, 'restore'])->name('file-manager.trash.restore');
    Route::delete('/trash/{id}', [TrashController::class, 'destroy'])->name('file-manager.trash.destroy');

    // Stats
    Route::get('/stats/usage', [StatsController::class, 'index']);

    // Settings
    Route::patch('/settings', [\Iqonic\FileManager\Http\Controllers\SettingsController::class, 'update']);
    Route::post('/settings/test-s3', [\Iqonic\FileManager\Http\Controllers\SettingsController::class, 'testS3Connection']);
    Route::post('/settings/test-ffmpeg', [\Iqonic\FileManager\Http\Controllers\SettingsController::class, 'testFFMpeg']);
    Route::post('/settings/sync-s3', [\Iqonic\FileManager\Http\Controllers\SettingsController::class, 'syncExistingData']);

    // Favorites
    Route::post('/files/{file}/favorite', [FileController::class, 'toggleFavorite'])->name('files.favorite');
    Route::get('/favorites', [FileController::class, 'favorites'])->name('favorites');

    // Image Variant Preview
    Route::get('/files/{file}/variant/{preset}', function (\Iqonic\FileManager\Models\File $file, string $preset) {
        $variant = $file->imageVariants()->where('profile', $preset)->first();
        if (!$variant) abort(404);
        $path = \Illuminate\Support\Facades\Storage::disk($variant->disk)->path($variant->path);
        if (!file_exists($path)) abort(404);
        return response()->file($path);
    })->name('variant.preview');

    // User Preferences
    Route::get('/preferences', function(\Illuminate\Http\Request $request) {
        return response()->json(\Iqonic\FileManager\Models\UserPreference::where('user_id', $request->user()->id)->get());
    })->name('preferences.index');

    Route::post('/preferences', function(\Illuminate\Http\Request $request) {
        $request->validate(['key' => 'required|string', 'value' => 'required']);
        \Iqonic\FileManager\Models\UserPreference::set($request->user()->id, $request->input('key'), $request->input('value'));
        return response()->json(['message' => 'Preference saved']);
    })->name('preferences.store');

    }); // End Protected Routes

}); // End Outer Group

// Public Share Route (No Auth Middleware, but maybe throttle)
Route::get(config('file-manager.route_prefix') . '/shares/{token}', [ShareController::class, 'show']);
