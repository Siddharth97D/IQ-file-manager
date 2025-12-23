@extends('file-manager::layout')

@section('title', 'Files')

@section('actions')
    <button @click="showUploadModal = true" class="theme-bg-primary hover:opacity-90 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
        Upload
    </button>
    <button @click="showNewFolderModal = true" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium flex items-center">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path></svg>
        New Folder
    </button>
@endsection

@section('content')
    @php
        $files = \Iqonic\FileManager\Facades\FileManager::listFiles(['folder_id' => request('folder_id')]);
    @endphp
    <div x-data="{ allFiles: @js($files->values()->toArray()) }">
        <!-- Breadcrumbs -->
        <nav class="flex mb-4" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('file-manager.dashboard', array_merge(request()->query(), ['folder_id' => null])) }}" class="inline-flex items-center text-sm font-medium text-gray-700 theme-text-primary hover:opacity-80">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg>
                        Home
                    </a>
                </li>
                @isset($breadcrumbs)
                    @foreach($breadcrumbs as $crumb)
                        <li>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                                <a href="{{ route('file-manager.dashboard', array_merge(request()->query(), ['folder_id' => $crumb->id])) }}" class="ml-1 text-sm font-medium text-gray-700 theme-text-primary hover:opacity-80 md:ml-2">{{ $crumb->basename }}</a>
                            </div>
                        </li>
                    @endforeach
                @endisset
                
                <!-- Search Breadcrumb -->
                <li x-show="showSearch" style="display: none;">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Search Results</span>
                        <button @click="clearSearch()" class="ml-2 text-gray-400 hover:text-gray-600" title="Clear Search">
                             <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Toolbar -->
        <div class="flex justify-between items-center mb-6">
            <div class="flex space-x-2">
                <button @click="view = 'grid'" :class="{'bg-gray-200': view === 'grid'}" class="p-2 rounded hover:bg-gray-100">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                </button>
                <button @click="view = 'list'" :class="{'bg-gray-200': view === 'list'}" class="p-2 rounded hover:bg-gray-100">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
                <button @click="if(selectedFiles.length === allFiles.length) { selectedFiles = [] } else { selectedFiles = [...allFiles] }" 
                        class="p-2 rounded hover:bg-gray-100 text-gray-600 flex items-center text-sm font-medium"
                        title="Select All">
                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    <span x-text="selectedFiles.length === allFiles.length ? 'Deselect All' : 'Select All'"></span>
                </button>
                <button @click="refresh()" 
                        class="p-2 rounded hover:bg-gray-100 text-gray-600 flex items-center text-sm font-medium"
                        title="Refresh">
                    <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                    Refresh
                </button>
            </div>
            <div class="relative w-full max-w-xl flex items-center space-x-2">
                <!-- Search Input -->
                <div class="relative flex-1">
                    <input type="text" 
                           x-model.debounce.300ms="searchQuery" 
                           @input="performSearch"
                           placeholder="Search files..." 
                           class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 theme-ring-primary theme-border-primary w-full bg-white/50 backdrop-blur-sm">
                    <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>

                <!-- Search Button -->
                <button @click="performSearch" class="theme-bg-primary hover:opacity-90 text-white px-4 py-2 rounded-lg text-sm font-medium">
                    Search
                </button>

                <!-- Filter Button -->
                <button @click="showAdvancedSearch = !showAdvancedSearch" 
                        class="p-2 rounded-lg border border-gray-300 bg-white/50 backdrop-blur-sm hover:bg-gray-50 focus:outline-none focus:ring-2 theme-ring-primary"
                        :class="{'bg-blue-50 theme-border-primary text-blue-600': showAdvancedSearch}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                </button>

                <!-- Advanced Search Panel -->
                <div x-show="showAdvancedSearch" 
                     @click.away="showAdvancedSearch = false"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translateY-2"
                     x-transition:enter-end="opacity-100 translateY-0"
                     class="absolute top-12 right-0 w-80 bg-white/90 glass-panel rounded-xl shadow-2xl p-4 z-50">
                    
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <h3 class="text-sm font-semibold text-gray-900">Advanced Filters</h3>
                            <button @click="searchFilters = { mime_group: 'all', date_from: '', date_to: '', scope: 'global' }; performSearch()" class="text-xs text-blue-600 hover:text-blue-800">
                                Reset
                            </button>
                        </div>
                        
                        <!-- Type Filter -->
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">File Type</label>
                            <select x-model="searchFilters.mime_group" @change="performSearch" class="block w-full text-sm border-gray-300 rounded-md bg-white/50 focus:ring-blue-500 focus:border-blue-500">
                                <option value="all">All Types</option>
                                <option value="folder">Folders Only</option>
                                <option value="image">Images</option>
                                <option value="video">Videos</option>
                                <option value="audio">Audio</option>
                                <option value="document">Documents</option>
                            </select>
                        </div>

                        <!-- Scope -->
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Search Scope</label>
                            <select x-model="searchFilters.scope" @change="performSearch" class="block w-full text-sm border-gray-300 rounded-md bg-white/50 focus:ring-blue-500 focus:border-blue-500">
                                <option value="global">Entire Drive (Global)</option>
                                <option value="current">Current Folder</option>
                            </select>
                        </div>

                        <!-- Date Range -->
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">From</label>
                                <input type="date" x-model="searchFilters.date_from" @change="performSearch" class="block w-full text-xs border-gray-300 rounded-md bg-white/50">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">To</label>
                                <input type="date" x-model="searchFilters.date_to" @change="performSearch" class="block w-full text-xs border-gray-300 rounded-md bg-white/50">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Search Results Dropdown Removed as requested -->
            </div>
        </div>

        <!-- Selection Info for Picker Mode -->
        <div x-show="pickerMode && pickerMultiple" class="mb-4 bg-blue-50 border-l-4 border-blue-400 p-3" style="display: none;">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        Multiple selection enabled. Select files and click "Confirm Selection".
                    </p>
                </div>
            </div>
        </div>

                <!-- Premium Grid View -->
                <div x-show="view === 'grid' && !showSearch" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-6">
                    @foreach($files as $file)
                    <div 
                        class="group relative bg-white/60 backdrop-blur-md rounded-2xl border border-white/40 p-4 transition-all duration-300 hover:shadow-xl hover:-translate-y-1 cursor-pointer"
                        :class="{'ring-2 theme-ring-primary shadow-lg bg-indigo-50/50': selectedFiles.some(f => f.id === {{ $file->id }})}"
                        @click="handleFileClick(allFiles.find(f => f.id == {{ $file->id }}), allFiles)"
                        @dblclick="{{ $file->type === 'folder' ? "window.location.href = '" . route('file-manager.dashboard', array_merge(request()->query(), ['folder_id' => $file->id])) . "'" : "openPreview(allFiles.find(f => f.id == " . $file->id . "), allFiles)" }}"
                    >
                        
                        <!-- Selection Checkbox (Visible on Hover or Selected) -->
                        <div 
                             class="absolute top-3 left-3 z-10 transition-opacity duration-200"
                             :class="{'opacity-100': selectedFiles.some(f => f.id === {{ $file->id }}), 'opacity-0 group-hover:opacity-100': !selectedFiles.some(f => f.id === {{ $file->id }})}"
                        >
                             <div class="w-6 h-6 rounded-lg border-2 flex items-center justify-center transition-all duration-200 backdrop-blur-sm"
                                  :class="{'theme-bg-primary theme-border-primary text-white': selectedFiles.some(f => f.id === {{ $file->id }}), 'bg-white/50 border-gray-300': !selectedFiles.some(f => f.id === {{ $file->id }})}">
                                 <svg class="w-4 h-4" x-show="selectedFiles.some(f => f.id === {{ $file->id }})" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                             </div>
                        </div>

                        <!-- 3-Dot Menu (Top Right if not picker) -->
                        @if(!$pickerMode)
                        <div class="absolute top-3 right-3 z-20 opacity-0 group-hover:opacity-100 transition-opacity duration-200"
                             @click.stop="toggleMenu({{ $file->id }})">
                            <button class="p-1.5 rounded-lg hover:bg-black/5 text-gray-500 hover:text-gray-700 transition-colors">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path></svg>
                            </button>
                            
                            <!-- Dropdown -->
                            <div x-show="activeMenuFileId === {{ $file->id }}" 
                                 @click.away="closeMenu()"
                                 class="absolute right-0 mt-2 w-48 bg-white/90 backdrop-blur-xl rounded-xl shadow-2xl border border-white/50 py-1 z-30 transform origin-top-right transition-all duration-200"
                                 style="display: none;"
                                 x-transition:enter="ease-out duration-100"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100">
                                 <div class="py-1">
                                    <button @click.stop="openRename({{ $file->toJson() }}); closeMenu()" class="flex items-center w-full px-4 py-2.5 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-600 transition-colors">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        Rename
                                     </button>
                                 </div>
                            </div>
                        </div>
                        @endif

                        <!-- Icon / Thumbnail Area -->
                        <div class="aspect-w-1 aspect-h-1 mb-4 rounded-xl overflow-hidden bg-gray-50/50 flex items-center justify-center relative group-hover:scale-105 transition-transform duration-300">
                            @if($file->type === 'folder')
                                <div class="w-16 h-16 bg-yellow-100/80 rounded-2xl flex items-center justify-center text-yellow-500 mb-1 shadow-inner backdrop-blur-sm">
                                    <svg class="w-10 h-10" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path></svg>
                                </div>
                            @elseif(str_starts_with($file->mime_type, 'image/'))
                                <img src="{{ route('file-manager.preview', $file->id) }}" class="object-cover w-full h-full transform transition-transform duration-500">
                            @elseif(str_starts_with($file->mime_type, 'video/') && $file->thumbnail_path)
                                <div class="relative w-full h-full">
                                    <img src="{{ route('file-manager.preview', ['file' => $file->id, 'thumbnail' => 'true']) }}" class="object-cover w-full h-full">
                                    <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-10">
                                        <div class="w-10 h-10 bg-white bg-opacity-90 rounded-full flex items-center justify-center shadow-lg backdrop-blur-sm">
                                            <svg class="w-5 h-5 text-indigo-600 pl-0.5" fill="currentColor" viewBox="0 0 20 20"><path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"></path></svg>
                                        </div>
                                    </div>
                                </div>
                            @elseif($file->mime_type === 'application/pdf')
                                <div class="w-16 h-16 bg-red-50/80 rounded-2xl flex items-center justify-center text-red-500 shadow-inner backdrop-blur-sm">
                                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                </div>
                            @else
                                <div class="w-16 h-16 bg-indigo-50/80 rounded-2xl flex items-center justify-center theme-text-primary shadow-inner backdrop-blur-sm">
                                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                </div>
                            @endif
                        </div>
                        
                        <!-- File Info -->
                        <div>
                            <h3 class="text-sm font-semibold text-gray-800 truncate mb-1" title="{{ $file->basename }}">{{ $file->basename }}</h3>
                            <div class="flex items-center justify-between text-xs text-gray-500">
                                @if($file->type === 'folder')
                                    <div class="flex space-x-3">
                                        <span class="flex items-center" title="{{ $file->sub_folders_count }} Folders">
                                            <svg class="w-3 h-3 mr-1 text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path></svg>
                                            <span class="font-medium">{{ $file->sub_folders_count }}</span>
                                        </span>
                                        <span class="flex items-center" title="{{ $file->sub_files_count }} Files">
                                            <svg class="w-3 h-3 mr-1 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                            <span class="font-medium">{{ $file->sub_files_count }}</span>
                                        </span>
                                    </div>
                                @else
                                    <span>{{ \Illuminate\Support\Number::fileSize($file->size) }}</span>
                                @endif
                                <span>{{ $file->created_at->diffForHumans(null, true) }}</span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

        <!-- Search Results Grid View (Client-side rendered) -->
        <div x-show="showSearch && view === 'grid'" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-6" style="display: none;">
            <template x-for="file in searchResults" :key="file.id">
            <div 
                class="group relative bg-white/60 backdrop-blur-md rounded-2xl border border-white/40 p-4 transition-all duration-300 hover:shadow-xl hover:-translate-y-1 cursor-pointer"
                :class="{'ring-2 theme-ring-primary shadow-lg bg-indigo-50/50': selectedFiles.some(f => f.id === file.id)}"
                @click="selectFile(file)"
                @dblclick="file.type === 'folder' ? window.location.href = '{{ route('file-manager.dashboard') }}?folder_id=' + file.id : openPreview(file, searchResults)"
            >
                
                <!-- Selection Checkbox -->
                <div x-show="pickerMode" 
                        class="absolute top-3 right-3 z-10 transition-opacity duration-200"
                        :class="{'opacity-100': selectedFiles.some(f => f.id === file.id), 'opacity-0 group-hover:opacity-100': !selectedFiles.some(f => f.id === file.id)}"
                >
                        <div class="w-6 h-6 rounded-lg border-2 flex items-center justify-center transition-all duration-200 backdrop-blur-sm"
                            :class="{'theme-bg-primary theme-border-primary text-white': selectedFiles.some(f => f.id === file.id), 'bg-white/50 border-gray-300': !selectedFiles.some(f => f.id === file.id)}">
                            <svg class="w-4 h-4" x-show="selectedFiles.some(f => f.id === file.id)" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                </div>
                
                <!-- Icon / Thumbnail Area -->
                <div class="aspect-w-1 aspect-h-1 mb-4 rounded-xl overflow-hidden bg-gray-50/50 flex items-center justify-center relative group-hover:scale-105 transition-transform duration-300">
                    <template x-if="file.type === 'folder'">
                        <div class="w-16 h-16 bg-yellow-100/80 rounded-2xl flex items-center justify-center text-yellow-500 mb-1 shadow-inner backdrop-blur-sm">
                            <svg class="w-10 h-10" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path></svg>
                        </div>
                    </template>
                    <template x-if="file.type !== 'folder' && file.mime_type.startsWith('image/')">
                        <img :src="window.apiBaseUrl + '/files/' + file.id + '/preview'" class="object-cover w-full h-full transform transition-transform duration-500">
                    </template>
                     <template x-if="file.type !== 'folder' && !file.mime_type.startsWith('image/')">
                        <div class="w-16 h-16 bg-indigo-50/80 rounded-2xl flex items-center justify-center theme-text-primary shadow-inner backdrop-blur-sm">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        </div>
                    </template>
                </div>
                
                <!-- File Info -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-800 truncate mb-1" :title="file.basename" x-text="file.basename"></h3>
                    
                    <!-- Location Context -->
                    <template x-if="file.parent">
                         <div class="flex items-center text-[10px] text-gray-400 mb-1" :title="'in ' + file.parent.basename">
                            <svg class="w-3 h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                            <span class="truncate" x-text="file.parent.basename"></span>
                         </div>
                    </template>

                    <div class="flex items-center justify-between text-xs text-gray-500">
                         <template x-if="file.type === 'folder'">
                            <div class="flex space-x-3">
                                <span class="flex items-center" :title="file.sub_folders_count + ' Folders'">
                                    <svg class="w-3 h-3 mr-1 text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path></svg>
                                    <span class="font-medium" x-text="file.sub_folders_count"></span>
                                </span>
                                <span class="flex items-center" :title="file.sub_files_count + ' Files'">
                                    <svg class="w-3 h-3 mr-1 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                    <span class="font-medium" x-text="file.sub_files_count"></span>
                                </span>
                            </div>
                         </template>
                         <template x-if="file.type !== 'folder'">
                            <span x-text="(file.size / 1024).toFixed(1) + ' KB'"></span>
                         </template>
                         
                         <span x-text="file.human_date"></span>
                    </div>
                </div>
            </div>
            </template>
             <div x-show="searchResults.length === 0 && !isSearching" class="col-span-full text-center py-10 text-gray-500">
                No results found.
            </div>
             <div x-show="isSearching" class="col-span-full text-center py-10 text-gray-500">
                Searching...
            </div>
        </div>

        <!-- List View (Server) -->
        <div x-show="view === 'list' && !showSearch" class="bg-white border border-gray-200 rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($files as $file)
                    <tr class="hover:bg-gray-50 cursor-pointer" 
                        :class="{'bg-blue-50': selectedFiles.some(f => f.id === {{ $file->id }})}"
                        @if($file->type === 'folder')
                             @click="handleFileClick(allFiles.find(f => f.id == {{ $file->id }}), allFiles)"
                             @dblclick="window.location.href='{{ route('file-manager.dashboard', array_merge(request()->query(), ['folder_id' => $file->id])) }}'"
                        @else
                            @click="handleFileClick(allFiles.find(f => f.id == {{ $file->id }}), allFiles)"
                            @dblclick="openPreview(allFiles.find(f => f.id == {{ $file->id }}), allFiles)"
                        @endif
                    >
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <!-- Selection Checkbox -->
                                <div class="mr-3" @click.stop="selectFile(allFiles.find(f => f.id == {{ $file->id }}))">
                                    <div class="w-5 h-5 rounded border border-gray-300 bg-white flex items-center justify-center transition-colors shadow-sm"
                                         :class="{'theme-bg-primary border-transparent': selectedFiles.some(f => f.id === {{ $file->id }})}">
                                        <svg x-show="selectedFiles.some(f => f.id === {{ $file->id }})" class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                    </div>
                                </div>
                                <div class="flex-shrink-0 h-10 w-10 bg-gray-100 rounded flex items-center justify-center text-gray-400">
                                    @if($file->type === 'folder')
                                        <svg class="w-6 h-6 text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path></svg>
                                    @elseif(str_starts_with($file->mime_type, 'image/'))
                                        <img src="{{ route('file-manager.preview', $file->id) }}" class="h-8 w-8 rounded object-cover">
                                    @elseif(str_starts_with($file->mime_type, 'video/') && $file->thumbnail_path)
                                        <img src="{{ route('file-manager.preview', ['file' => $file->id, 'thumbnail' => 'true']) }}" class="h-8 w-8 rounded object-cover">
                                    @else
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                    @endif
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $file->basename }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $file->type === 'folder' ? '-' : \Illuminate\Support\Number::fileSize($file->size) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $file->type === 'folder' ? 'Folder' : $file->mime_type }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $file->created_at->format('M d, Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium relative">
                            <button @click.stop="toggleMenu({{ $file->id }})" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path></svg>
                            </button>
                            <!-- Dropdown -->
                            <div x-show="activeMenuFileId === {{ $file->id }}" 
                                 @click.away="activeMenuFileId = null"
                                 class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-50 border border-gray-200 text-left" 
                                 style="display: none;">
                                <div class="py-1">
                                    <button @click.stop="openRename({{ $file->toJson() }}); closeMenu()" class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        Rename
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Search Results List View (Client-side) -->
        <div x-show="view === 'list' && showSearch" class="bg-white border border-gray-200 rounded-lg overflow-hidden" style="display: none;">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="file in searchResults" :key="file.id">
                    <tr class="hover:bg-gray-50 cursor-pointer" 
                        :class="{'bg-blue-50': pickerMode && pickerMultiple && selectedFiles.some(f => f.id === file.id)}"
                        @click="if(file.type === 'folder') { window.location.href = '{{ route('file-manager.dashboard') }}?folder_id=' + file.id } else { pickerMode ? selectFile(file) : openPreview(file, searchResults) }"
                    >
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <!-- Selection Checkbox/Radio -->
                                <div x-show="pickerMode" class="mr-3" @click.stop="selectFile(file)">
                                    <template x-if="file.type !== 'folder'">
                                    <div>
                                        <div x-show="pickerMultiple" 
                                             class="w-5 h-5 rounded border border-gray-300 bg-white flex items-center justify-center transition-colors"
                                             :class="{'theme-bg-primary border-transparent': selectedFiles.some(f => f.id === file.id)}">
                                            <svg x-show="selectedFiles.some(f => f.id === file.id)" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                        </div>
                                        <div x-show="!pickerMultiple" 
                                             class="w-5 h-5 rounded-full border border-gray-300 bg-white flex items-center justify-center">
                                             <div class="w-2.5 h-2.5 rounded-full theme-bg-primary opacity-0 hover:opacity-50"></div>
                                        </div>
                                    </div>
                                    </template>
                                </div>
                                <div class="flex-shrink-0 h-10 w-10 bg-gray-100 rounded flex items-center justify-center text-gray-400">
                                    <template x-if="file.type === 'folder'">
                                        <svg class="w-6 h-6 text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path></svg>
                                    </template>
                                    <template x-if="file.type !== 'folder' && file.mime_type.startsWith('image/')">
                                        <img :src="window.apiBaseUrl + '/files/' + file.id + '/preview'" class="h-8 w-8 rounded object-cover">
                                    </template>
                                    <template x-if="file.type !== 'folder' && !file.mime_type.startsWith('image/')">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                    </template>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900" x-text="file.basename"></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                             <template x-if="file.parent">
                                <span class="flex items-center text-gray-500" :title="'in ' + file.parent.basename">
                                     <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                                     <span x-text="file.parent.basename" class="truncate max-w-[100px]"></span>
                                </span>
                            </template>
                            <template x-if="!file.parent">
                                <span class="text-gray-300">-</span>
                            </template>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <template x-if="file.type === 'folder'">
                                <div class="flex space-x-2">
                                    <span class="flex items-center" :title="file.sub_folders_count + ' Folders'">
                                        <svg class="w-3 h-3 mr-1 text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path></svg>
                                        <span x-text="file.sub_folders_count"></span>
                                    </span>
                                    <span class="flex items-center" :title="file.sub_files_count + ' Files'">
                                        <svg class="w-3 h-3 mr-1 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                        <span x-text="file.sub_files_count"></span>
                                    </span>
                                </div>
                            </template>
                            <template x-if="file.type !== 'folder'">
                                <span x-text="(file.size / 1024).toFixed(1) + ' KB'"></span>
                            </template>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="file.type === 'folder' ? 'Folder' : file.mime_type"></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="file.human_date"></td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium relative">
                            <button @click.stop="toggleMenu(file.id)" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path></svg>
                            </button>
                            <!-- Dropdown -->
                            <div x-show="activeMenuFileId === file.id" 
                                 @click.away="activeMenuFileId = null"
                                 class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-50 border border-gray-200 text-left" 
                                 style="display: none;">
                                <div class="py-1">
                                    <button @click.stop="openRename(file); closeMenu()" class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        Rename
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    </template>
                </tbody>
            </table>
             <div x-show="searchResults.length === 0 && !isSearching" class="text-center py-10 text-gray-500">
                No results found.
            </div>
             <div x-show="isSearching" class="text-center py-10 text-gray-500">
                Searching...
            </div>
        </div>
    </div>

    <!-- Upload Modal -->
    <div x-show="showUploadModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 text-center">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Upload Files
                            </h3>
                            <div class="mt-2">
                                <div class="flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                    <div class="space-y-1 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <div class="flex text-sm text-gray-600">
                                            <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                                <span>Upload files</span>
                                                <input id="file-upload" name="file-upload" type="file" class="sr-only" @change="handleFileSelect" multiple>
                                            </label>
                                            <p class="pl-1">or drag and drop</p>
                                        </div>
                                        <p class="text-xs text-gray-500">
                                            PNG, JPG, GIF up to 10MB
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:w-auto sm:text-sm" @click="showUploadModal = false">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- New Folder Modal -->
    <div x-show="showNewFolderModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 text-center">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Create New Folder
                            </h3>
                            <div class="mt-2">
                                <input type="text" x-model="newFolderName" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border" placeholder="Folder Name">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm" @click="createFolder()">
                        Create
                    </button>
                    <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" @click="showNewFolderModal = false">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Rename Modal -->
    <div x-show="showRenameModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 text-center">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Rename File</h3>
                    <div class="mt-2">
                        <input type="text" x-model="selectedFile.basename" class="shadow-sm focus:ring-blue-500 focus:border-blue-500 block w-full sm:text-sm border-gray-300 rounded-md p-2 border">
                        <p class="text-xs text-gray-500 mt-1">Extension cannot be changed.</p>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 sm:ml-3 sm:w-auto sm:text-sm" @click="renameFile()">
                        Rename
                    </button>
                    <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" @click="showRenameModal = false">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Actions Toolbar -->
    <div x-show="selectedFiles.length > 0 && !pickerMode" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-10"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-10"
         class="fixed bottom-8 left-1/2 -translate-x-1/2 z-[60] bg-white/80 backdrop-blur-2xl px-6 py-4 rounded-2xl shadow-2xl border border-white/50 flex items-center space-x-6">
        
        <div class="flex items-center space-x-2 border-r border-gray-200 pr-6 mr-2">
            <span class="flex items-center justify-center min-w-8 h-8 px-2 rounded-full theme-bg-primary text-white text-xs font-bold" x-text="selectedFiles.length"></span>
            <span class="text-sm font-semibold text-gray-700">Selected</span>
        </div>

        <div class="flex items-center space-x-3">
            <button @click="openBulkMove()" 
                    class="flex items-center px-4 py-2 bg-indigo-50 text-indigo-700 rounded-xl hover:bg-indigo-100 transition-colors font-medium text-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                Move
            </button>
            <button @click="bulkDownload()" 
                    class="flex items-center px-4 py-2 bg-blue-50 text-blue-700 rounded-xl hover:bg-blue-100 transition-colors font-medium text-sm">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                Download
            </button>
            <button @click="bulkDelete()" 
                    class="flex items-center px-4 py-2 bg-red-50 text-red-700 rounded-xl hover:bg-red-100 transition-colors font-medium text-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                Delete
            </button>
            <button @click="selectedFiles = []" 
                    class="flex items-center px-4 py-2 text-gray-500 hover:text-gray-700 transition-colors font-medium text-sm">
                Cancel
            </button>
        </div>
    </div>

    <!-- Move Modal -->
    <div x-show="showMoveModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 text-center">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900" x-text="bulkMode ? 'Move Selected Items' : 'Move File'">Move File</h3>
                    <div class="mt-2 text-sm text-gray-500 mb-4" x-show="!bulkMode && selectedFile">
                         Moving: <span class="font-semibold" x-text="selectedFile.basename"></span>
                    </div>
                    <div class="mt-2 text-sm text-gray-500 mb-4" x-show="bulkMode">
                         Moving <span class="font-semibold" x-text="selectedFiles.length"></span> items.
                    </div>
                    <div class="mt-2">
                        <select id="move-folder-select" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md border">
                            <option value="">Select Folder...</option>
                            <template x-for="folder in folders" :key="folder.id">
                                <option :value="folder.id" x-text="folder.basename"></option>
                            </template>
                        </select>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 sm:ml-3 sm:w-auto sm:text-sm" @click="moveFile()">
                        Move
                    </button>
                    <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" @click="showMoveModal = false; bulkMode = false;">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
