@extends('file-manager::layout')

@section('title', 'Trash')

@section('actions')
    <button onclick="if(confirm('Empty Trash?')) document.getElementById('empty-trash-form').submit()" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center">
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
        Empty Trash
    </button>
    <form id="empty-trash-form" action="{{ route('file-manager.trash.empty') }}" method="POST" class="hidden">@csrf @method('DELETE')</form>
@endsection

@section('content')
    <div class="glass-panel rounded-2xl overflow-hidden" x-data="{
        selectedFiles: [],
        allFiles: @json($files->items()),

        selectFile(file) {
            if (!file) return;
            const index = this.selectedFiles.findIndex(f => f.id === file.id);
            if (index === -1) {
                this.selectedFiles.push(file);
            } else {
                this.selectedFiles.splice(index, 1);
            }
        },

        clickTimeout: null,
        handleFileClick(file) {
            if (this.clickTimeout) {
                clearTimeout(this.clickTimeout);
                this.clickTimeout = null;
                return;
            }
            this.clickTimeout = setTimeout(() => {
                this.selectFile(file);
                this.clickTimeout = null;
            }, 250);
        },

        async restoreFile(id) {
            if (!confirm('Restore this file?')) return;
            this.executeRestore([id]);
        },

        async bulkRestore() {
            if (!confirm(`Restore ${this.selectedFiles.length} items?`)) return;
            this.executeRestore(this.selectedFiles.map(f => f.id));
        },

        async executeRestore(ids) {
            try {
                const response = await fetch(`${window.apiBaseUrl}/api/trash/bulk-restore`, {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                    },
                    body: JSON.stringify({ ids })
                });
                
                if (response.ok) {
                    window.location.reload();
                } else {
                    const data = await response.json();
                    alert(data.message || 'Restore failed');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred');
            }
        },

        async deleteFile(id) {
            if (!confirm('Permanently delete this file? This cannot be undone.')) return;
            this.executeDelete([id]);
        },

        async bulkDelete() {
            if (!confirm(`Permanently delete ${this.selectedFiles.length} items? This cannot be undone.`)) return;
            this.executeDelete(this.selectedFiles.map(f => f.id));
        },

        async executeDelete(ids) {
            try {
                const response = await fetch(`${window.apiBaseUrl}/api/trash/bulk-destroy`, {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                    },
                    body: JSON.stringify({ ids })
                });
                
                if (response.ok) {
                    window.location.reload();
                } else {
                    const data = await response.json();
                    alert(data.message || 'Delete failed');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred');
            }
        }
    }">
        <table class="min-w-full divide-y divide-gray-200/50">
            <thead class="bg-gray-50/50 backdrop-blur-sm">
                <tr>
                    <th class="px-6 py-3 text-left">
                         <div class="flex items-center">
                            <div @click="if(selectedFiles.length === allFiles.length) { selectedFiles = [] } else { selectedFiles = [...allFiles] }" 
                                 class="w-5 h-5 rounded border border-gray-300 bg-white flex items-center justify-center cursor-pointer transition-colors"
                                 :class="{'theme-bg-primary border-transparent': selectedFiles.length === allFiles.length && allFiles.length > 0}">
                                <svg x-show="selectedFiles.length === allFiles.length && allFiles.length > 0" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <span class="ml-3 text-xs font-medium text-gray-500 uppercase tracking-wider">Name</span>
                        </div>
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deleted At</th>
                    <th class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200/50">
                @forelse($files as $file)
                <tr class="hover:bg-white/30 transition-colors cursor-pointer"
                    :class="{'bg-indigo-50/50': selectedFiles.some(f => f.id === {{ $file->id }})}"
                    @click="handleFileClick(allFiles.find(f => f.id == {{ $file->id }}))">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="mr-3" @click.stop="selectFile(allFiles.find(f => f.id == {{ $file->id }}))">
                                <div class="w-5 h-5 rounded border border-gray-300 bg-white flex items-center justify-center transition-colors shadow-sm"
                                     :class="{'theme-bg-primary border-transparent': selectedFiles.some(f => f.id === {{ $file->id }})}">
                                    <svg x-show="selectedFiles.some(f => f.id === {{ $file->id }})" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                </div>
                            </div>
                            <div class="flex-shrink-0 h-10 w-10 bg-gray-100/50 rounded flex items-center justify-center text-gray-400 overflow-hidden border border-gray-200/50">
                                @if($file->type === 'folder')
                                    <svg class="w-6 h-6 text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"></path></svg>
                                @elseif(str_starts_with($file->mime_type, 'image/'))
                                    <img src="{{ route('file-manager.preview', $file->id) }}" class="object-cover w-full h-full rounded">
                                @else
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                @endif
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">{{ $file->basename }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $file->type === 'folder' ? '-' : \Illuminate\Support\Number::fileSize($file->size) }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $file->deleted_at->format('M d, Y H:i') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <button @click.stop="restoreFile({{ $file->id }})" class="theme-text-primary hover:opacity-80 mr-3 font-semibold">Restore</button>
                        <button @click.stop="deleteFile({{ $file->id }})" class="text-red-600 hover:text-red-900">Delete</button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-12 text-center text-gray-500 text-sm">
                        Trash is empty
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Pagination Links -->
        <div class="px-6 py-4 bg-gray-50/50 border-t border-gray-200/50">
            {{ $files->links() }}
        </div>

        <!-- Bulk Actions Toolbar (Trash) -->
        <div x-show="selectedFiles.length > 0" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-10"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-10"
             class="fixed bottom-8 left-1/2 -translate-x-1/2 z-[60] bg-white/80 backdrop-blur-2xl px-6 py-4 rounded-2xl shadow-2xl border border-white/50 flex items-center space-x-6">
            
            <div class="flex items-center space-x-2 border-r border-gray-200 pr-6 mr-2">
                <span class="flex items-center justify-center min-w-8 h-8 px-2 rounded-full theme-bg-primary text-white text-xs font-bold" x-text="selectedFiles.length"></span>
                <span class="text-sm font-semibold text-gray-700">Items selected</span>
            </div>

            <div class="flex items-center space-x-3">
                <button @click="bulkRestore()" 
                        class="flex items-center px-4 py-2 bg-indigo-50 text-indigo-700 rounded-xl hover:bg-indigo-100 transition-colors font-medium text-sm">
                    Restore
                </button>
                <button @click="bulkDelete()" 
                        class="flex items-center px-4 py-2 bg-red-50 text-red-700 rounded-xl hover:bg-red-100 transition-colors font-medium text-sm">
                    Delete Permanently
                </button>
                <button @click="selectedFiles = []" 
                        class="flex items-center px-4 py-2 text-gray-500 hover:text-gray-700 transition-colors font-medium text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
@endsection
