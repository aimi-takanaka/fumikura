document.addEventListener('alpine:init', () => {
    window.Alpine.data('storyBodyFont', () => ({
        font: 'noto',

        init() {
            this.font = localStorage.getItem('story-body-font') || 'noto';
            this.apply();
        },

        setFont(font) {
            this.font = font;
            localStorage.setItem('story-body-font', font);
            this.apply();
        },

        apply() {
            if (this.font === 'zen-maru' || this.font === 'kaisei-tokumin') {
                document.documentElement.dataset.storyBodyFont = this.font;
            } else {
                delete document.documentElement.dataset.storyBodyFont;
            }
        },
    }));

    window.Alpine.data('appearanceTheme', () => ({
        theme: 'system',

        init() {
            this.theme = localStorage.getItem('appearance') || 'system';
            this.apply();
        },

        setTheme(theme) {
            this.theme = theme;
            localStorage.setItem('appearance', theme);
            this.apply();
        },

        apply() {
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const isDark = this.theme === 'dark' || (this.theme === 'system' && prefersDark);

            document.documentElement.classList.toggle('dark', isDark);
        },
    }));

    window.Alpine.data('toastStack', () => ({
        toasts: [],

        init() {
            window.addEventListener('notify', (event) => {
                this.push(event.detail?.type ?? 'info', event.detail?.message ?? '');
            });
        },

        push(type, message) {
            if (! message) {
                return;
            }

            const id = Date.now() + Math.random();
            this.toasts.push({ id, type, message });

            setTimeout(() => {
                this.toasts = this.toasts.filter((toast) => toast.id !== id);
            }, 3500);
        },

        alertClass(type) {
            return {
                success: 'alert-success',
                error: 'alert-error',
                warning: 'alert-warning',
                info: 'alert-info',
            }[type] ?? 'alert-info';
        },
    }));
});
