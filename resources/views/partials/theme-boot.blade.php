<script>
    (function () {
        var mode = @json($mode ?? 'system');
        var dark = mode === 'dark'
            || (mode === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);

        var root = document.documentElement;
        root.dataset.theme = dark ? 'dark' : 'light';
        root.dataset.themeMode = mode;

        var themeColor = document.querySelector('meta[name="theme-color"]');
        if (themeColor) {
            themeColor.setAttribute('content', dark ? '#070b1a' : '#eef0fb');
        }
    })();
</script>
