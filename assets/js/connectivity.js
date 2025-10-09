// UI: badges online/offline + triggers de sincronización + logging global
(() => {
    const badge = document.getElementById('badge-offline');

    function updateBadge() {
        if (badge) badge.style.display = navigator.onLine ? 'none' : 'inline-block';
    }

    window.addEventListener('online', () => { updateBadge(); try { window.SyncEngine?.processOutbox(); } catch (e) { console.error(e); } });
    window.addEventListener('offline', () => { updateBadge(); });

    // Logging de errores para no “comernos” fallos silenciosos
    window.addEventListener('error', (e) => {
        console.error('[global error]', e.error || e.message || e);
    });
    window.addEventListener('unhandledrejection', (e) => {
        console.error('[unhandledrejection]', e.reason || e);
    });

    // Intento de sync al cargar si hay red
    document.addEventListener('DOMContentLoaded', () => {
        updateBadge();
        if (navigator.onLine) window.SyncEngine?.processOutbox();
    });
})();
