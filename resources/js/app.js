(() => {
    const normalizeAppearance = (appearance) => {
        if (appearance === 'dark' || appearance === 'light') {
            return appearance;
        }

        // Migrate legacy "system" (and unknown values) to an explicit preference.
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

        return prefersDark ? 'dark' : 'light';
    };

    const resolveThemeName = (appearance) => {
        return normalizeAppearance(appearance) === 'dark' ? 'fumikura-dark' : 'fumikura';
    };

    const canChangeTheme = () => window.FumikuraThemeConfig?.canChange === true;

    const applyAppearance = (appearance = null) => {
        // Guests always see light mode; keep any saved preference for after login.
        if (! canChangeTheme()) {
            document.documentElement.setAttribute('data-theme', 'fumikura');
            document.documentElement.classList.remove('dark');

            return 'light';
        }

        const value = normalizeAppearance(appearance ?? localStorage.getItem('appearance') ?? 'light');

        if (localStorage.getItem('appearance') !== value) {
            localStorage.setItem('appearance', value);
        }

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
            theme: 'light',

            init() {
                this.theme = applyAppearance();
            },

            setTheme(theme) {
                this.theme = normalizeAppearance(theme);
                localStorage.setItem('appearance', this.theme);
                applyAppearance(this.theme);
            },
        }));

        window.Alpine.data('themeToggle', () => ({
            theme: 'light',

            init() {
                this.theme = applyAppearance();
            },

            cycle() {
                const next = this.theme === 'dark' ? 'light' : 'dark';

                this.theme = next;
                localStorage.setItem('appearance', next);
                applyAppearance(next);
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

        /**
         * Persist story create/edit fields to localStorage so a reload keeps the draft.
         */
        window.Alpine.data('storyComposerDraft', (config = {}) => ({
            key: config.key,
            skipRestore: Boolean(config.skipRestore),
            timer: null,

            init() {
                if (! this.key) {
                    return;
                }

                if (! this.skipRestore) {
                    this.restore();
                }

                this.$wire.$watch('title', () => this.scheduleSave());
                this.$wire.$watch('body', () => this.scheduleSave());
                this.$wire.$watch('status', () => this.scheduleSave());
            },

            scheduleSave() {
                clearTimeout(this.timer);
                this.timer = setTimeout(() => this.persist(), 400);
            },

            persist() {
                const title = this.$wire.title ?? '';
                const body = this.$wire.body ?? '';
                const status = this.$wire.status ?? '';

                if (title === '' && body === '') {
                    localStorage.removeItem(this.key);

                    return;
                }

                localStorage.setItem(this.key, JSON.stringify({
                    title,
                    body,
                    status,
                    savedAt: Date.now(),
                }));
            },

            restore() {
                const raw = localStorage.getItem(this.key);

                if (! raw) {
                    return;
                }

                try {
                    const data = JSON.parse(raw);

                    if (typeof data.title === 'string') {
                        this.$wire.title = data.title;
                    }

                    if (typeof data.body === 'string') {
                        this.$wire.body = data.body;
                    }

                    if (typeof data.status === 'string' && data.status !== '') {
                        this.$wire.status = data.status;
                    }
                } catch {
                    localStorage.removeItem(this.key);
                }
            },
        }));
    });
})();
