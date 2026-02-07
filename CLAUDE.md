# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

SVE (Sistema de Compras Conjuntas) - A PHP-based collective purchasing and agricultural management system for cooperatives, producers, engineers, and service providers in the wine/agricultural sector.

## Technology Stack

- **Backend**: PHP 8+ with PDO for database access
- **Database**: MySQL 5.7/8
- **Frontend**: Vanilla JavaScript with custom Impulsa framework
- **Architecture**: Classic MVC pattern
- **Session Management**: Custom sliding session with 1-hour inactivity timeout
- **Email**: PHPMailer with Hostinger SMTP
- **Offline Support**: Service Worker with progressive web app capabilities

## Database Configuration

Database credentials are loaded from `.env` file in the root directory:
- `DB_HOST` - Database host
- `DB_NAME` - Database name
- `DB_USER` - Database username
- `DB_PASS` - Database password

Connection is established in `config.php` which creates a `$pdo` object used throughout the application.

## Architecture & Code Organization

### MVC Pattern
The application follows a strict MVC structure:

```
controllers/   - Business logic and request handling
models/        - Database queries and data operations
views/         - HTML templates organized by role
  ├── cooperativa/
  ├── productor/
  ├── ingeniero/
  ├── sve/
  ├── drone_pilot/
  ├── tractor_pilot/
  └── partials/   - Shared components
```

### Naming Conventions
- Controllers: `<role>_<feature>Controller.php` (e.g., `sve_productosController.php`)
- Models: `<role>_<feature>Model.php` (e.g., `sve_productosModel.php`)
- Views: `<role>_<feature>.php` (e.g., `sve_productos.php`)

### Entry Points
- `index.php` - Login page and authentication entry point
- `logout.php` - Session destruction
- `ping.php` - Session keepalive endpoint (heartbeat)

## User Roles & Access Control

The system has 6 distinct user roles with dedicated dashboards:

1. **sve** - System administrators (full access)
2. **cooperativa** - Cooperative organizations
3. **productor** - Agricultural producers
4. **ingeniero** - Engineers assigned to cooperatives
5. **piloto_drone** - Drone service pilots
6. **piloto_tractor** - Tractor service pilots

### Role-Based Visibility
Authorization is handled by `lib/Authz_vista.php` which provides SQL predicates for filtering data:
- **sve**: Sees all data
- **cooperativa**: Sees only producers associated with their cooperative
- **ingeniero**: Sees producers from cooperatives they're assigned to
- Other roles have specific access patterns

Use `AuthzVista::sqlVisibleProductores($columnName, $context, &$params)` to enforce visibility rules in queries.

## Session Management

Sessions use a sliding expiration pattern managed by `middleware/sessionManager.php`:

- **Timeout**: 1 hour of inactivity (`SESSION_INACTIVITY` constant)
- **Sliding Window**: Each request refreshes the session cookie
- **Security**: HttpOnly, SameSite=Lax, strict mode enabled
- **HTTPS Detection**: Supports proxy/CDN via `HTTP_X_FORWARDED_PROTO`

### Using Sessions in Views
All protected views must include:
```php
require_once '../../middleware/authMiddleware.php';
checkAccess('role_name'); // e.g., 'sve', 'cooperativa', 'productor'
```

This automatically:
- Enforces session timeout
- Validates user role
- Refreshes session cookie (sliding expiration)
- Redirects to login if unauthorized

## Key Domain Modules

### 1. Operativos (Joint Purchase Operations)
- Tables: `operativos`, `operativos_productos`, `operativos_cooperativas_participacion`
- Manages collective purchasing campaigns with open/closed states
- Associates products and cooperatives to campaigns
- Auto-closes expired operations via `views/partials/cierre_operativos.php`

### 2. Cosecha Mecánica (Mechanical Harvesting)
- Table: `CosechaMecanica` with estados: borrador, abierto, cerrado
- Contract-based system with cooperative participation and producer enrollment
- Quality bonuses (óptima, muy buena, buena) and base costs
- Includes field assessments and risk analysis

### 3. Drones (Aerial Application Service)
- Complex multi-table system: `drones_solicitud`, `drones_solicitud_item`, etc.
- Workflow states: ingresada → procesando → aprobada_coop → visita_realizada → completada/cancelada
- Includes pathology/product management, cost calculation, and service reports
- Calendar integration for pilot scheduling
- Product stock management with active ingredients and application parameters

### 4. Fincas & Cuarteles (Farms & Vineyard Blocks)
- Hierarchical: `prod_fincas` → `prod_cuartel` (blocks)
- Extensive metadata: water rights, soil analysis, machinery, yield history
- Related tables for limitations, risks, and management issues

### 5. Productores (Producers)
- Core table: `usuarios` with `usuarios_info`
- Additional tables: `info_productor`, `prod_colaboradores`, `prod_hijos`
- Many-to-many relationships via `rel_productor_coop` and `rel_productor_finca`
- Categorical ranking system (A/B/C)

## Database Schema Notes

- **id_real**: String-based business identifier (separate from auto-increment `id`)
- **Timestamps**: Most tables have `created_at` and optionally `updated_at`
- **Enums**: Heavily used for state management and controlled vocabularies
- **Audit Trail**: `login_auditoria` logs all authentication attempts
- **System Logs**: `system_audit_log` for comprehensive activity tracking

## Frontend Framework

The application uses a custom framework hosted at `https://framework.impulsagroup.com`:
- CSS: `framework.css`
- JS: `framework.js`
- Material Icons for UI elements

Views often include inline `<style>` and `<script>` sections for page-specific logic.

## Email Configuration

PHPMailer is configured in `config.php` with constants:
- `MAIL_HOST`: smtp.hostinger.com
- `MAIL_PORT`: 465 (SSL)
- `MAIL_USER` / `MAIL_PASS` / `MAIL_FROM`

All system emails are sent via PHPMailer using SMTP through Hostinger.

## CSV Import System

The README.md documents an extensive CSV import mapping for producer data, showing how CSV columns map to database tables. Key destinations:
- `usuarios` / `usuarios_info` - Basic user data
- `info_productor` - Producer-specific information
- `prod_colaboradores` / `prod_hijos` - Family/worker data
- `prod_cuartel` / `prod_cuartel_rendimientos` - Vineyard block and yield data
- `prod_cuartel_limitantes` / `prod_cuartel_riesgos` - Limitations and risks

## Important Implementation Patterns

### 1. PDO Parameter Binding
Always use prepared statements with named parameters:
```php
$stmt = $pdo->prepare("SELECT * FROM table WHERE column = :param");
$stmt->execute(['param' => $value]);
```

### 2. Error Display
Development mode uses:
```php
ini_set('display_errors', '1');
error_reporting(E_ALL);
```
This should be disabled in production.

### 3. Type Safety
Modern code uses strict types:
```php
declare(strict_types=1);
```

### 4. JSON Responses for AJAX
Controllers often return JSON for asynchronous operations:
```php
header('Content-Type: application/json');
echo json_encode(['status' => 'success', 'data' => $result]);
```

## Code Style Guidelines

- Use strict type declarations for new PHP files
- Follow existing naming conventions (role prefixes)
- Keep models focused on data operations only
- Controllers handle request/response and call models
- Views should be minimal - avoid complex PHP logic in templates
- Sanitize all user input and use parameterized queries
- Maintain consistent indentation (tabs/spaces as per file)

## Common Gotchas

1. **Session Paths**: Session management is centralized - don't modify session settings outside `sessionManager.php`
2. **Role Checks**: Always use `checkAccess()` - don't manually check `$_SESSION['rol']`
3. **Database References**: The schema uses both `id` (auto-increment) and `id_real` (business key) - understand which to use
4. **Timezone**: Set to `America/Argentina/Buenos_Aires` in `config.php`
5. **Password Hashing**: Uses `password_verify()` - passwords are hashed with PHP's password functions

## Testing & Debugging

- Login audit trail: Check `login_auditoria` table for authentication issues
- System logs: `system_audit_log` tracks requests, errors, and exceptions
- Session debugging: Monitor `$_SESSION['LAST_ACTIVITY']` to troubleshoot timeout issues
- Database schema reference: `assets/estructura_bbdd.md` documents all tables and relationships

## Cron Jobs

The `cron/` directory contains scheduled tasks:
- `cerrar_cosecha_mecanica.php` - Closes expired mechanical harvesting contracts

---

## Sistema Offline (Piloto Drone)

### Descripción General

El rol `piloto_drone` cuenta con funcionalidad offline-first que permite trabajar completamente sin conexión a internet después del primer login. El sistema guarda reportes, fotos y firmas localmente y sincroniza automáticamente cuando vuelve la conexión.

### Arquitectura del Sistema Offline

#### Archivos Principales

1. **service-worker.js** (v4.2)
   - Service Worker con estrategia offline-first
   - Pre-cachea recursos estáticos locales
   - Ignora recursos externos problemáticos (framework.impulsagroup.com) para evitar errores CORS
   - Background Sync para sincronización automática
   - Cache runtime para recursos dinámicos

2. **offline-sync.js** (v4.1)
   - Módulo principal de sincronización offline
   - Gestión de IndexedDB para almacenamiento local
   - Cola de sincronización con reintentos automáticos
   - Sistema de autenticación offline con tokens
   - API pública para guardar/sincronizar datos

3. **offline-init.js** (v1.1)
   - Inicialización automática del sistema offline
   - Pre-cacheo de recursos críticos al primer login
   - Verificación de compatibilidad del navegador
   - Registro del Service Worker

4. **offline-diagnostics.js** (v1.0)
   - Herramientas de diagnóstico y debugging
   - Solo se carga con parámetro `?debug=1`
   - Comandos para inspeccionar estado del sistema

5. **Herramientas de Mantenimiento**
   - `reset-offline.html` - Reset completo del sistema offline
   - `force-update-sw.html` - Fuerza actualización del Service Worker
   - Limpian caches, IndexedDB, Service Workers y LocalStorage

### IndexedDB Schema

**Base de datos**: `SVE_DroneSync` (versión 1)

**Object Stores**:

1. **reports** (Reportes pendientes)
   ```javascript
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
   ```
   - Índices: `solicitud_id`, `timestamp`, `synced`

2. **photos** (Fotos pendientes)
   ```javascript
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
   - Índices: `report_id`, `synced`

3. **sync_meta** (Metadata de sincronización)
   - Key-value store para metadata del sistema

### Autenticación Offline

#### Tokens Offline

El sistema usa tokens temporales almacenados en LocalStorage:

**LocalStorage Keys**:
- `sve_offline_token` - Token encriptado (base64) con datos de sesión
- `sve_offline_session` - Datos completos del usuario
- `sve_offline_initialized` - Flag de inicialización
- `sve_offline_init_date` - Fecha de primera inicialización

**Estructura del Token**:
```javascript
{
  id: number,
  usuario: string,
  rol: 'piloto_drone',
  nombre: string,
  correo: string,
  timestamp: number,
  expiresAt: number // 30 días desde creación
}
```

#### Flujo de Autenticación

1. **Primer Login (Online)**:
   - Usuario ingresa credenciales
   - Sistema verifica contra BD
   - Si rol es `piloto_drone`: genera token offline
   - Guarda token y datos en LocalStorage
   - Token válido por 30 días

2. **Login Offline (Sin Conexión)**:
   - Usuario abre la aplicación sin internet
   - Sistema detecta token válido en LocalStorage
   - Auto-login automático sin pedir credenciales
   - Redirige a `drone_pilot_dashboard.php?offline=1`

3. **Renovación de Token**:
   - Al detectar conexión, token se renueva automáticamente
   - Extiende validez por 30 días más
   - Mantiene sesión offline activa

### Flujo de Trabajo Offline

#### Escenario 1: Guardar Reporte Offline

```
Usuario sin conexión
  ↓
Abre modal de reporte
  ↓
Completa formulario, firmas, fotos
  ↓
Click "Guardar reporte"
  ↓
Sistema detecta offline (navigator.onLine === false)
  ↓
Guarda en IndexedDB:
  - Datos del formulario → reports
  - Fotos (convertidas a base64) → photos
  - Firmas (base64) → reports
  ↓
Muestra notificación: "Guardado offline"
  ↓
Badge rojo muestra cantidad pendiente
```

#### Escenario 2: Sincronización Automática

```
Conexión restaurada
  ↓
Evento 'online' detectado
  ↓
offlineSync.syncPendingData() se ejecuta
  ↓
Lee reportes pendientes de IndexedDB
  ↓
Para cada reporte:
  - Convierte datos a FormData
  - Convierte fotos base64 a Blob
  - Envía POST al servidor
  - Si éxito: marca como synced = true
  ↓
Actualiza UI:
  - Badge desaparece
  - Notificación: "X reportes sincronizados"
  ↓
Limpieza opcional de datos sincronizados
```

### API Pública de offline-sync.js

#### Métodos Principales

```javascript
// Guardar reporte offline
await window.offlineSync.saveReportOffline(reportData, photos, firmaCliente, firmaPiloto);

// Sincronizar manualmente
await window.offlineSync.syncPendingData();

// Obtener cantidad pendiente
const count = await window.offlineSync.getPendingCount();

// Verificar sesión offline válida
const isValid = window.offlineSync.hasValidOfflineSession();

// Renovar token
window.offlineSync.renewOfflineToken();

// Limpiar sesión offline
window.offlineSync.clearOfflineSession();

// Estadísticas
const stats = await window.offlineSync.getStats();
// Retorna: { pending, synced, total, isOnline, isSyncing }
```

#### Eventos Personalizados

```javascript
// Escuchar eventos
window.offlineSync.on('sync-start', () => {});
window.offlineSync.on('sync-complete', (detail) => {});
window.offlineSync.on('sync-error', (detail) => {});
window.offlineSync.on('report-saved', (detail) => {});
window.offlineSync.on('connection-restored', () => {});
window.offlineSync.on('connection-lost', () => {});
```

### Indicadores Visuales

#### Indicador de Estado (Esquina inferior derecha)

- 🟢 **Punto verde + "En línea"**: Conexión activa, sin pendientes
- 🔴 **Punto rojo + "Sin conexión"**: Modo offline
- 🟠 **Punto naranja + "Sincronizando..."**: Sincronización en progreso
- **Badge rojo con número**: Cantidad de reportes pendientes

### Troubleshooting

#### Problema: Errores CORS de framework.impulsagroup.com

**Causa**: Service Worker viejo intenta cachear recursos externos

**Solución**:
```javascript
// Ejecutar en consola
(async function() {
    const regs = await navigator.serviceWorker.getRegistrations();
    for (const r of regs) await r.unregister();
    const keys = await caches.keys();
    for (const k of keys) await caches.delete(k);
    location.reload();
})();
```

**Verificar versión correcta**:
```javascript
caches.keys().then(k => console.log(k));
// Debe mostrar: ["sve-precache-v4.2", "sve-runtime-v4.2"]
```

#### Problema: No sincroniza automáticamente

**Diagnóstico**:
```javascript
// Ver estado
await window.offlineSync.getStats();

// Forzar sincronización manual
await window.offlineSync.syncPendingData();
```

#### Problema: Token expirado

**Síntoma**: No puede entrar offline después de 30 días

**Solución**:
1. Conectarse a internet
2. Iniciar sesión normalmente
3. Token se regenera automáticamente

#### Problema: Datos no se guardan offline

**Verificar**:
```javascript
// IndexedDB disponible
console.log(!!window.indexedDB);

// Ver reportes guardados
const reports = await window.offlineSync.getPendingReports();
console.log(reports);
```

### Scripts de Diagnóstico

#### Con parámetro ?debug=1

```
https://compraconjunta.sve.com.ar/views/drone_pilot/drone_pilot_dashboard.php?debug=1
```

Comandos disponibles en consola:
```javascript
// Diagnóstico completo
await SVE_Diagnostics.run();

// Forzar sincronización
await SVE_Diagnostics.forceSync();

// Probar guardado offline
await SVE_Diagnostics.testSaveOffline();

// Limpiar todo
await SVE_Diagnostics.clearAll();
```

### Limitaciones Conocidas

1. **Solo piloto_drone**: Otros roles no tienen acceso offline
2. **Expiración 30 días**: Requiere reconexión al menos mensual
3. **Tamaño de fotos**: Recomendado máximo 50MB total de datos offline
4. **Navegadores antiguos**: IE11 no soporta Service Workers
5. **Modo incógnito**: LocalStorage no persiste entre sesiones
6. **Un usuario por navegador**: Token se sobrescribe si inicia otro usuario

### Versioning

Al modificar archivos offline, actualizar versiones:

**service-worker.js**:
```javascript
const CACHE_VERSION = 'v4.2'; // Incrementar cuando cambie
```

**Scripts en HTML**:
```html
<script src="offline-sync.js?v=4.1"></script>
<script src="offline-init.js?v=1.1"></script>
```

**Service Worker registration**:
```javascript
navigator.serviceWorker.register('/service-worker.js?v=4.2');
```

### Mantenimiento

#### Reset Completo

Usuario puede usar: `https://compraconjunta.sve.com.ar/reset-offline.html`

Elimina:
- Todos los caches
- Service Workers
- IndexedDB
- LocalStorage offline
- SessionStorage

#### Actualización Forzada

Usuario puede usar: `https://compraconjunta.sve.com.ar/force-update-sw.html`

Fuerza actualización del Service Worker sin borrar datos de usuario.

### Seguridad

- ✅ Tokens no contienen contraseñas (solo datos de sesión)
- ✅ Expiración automática en 30 días
- ✅ Renovación automática al reconectar
- ✅ Logout simple no borra datos offline (evita errores de usuario)
- ⚠️ Token es base64 (ofuscado), no encriptación real
- ⚠️ No usar en dispositivos compartidos sin cerrar sesión

### Referencias Adicionales

- **OFFLINE-SYSTEM.md**: Documentación detallada del sistema offline
- **OFFLINE-AUTH.md**: Documentación del sistema de autenticación offline
- Ver también archivos de documentación en el root del proyecto
