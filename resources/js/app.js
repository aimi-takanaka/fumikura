document.addEventListener('alpine:init', () => {
    const resolveThemeName = (appearance) => {
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        const isDark = appearance === 'dark' || (appearance === 'system' && prefersDark);

        return isDark ? 'fumikura-dark' : 'fumikura';
    };

    const applyAppearance = (appearance) => {
        const themeName = resolveThemeName(appearance);

        document.documentElement.setAttribute('data-theme', themeName);
        document.documentElement.classList.toggle('dark', themeName === 'fumikura-dark');
    };

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

            this.mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
            this.onMediaChange = () => {
                if (this.theme === 'system') {
                    this.apply();
                }
            };
            this.mediaQuery.addEventListener('change', this.onMediaChange);
        },

        destroy() {
            this.mediaQuery?.removeEventListener('change', this.onMediaChange);
        },

        setTheme(theme) {
            this.theme = theme;
            localStorage.setItem('appearance', theme);
            this.apply();
        },

        apply() {
            applyAppearance(this.theme);
        },
    }));

    window.Alpine.data('themeToggle', () => ({
        theme: 'system',

        init() {
            this.theme = localStorage.getItem('appearance') || 'system';
        },

        cycle() {
            const order = ['light', 'dark', 'system'];
            const next = order[(order.indexOf(this.theme) + 1) % order.length];

            this.theme = next;
            localStorage.setItem('appearance', next);
            applyAppearance(next);
        },

        label() {
            return {
                light: 'ライト',
                dark: 'ダーク',
                system: 'システム',
            }[this.theme] ?? 'テーマ';
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
