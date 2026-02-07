# Sistema Offline para Piloto de Drones

## Descripción General

El sistema SVE ahora cuenta con funcionalidad **offline-first** para el rol `piloto_drone`. Esto permite que los pilotos puedan:

- ✅ Trabajar completamente sin conexión a internet
- ✅ Cargar formularios, firmas e imágenes offline
- ✅ Sincronización automática cuando vuelva la conexión
- ✅ Indicador visual del estado de conexión
- ✅ Cola de sincronización con reintentos automáticos

## Arquitectura del Sistema

### 1. **Service Worker** (`service-worker.js`)
- **Versión**: v4.0
- **Estrategia**: Offline-first con cache dinámico
- **Precaching**: Recursos estáticos (HTML, CSS, JS, fuentes, imágenes)
- **Background Sync**: Sincronización en segundo plano cuando vuelve la conexión

### 2. **Módulo de Sincronización** (`offline-sync.js`)
- **IndexedDB**: Base de datos local para almacenar reportes y fotos
- **Stores**:
  - `reports`: Reportes pendientes de sincronizar
  - `photos`: Fotos asociadas a reportes
  - `sync_meta`: Metadata de sincronización

### 3. **Dashboard del Piloto** (`drone_pilot_dashboard.php`)
- Integración con `offline-sync.js`
- Detección automática de estado online/offline
- Guardado local cuando no hay conexión
- Envío normal al servidor cuando hay conexión

## Flujo de Trabajo

### Escenario 1: Con Conexión (Normal)

1. Piloto inicia sesión
2. Service Worker cachea todos los recursos necesarios
3. Piloto carga formulario de reporte
4. Datos se envían directamente al servidor
5. Confirmación inmediata

### Escenario 2: Sin Conexión (Offline)

1. Piloto pierde conexión a internet
2. **Indicador visual** muestra "Sin conexión" (punto rojo)
3. Piloto carga formulario normalmente
4. Al guardar, datos se almacenan en **IndexedDB local**:
   - Formulario completo
   - Firmas digitales (base64)
   - Fotos (convertidas a base64)
5. **Badge rojo** muestra cantidad de reportes pendientes
6. Notificación: "Reporte guardado offline. Se sincronizará automáticamente."

### Escenario 3: Reconexión (Sincronización Automática)

1. Conexión a internet se restaura
2. **Indicador visual** cambia a "Sincronizando..." (punto naranja)
3. Sistema sincroniza automáticamente:
   - Lee reportes pendientes de IndexedDB
   - Convierte datos a FormData
   - Envía al servidor uno por uno
   - Marca como sincronizado si es exitoso
4. **Indicador visual** cambia a "En línea" (punto verde)
5. Badge desaparece cuando todo está sincronizado
6. Notificación: "X reporte(s) sincronizado(s) correctamente"

## Características Técnicas

### IndexedDB Schema

```javascript
// Store: reports
{
  id: auto-increment,
  solicitud_id: number,
  nom_cliente: string,
  nom_piloto: string,
  fecha_visita: date,
  // ... todos los campos del formulario
  firma_cliente_base64: string,
  firma_piloto_base64: string,
  photosCount: number,
  timestamp: timestamp,
  synced: boolean,
  syncedAt: timestamp (opcional)
}

// Store: photos
{
  id: auto-increment,
  report_id: number (FK a reports.id),
  filename: string,
  size: number,
  type: string,
  data: base64,
  synced: boolean,
  timestamp: timestamp
}
```

### API Pública de `offline-sync.js`

```javascript
// Guardar reporte offline
await window.offlineSync.saveReportOffline(reportData, photos, firmaCliente, firmaPiloto);

// Sincronizar manualmente
await window.offlineSync.syncPendingData();

// Obtener cantidad de reportes pendientes
const count = await window.offlineSync.getPendingCount();

// Obtener estadísticas
const stats = await window.offlineSync.getStats();
// Retorna: { pending, synced, total, isOnline, isSyncing }

// Limpiar datos ya sincronizados
await window.offlineSync.clearSyncedData();

// Escuchar eventos
window.offlineSync.on('sync-complete', (detail) => {
  console.log(`${detail.success} reportes sincronizados`);
});
```

### Eventos Disponibles

- `offlineSync:connection-restored`: Conexión restaurada
- `offlineSync:connection-lost`: Conexión perdida
- `offlineSync:report-saved`: Reporte guardado offline
- `offlineSync:sync-start`: Sincronización iniciada
- `offlineSync:sync-complete`: Sincronización completada
- `offlineSync:sync-error`: Error en sincronización

## Indicador Visual de Estado

El sistema incluye un indicador flotante en la esquina inferior derecha que muestra:

- **Punto verde + "En línea"**: Conexión activa, sin datos pendientes
- **Punto rojo + "Sin conexión"**: Sin conexión a internet
- **Punto naranja + "Sincronizando..."**: Sincronización en progreso
- **Badge rojo con número**: Cantidad de reportes pendientes de sincronizar

## Mantenimiento y Limpieza

### Limpiar datos sincronizados

El sistema **NO** elimina automáticamente los datos sincronizados para evitar pérdida de información. Para limpiar manualmente:

```javascript
await window.offlineSync.clearSyncedData();
```

### Restablecer todo el sistema offline

Desde el menú lateral de `index.php`, usar la opción:
- **"Restablecer versión offline"**: Elimina caches, storage, IndexedDB y desregistra el service worker

## Requisitos del Navegador

- ✅ **Service Worker**: Chrome 40+, Firefox 44+, Safari 11.1+
- ✅ **IndexedDB**: Todos los navegadores modernos
- ✅ **Background Sync**: Chrome 49+, Edge 79+ (opcional, mejora la experiencia)

## Consideraciones de Seguridad

1. **Datos sensibles**: Las firmas y fotos se almacenan en IndexedDB (local al navegador)
2. **Borrado seguro**: Al cerrar sesión o limpiar datos del navegador, todo se elimina
3. **Validación**: El servidor sigue validando todos los datos recibidos
4. **Credenciales**: No se almacenan credenciales en IndexedDB

## Pruebas

### Simular modo offline en Chrome DevTools

1. Abrir DevTools (F12)
2. Ir a pestaña **Network**
3. Seleccionar **Offline** en el dropdown de throttling
4. Verificar que el indicador muestre "Sin conexión"
5. Cargar un reporte de prueba
6. Verificar que aparezca el badge de pendientes
7. Cambiar a **Online**
8. Verificar sincronización automática

### Verificar IndexedDB

1. DevTools → **Application** → **Storage** → **IndexedDB**
2. Expandir **SVE_DroneSync**
3. Revisar stores: `reports`, `photos`, `sync_meta`

### Verificar Service Worker

1. DevTools → **Application** → **Service Workers**
2. Verificar que esté registrado y **activo**
3. Ver cache storage en **Cache Storage**

## Solución de Problemas

### El indicador no aparece
- Verificar que `offline-sync.js` esté cargado
- Revisar consola del navegador por errores

### No sincroniza automáticamente
- Verificar conexión a internet
- Abrir DevTools → Application → Service Workers
- Click en **Update** para forzar actualización
- Ejecutar manualmente: `window.offlineSync.syncPendingData()`

### Error "Base de datos no inicializada"
- Recargar la página
- Verificar que IndexedDB esté habilitado en el navegador
- Limpiar datos del sitio y volver a intentar

### Reportes duplicados
- El sistema detecta si un reporte ya fue enviado
- Si persiste, ejecutar: `window.offlineSync.clearSyncedData()`

## Limitaciones Conocidas

1. **Tamaño de fotos**: IndexedDB puede manejar cientos de MB, pero se recomienda no exceder 50MB por sesión
2. **Navegadores antiguos**: IE11 no soporta Service Workers
3. **Sincronización en background**: Solo funciona en Chrome/Edge con Background Sync API

## Roadmap Futuro

- [ ] Compresión de imágenes antes de guardar
- [ ] Sincronización incremental (delta sync)
- [ ] Notificaciones push cuando se complete la sincronización
- [ ] Modo "solo lectura" offline para consultar datos anteriores
- [ ] Exportación de datos offline a JSON/CSV

---

**Última actualización**: 2026-02-07
**Versión del sistema**: 4.0
**Mantenedor**: Impulsa Desarrollos
