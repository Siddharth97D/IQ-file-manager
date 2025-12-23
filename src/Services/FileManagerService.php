<?php

namespace Iqonic\FileManager\Services;

use Iqonic\FileManager\Models\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FileManagerService
{
    /**
     * List files based on filters
     *
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Pagination\LengthAwarePaginator
     */
    public function listFiles(array $filters = [])
    {
        $query = File::query();
        
        // Filter by Owner (assuming auth)
        if (Auth::check()) {
            $query->where('owner_id', Auth::id());
        }

        // Exclude Trash
        $query->whereNull('deleted_at');

        // Search Query
        if (!empty($filters['search'])) {
            $query->where('basename', 'like', '%' . $filters['search'] . '%');
        }

        // Mime Group Filter (Advanced)
        if (!empty($filters['mime_group']) && $filters['mime_group'] !== 'all') {
            if ($filters['mime_group'] === 'folder') {
                $query->where('type', 'folder');
            } else {
                switch ($filters['mime_group']) {
                    case 'image':
                        $query->where('mime_type', 'like', 'image/%');
                        break;
                    case 'video':
                        $query->where('mime_type', 'like', 'video/%');
                        break;
                    case 'audio':
                        $query->where('mime_type', 'like', 'audio/%');
                        break;
                    case 'document':
                        $query->where(function($q) {
                             $q->where('mime_type', 'like', 'application/pdf')
                               ->orWhere('mime_type', 'like', 'application/msword')
                               ->orWhere('mime_type', 'like', 'application/vnd.openxmlformats-officedocument.%') // Word/Office
                               ->orWhere('mime_type', 'like', 'text/%');
                        });
                        break;
                }
            }
        }

        // Date Range
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        // Scope Logic
        // Determine if we are in "Search/Filter Mode" or "Navigation Mode"
        $isSearching = !empty($filters['search']) || 
                      (!empty($filters['mime_group']) && $filters['mime_group'] !== 'all') ||
                      !empty($filters['date_from']) || 
                      !empty($filters['date_to']);

        if ($isSearching) {
            // Apply Scope
            // If scope is 'current', restrict to current folder
            if (isset($filters['scope']) && $filters['scope'] === 'current' && !empty($filters['folder_id'])) {
                $query->where('parent_id', $filters['folder_id']);
            }
            // If scope is 'global' (default for search), we do NOT restrict parent_id, searching whole drive
        } else {
            // Navigation Mode: Strict parent_id filtering
            if (isset($filters['folder_id']) && $filters['folder_id'] !== null) {
                $query->where('parent_id', $filters['folder_id']);
            } else {
                $query->whereNull('parent_id');
            }
        }

        // Order by Type (Folder first) then Latest
        $query->orderByRaw("CASE WHEN type = 'folder' THEN 1 ELSE 2 END");
        $query->latest();

        $query->with('parent');
        $query->withCount(['subFiles', 'subFolders']);

        return $query->paginate(20);
    }
    /**
     * Create a new folder
     */
    public function createFolder(string $name, ?int $parentId = null): File
    {
        $path = $name;
        $disk = config('file-manager.disk', 'public');

        if ($parentId) {
            $parent = File::find($parentId);
            if ($parent) {
                $path = $parent->path . '/' . $name;
                $disk = $parent->disk; 
            }
        }

        // Create directory on disk
        if (!Storage::disk($disk)->exists($path)) {
            Storage::disk($disk)->makeDirectory($path);
        }

        return File::create([
            'basename' => $name,
            'path' => $path,
            'type' => 'folder',
            'mime_type' => 'directory',
            'extension' => '', 
            'parent_id' => $parentId,
            'owner_id' => Auth::id(),
            'disk' => $disk,
            'size' => 0,
        ]);
    }

    /**
     * Upload a file
     */
    public function upload(\Illuminate\Http\UploadedFile $uploadedFile, array $data = []): File
    {
        $disk = config('file-manager.disk', 'public');
        $parentId = $data['parent_id'] ?? null;
        $path = '';

        if ($parentId) {
            $parent = File::find($parentId);
            if ($parent) {
                $path = $parent->path;
                $disk = $parent->disk;
            }
        }

        // Generate unique filename
        $filename = $uploadedFile->getClientOriginalName();
        $extension = $uploadedFile->getClientOriginalExtension();
        $basename = pathinfo($filename, PATHINFO_FILENAME);
        
        // Handle duplicate names if necessary (simple append for now or rely on storage)
        $storagePath = $path ? $path : '/'; 
        
        $filePath = Storage::disk($disk)->putFileAs($storagePath, $uploadedFile, $filename);

        return File::create([
            'basename' => $filename,
            'path' => $filePath,
            'type' => 'file',
            'mime_type' => $uploadedFile->getMimeType(),
            'extension' => $extension,
            'parent_id' => $parentId,
            'owner_id' => Auth::id(),
            'disk' => $disk,
            'size' => $uploadedFile->getSize(),
        ]);
    }

    /**
     * Rename a file or folder
     */
    public function rename(File $file, string $newName): bool
    {
        $oldPath = $file->path;
        $newPath = $file->parent ? $file->parent->path . '/' . $newName : $newName;

        if ($oldPath === $newPath) return true;

        if (Storage::disk($file->disk)->exists($oldPath)) {
            if (Storage::disk($file->disk)->move($oldPath, $newPath)) {
                $this->updateDatabasePaths($file, $file->parent_id, $newPath, $file->disk);
                $file->update(['basename' => $newName]); // Update basename specifically
                return true;
            }
        } else {
            $this->updateDatabasePaths($file, $file->parent_id, $newPath, $file->disk);
            $file->update(['basename' => $newName]);
            return true;
        }

        return false;
    }

    /**
     * Move a file or folder
     */
    public function move(File $file, ?int $parentId): bool
    {
        $oldPath = $file->path;
        $newPath = $file->basename;
        $newDisk = $file->disk;
        
        if ($parentId) {
            $parent = File::find($parentId);
            if ($parent) {
                // Prevent moving into its own child or itself
                if ($parentId == $file->id || str_starts_with($parent->path, $file->path . '/')) {
                    return false;
                }
                $newPath = $parent->path . '/' . $file->basename;
                $newDisk = $parent->disk;
            } else {
                // Moving to root, use default disk or keep current?
                // For root moves, we usually keep the current disk or use config
                $newDisk = config('file-manager.disk', 'public');
            }
        }

        if ($oldPath === $newPath && $file->disk === $newDisk) return true;

        if (Storage::disk($file->disk)->exists($oldPath)) {
             // Note: move() across different disks might fail depending on driver.
             // If disks differ, we should copy and delete, but for now we assume same disk.
             if (Storage::disk($file->disk)->move($oldPath, $newPath)) {
                 $this->updateDatabasePaths($file, $parentId, $newPath, $newDisk);
                 return true;
            }
        } else {
             $this->updateDatabasePaths($file, $parentId, $newPath, $newDisk);
             return true;
        }

        return false;
    }

    /**
     * Recursively update paths and disks in database for a moved folder
     */
    protected function updateDatabasePaths(File $file, ?int $parentId, string $newPath, string $newDisk)
    {
        $file->update([
            'parent_id' => $parentId,
            'path' => $newPath,
            'disk' => $newDisk,
        ]);

        if ($file->type === 'folder') {
            foreach ($file->children as $child) {
                $childNewPath = $newPath . '/' . $child->basename;
                $this->updateDatabasePaths($child, $file->id, $childNewPath, $newDisk);
            }
        }
    }

    /**
     * Delete a file or folder
     */
    public function delete(File $file): bool
    {
        return $file->delete();
    }

    /**
     * Delete multiple files or folders
     */
    public function bulkDelete(array $ids): bool
    {
        return File::whereIn('id', $ids)->delete();
    }

    /**
     * Move multiple files or folders
     */
    public function bulkMove(array $ids, ?int $parentId): bool
    {
        $files = File::whereIn('id', $ids)->get();
        $success = true;

        foreach ($files as $file) {
            if (!$this->move($file, $parentId)) {
                $success = false;
            }
        }

        return $success;
    }

    /**
     * Restore multiple files or folders
     */
    public function bulkRestore(array $ids): bool
    {
        return File::onlyTrashed()->whereIn('id', $ids)->restore();
    }

    /**
     * Get folder tree
     */
    public function getFolderTree(?int $parentId = null): array
    {
        $query = File::folders();
        if (Auth::check()) {
            $query->where('owner_id', Auth::id());
        }
        
        $folders = $query->get();
        return $this->buildTree($folders, $parentId);
    }

    protected function buildTree($folders, $parentId = null)
    {
        $branch = [];
        foreach ($folders as $folder) {
            if ($folder->parent_id == $parentId) {
                $children = $this->buildTree($folders, $folder->id);
                if ($children) {
                    $folder->children_tree = $children;
                }
                $branch[] = $folder;
            }
        }
        return $branch;
    }

    /**
     * Restore a file or folder
     */
    public function restore(File $file): bool
    {
        return $file->restore();
    }

    /**
     * Download folder as Zip
     */
    public function downloadFolder(File $folder): string
    {
        $zipFileName = $folder->basename . '_' . time() . '.zip';
        $zipPath = storage_path('app/temp/' . $zipFileName); // Local temp path
        
        // Ensure temp dir exists
        if (!file_exists(dirname($zipPath))) {
            mkdir(dirname($zipPath), 0755, true);
        }

        $zip = new \ZipArchive;
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
            // Get all files recursively
            $files = Storage::disk($folder->disk)->allFiles($folder->path);
            
            // If folder is empty, add a placeholder file so zip is created
            if (empty($files)) {
                $zip->addFromString('empty.txt', 'This folder is empty.');
            } else {
                foreach ($files as $filePath) {
                    // Get file content
                    $content = Storage::disk($folder->disk)->get($filePath);
                    
                    // Calculate relative path inside the zip
                    $relativePath = substr($filePath, strlen($folder->path) + 1);
                    
                    if (!empty($relativePath)) {
                        $zip->addFromString($relativePath, $content);
                    }
                }
            }
            $zip->close();
        }
        
        if (!file_exists($zipPath)) {
             // Fallback or throw exception
             throw new \Exception("Failed to create zip file at $zipPath");
        }

        return $zipPath;
    }

    /**
     * Download multiple files/folders as Zip
     */
    public function bulkDownload(array $ids): string
    {
        $files = File::whereIn('id', $ids)->get();
        $zipFileName = 'bulk_download_' . time() . '.zip';
        $zipPath = storage_path('app/temp/' . $zipFileName);
        
        if (!file_exists(dirname($zipPath))) {
            mkdir(dirname($zipPath), 0755, true);
        }

        $zip = new \ZipArchive;
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === TRUE) {
            foreach ($files as $file) {
                if ($file->type === 'folder') {
                    $storageFiles = Storage::disk($file->disk)->allFiles($file->path);
                    foreach ($storageFiles as $filePath) {
                        $content = Storage::disk($file->disk)->get($filePath);
                        // Relative path: folder_name / relative_path_inside_folder
                        $relativePath = $file->basename . '/' . substr($filePath, strlen($file->path) + 1);
                        $zip->addFromString($relativePath, $content);
                    }
                } else {
                    $content = Storage::disk($file->disk)->get($file->path);
                    $zip->addFromString($file->basename, $content);
                }
            }
            $zip->close();
        }

        return $zipPath;
    }
}
