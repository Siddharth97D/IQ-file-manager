<?php

namespace Iqonic\FileManager\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Iqonic\FileManager\Facades\FileManager;
use Iqonic\FileManager\Models\File;

class FileController extends Controller
{
    use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    public function index(Request $request)
    {
        $files = FileManager::listFiles($request->all());
        return response()->json($files);
    }

    public function update(Request $request, File $file)
    {
        $this->authorize('update', $file);
        
        if ($request->has('name')) {
            FileManager::rename($file, $request->input('name'));
        }

        if ($request->has('parent_id')) {
            FileManager::move($file, $request->input('parent_id'));
        }
        
        return response()->json(['message' => 'File updated']);
    }

    public function destroy(File $file)
    {
        $this->authorize('delete', $file);
        
        FileManager::delete($file);
        
        return response()->json(['message' => 'File deleted']);
    }

    public function restore($id)
    {
        $file = File::withTrashed()->findOrFail($id);
        $this->authorize('update', $file); // Assuming update permission is enough for restore
        
        FileManager::restore($file);
        
        return response()->json(['message' => 'File restored']);
    }

    public function download(File $file)
    {
        $this->authorize('view', $file);
        
        return response()->download(Storage::disk($file->disk)->path($file->path));
    }


    public function preview(File $file)
    {
        $this->authorize('view', $file);
        
        // Serve thumbnail if requested and available
        if (request()->has('thumbnail') && $file->thumbnail_path) {
            $thumbPath = Storage::disk($file->disk)->path($file->thumbnail_path);
            if (file_exists($thumbPath)) {
                return response()->file($thumbPath);
            }
        }
        
        $path = Storage::disk($file->disk)->path($file->path);
        
        // For video files, use streaming response with Range support
        if (str_starts_with($file->mime_type, 'video/')) {
            return response()->file($path, [
                'Content-Type' => $file->mime_type,
                'Accept-Ranges' => 'bytes',
            ]);
        }
        
        // For other files, return normally
        return response()->file($path);
    }

    public function createFolder(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:files,id'
        ]);

        $folder = FileManager::createFolder($request->name, $request->parent_id);

        return response()->json($folder);
    }

    public function downloadFolder(File $folder)
    {
        $this->authorize('view', $folder); // Ensure user can view folder
        
        $zipPath = FileManager::downloadFolder($folder);
        
        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:files,id',
            'parent_id' => 'nullable|exists:files,id'
        ]);

        FileManager::bulkMove($request->ids, $request->parent_id);

        return response()->json(['message' => 'Files moved successfully']);
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:files,id'
        ]);

        FileManager::bulkDelete($request->ids);

        return response()->json(['message' => 'Files deleted successfully']);
    }

    public function folderTree()
    {
        return response()->json(FileManager::getFolderTree());
    }

    public function bulkDownload(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:files,id'
        ]);

        $zipPath = FileManager::bulkDownload($request->ids);

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }
}
