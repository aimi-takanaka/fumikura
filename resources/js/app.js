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
});
