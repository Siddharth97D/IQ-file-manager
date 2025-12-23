<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Manager</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        window.apiBaseUrl = "{{ url(config('file-manager.route_prefix') . '/api') }}";
    </script>
    <!-- Google Fonts: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700&family=Maven+Pro:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        [x-cloak] { display: none !important; }

        /* Dynamic Theme Variables */
        @php
            function hex2rgb($hex) {
                $hex = str_replace('#', '', $hex);
                if(strlen($hex) == 3) {
                    $r = hexdec(substr($hex,0,1).substr($hex,0,1));
                    $g = hexdec(substr($hex,1,1).substr($hex,1,1));
                    $b = hexdec(substr($hex,2,1).substr($hex,2,1));
                } else {
                    $r = hexdec(substr($hex,0,2));
                    $g = hexdec(substr($hex,2,2));
                    $b = hexdec(substr($hex,4,2));
                }
                return "$r, $g, $b";
            }

            $primary = \Iqonic\FileManager\Models\Setting::get('theme_primary_color', '#6366f1');
            $sidebarActive = \Iqonic\FileManager\Models\Setting::get('theme_sidebar_active', '#6366f1');

            $themeSettings = [
                'primary_color' => $primary,
                'primary_rgb' => hex2rgb($primary),
                'sidebar_bg' => \Iqonic\FileManager\Models\Setting::get('theme_sidebar_bg', '#0f172a'),
                'sidebar_text' => \Iqonic\FileManager\Models\Setting::get('theme_sidebar_text', '#94a3b8'),
                'sidebar_active' => $sidebarActive,
                'sidebar_active_rgb' => hex2rgb($sidebarActive),
                'sidebar_hover_bg' => \Iqonic\FileManager\Models\Setting::get('theme_sidebar_hover_bg', '#ffffff1a'), // Default ~10% white
                'sidebar_hover_text' => \Iqonic\FileManager\Models\Setting::get('theme_sidebar_hover_text', '#ffffff'),
                'active_font_color' => \Iqonic\FileManager\Models\Setting::get('theme_active_font_color', '#ffffff'),
                'border_radius' => \Iqonic\FileManager\Models\Setting::get('theme_border_radius', '0.75rem'),
                'spacing' => \Iqonic\FileManager\Models\Setting::get('theme_spacing', '1rem'),
                'font_family' => 'Maven Pro, sans-serif', // Updated fallback
                'heading_font' => 'Maven Pro, sans-serif', // Updated fallback
            ];
        @endphp

        :root {
            --primary-color: {{ $themeSettings['primary_color'] }};
            --primary-rgb: {{ $themeSettings['primary_rgb'] }};
            --sidebar-bg: {{ $themeSettings['sidebar_bg'] }};
            --sidebar-text: {{ $themeSettings['sidebar_text'] }};
            --sidebar-active: {{ $themeSettings['sidebar_active'] }};
            --sidebar-active-rgb: {{ $themeSettings['sidebar_active_rgb'] }};
            --sidebar-hover-bg: {{ $themeSettings['sidebar_hover_bg'] }};
            --sidebar-hover-text: {{ $themeSettings['sidebar_hover_text'] }};
            --active-font-color: {{ $themeSettings['active_font_color'] }};
            --border-radius: {{ $themeSettings['border_radius'] }};
            --font-family: {{ $themeSettings['font_family'] }};
            --heading-font: {{ $themeSettings['heading_font'] }};
        }

        body { 
            font-family: var(--font-family); 
            background-color: #f3f4f6;
            background-image: 
                radial-gradient(at 0% 0%, hsla(253,16%,7%,1) 0, transparent 50%), 
                radial-gradient(at 50% 0%, hsla(225,39%,30%,1) 0, transparent 50%), 
                radial-gradient(at 100% 0%, hsla(339,49%,30%,1) 0, transparent 50%);
            background-size: 100% 100vh;
            background-repeat: no-repeat;
            background-attachment: fixed;
            color: #334155;
            height: 100vh;
            overflow: hidden;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: var(--heading-font);
        }

        /* Scrollbar Styling */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(156, 163, 175, 0.5); border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(156, 163, 175, 0.8); }

        /* Utilities */
        .glass-panel {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.1);
        }
        
        .glass-sidebar {
            background: rgba(15, 23, 42, 0.85); /* Fallback */
            background: var(--sidebar-bg); /* User setting */
            backdrop-filter: blur(12px);
            border-right: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Dynamic Theme Classes */
        .theme-text-primary { color: var(--primary-color) !important; }
        .theme-border-primary { border-color: var(--primary-color) !important; }
        .theme-ring-primary { --tw-ring-color: var(--primary-color) !important; }
        .theme-bg-primary { background-color: var(--primary-color) !important; }
        
        /* Sidebar Styling */
        .sidebar { 
            @apply glass-sidebar;
            color: var(--sidebar-text); 
        }
        
        .sidebar-link { 
            position: relative;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 12px; /* iOS rounded */
            margin-bottom: 0.5rem;
            color: var(--sidebar-text) !important;
            border: 1px solid transparent;
        }
        
        .sidebar-link:hover { 
            background-color: var(--sidebar-hover-bg); 
            color: var(--sidebar-hover-text) !important;
            transform: translateX(4px);
            border-color: rgba(255, 255, 255, 0.1);
        }
        
        .sidebar-link.active { 
            background: rgba(var(--sidebar-active-rgb), 0.2) !important; /* Dynamic tint */
            color: var(--active-font-color) !important; /* Dynamic Active Font Color */
            font-weight: 600;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(var(--sidebar-active-rgb), 0.3);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        /* Remove old active indicator, use glow instead */
        .sidebar-link.active::before { display: none; }

        .sidebar-link svg {
            transition: all 0.3s ease;
        }
        .sidebar-link.active svg {
            color: var(--active-font-color) !important;
            filter: drop-shadow(0 0 5px rgba(var(--sidebar-active-rgb), 0.5));
        }

        /* Animations */
        .hover-lift { transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); }
        .hover-lift:hover { transform: translateY(-4px) scale(1.02); }

        .shimmer {
            background: linear-gradient(90deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.4) 50%, rgba(255,255,255,0) 100%);
            background-size: 200% 100%;
            animation: shimmer 2s infinite;
        }
        @keyframes shimmer {
            0% { background-position: 150% 0; }
            100% { background-position: -150% 0; }
        }
    </style>
</head>
<body class="bg-gray-100 font-sans antialiased" x-data="{ 
    view: 'grid', 
    showUploadModal: false, 
    showNewFolderModal: false,
    showRenameModal: false,
    showMoveModal: false,
    bulkMode: false,
    selectedFile: null,
    targetInput: '{{ $targetInput ?? '' }}',
    targetInput: '{{ $targetInput ?? '' }}',
    pickerMode: @json(filter_var($pickerMode ?? false, FILTER_VALIDATE_BOOLEAN)),
    pickerMultiple: @json(filter_var($multiple ?? false, FILTER_VALIDATE_BOOLEAN)),
    selectedFiles: [],
    newFolderName: '',
    searchQuery: '',
    searchFilters: {
        mime_group: 'all',
        date_from: '',
        date_to: '',
        scope: 'global'
    },
    showAdvancedSearch: false,
    searchResults: [],
    isSearching: false,
    showSearch: false,
    activeMenuFileId: null,

    clearSearch() {
        this.searchQuery = '';
        this.searchFilters = { mime_group: 'all', date_from: '', date_to: '', scope: 'global' };
        this.searchResults = [];
        this.showSearch = false;
    },

    // File Preview Modal
    showPreviewModal: false,
    previewFileIndex: 0,
    previewFiles: [],

    toggleMenu(fileId) {
        if (this.activeMenuFileId === fileId) {
            this.activeMenuFileId = null;
        } else {
            this.activeMenuFileId = fileId;
        }
    },
    
    closeMenu() {
        this.activeMenuFileId = null;
    },

    async performSearch() {
        // Allow search if query is present OR if filters are active (mime_group is not 'all', or dates set)
        const hasFilters = this.searchFilters.mime_group !== 'all' || this.searchFilters.date_from || this.searchFilters.date_to;
        
        if (this.searchQuery.length < 2 && !hasFilters) {
            this.searchResults = [];
            this.showSearch = false;
            return;
        }
        this.isSearching = true;
        this.showSearch = true;
        try {
            const params = new URLSearchParams({
                search: this.searchQuery,
                mime_group: this.searchFilters.mime_group,
                scope: this.searchFilters.scope,
                date_from: this.searchFilters.date_from,
                date_to: this.searchFilters.date_to
            });
            
            // If scope is current, we should probably pass the current folder_id (from URL/request)
            // But getting that from JS strictly might be tricky if not in state.
            // Fortunately `request('folder_id')` is in PHP, usually present in URL.
            // Let's rely on backend: if scope is 'current', it needs folder_id. 
            // The API usually gets it from query params.
            // Wait, this is an AJAX call. URLSearchParams won't auto-include current page params.
            // We need to inject current folder_id into JS state or read it from URL.
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('folder_id')) {
                params.append('folder_id', urlParams.get('folder_id'));
            }

            const response = await fetch(`${window.apiBaseUrl}/files?${params.toString()}`);
            const data = await response.json();
            this.searchResults = data.data;
        } catch (error) {
            console.error('Search error:', error);
        } finally {
            this.isSearching = false;
        }
    },
    
    async createFolder() {
        if (!this.newFolderName) return;

        try {
            const response = await fetch(`${window.apiBaseUrl}/folders`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ 
                    name: this.newFolderName,
                    parent_id: '{{ request("folder_id") }}'
                })
            });
            
            if (response.ok) {
                this.showNewFolderModal = false;
                this.newFolderName = '';
                window.location.reload();
            } else {
                const data = await response.json();
                alert(data.message || 'Folder creation failed');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred');
        }
    },

    selectFile(file) {
        if (!file) return;
        
        // Prevent selection if we just double clicked (handled by dblclick)
        // We can use a small delay or check for event type if needed, but 
        // usually toggle selection on single click is fine.
        
        const index = this.selectedFiles.findIndex(f => f.id === file.id);
        if (this.pickerMode && !this.pickerMultiple) {
            this.selectedFiles = [file];
        } else {
            if (index === -1) {
                this.selectedFiles.push(file);
            } else {
                this.selectedFiles.splice(index, 1);
            }
        }
    },

    // Handle single click vs double click
    clickTimeout: null,
    handleFileClick(file, allFiles) {
        if (this.clickTimeout) {
            clearTimeout(this.clickTimeout);
            this.clickTimeout = null;
            // This was a double click, handled by @dblclick
            return;
        }

        this.clickTimeout = setTimeout(() => {
            this.selectFile(file);
            this.clickTimeout = null;
        }, 250); // Small delay to wait for potential second click
    },

    confirmSelection() {
        if (window.opener) {
            // Sanitize data to remove Alpine proxies and avoid DataCloneError
            const files = JSON.parse(JSON.stringify(this.selectedFiles));
            
            if (this.pickerMultiple) {
                window.opener.postMessage({ type: 'fm_selection', files: files }, '*');
            } else {
                // Return single file object for backward compatibility
                window.opener.postMessage({ type: 'fm_selection', file: files[0] }, '*');
            }
            window.close();
        } else {
            console.log('Selected:', this.selectedFiles);
            alert('Selected ' + this.selectedFiles.length + ' file(s)');
        }
    },

    openRename(file) {
        // Ensure file is an object, not a string
        if (typeof file === 'string') {
            try {
                file = JSON.parse(file);
            } catch (e) {
                console.error('Failed to parse file JSON:', e);
                return;
            }
        }
        // Deep copy to avoid Alpine proxy issues during modal edit
        this.selectedFile = JSON.parse(JSON.stringify(file));
        this.showRenameModal = true;
    },

    async renameFile() {
        if (!this.selectedFile || !this.selectedFile.basename) return;
        
        try {
            const url = this.selectedFile.type === 'folder' 
                ? `${window.apiBaseUrl}/folders/${this.selectedFile.id}`
                : `${window.apiBaseUrl}/files/${this.selectedFile.id}`;

            const response = await fetch(url, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ name: this.selectedFile.basename })
            });
            
            if (response.ok) {
                this.showRenameModal = false;
                window.location.reload();
            } else {
                const data = await response.json();
                alert(data.message || 'Rename failed');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred');
        }
    },

    openMove(file) {
        this.selectedFile = file;
        this.showMoveModal = true;
        this.fetchFolders();
    },

    folders: [],
    async fetchFolders() {
        try {
            const response = await fetch(`${window.apiBaseUrl}/folders/tree`);
            const data = await response.json();
            this.folders = this.flattenFolderTree(data);
        } catch (error) {
            console.error('Error fetching folders:', error);
        }
    },

    flattenFolderTree(nodes, level = 0) {
        let flat = [];
        nodes.forEach(node => {
            // Create a display name with indentation
            const prefix = '— '.repeat(level);
            flat.push({
                id: node.id,
                basename: prefix + node.basename,
                path: node.path
            });
            if (node.children_tree && node.children_tree.length > 0) {
                flat = flat.concat(this.flattenFolderTree(node.children_tree, level + 1));
            }
        });
        return flat;
    },

    async moveFile() {
        const folderSelect = document.getElementById('move-folder-select');
        const targetFolderId = folderSelect.value;
        
        if (!targetFolderId) return;

        if (this.bulkMode) {
             // Bulk move logic
             try {
                const response = await fetch(`${window.apiBaseUrl}/files/bulk-move`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ 
                        ids: this.selectedFiles.map(f => f.id),
                        parent_id: targetFolderId 
                    })
                });
                
                if (response.ok) {
                    this.showMoveModal = false;
                    this.bulkMode = false;
                    this.selectedFiles = [];
                    window.location.reload();
                } else {
                    const data = await response.json();
                    alert(data.message || 'Bulk move failed');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred');
            }
        } else {
            // Single move logic
            if (!this.selectedFile) return;

            try {
                const response = await fetch(`${window.apiBaseUrl}/files/${this.selectedFile.id}`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ parent_id: targetFolderId })
                });
                
                if (response.ok) {
                    this.showMoveModal = false;
                    window.location.reload();
                } else {
                    const data = await response.json();
                    alert(data.message || 'Move failed');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred');
            }
        }
    },

    openBulkMove() {
        this.bulkMode = true;
        this.selectedFile = null; // Clear single selection
        this.showMoveModal = true;
        this.fetchFolders();
    },

    async bulkDelete() {
        if (!confirm(`Are you sure you want to move ${this.selectedFiles.length} items to trash?`)) return;
        
        try {
            const response = await fetch(`${window.apiBaseUrl}/files/bulk-delete`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ ids: this.selectedFiles.map(f => f.id) })
            });
            
            if (response.ok) {
                this.selectedFiles = [];
                window.location.reload();
            } else {
                const data = await response.json();
                alert(data.message || 'Bulk delete failed');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred');
        }
    },

    async bulkDownload() {
        try {
            const response = await fetch(`${window.apiBaseUrl}/files/bulk-download`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ ids: this.selectedFiles.map(f => f.id) })
            });
            
            if (response.ok) {
                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `bulk_download_${Date.now()}.zip`;
                document.body.appendChild(a);
                a.click();
                a.remove();
                window.URL.revokeObjectURL(url);
            } else {
                const data = await response.json();
                alert(data.message || 'Bulk download failed');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred during download');
        }
    },

    refresh() {
        window.location.reload();
    },

    async openTrash(file) {
        if (!confirm('Are you sure you want to move this to trash?')) return;
        
        try {
            const response = await fetch(`${window.apiBaseUrl}/files/${file.id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            
            if (response.ok) {
                window.location.reload();
            } else {
                const data = await response.json();
                alert(data.message || 'Trash failed');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('An error occurred');
        }
    },

    // Upload & Preview Logic
    uploadQueue: [],
    showUploadPreview: false,
    isUploading: false,
    uploadProgress: 0,
    currentUploadFile: '',

    handleFileSelect(event) {
        const files = event.target.files;
        this.addFilesToQueue(files);
        event.target.value = ''; // Reset input
    },

    handleDrop(e) {
        e.preventDefault();
        this.isDragging = false;
        this.dragCounter = 0;
        if (e.dataTransfer.files.length > 0) {
            this.addFilesToQueue(e.dataTransfer.files);
        }
    },

    addFilesToQueue(files) {
        if (files.length === 0) return;
        
        Array.from(files).forEach(file => {
            // Generate preview if image
            const reader = new FileReader();
            const queueItem = {
                file: file,
                id: Math.random().toString(36).substr(2, 9),
                preview: null,
                status: 'pending', // pending, uploading, success, error
                error: null
            };

            if (file.type.startsWith('image/')) {
                reader.onload = (e) => {
                    queueItem.preview = e.target.result;
                };
                reader.readAsDataURL(file);
            }

            this.uploadQueue.push(queueItem);
        });

        this.showUploadPreview = true;
    },

    removeQueueItem(index) {
        this.uploadQueue.splice(index, 1);
        if (this.uploadQueue.length === 0) {
            this.showUploadPreview = false;
        }
    },

    async processUploadQueue() {
        if (this.isUploading) return;
        this.isUploading = true;
        this.uploadProgress = 0;

        for (let i = 0; i < this.uploadQueue.length; i++) {
            const item = this.uploadQueue[i];
            if (item.status === 'success') continue;

            item.status = 'uploading';
            this.currentUploadFile = item.file.name;

            const formData = new FormData();
            formData.append('file', item.file);
            formData.append('parent_id', '{{ request("folder_id") }}');

            try {
                const uploadUrl = `${window.apiBaseUrl}/files/upload`;
                console.log('Uploading to:', uploadUrl);
                
                // Use XMLHttpRequest instead of fetch for progress tracking
                const xhr = new XMLHttpRequest();
                
                // Track upload progress
                xhr.upload.onprogress = (event) => {
                    if (event.lengthComputable) {
                        const percentComplete = Math.round((event.loaded / event.total) * 100);
                        // Update overall progress based on current file and queue position
                        const filesCompleted = i;
                        const totalFiles = this.uploadQueue.length;
                        const fileProgress = percentComplete / 100;
                        this.uploadProgress = Math.round(((filesCompleted + fileProgress) / totalFiles) * 100);
                    }
                };

                // Wait for upload to complete
                await new Promise((resolve, reject) => {
                    xhr.onload = () => resolve(xhr);
                    xhr.onerror = () => reject(new Error('Upload failed'));
                    
                    xhr.open('POST', uploadUrl);
                    xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
                    xhr.setRequestHeader('Accept', 'application/json');
                    xhr.send(formData);
                });

                if (xhr.status >= 200 && xhr.status < 300) {
                    item.status = 'success';
                } else {
                    const data = JSON.parse(xhr.responseText);
                    item.status = 'error';
                    item.error = data.message || 'Failed';
                }
            } catch (error) {
                console.error('Upload error:', error);
                item.status = 'error';
                item.error = 'Network error';
            }
        }

        this.isUploading = false;
        this.currentUploadFile = '';
        
        // If all success, close and reload after short delay
        if (this.uploadQueue.every(item => item.status === 'success')) {
            setTimeout(() => {
                this.showUploadPreview = false;
                this.uploadQueue = [];
                window.location.reload();
            }, 1000);
        }
    },
    
    // Existing Drag Handlers
    isDragging: false,
    dragCounter: 0,

    handleDragEnter(e) {
        e.preventDefault();
        this.dragCounter++;
        this.isDragging = true;
    },

    handleDragLeave(e) {
        e.preventDefault();
        this.dragCounter--;
        if (this.dragCounter === 0) {
            this.isDragging = false;
        }
    },
    
    // Stub for old uploadFile used by input (replaced by handleFileSelect)
    uploadFile(event) {
        this.handleFileSelect(event);
    },

    // File Preview Methods
    openPreview(file, allFiles) {
        this.previewFiles = allFiles; // Allow folders to be previewed
        this.previewFileIndex = this.previewFiles.findIndex(f => f.id === file.id);
        this.showPreviewModal = true;
        
        // Add keyboard listener
        document.addEventListener('keydown', this.handlePreviewKeyboard.bind(this));
    },

    closePreview() {
        this.showPreviewModal = false;
        document.removeEventListener('keydown', this.handlePreviewKeyboard);
    },

    nextPreview() {
        if (this.previewFileIndex < this.previewFiles.length - 1) {
            this.previewFileIndex++;
        }
    },

    prevPreview() {
        if (this.previewFileIndex > 0) {
            this.previewFileIndex--;
        }
    },

    handlePreviewKeyboard(event) {
        if (!this.showPreviewModal) return;
        if (event.key === 'Escape') this.closePreview();
        if (event.key === 'ArrowRight') this.nextPreview();
        if (event.key === 'ArrowLeft') this.prevPreview();
    },

    get currentPreviewFile() {
        return this.previewFiles[this.previewFileIndex] || null;
    },

    downloadFile(file) {
        if (!file) return;
        const url = file.type === 'folder' 
            ? `${window.apiBaseUrl}/folders/${file.id}/download`
            : `${window.apiBaseUrl}/files/${file.id}/download`;
        window.location.href = url;
    },

    copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            alert('Copied to clipboard!');
        });
    }
}">
    <div class="flex h-screen overflow-hidden bg-gray-50">
        <!-- Sidebar -->
        <div class="w-72 sidebar flex flex-col shadow-xl z-20">
            <div class="p-8">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-xl theme-bg-primary flex items-center justify-center shadow-lg shadow-indigo-500/30">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                    </div>
                    <h1 class="text-2xl font-bold tracking-tight text-white">Media<span class="theme-text-primary">Hub</span></h1>
                </div>
            </div>
            
            <nav class="flex-1 px-4 space-y-2 overflow-y-auto">
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4 px-4 mt-4">Menu</div>
                
                <a href="{{ route('file-manager.dashboard') }}" class="sidebar-link flex items-center px-4 py-3 {{ request()->routeIs('file-manager.dashboard') ? 'active' : '' }}">
                    <svg class="w-5 h-5 mr-3 {{ request()->routeIs('file-manager.dashboard') ? 'theme-text-primary' : 'text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    <span class="font-medium">Dashboard</span>
                </a>
                
                <a href="{{ route('file-manager.trash') }}" class="sidebar-link flex items-center px-4 py-3 {{ request()->routeIs('file-manager.trash') ? 'active' : '' }}">
                    <svg class="w-5 h-5 mr-3 {{ request()->routeIs('file-manager.trash') ? 'theme-text-primary' : 'text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    <span class="font-medium">Trash</span>
                </a>
                
                <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4 px-4 mt-8">Configuration</div>
                
                <a href="{{ route('file-manager.settings') }}" class="sidebar-link flex items-center px-4 py-3 {{ request()->routeIs('file-manager.settings') ? 'active' : '' }}">
                    <svg class="w-5 h-5 mr-3 {{ request()->routeIs('file-manager.settings') ? 'theme-text-primary' : 'text-gray-500' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    <span class="font-medium">Settings</span>
                </a>
            </nav>
            
            <div class="p-6">
                <div class="glass-panel rounded-2xl p-4 bg-gray-800 border-gray-700">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-xs font-semibold text-gray-400">STORAGE</span>
                        <span class="text-xs font-bold text-white">45%</span>
                    </div>
                    <div class="w-full bg-gray-700 rounded-full h-2 mb-2">
                        <div class="theme-bg-primary h-2 rounded-full shadow-[0_0_10px_rgba(99,102,241,0.5)]" style="width: 45%"></div>
                    </div>
                    <div class="flex justify-between text-[10px] text-gray-500">
                        <span>450MB used</span>
                        <span>1GB total</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden relative"
             @dragenter.prevent="handleDragEnter" 
             @dragover.prevent 
             @dragleave.prevent="handleDragLeave" 
             @drop.prevent="handleDrop">
            
            <!-- Drag Overlay -->
            <div x-show="isDragging" 
                 class="absolute inset-0 z-50 theme-bg-primary bg-opacity-20 border-4 theme-border-primary border-dashed flex items-center justify-center pointer-events-none"
                 style="display: none;">
                 <div class="bg-white p-6 rounded-lg shadow-xl text-center">
                    <svg class="w-16 h-16 mx-auto theme-text-primary mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                    <h3 class="text-xl font-bold text-gray-800">Drop files to upload</h3>
                 </div>
            </div>

            <header class="bg-white border-b border-gray-200 p-4 flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-800">@yield('title', 'Dashboard')</h2>
                <div class="flex items-center space-x-4">
                    <!-- Actions -->
                    @yield('actions')
                </div>
            </header>
            <main class="flex-1 overflow-y-auto p-6">
                @yield('content')
            </main>
            </div>
        </div>
    </div>

    <!-- Upload Preview Modal -->
    <div x-show="showUploadPreview" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4 text-center">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                        Upload Files (<span x-text="uploadQueue.length"></span>)
                    </h3>
                    
                    <!-- File List -->
                    <div class="mt-2 max-h-60 overflow-y-auto space-y-2">
                        <template x-for="(item, index) in uploadQueue" :key="item.id">
                            <div class="flex items-center p-2 border rounded hover:bg-gray-50">
                                <!-- Preview -->
                                <div class="h-12 w-12 flex-shrink-0 mr-3 bg-gray-100 rounded overflow-hidden flex items-center justify-center">
                                    <template x-if="item.preview">
                                        <img :src="item.preview" class="h-full w-full object-cover">
                                    </template>
                                    <template x-if="!item.preview">
                                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                    </template>
                                </div>
                                
                                <!-- Info -->
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate" x-text="item.file.name"></p>
                                    <p class="text-xs text-gray-500" x-text="(item.file.size / 1024).toFixed(1) + ' KB'"></p>
                                    
                                    <!-- Status Error -->
                                    <template x-if="item.status === 'error'">
                                        <p class="text-xs text-red-600" x-text="item.error"></p>
                                    </template>
                                </div>

                                <!-- Status Icon -->
                                <div class="ml-2">
                                    <template x-if="item.status === 'uploading'">
                                        <svg class="animate-spin h-5 w-5 theme-text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    </template>
                                    <template x-if="item.status === 'success'">
                                        <svg class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    </template>
                                    <template x-if="item.status === 'pending'">
                                        <button @click="removeQueueItem(index)" class="text-gray-400 hover:text-red-500">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Progress Bar -->
                    <div x-show="isUploading" class="mt-4">
                        <div class="flex justify-between text-xs text-gray-500 mb-1">
                            <span x-text="currentUploadFile ? 'Uploading: ' + currentUploadFile : 'Processing...'"></span>
                            <span x-text="uploadProgress + '%'"></span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="theme-bg-primary h-2 rounded-full transition-all duration-300" :style="'width: ' + uploadProgress + '%'"></div>
                        </div>
                    </div>

                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 theme-bg-primary text-base font-medium text-white hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 theme-ring-primary sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50" 
                            @click="processUploadQueue()"
                            :disabled="isUploading || uploadQueue.length === 0">
                        <span x-text="isUploading ? 'Uploading...' : 'Start Upload'"></span>
                    </button>
                    <button type="button" 
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 theme-ring-primary sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" 
                            @click="showUploadPreview = false; uploadQueue = []"
                            :disabled="isUploading">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- File Preview Modal -->
    <div x-show="showPreviewModal" 
         @click.self="closePreview()"
         class="fixed inset-0 z-50 bg-black bg-opacity-95 flex items-center justify-center"
         style="display: none;"
         x-cloak>
        
        <!-- Close Button -->
        <button @click="closePreview()" class="absolute top-4 right-4 text-white hover:text-gray-300 z-50">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>

        <!-- Navigation Arrows -->
        <button @click="prevPreview()" 
                x-show="previewFileIndex > 0"
                class="absolute left-4 top-1/2 transform -translate-y-1/2 text-white hover:text-gray-300 bg-black bg-opacity-50 rounded-full p-3 z-50">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </button>

        <button @click="nextPreview()"
                x-show="previewFileIndex < previewFiles.length - 1"
                class="absolute right-4 top-1/2 transform -translate-y-1/2 text-white hover:text-gray-300 bg-black bg-opacity-50 rounded-full p-3 z-50">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
            </svg>
        </button>

        <!-- Content Container -->
        <div class="flex w-full h-full p-4 gap-4">
            <!-- Main Preview Area (70%) -->
            <div class="flex-1 flex items-center justify-center overflow-hidden">
                <template x-if="currentPreviewFile">
                    <div class="max-w-full max-h-full flex items-center justify-center">
                        <!-- Folder Preview -->
                         <template x-if="currentPreviewFile.type === 'folder'">
                             <div class="text-center text-white">
                                <svg class="w-48 h-48 mx-auto mb-4 text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path></svg>
                                <p class="text-3xl font-bold" x-text="currentPreviewFile.basename"></p>
                                <p class="text-lg opacity-80 mt-2">Folder</p>
                             </div>
                         </template>

                        <!-- Image Preview -->
                        <template x-if="currentPreviewFile.type !== 'folder' && currentPreviewFile.mime_type && currentPreviewFile.mime_type.startsWith('image/')">
                            <img :src="`{{ url(config('file-manager.route_prefix')) }}/api/files/${currentPreviewFile.id}/preview`" 
                                 :alt="currentPreviewFile.basename"
                                 class="max-w-full max-h-full object-contain">
                        </template>

                        <!-- Video Preview -->
                        <template x-if="currentPreviewFile.type !== 'folder' && currentPreviewFile.mime_type && currentPreviewFile.mime_type.startsWith('video/')">
                            <video :key="currentPreviewFile.id" controls autoplay class="max-w-full max-h-full">
                                <source :src="`{{ url(config('file-manager.route_prefix')) }}/api/files/${currentPreviewFile.id}/preview`" :type="currentPreviewFile.mime_type">
                                Your browser does not support the video tag.
                            </video>
                        </template>

                        <!-- PDF Preview -->
                        <template x-if="currentPreviewFile.type !== 'folder' && currentPreviewFile.mime_type === 'application/pdf'">
                            <iframe :src="`{{ url(config('file-manager.route_prefix')) }}/api/files/${currentPreviewFile.id}/preview`"
                                    class="w-full h-full border-0">
                            </iframe>
                        </template>

                        <!-- Other Files (Generic) -->
                        <template x-if="currentPreviewFile.type !== 'folder' && currentPreviewFile.mime_type && !currentPreviewFile.mime_type.startsWith('image/') && !currentPreviewFile.mime_type.startsWith('video/') && currentPreviewFile.mime_type !== 'application/pdf'">
                            <div class="text-center text-white">
                                <svg class="w-32 h-32 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                                <p class="text-xl mb-4">Preview not available for this file type</p>
                                <a :href="`{{ url(config('file-manager.route_prefix')) }}/api/files/${currentPreviewFile.id}/download`" 
                                   class="inline-block theme-bg-primary hover:opacity-90 text-white px-6 py-3 rounded-lg">
                                    Download File
                                </a>
                            </div>
                        </template>
                    </div>
                </template>
            </div>

            <!-- File Info Panel (30%) -->
            <div class="w-96 bg-white rounded-lg p-6 overflow-y-auto flex-shrink-0">
                <template x-if="currentPreviewFile">
                    <div>
                        <!-- Header -->
                        <div class="flex items-start justify-between mb-6">
                            <h3 class="text-xl font-bold text-gray-900">Information</h3>
                             <span x-show="currentPreviewFile.type === 'folder'" 
                                   class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded-full">
                                Folder
                             </span>
                        </div>
                        
                        <!-- File Name -->
                        <div class="mb-5">
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Name</label>
                            <p class="text-sm font-medium text-gray-900 break-words" x-text="currentPreviewFile.basename"></p>
                        </div>

                        <!-- Location / Parent -->
                         <div class="mb-5">
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Location</label>
                            <div class="flex items-center text-sm text-gray-900">
                                <template x-if="currentPreviewFile.parent">
                                    <div class="flex items-center" :title="currentPreviewFile.parent.basename">
                                        <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                                        <span x-text="currentPreviewFile.parent.basename" class="truncate max-w-[200px]"></span>
                                    </div>
                                </template>
                                <template x-if="!currentPreviewFile.parent">
                                    <span class="text-gray-400 italic">Home / Root</span>
                                </template>
                            </div>
                        </div>

                        <!-- Counts (For Folder) -->
                        <template x-if="currentPreviewFile.type === 'folder'">
                            <div class="mb-5 grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Files</label>
                                    <p class="text-lg font-bold text-blue-600" x-text="currentPreviewFile.sub_files_count || 0"></p>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Folders</label>
                                    <p class="text-lg font-bold text-yellow-500" x-text="currentPreviewFile.sub_folders_count || 0"></p>
                                </div>
                            </div>
                        </template>

                        <!-- File Size (For File) -->
                        <template x-if="currentPreviewFile.type !== 'folder'">
                            <div class="mb-5">
                                <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Size</label>
                                <p class="text-sm text-gray-900" x-text="(currentPreviewFile.size / 1024 / 1024).toFixed(2) + ' MB'"></p>
                            </div>
                        </template>

                        <!-- File Type -->
                        <div class="mb-5">
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Type</label>
                            <p class="text-sm text-gray-900" x-text="currentPreviewFile.type === 'folder' ? 'Directory' : (currentPreviewFile.mime_type || 'Unknown')"></p>
                        </div>

                        <!-- Date -->
                        <div class="mb-5" x-show="currentPreviewFile.created_at">
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">Created</label>
                            <p class="text-sm text-gray-900" x-text="currentPreviewFile.human_date || new Date(currentPreviewFile.created_at).toLocaleDateString()"></p>
                        </div>

                        <hr class="border-gray-100 my-5">

                        <!-- File URL with Copy Button -->
                        <div class="mb-4">
                            <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2" x-text="currentPreviewFile.type === 'folder' ? 'Download Zip Link' : 'File Download Link'"></label>
                            <div class="flex gap-2">
                                <input type="text" 
                                       :value="`{{ url(config('file-manager.route_prefix')) }}/api/${currentPreviewFile.type === 'folder' ? 'folders' : 'files'}/${currentPreviewFile.id}/download`"
                                       readonly
                                       class="flex-1 text-xs bg-gray-50 border border-gray-200 rounded px-3 py-2 text-gray-600 overflow-hidden text-ellipsis focus:outline-none">
                                <button @click="copyToClipboard(`{{ url(config('file-manager.route_prefix')) }}/api/${currentPreviewFile.type === 'folder' ? 'folders' : 'files'}/${currentPreviewFile.id}/download`)"
                                        class="theme-bg-primary hover:opacity-90 text-white px-3 py-2 rounded text-xs font-medium flex-shrink-0 transition-colors">
                                    Copy
                                </button>
                            </div>
                        </div>

                        <!-- Download Button -->
                        <a :href="`{{ url(config('file-manager.route_prefix')) }}/api/${currentPreviewFile.type === 'folder' ? 'folders' : 'files'}/${currentPreviewFile.id}/download`"
                           class="block w-full theme-bg-primary hover:opacity-90 text-white text-center px-4 py-3 rounded-xl font-semibold shadow-lg shadow-indigo-500/20 transition-all hover:scale-[1.02]">
                            <span x-text="currentPreviewFile.type === 'folder' ? 'Download as Zip' : 'Download File'"></span>
                        </a>

                        <!-- Counter -->
                        <div class="mt-6 text-center text-xs text-gray-400">
                            Item <span x-text="previewFileIndex + 1"></span> of <span x-text="previewFiles.length"></span>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
    <!-- Multi/Single Select Confirm Button -->
    <div x-show="pickerMode && selectedFiles.length > 0" 
         class="fixed bottom-6 right-6 z-50 transition-transform duration-300 transform translate-y-0"
         style="display: none;"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="translate-y-full"
         x-transition:enter-end="translate-y-0"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="translate-y-0"
         x-transition:leave-end="translate-y-full">
        <button @click="confirmSelection()" 
                class="theme-bg-primary text-white px-6 py-3 rounded-full shadow-lg hover:opacity-90 font-medium flex items-center space-x-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <span x-text="pickerMultiple ? 'Confirm Selection (' + selectedFiles.length + ')' : 'Confirm Selection'"></span>
        </button>
    </div>

</body>
</html>
