<script>
(function () {
    'use strict';
    if (window.top !== window || window.ibConcurrentUriAppearance) return;
    const head = window.ibHeadMetadata;
    if (!head) return;
    const config = window.ibConcurrentUriAppearanceConfig || {};
    let warning = false;
    let defaultIcon;

    function warningFavicon() {
        if (config.warningFavicon) return config.warningFavicon;
        if (!defaultIcon) {
            const canvas = document.createElement('canvas');
            canvas.width = canvas.height = 32;
            const context = canvas.getContext('2d');
            context.beginPath();
            context.moveTo(16, 2);
            context.lineTo(31, 29);
            context.lineTo(1, 29);
            context.closePath();
            context.fillStyle = config.warningThemeColor || '#facc15';
            context.fill();
            context.strokeStyle = '#422006';
            context.lineWidth = 2;
            context.lineJoin = 'round';
            context.stroke();
            context.fillStyle = '#422006';
            context.fillRect(14, 11, 4, 9);
            context.fillRect(14, 23, 4, 4);
            defaultIcon = { path: canvas.toDataURL('image/png'), type: 'image/png', sizes: '32x32' };
        }
        return defaultIcon;
    }

    function update() {
        const client = window.ibConcurrentUri;
        if (!client) return;
        const state = client.state?.() || (client.isOwner() ? 'owner' : 'unavailable');
        // Keep the current appearance while the first/recovery heartbeat is pending.
        if (state === 'checking') return;
        const blocked = state !== 'owner';
        if (blocked === warning) return;
        if (blocked) {
            head.setFavicon(warningFavicon());
            head.setThemeColor(config.warningThemeColor || '#facc15');
        } else {
            head.restoreFavicon();
            head.restoreThemeColor();
        }
        warning = blocked;
    }

    window.ibConcurrentUriAppearance = { update };
    window.addEventListener('ib-concurrent-uri-state', update);
    window.addEventListener('pageshow', update);
    update();
})();
</script>
