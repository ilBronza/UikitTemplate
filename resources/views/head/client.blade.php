<script>
(function () {
    'use strict';
    if (window.ibHeadMetadata) return;

    const iconSelector = 'link[rel~="icon" i]';
    const themeSelector = 'meta[name="theme-color" i]';
    const originalIcons = Array.from(document.head.querySelectorAll(iconSelector), node => node.cloneNode(true));
    const originalThemes = Array.from(document.head.querySelectorAll(themeSelector), node => node.cloneNode(true));
    let faviconChanged = false;
    let themeChanged = false;

    function replace(selector, nodes) {
        document.head.querySelectorAll(selector).forEach(node => node.remove());
        nodes.forEach(node => document.head.appendChild(node.cloneNode(true)));
    }

    function setFavicon(icon) {
        const value = typeof icon === 'string' ? { path: icon } : icon;
        const nodes = [];
        if (value?.path) {
            const node = document.createElement('link');
            node.rel = 'icon';
            node.setAttribute('href', value.path);
            if (value.type) node.type = value.type;
            if (value.sizes) node.setAttribute('sizes', value.sizes);
            nodes.push(node);
        }
        replace(iconSelector, nodes);
        faviconChanged = true;
    }

    function setThemeColor(color) {
        const nodes = [];
        if (color) {
            const node = document.createElement('meta');
            node.name = 'theme-color';
            node.content = color;
            nodes.push(node);
        }
        replace(themeSelector, nodes);
        themeChanged = true;
    }

    function restoreFavicon() {
        if (!faviconChanged) return;
        replace(iconSelector, originalIcons);
        faviconChanged = false;
    }

    function restoreThemeColor() {
        if (!themeChanged) return;
        replace(themeSelector, originalThemes);
        themeChanged = false;
    }

    window.ibHeadMetadata = { setFavicon, setThemeColor, restoreFavicon, restoreThemeColor };
})();
</script>
