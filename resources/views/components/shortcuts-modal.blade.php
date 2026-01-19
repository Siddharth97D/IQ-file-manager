<div 
    x-data="{ open: false }"
    @fm-shortcut.window="if ($event.detail.action === 'show-help') open = true; if ($event.detail.action === 'escape') open = false;"
    class="relative z-50"
    aria-labelledby="modal-title" 
    role="dialog" 
    aria-modal="true"
    x-show="open"
    x-cloak
>
    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" x-show="open" x-transition.opacity></div>

    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div 
                class="relative transform overflow-hidden rounded-lg bg-white dark:bg-gray-800 text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg"
                @click.outside="open = false"
                x-show="open"
                x-transition.scale
            >
                <div class="bg-white dark:bg-gray-800 px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                            <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-white" id="modal-title">Keyboard Shortcuts</h3>
                            <div class="mt-4 grid grid-cols-2 gap-4">
                                <div class="flex justify-between items-center bg-gray-50 dark:bg-gray-700 p-2 rounded">
                                    <span class="text-sm text-gray-600 dark:text-gray-300">Select All</span>
                                    <kbd class="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 border border-gray-200 rounded dark:bg-gray-600 dark:text-gray-100 dark:border-gray-500">Ctrl + A</kbd>
                                </div>
                                <div class="flex justify-between items-center bg-gray-50 dark:bg-gray-700 p-2 rounded">
                                    <span class="text-sm text-gray-600 dark:text-gray-300">Delete Selected</span>
                                    <kbd class="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 border border-gray-200 rounded dark:bg-gray-600 dark:text-gray-100 dark:border-gray-500">Del</kbd>
                                </div>
                                <div class="flex justify-between items-center bg-gray-50 dark:bg-gray-700 p-2 rounded">
                                    <span class="text-sm text-gray-600 dark:text-gray-300">Close Modal</span>
                                    <kbd class="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 border border-gray-200 rounded dark:bg-gray-600 dark:text-gray-100 dark:border-gray-500">Esc</kbd>
                                </div>
                                <div class="flex justify-between items-center bg-gray-50 dark:bg-gray-700 p-2 rounded">
                                    <span class="text-sm text-gray-600 dark:text-gray-300">Show Help</span>
                                    <kbd class="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 border border-gray-200 rounded dark:bg-gray-600 dark:text-gray-100 dark:border-gray-500">?</kbd>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                    <button type="button" class="mt-3 inline-flex w-full justify-center rounded-md bg-white dark:bg-gray-600 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto" @click="open = false">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>
