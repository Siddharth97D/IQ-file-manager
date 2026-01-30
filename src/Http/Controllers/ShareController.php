<?php

namespace Iqonic\FileManager\Http\Controllers;

use Illuminate\Http\Request;
use Iqonic\FileManager\Models\File;
use Iqonic\FileManager\Models\FileShare;
use Iqonic\FileManager\Services\FileManagerService;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;

class ShareController extends Controller
{
    use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;
    use \Illuminate\Foundation\Validation\ValidatesRequests;

    protected $fileManagerService;

    public function __construct(FileManagerService $fileManagerService)
    {
        $this->fileManagerService = $fileManagerService;
    }

    /**
     * Create a share link (API Endpoint).
     */
    public function store(Request $request, $id)
    {
        $file = File::findOrFail($id);
        $this->authorize('view', $file);

        $request->validate([
            'password' => 'nullable|string|min:4',
            'expires_at' => 'nullable|date|after:now',
            'max_downloads' => 'nullable|integer|min:1',
        ]);

        $share = $this->fileManagerService->createShareLink($file, $request->only(['password', 'expires_at', 'max_downloads']));

        return response()->json([
            'message' => 'Share link created successfully',
            'url' => route('file-manager.share.show', ['token' => $share->token]),
            'share' => $share
        ]);
    }

    /**
     * Show the public download page (Web Route).
     */
    public function show($token)
    {
        $share = FileShare::with('file')->where('token', $token)->firstOrFail();

        if (!$share->isValid()) {
            abort(404, 'Link expired or invalid.');
        }

        // If password protected, show password form
        if ($share->password_hash && !session('share_unlocked_' . $token)) {
            return view('file-manager::share.password', compact('share'));
        }

        // If direct access (or after password), show download/preview page
        return view('file-manager::share.download', compact('share'));
    }

    /**
     * Validate password and show content (Web Route POST).
     */
    public function unlock(Request $request, $token)
    {
        $share = FileShare::where('token', $token)->firstOrFail();

        if (!$share->isValid()) {
             return back()->with('error', 'Link expired.');
        }

        if (!$share->checkPassword($request->password)) {
            return back()->with('error', 'Invalid password.');
        }

        // Store in session (not flash, so it persists for subsequent requests like download/preview)
        session()->put('share_unlocked_' . $token, true);

        return view('file-manager::share.download', compact('share'));
    }

    /**
     * Download the file.
     */
    public function download(Request $request, $token)
    {
        $share = FileShare::with('file')->where('token', $token)->firstOrFail();

        if (!$share->isValid()) {
            abort(403, 'Link expired.');
        }

        // Check password if set (unless unlocked in session)
        if ($share->password_hash && !session('share_unlocked_' . $token)) {
             abort(403, 'Password required.');
        }

        // Increment download count
        $share->increment('downloads');

        $path = $share->file->path;
        $disk = $share->file->disk; // Use the disk associated with the file

        return Storage::disk($disk)->download($path, $share->file->basename);
    }

    /**
     * Preview the file (Inline, no download count increment).
     */
    public function preview(Request $request, $token)
    {
        $share = FileShare::with('file')->where('token', $token)->firstOrFail();

        if (!$share->isValid()) {
            abort(403, 'Link expired.');
        }

        // Check password if set (unless unlocked in session)
        if ($share->password_hash && !session('share_unlocked_' . $token)) {
             abort(403, 'Password required.');
        }

        $path = $share->file->path;
        $disk = $share->file->disk; // Use the disk associated with the file
        
        $fullPath = Storage::disk($disk)->path($path);

        if (!file_exists($fullPath)) {
            abort(404, 'File not found on server.');
        }

        return response()->file($fullPath, [
            'Content-Type' => $share->file->mime_type,
            'Content-Disposition' => 'inline; filename="' . $share->file->basename . '"'
        ]);
    }
}
