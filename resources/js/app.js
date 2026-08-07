(() => {
    const resolveThemeName = (appearance) => {
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        const isDark = appearance === 'dark' || (appearance === 'system' && prefersDark);

        return isDark ? 'fumikura-dark' : 'fumikura';
    };

    const applyAppearance = (appearance = null) => {
        const value = appearance ?? localStorage.getItem('appearance') ?? 'system';
        const themeName = resolveThemeName(value);

        document.documentElement.setAttribute('data-theme', themeName);
        document.documentElement.classList.toggle('dark', themeName === 'fumikura-dark');

        return value;
    };

    window.FumikuraTheme = {
        resolve: resolveThemeName,
        apply: applyAppearance,
    };

    // Re-apply after Livewire wire:navigate replaces page markup.
    document.addEventListener('livewire:navigated', () => {
        applyAppearance();

        const font = localStorage.getItem('story-body-font');

        if (font === 'zen-maru' || font === 'kaisei-tokumin') {
            document.documentElement.dataset.storyBodyFont = font;
        } else {
            delete document.documentElement.dataset.storyBodyFont;
        }
    });

    // Also apply as soon as this script loads (full page load / Vite HMR).
    applyAppearance();

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
                this.theme = applyAppearance();

                this.mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
                this.onMediaChange = () => {
                    if (this.theme === 'system') {
                        applyAppearance(this.theme);
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
                applyAppearance(theme);
            },
        }));

        window.Alpine.data('themeToggle', () => ({
            theme: 'system',

            init() {
                this.theme = applyAppearance();
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
})();
