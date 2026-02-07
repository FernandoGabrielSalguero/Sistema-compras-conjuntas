/**
 * SVE Offline Initialization
 * Script para inicializar el sistema offline al primer login
 * Se ejecuta automáticamente cuando el piloto_drone inicia sesión
 */

(function() {
    'use strict';

    // Solo ejecutar para rol piloto_drone
    const userRole = document.body.dataset.role || sessionStorage.getItem('user_role');
    if (userRole !== 'piloto_drone') {
        return;
    }

    console.log('[OfflineInit] Inicializando sistema offline para piloto_drone');

    /**
     * Registra el Service Worker
     */
    async function registerServiceWorker() {
        if (!('serviceWorker' in navigator)) {
            console.warn('[OfflineInit] Service Worker no soportado');
            return null;
        }

        try {
            // Cache busting: agregar parámetro de versión
            const registration = await navigator.serviceWorker.register('/service-worker.js?v=4.2', {
                scope: '/'
            });

            console.log('[OfflineInit] Service Worker registrado:', registration.scope);

            // Forzar actualización
            await registration.update();

            // Esperar a que esté activo
            await navigator.serviceWorker.ready;
            console.log('[OfflineInit] Service Worker activo');

            return registration;
        } catch (error) {
            console.error('[OfflineInit] Error al registrar Service Worker:', error);
            return null;
        }
    }

    /**
     * Pre-cachea recursos críticos
     */
    async function precacheResources() {
        const criticalResources = [
            '/views/drone_pilot/drone_pilot_dashboard.php',
            '/offline-sync.js',
            '/offline-init.js',
            '/views/partials/spinner-global.js'
        ];

        console.log('[OfflineInit] Pre-cacheando recursos críticos...');

        const promises = criticalResources.map(url => {
            return fetch(url, {
                method: 'GET',
                credentials: 'same-origin',
                cache: 'reload' // Fuerza actualización del cache
            }).catch(err => {
                console.warn(`[OfflineInit] No se pudo cachear ${url}:`, err);
            });
        });

        await Promise.allSettled(promises);
        console.log('[OfflineInit] Pre-cacheo completado');
    }

    /**
     * Verifica disponibilidad de IndexedDB
     */
    function checkIndexedDB() {
        if (!window.indexedDB) {
            console.error('[OfflineInit] IndexedDB no disponible');
            return false;
        }
        console.log('[OfflineInit] IndexedDB disponible');
        return true;
    }

    /**
     * Muestra notificación de sistema offline listo
     */
    function showReadyNotification() {
        // Buscar función showAlert si existe
        if (typeof window.showAlert === 'function') {
            window.showAlert('success', 'Sistema offline activado. Puedes trabajar sin conexión.');
        } else {
            console.log('[OfflineInit] ✅ Sistema offline listo');
        }
    }

    /**
     * Inicialización principal
     */
    async function init() {
        // Verificar IndexedDB
        if (!checkIndexedDB()) {
            console.error('[OfflineInit] No se puede inicializar sin IndexedDB');
            return;
        }

        // Registrar Service Worker
        const registration = await registerServiceWorker();
        if (!registration) {
            console.error('[OfflineInit] No se pudo registrar Service Worker');
            return;
        }

        // Pre-cachear recursos
        await precacheResources();

        // Marcar como inicializado
        try {
            localStorage.setItem('sve_offline_initialized', 'true');
            localStorage.setItem('sve_offline_init_date', new Date().toISOString());
        } catch (e) {
            console.warn('[OfflineInit] No se pudo guardar estado de inicialización');
        }

        // Notificar que está listo
        showReadyNotification();

        console.log('[OfflineInit] ✅ Inicialización completa');
    }

    /**
     * Verifica si ya fue inicializado
     */
    function isAlreadyInitialized() {
        try {
            return localStorage.getItem('sve_offline_initialized') === 'true';
        } catch (e) {
            return false;
        }
    }

    // Ejecutar al cargar el DOM
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            // Solo inicializar si no se ha hecho antes O si ha pasado más de 1 día
            if (!isAlreadyInitialized()) {
                setTimeout(init, 1000); // Delay para no bloquear la carga inicial
            } else {
                console.log('[OfflineInit] Ya inicializado previamente');
                // Re-registrar SW por si acaso
                registerServiceWorker();
            }
        });
    } else {
        if (!isAlreadyInitialized()) {
            setTimeout(init, 1000);
        } else {
            registerServiceWorker();
        }
    }

    // Exportar funciones para uso manual si se necesita
    window.SVE_OfflineInit = {
        init,
        registerServiceWorker,
        precacheResources,
        checkIndexedDB,
        isAlreadyInitialized
    };

})();
