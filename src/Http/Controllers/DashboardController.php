<?php

namespace Iqonic\FileManager\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Iqonic\FileManager\Models\File;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $pickerMode = $request->query('picker', false);
        $multiple = $request->query('multiple', false);
        $targetInput = $request->query('target', null);
        $folderId = $request->query('folder_id', null);
        
        $breadcrumbs = [];
        if ($folderId) {
            $folder = File::with('parent')->find($folderId);
            if ($folder) {
                // Build breadcrumbs
                $current = $folder;
                while ($current) {
                    array_unshift($breadcrumbs, $current);
                    $current = $current->parent;
                }
            }
        }

        return view('file-manager::dashboard', compact('pickerMode', 'multiple', 'targetInput', 'folderId', 'breadcrumbs'));
    }

    public function trash(Request $request)
    {
        $files = File::onlyTrashed()
            ->where('owner_id', Auth::id())
            ->latest()
            ->paginate(20);
            
        $targetInput = $request->query('target', null);
        return view('file-manager::trash', compact('files', 'targetInput'));
    }

    public function settings(Request $request)
    {
        // Check permission if needed
        $targetInput = $request->query('target', null);
        return view('file-manager::settings', compact('targetInput'));
    }
}
