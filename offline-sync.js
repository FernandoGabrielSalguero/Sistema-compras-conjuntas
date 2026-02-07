/**
 * SVE Offline Sync Module
 * Maneja almacenamiento offline con IndexedDB y sincronización automática
 * para el rol piloto_drone
 */

const SYNC_DB_NAME = 'SVE_DroneSync';
const SYNC_DB_VERSION = 1;

class OfflineSync {
    constructor() {
        this.db = null;
        this.syncing = false;
        this.syncCallbacks = [];
        this.init();
    }

    /**
     * Inicializa IndexedDB y event listeners
     */
    async init() {
        try {
            this.db = await this.openDB();
            this.setupEventListeners();
            console.log('[OfflineSync] Inicializado correctamente');

            // Intenta sincronizar datos pendientes al iniciar
            if (navigator.onLine) {
                setTimeout(() => this.syncPendingData(), 2000);
            }
        } catch (error) {
            console.error('[OfflineSync] Error al inicializar:', error);
        }
    }

    /**
     * Abre o crea la base de datos IndexedDB
     */
    openDB() {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(SYNC_DB_NAME, SYNC_DB_VERSION);

            request.onerror = () => reject(request.error);
            request.onsuccess = () => resolve(request.result);

            request.onupgradeneeded = (event) => {
                const db = event.target.result;

                // Store para reportes pendientes
                if (!db.objectStoreNames.contains('reports')) {
                    const reportStore = db.createObjectStore('reports', {
                        keyPath: 'id',
                        autoIncrement: true
                    });
                    reportStore.createIndex('solicitud_id', 'solicitud_id', { unique: false });
                    reportStore.createIndex('timestamp', 'timestamp', { unique: false });
                    reportStore.createIndex('synced', 'synced', { unique: false });
                }

                // Store para fotos pendientes
                if (!db.objectStoreNames.contains('photos')) {
                    const photoStore = db.createObjectStore('photos', {
                        keyPath: 'id',
                        autoIncrement: true
                    });
                    photoStore.createIndex('report_id', 'report_id', { unique: false });
                    photoStore.createIndex('synced', 'synced', { unique: false });
                }

                // Store para metadata de sincronización
                if (!db.objectStoreNames.contains('sync_meta')) {
                    db.createObjectStore('sync_meta', { keyPath: 'key' });
                }

                console.log('[OfflineSync] Base de datos creada/actualizada');
            };
        });
    }

    /**
     * Configura listeners para detectar conexión y sincronizar
     */
    setupEventListeners() {
        // Listener para cuando vuelve la conexión
        window.addEventListener('online', async () => {
            console.log('[OfflineSync] Conexión restaurada, sincronizando...');
            this.dispatchEvent('connection-restored');
            await this.syncPendingData();
        });

        window.addEventListener('offline', () => {
            console.log('[OfflineSync] Sin conexión');
            this.dispatchEvent('connection-lost');
        });

        // Background Sync API (si está disponible)
        if ('serviceWorker' in navigator && 'sync' in navigator.serviceWorker) {
            navigator.serviceWorker.ready.then(registration => {
                registration.sync.register('sync-drone-reports').catch(err => {
                    console.warn('[OfflineSync] Background Sync no disponible:', err);
                });
            });
        }
    }

    /**
     * Guarda un reporte offline
     * @param {Object} reportData - Datos del reporte
     * @param {Array} photos - Array de archivos de fotos
     * @param {String} firmaCliente - Base64 de firma cliente
     * @param {String} firmaPiloto - Base64 de firma piloto
     */
    async saveReportOffline(reportData, photos = [], firmaCliente = '', firmaPiloto = '') {
        if (!this.db) {
            throw new Error('Base de datos no inicializada');
        }

        const transaction = this.db.transaction(['reports', 'photos'], 'readwrite');
        const reportStore = transaction.objectStore('reports');
        const photoStore = transaction.objectStore('photos');

        try {
            // Preparar datos del reporte
            const report = {
                ...reportData,
                timestamp: Date.now(),
                synced: false,
                firma_cliente_base64: firmaCliente,
                firma_piloto_base64: firmaPiloto,
                photosCount: photos.length
            };

            // Guardar reporte
            const reportRequest = reportStore.add(report);
            const reportId = await new Promise((resolve, reject) => {
                reportRequest.onsuccess = () => resolve(reportRequest.result);
                reportRequest.onerror = () => reject(reportRequest.error);
            });

            // Guardar fotos asociadas
            for (let i = 0; i < photos.length; i++) {
                const file = photos[i];
                const photoData = await this.fileToBase64(file);

                const photo = {
                    report_id: reportId,
                    filename: file.name,
                    size: file.size,
                    type: file.type,
                    data: photoData,
                    synced: false,
                    timestamp: Date.now()
                };

                await new Promise((resolve, reject) => {
                    const photoRequest = photoStore.add(photo);
                    photoRequest.onsuccess = () => resolve();
                    photoRequest.onerror = () => reject(photoRequest.error);
                });
            }

            console.log(`[OfflineSync] Reporte guardado offline (ID: ${reportId})`);
            this.dispatchEvent('report-saved', { reportId, solicitudId: reportData.solicitud_id });

            return reportId;
        } catch (error) {
            console.error('[OfflineSync] Error al guardar reporte offline:', error);
            throw error;
        }
    }

    /**
     * Convierte un archivo a Base64
     */
    fileToBase64(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            reader.onload = () => resolve(reader.result);
            reader.onerror = reject;
            reader.readAsDataURL(file);
        });
    }

    /**
     * Base64 a Blob
     */
    base64ToBlob(base64, type = 'image/jpeg') {
        const byteString = atob(base64.split(',')[1]);
        const ab = new ArrayBuffer(byteString.length);
        const ia = new Uint8Array(ab);
        for (let i = 0; i < byteString.length; i++) {
            ia[i] = byteString.charCodeAt(i);
        }
        return new Blob([ab], { type });
    }

    /**
     * Sincroniza todos los datos pendientes
     */
    async syncPendingData() {
        if (this.syncing) {
            console.log('[OfflineSync] Sincronización ya en curso');
            return;
        }

        if (!navigator.onLine) {
            console.log('[OfflineSync] Sin conexión, sincronización pospuesta');
            return;
        }

        this.syncing = true;
        this.dispatchEvent('sync-start');

        try {
            const pendingReports = await this.getPendingReports();
            console.log(`[OfflineSync] Sincronizando ${pendingReports.length} reporte(s) pendiente(s)`);

            let successCount = 0;
            let errorCount = 0;

            for (const report of pendingReports) {
                try {
                    await this.syncSingleReport(report);
                    successCount++;
                } catch (error) {
                    console.error(`[OfflineSync] Error al sincronizar reporte ${report.id}:`, error);
                    errorCount++;
                }
            }

            this.dispatchEvent('sync-complete', {
                success: successCount,
                errors: errorCount,
                total: pendingReports.length
            });

            console.log(`[OfflineSync] Sincronización completada: ${successCount} éxitos, ${errorCount} errores`);
        } catch (error) {
            console.error('[OfflineSync] Error en sincronización:', error);
            this.dispatchEvent('sync-error', { error });
        } finally {
            this.syncing = false;
        }
    }

    /**
     * Obtiene reportes pendientes de sincronizar
     */
    getPendingReports() {
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction(['reports'], 'readonly');
            const store = transaction.objectStore('reports');
            const index = store.index('synced');
            const request = index.getAll(false);

            request.onsuccess = () => resolve(request.result || []);
            request.onerror = () => reject(request.error);
        });
    }

    /**
     * Obtiene fotos asociadas a un reporte
     */
    getReportPhotos(reportId) {
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction(['photos'], 'readonly');
            const store = transaction.objectStore('photos');
            const index = store.index('report_id');
            const request = index.getAll(reportId);

            request.onsuccess = () => resolve(request.result || []);
            request.onerror = () => reject(request.error);
        });
    }

    /**
     * Sincroniza un reporte individual con el servidor
     */
    async syncSingleReport(report) {
        const photos = await this.getReportPhotos(report.id);

        // Preparar FormData para el envío
        const formData = new FormData();
        formData.append('action', 'crear_reporte');
        formData.append('solicitud_id', report.solicitud_id);

        // Agregar todos los campos del reporte
        const fields = [
            'nom_cliente', 'nom_piloto', 'nom_encargado', 'fecha_visita',
            'hora_ingreso', 'hora_egreso', 'nombre_finca', 'cultivo_pulverizado',
            'cuadro_cuartel', 'sup_pulverizada', 'vol_aplicado', 'vel_viento',
            'temperatura', 'humedad_relativa', 'lavado_dron_miner',
            'triple_lavado_envases', 'observaciones'
        ];

        fields.forEach(field => {
            if (report[field] !== undefined && report[field] !== null) {
                formData.append(field, report[field]);
            }
        });

        // Agregar firmas
        if (report.firma_cliente_base64) {
            formData.append('firma_cliente_base64', report.firma_cliente_base64);
        }
        if (report.firma_piloto_base64) {
            formData.append('firma_piloto_base64', report.firma_piloto_base64);
        }

        // Convertir fotos de base64 a archivos
        for (let i = 0; i < photos.length; i++) {
            const photo = photos[i];
            const blob = this.base64ToBlob(photo.data, photo.type);
            const file = new File([blob], photo.filename, { type: photo.type });
            formData.append('fotos[]', file);
        }

        // Enviar al servidor
        const response = await fetch('../../controllers/drone_pilot_dashboardController.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const result = await response.json();
        if (!result.ok) {
            throw new Error(result.message || 'Error del servidor');
        }

        // Marcar como sincronizado
        await this.markReportAsSynced(report.id);

        // Marcar fotos como sincronizadas
        for (const photo of photos) {
            await this.markPhotoAsSynced(photo.id);
        }

        console.log(`[OfflineSync] Reporte ${report.id} sincronizado correctamente`);
        return result;
    }

    /**
     * Marca un reporte como sincronizado
     */
    markReportAsSynced(reportId) {
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction(['reports'], 'readwrite');
            const store = transaction.objectStore('reports');
            const request = store.get(reportId);

            request.onsuccess = () => {
                const report = request.result;
                if (report) {
                    report.synced = true;
                    report.syncedAt = Date.now();
                    const updateRequest = store.put(report);
                    updateRequest.onsuccess = () => resolve();
                    updateRequest.onerror = () => reject(updateRequest.error);
                } else {
                    resolve();
                }
            };
            request.onerror = () => reject(request.error);
        });
    }

    /**
     * Marca una foto como sincronizada
     */
    markPhotoAsSynced(photoId) {
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction(['photos'], 'readwrite');
            const store = transaction.objectStore('photos');
            const request = store.get(photoId);

            request.onsuccess = () => {
                const photo = request.result;
                if (photo) {
                    photo.synced = true;
                    photo.syncedAt = Date.now();
                    const updateRequest = store.put(photo);
                    updateRequest.onsuccess = () => resolve();
                    updateRequest.onerror = () => reject(updateRequest.error);
                } else {
                    resolve();
                }
            };
            request.onerror = () => reject(request.error);
        });
    }

    /**
     * Obtiene el número de reportes pendientes
     */
    async getPendingCount() {
        const reports = await this.getPendingReports();
        return reports.length;
    }

    /**
     * Limpia datos ya sincronizados (para mantener la DB limpia)
     */
    async clearSyncedData() {
        const transaction = this.db.transaction(['reports', 'photos'], 'readwrite');
        const reportStore = transaction.objectStore('reports');
        const photoStore = transaction.objectStore('photos');

        // Eliminar reportes sincronizados
        const reportIndex = reportStore.index('synced');
        const reportRequest = reportIndex.openCursor(true);

        await new Promise((resolve) => {
            reportRequest.onsuccess = (event) => {
                const cursor = event.target.result;
                if (cursor) {
                    cursor.delete();
                    cursor.continue();
                } else {
                    resolve();
                }
            };
        });

        // Eliminar fotos sincronizadas
        const photoIndex = photoStore.index('synced');
        const photoRequest = photoIndex.openCursor(true);

        await new Promise((resolve) => {
            photoRequest.onsuccess = (event) => {
                const cursor = event.target.result;
                if (cursor) {
                    cursor.delete();
                    cursor.continue();
                } else {
                    resolve();
                }
            };
        });

        console.log('[OfflineSync] Datos sincronizados eliminados');
    }

    /**
     * Evento personalizado
     */
    dispatchEvent(eventName, detail = {}) {
        window.dispatchEvent(new CustomEvent(`offlineSync:${eventName}`, { detail }));
    }

    /**
     * Registra callback para eventos de sincronización
     */
    on(event, callback) {
        window.addEventListener(`offlineSync:${event}`, (e) => callback(e.detail));
    }

    /**
     * Verifica si hay conexión
     */
    isOnline() {
        return navigator.onLine;
    }

    /**
     * Obtiene estadísticas de sincronización
     */
    async getStats() {
        const pending = await this.getPendingReports();
        const totalReports = await this.getAllReports();

        return {
            pending: pending.length,
            synced: totalReports.length - pending.length,
            total: totalReports.length,
            isOnline: this.isOnline(),
            isSyncing: this.syncing
        };
    }

    /**
     * Obtiene todos los reportes
     */
    getAllReports() {
        return new Promise((resolve, reject) => {
            const transaction = this.db.transaction(['reports'], 'readonly');
            const store = transaction.objectStore('reports');
            const request = store.getAll();

            request.onsuccess = () => resolve(request.result || []);
            request.onerror = () => reject(request.error);
        });
    }
}

// Instancia global
window.offlineSync = new OfflineSync();

// Export para módulos
if (typeof module !== 'undefined' && module.exports) {
    module.exports = OfflineSync;
}
