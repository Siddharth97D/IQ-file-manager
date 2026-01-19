document.addEventListener('DOMContentLoaded', () => {
    const themeToggleBtn = document.getElementById('fm-theme-toggle');
    const htmlElement = document.documentElement;
    
    // Initialize theme from server config or local storage
    // We assume backend injects a window.fmConfig object with user preferences if available
    
    function applyTheme(theme) {
        if (theme === 'dark') {
            htmlElement.classList.add('dark');
        } else {
            htmlElement.classList.remove('dark');
        }
    }

    // Toggle theme
    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', async () => {
            const isDark = htmlElement.classList.contains('dark');
            const newTheme = isDark ? 'light' : 'dark';
            
            applyTheme(newTheme);

            // Save preference to backend
            try {
                await fetch('/file-manager/api/preferences', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        key: 'theme',
                        value: newTheme
                    })
                });
            } catch (error) {
                console.error('Failed to save theme preference', error);
            }
            
            // Also save to localStorage for instant load next time (before auth/api loads)
            localStorage.setItem('fm_theme', newTheme);
        });
    }

    // Initial Load
    const savedTheme = localStorage.getItem('fm_theme');
    if (savedTheme) {
        applyTheme(savedTheme);
    } else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
        // Auto check system preference if no user preference
        applyTheme('dark');
    }
});
