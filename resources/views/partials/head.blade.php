<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>{{ $title ?? '文倉' }}</title>

<link rel="icon" href="{{ asset('img/logo/favicon.ico') }}" sizes="any">
<link rel="apple-touch-icon" href="{{ asset('img/logo/icon-180.png') }}">

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Kaisei+Tokumin:wght@400;500&family=Noto+Sans+JP:wght@400;500;700&family=Zen+Maru+Gothic:wght@400;500;700&display=swap" rel="stylesheet">

<script>
    (function () {
        var font = localStorage.getItem('story-body-font');

        if (font === 'zen-maru' || font === 'kaisei-tokumin') {
            document.documentElement.dataset.storyBodyFont = font;
        }

        var appearance = localStorage.getItem('appearance');
        if (appearance !== 'light' && appearance !== 'dark') {
            appearance = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            localStorage.setItem('appearance', appearance);
        }
        var isDark = appearance === 'dark';
        var themeName = isDark ? 'fumikura-dark' : 'fumikura';

        document.documentElement.setAttribute('data-theme', themeName);
        document.documentElement.classList.toggle('dark', isDark);
    })();
</script>

@vite(['resources/css/app.css', 'resources/js/app.js'])
