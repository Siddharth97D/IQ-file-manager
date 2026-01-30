<div x-data="{ 
    show: false,
    file: null,
    step: 'create', // create, result
    form: {
        password: '',
        expires_at: '',
        max_downloads: ''
    },
    loading: false,
    shareUrl: '',
    error: '',

    init() {
        window.addEventListener('fm:share', (e) => {
            this.open(e.detail.file);
        });
    },

    open(file) {
        this.file = file;
        this.step = 'create';
        this.form = { password: '', expires_at: '', max_downloads: '' };
        this.shareUrl = '';
        this.error = '';
        this.show = true;
    },

    close() {
        this.show = false;
        this.file = null;
    },

    async createLink() {
        this.loading = true;
        this.error = '';

        try {
            const response = await fetch(`${window.apiBaseUrl}/files/${this.file.id}/share`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''
                },
                body: JSON.stringify(this.form)
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Failed to create link');
            }

            this.shareUrl = data.url;
            this.step = 'result';
        } catch (e) {
            this.error = e.message;
        } finally {
            this.loading = false;
        }
    },

    copyLink() {
        navigator.clipboard.writeText(this.shareUrl).then(() => {
            alert('Link copied!');
        });
    }
}"
x-show="show" 
style="display: none;" 
x-cloak 
class="fixed inset-0 z-[60] overflow-y-auto" 
aria-labelledby="modal-title" 
role="dialog" 
aria-modal="true">
    
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="show" 
             x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100" 
             x-transition:leave="ease-in duration-200" 
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0" 
             class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
             @click="close()" 
             aria-hidden="true"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div x-show="show" 
             x-transition:enter="ease-out duration-300" 
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
             x-transition:leave="ease-in duration-200" 
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
             class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            
            <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path></svg>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100" id="modal-title">
                            Share File
                        </h3>
                        
                        <!-- Create Step -->
                        <div x-show="step === 'create'" class="mt-4 space-y-4">
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Create a public link for <span class="font-bold" x-text="file?.basename"></span>.
                            </p>
                            
                            <!-- Options -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password (Optional)</label>
                                <input type="password" x-model="form.password" class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-700 dark:text-white" placeholder="Leave empty for public access">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Expires At (Optional)</label>
                                <input type="datetime-local" x-model="form.expires_at" class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-700 dark:text-white">
                            </div>

                             <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Max Downloads (Optional)</label>
                                <input type="number" x-model="form.max_downloads" class="mt-1 block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-700 dark:text-white" placeholder="e.g. 5">
                            </div>

                            <p x-show="error" class="text-red-500 text-sm" x-text="error"></p>
                        </div>

                        <!-- Result Step -->
                        <div x-show="step === 'result'" class="mt-4">
                            <p class="text-sm text-green-600 font-medium mb-2">Link created successfully!</p>
                            <div class="flex items-center space-x-2">
                                <input type="text" x-model="shareUrl" readonly class="flex-1 block w-full border border-gray-300 dark:border-gray-600 rounded-md shadow-sm py-2 px-3 bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-200">
                                <button @click="copyLink()" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Copy
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            
            <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse" x-show="step === 'create'">
                <button type="button" @click="createLink()" :disabled="loading" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
                    <span x-text="loading ? 'Creating...' : 'Create Link'"></span>
                </button>
                <button type="button" @click="close()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-500 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
             <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse" x-show="step === 'result'">
                <button type="button" @click="close()" class="w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-gray-500 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Close
                </button>
            </div>

        </div>
    </div>
</div>
