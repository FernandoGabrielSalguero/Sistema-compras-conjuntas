// UI: badges online/offline + triggers de sincronización
(() => {
    const badge = document.getElementById('badge-offline');

    function updateBadge() {
        if (badge) badge.style.display = navigator.onLine ? 'none' : 'inline-block';
    }

    window.addEventListener('online', () => { updateBadge(); window.SyncEngine?.processOutbox(); });
    window.addEventListener('offline', () => { updateBadge(); });

    // Intento de sync al cargar si hay red
    document.addEventListener('DOMContentLoaded', () => {
        updateBadge();
        if (navigator.onLine) window.SyncEngine?.processOutbox();
    });
})();
