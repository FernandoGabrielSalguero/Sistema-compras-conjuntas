/**
 * SVE Offline Diagnostics
 * Herramienta de diagnóstico para el sistema offline
 * Ejecutar en la consola: await SVE_Diagnostics.run()
 */

const SVE_Diagnostics = {
    /**
     * Ejecuta todos los diagnósticos
     */
    async run() {
        console.log('🔍 SVE Offline Diagnostics\n');
        console.log('═'.repeat(60));

        await this.checkBrowser();
        await this.checkServiceWorker();
        await this.checkIndexedDB();
        await this.checkCache();
        await this.checkOfflineSync();
        await this.checkNetwork();

        console.log('═'.repeat(60));
        console.log('✅ Diagnóstico completado\n');
    },

    /**
     * Verifica compatibilidad del navegador
     */
    async checkBrowser() {
        console.log('\n📱 NAVEGADOR');
        console.log('─'.repeat(60));
        console.log(`User Agent: ${navigator.userAgent}`);
        console.log(`Online: ${navigator.onLine ? '✅' : '❌'}`);
        console.log(`Service Worker: ${('serviceWorker' in navigator) ? '✅' : '❌'}`);
        console.log(`IndexedDB: ${window.indexedDB ? '✅' : '❌'}`);
        console.log(`Cache API: ${window.caches ? '✅' : '❌'}`);
        console.log(`Background Sync: ${('serviceWorker' in navigator && 'sync' in ServiceWorkerRegistration.prototype) ? '✅' : '❌'}`);
    },

    /**
     * Verifica estado del Service Worker
     */
    async checkServiceWorker() {
        console.log('\n⚙️ SERVICE WORKER');
        console.log('─'.repeat(60));

        if (!('serviceWorker' in navigator)) {
            console.log('❌ Service Worker no soportado');
            return;
        }

        try {
            const registration = await navigator.serviceWorker.getRegistration();
            if (registration) {
                console.log(`✅ Registrado en: ${registration.scope}`);
                console.log(`Estado: ${registration.active ? 'Activo' : 'Inactivo'}`);
                console.log(`Instalando: ${registration.installing ? 'Sí' : 'No'}`);
                console.log(`Esperando: ${registration.waiting ? 'Sí' : 'No'}`);
            } else {
                console.log('❌ Service Worker no registrado');
            }
        } catch (error) {
            console.error('Error al verificar Service Worker:', error);
        }
    },

    /**
     * Verifica estado de IndexedDB
     */
    async checkIndexedDB() {
        console.log('\n💾 INDEXEDDB');
        console.log('─'.repeat(60));

        if (!window.indexedDB) {
            console.log('❌ IndexedDB no disponible');
            return;
        }

        try {
            const databases = await indexedDB.databases();
            console.log(`Bases de datos encontradas: ${databases.length}`);

            const sveDb = databases.find(db => db.name === 'SVE_DroneSync');
            if (sveDb) {
                console.log(`✅ SVE_DroneSync encontrada (v${sveDb.version})`);

                // Abrir y verificar stores
                const db = await new Promise((resolve, reject) => {
                    const request = indexedDB.open('SVE_DroneSync');
                    request.onsuccess = () => resolve(request.result);
                    request.onerror = () => reject(request.error);
                });

                console.log('Stores:');
                Array.from(db.objectStoreNames).forEach(name => {
                    console.log(`  - ${name}`);
                });

                // Contar registros
                const tx = db.transaction(['reports', 'photos'], 'readonly');
                const reportStore = tx.objectStore('reports');
                const photoStore = tx.objectStore('photos');

                const reportCount = await new Promise((resolve) => {
                    const req = reportStore.count();
                    req.onsuccess = () => resolve(req.result);
                    req.onerror = () => resolve(0);
                });

                const photoCount = await new Promise((resolve) => {
                    const req = photoStore.count();
                    req.onsuccess = () => resolve(req.result);
                    req.onerror = () => resolve(0);
                });

                console.log(`Reportes almacenados: ${reportCount}`);
                console.log(`Fotos almacenadas: ${photoCount}`);

                db.close();
            } else {
                console.log('⚠️ SVE_DroneSync no encontrada (se creará al usar el sistema)');
            }
        } catch (error) {
            console.error('Error al verificar IndexedDB:', error);
        }
    },

    /**
     * Verifica cache del Service Worker
     */
    async checkCache() {
        console.log('\n📦 CACHE STORAGE');
        console.log('─'.repeat(60));

        if (!window.caches) {
            console.log('❌ Cache API no disponible');
            return;
        }

        try {
            const cacheNames = await caches.keys();
            console.log(`Caches encontrados: ${cacheNames.length}`);

            for (const name of cacheNames) {
                const cache = await caches.open(name);
                const keys = await cache.keys();
                console.log(`  ${name}: ${keys.length} recursos`);
            }
        } catch (error) {
            console.error('Error al verificar cache:', error);
        }
    },

    /**
     * Verifica estado del módulo OfflineSync
     */
    async checkOfflineSync() {
        console.log('\n🔄 OFFLINE SYNC');
        console.log('─'.repeat(60));

        if (!window.offlineSync) {
            console.log('❌ OfflineSync no inicializado');
            return;
        }

        try {
            const stats = await window.offlineSync.getStats();
            console.log(`✅ OfflineSync activo`);
            console.log(`Reportes totales: ${stats.total}`);
            console.log(`Pendientes: ${stats.pending}`);
            console.log(`Sincronizados: ${stats.synced}`);
            console.log(`Estado conexión: ${stats.isOnline ? 'Online' : 'Offline'}`);
            console.log(`Sincronizando: ${stats.isSyncing ? 'Sí' : 'No'}`);
        } catch (error) {
            console.error('Error al obtener estadísticas:', error);
        }
    },

    /**
     * Verifica estado de la red
     */
    async checkNetwork() {
        console.log('\n🌐 RED');
        console.log('─'.repeat(60));
        console.log(`Navigator.onLine: ${navigator.onLine ? '✅ Online' : '❌ Offline'}`);

        // Test de conectividad real
        try {
            const start = performance.now();
            const response = await fetch('/ping.php', {
                method: 'HEAD',
                cache: 'no-cache'
            });
            const latency = Math.round(performance.now() - start);

            if (response.ok) {
                console.log(`✅ Servidor accesible (${latency}ms)`);
            } else {
                console.log(`⚠️ Servidor respondió con código ${response.status}`);
            }
        } catch (error) {
            console.log(`❌ No se pudo conectar al servidor: ${error.message}`);
        }
    },

    /**
     * Fuerza sincronización manual
     */
    async forceSync() {
        console.log('🔄 Forzando sincronización...');
        if (window.offlineSync) {
            await window.offlineSync.syncPendingData();
        } else {
            console.log('❌ OfflineSync no disponible');
        }
    },

    /**
     * Limpia todos los datos offline
     */
    async clearAll() {
        console.log('🗑️ Limpiando datos offline...');

        // Caches
        if (window.caches) {
            const cacheNames = await caches.keys();
            for (const name of cacheNames) {
                await caches.delete(name);
                console.log(`✅ Cache eliminado: ${name}`);
            }
        }

        // IndexedDB
        if (window.indexedDB) {
            await indexedDB.deleteDatabase('SVE_DroneSync');
            console.log('✅ IndexedDB eliminado');
        }

        // Service Worker
        if ('serviceWorker' in navigator) {
            const registrations = await navigator.serviceWorker.getRegistrations();
            for (const registration of registrations) {
                await registration.unregister();
                console.log('✅ Service Worker desregistrado');
            }
        }

        // LocalStorage
        localStorage.removeItem('sve_offline_initialized');
        localStorage.removeItem('sve_offline_init_date');
        console.log('✅ LocalStorage limpiado');

        console.log('\n✅ Limpieza completa. Recarga la página.');
    },

    /**
     * Simula guardar un reporte offline
     */
    async testSaveOffline() {
        console.log('🧪 Probando guardado offline...');

        if (!window.offlineSync) {
            console.log('❌ OfflineSync no disponible');
            return;
        }

        const testReport = {
            solicitud_id: 999,
            nom_cliente: 'Test Cliente',
            nom_piloto: 'Test Piloto',
            fecha_visita: '2026-02-07',
            hora_ingreso: '10:00',
            hora_egreso: '12:00'
        };

        try {
            const reportId = await window.offlineSync.saveReportOffline(testReport, [], '', '');
            console.log(`✅ Reporte de prueba guardado con ID: ${reportId}`);

            const stats = await window.offlineSync.getStats();
            console.log(`Pendientes ahora: ${stats.pending}`);
        } catch (error) {
            console.error('❌ Error al guardar:', error);
        }
    }
};

// Hacer accesible globalmente
window.SVE_Diagnostics = SVE_Diagnostics;

console.log('%c💡 SVE Offline Diagnostics cargado', 'color: #0ea5e9; font-weight: bold');
console.log('Ejecuta: await SVE_Diagnostics.run()');
console.log('Comandos disponibles:');
console.log('  - SVE_Diagnostics.run()          : Ejecutar diagnóstico completo');
console.log('  - SVE_Diagnostics.forceSync()    : Forzar sincronización');
console.log('  - SVE_Diagnostics.clearAll()     : Limpiar todos los datos offline');
console.log('  - SVE_Diagnostics.testSaveOffline() : Probar guardado offline');
