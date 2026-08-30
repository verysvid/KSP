import './bootstrap';
import './sweetalert';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.data('appLayout', () => ({
    sidebarOpen: false,
    darkMode: false,

    init() {
        const savedTheme = localStorage.getItem('koperasi-theme');

        this.darkMode = savedTheme
            ? savedTheme === 'dark'
            : window.matchMedia('(prefers-color-scheme: dark)').matches;

        this.applyTheme();

        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024) {
                this.sidebarOpen = false;
            }
        });
    },

    toggleDarkMode() {
        this.darkMode = !this.darkMode;

        localStorage.setItem(
            'koperasi-theme',
            this.darkMode ? 'dark' : 'light'
        );

        this.applyTheme();
    },

    applyTheme() {
        document.documentElement.classList.toggle('dark', this.darkMode);
    },
}));

Alpine.start();
