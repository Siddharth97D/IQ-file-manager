export class ShortcutManager {
    constructor() {
        this.init();
    }

    init() {
        document.addEventListener('keydown', (e) => {
            // Ignore if in input or textarea
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.isContentEditable) {
                return;
            }

            // Ctrl/Cmd + A: Select All
            if ((e.ctrlKey || e.metaKey) && e.key === 'a') {
                e.preventDefault();
                this.dispatch('select-all');
            }

            // Delete: Delete selected
            if (e.key === 'Delete' || e.key === 'Backspace') {
                e.preventDefault();
                this.dispatch('delete-selected');
            }

            // Escape: Close modals / clear selection
            if (e.key === 'Escape') {
                e.preventDefault();
                this.dispatch('escape');
            }

            // ?: Show Help
            if (e.key === '?') {
                e.preventDefault();
                this.dispatch('show-help');
            }
        });
    }

    dispatch(action) {
        window.dispatchEvent(new CustomEvent('fm-shortcut', { detail: { action } }));
    }
}

// Initialize
window.ShortcutManager = new ShortcutManager();
