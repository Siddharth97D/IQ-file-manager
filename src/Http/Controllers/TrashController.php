<?php

namespace Iqonic\FileManager\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Iqonic\FileManager\Facades\FileManager;
use Iqonic\FileManager\Models\File;

class TrashController extends Controller
{
    use \Illuminate\Foundation\Auth\Access\AuthorizesRequests;

    public function index(Request $request)
    {
        $files = File::onlyTrashed()
            ->where('owner_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return response()->json($files);
    }

    public function empty(Request $request)
    {
        File::onlyTrashed()
            ->where('owner_id', $request->user()->id)
            ->forceDelete();

        return response()->json(['message' => 'Trash emptied']);
    }
    public function restore($id)
    {
        $file = File::onlyTrashed()->findOrFail($id);
        $this->authorize('update', $file);
        
        // Use service to restore
        FileManager::restore($file);

        return response()->json(['message' => 'File restored']);
    }

    public function destroy($id)
    {
        $file = File::onlyTrashed()->findOrFail($id);
        $this->authorize('delete', $file);
        
        // Use service to force delete
        FileManager::delete($file, true); // true = force delete

        return response()->json(['message' => 'File permanently deleted']);
    }

    public function bulkRestore(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:files,id'
        ]);

        FileManager::bulkRestore($request->ids);

        return response()->json(['message' => 'Files restored successfully']);
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:files,id'
        ]);

        File::onlyTrashed()->whereIn('id', $request->ids)->forceDelete();

        return response()->json(['message' => 'Files permanently deleted']);
    }
}
