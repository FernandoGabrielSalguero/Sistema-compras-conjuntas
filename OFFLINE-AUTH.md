# Sistema de Autenticación Offline

## Descripción

Sistema de autenticación offline para el rol `piloto_drone` que permite acceder a la aplicación sin conexión a internet después de haber iniciado sesión al menos una vez.

## ¿Cómo Funciona?

### Primer Login (Con Conexión)

1. El piloto ingresa usuario y contraseña en `index.php`
2. Sistema verifica credenciales contra la base de datos
3. Si es exitoso y el rol es `piloto_drone`:
   - Se crea sesión normal PHP
   - **Se genera token offline** (válido 30 días)
   - **Se guardan datos de sesión en localStorage**:
     - Token encriptado
     - Datos del usuario (nombre, correo, rol, etc.)
     - Timestamp de expiración
4. Piloto es redirigido a su dashboard

### Acceso Offline (Sin Conexión)

1. Piloto abre `https://compraconjunta.sve.com.ar` sin conexión
2. Sistema detecta:
   - ❌ No hay conexión a internet
   - ✅ Hay token offline válido almacenado
3. **Auto-login automático**:
   - Lee token y datos desde localStorage
   - Verifica que no haya expirado
   - Redirige a dashboard con `?offline=1`
4. Dashboard carga en modo offline:
   - Sesión PHP simulada (no verifica contra BD)
   - Datos cargados desde localStorage
   - Todas las funcionalidades offline disponibles

### Reconexión (Vuelve Internet)

1. Sistema detecta conexión restaurada
2. **Renueva automáticamente el token** (extiende 30 días más)
3. Sincroniza reportes pendientes
4. Usuario puede seguir trabajando normalmente

## Seguridad

### ✅ Medidas de Seguridad

1. **Solo rol piloto_drone**: Otros roles NO tienen acceso offline
2. **Token temporal**: Expira en 30 días automáticamente
3. **No se guardan contraseñas**: Solo token generado
4. **Renovación automática**: Token se renueva con cada conexión
5. **Logout limpia datos**: Opción de borrar sesión offline al cerrar sesión

### ⚠️ Consideraciones

- Los datos se almacenan en **localStorage del navegador**
- Si se limpia el navegador, se pierde la sesión offline
- El token es **ofuscado** (base64) pero no es encriptación real
- **Recomendación**: No usar en dispositivos compartidos sin cerrar sesión

## Estructura de Datos

### Token Offline (localStorage: `sve_offline_token`)

```json
{
  "id": 123,
  "usuario": "piloto_drone_01",
  "rol": "piloto_drone",
  "nombre": "Juan Pérez",
  "correo": "juan@ejemplo.com",
  "timestamp": 1707264000000,
  "expiresAt": 1709856000000
}
```

Codificado en base64 para almacenamiento.

### Datos de Sesión (localStorage: `sve_offline_session`)

```json
{
  "usuario": "piloto_drone_01",
  "rol": "piloto_drone",
  "nombre": "Juan Pérez",
  "correo": "juan@ejemplo.com",
  "telefono": "+54 261 1234567",
  "direccion": "Calle Falsa 123",
  "usuario_id": 123,
  "id_real": "DRONE001",
  "cuit": "20123456789",
  "savedAt": 1707264000000
}
```

## API JavaScript

### Métodos Disponibles

```javascript
// Guardar sesión offline (automático al hacer login)
window.offlineSync.saveOfflineSession(userData);

// Obtener token si existe y es válido
const token = window.offlineSync.getOfflineToken();

// Obtener datos de sesión offline
const session = window.offlineSync.getOfflineSession();

// Verificar si hay sesión válida
const isValid = window.offlineSync.hasValidOfflineSession();

// Renovar token (automático al reconectar)
window.offlineSync.renewOfflineToken();

// Limpiar sesión offline
window.offlineSync.clearOfflineSession();
```

## Flujo de Archivos

### index.php
- Detecta si está offline y hay token válido
- Auto-redirige a dashboard si hay sesión offline
- Muestra error si está offline sin sesión previa
- Guarda sesión offline al hacer login exitoso

### drone_pilot_dashboard.php
- Detecta parámetro `?offline=1`
- En modo offline: crea sesión simulada
- En modo online: guarda/renueva sesión offline
- Listener para renovar token al reconectar

### offline-sync.js
- Gestiona tokens y sesiones offline
- Métodos de guardado/lectura/validación
- Verificación de expiración (30 días)
- Limpieza de datos

## Cómo Probar

### Escenario 1: Primer Login

1. **Conectado a internet**
2. Ir a `https://compraconjunta.sve.com.ar`
3. Iniciar sesión como `piloto_drone`
4. Verificar en consola:
   ```
   [Dashboard] Sesión offline guardada correctamente
   ```
5. Abrir DevTools → Application → Local Storage
6. Verificar claves:
   - `sve_offline_token`
   - `sve_offline_session`

### Escenario 2: Acceso Offline

1. **Con sesión ya guardada** (paso anterior)
2. Activar modo offline:
   - Chrome DevTools → Network → Offline
   - O desconectar WiFi
3. Cerrar pestaña y reabrir `https://compraconjunta.sve.com.ar`
4. Verificar:
   - ✅ Redirige automáticamente a dashboard
   - ✅ URL termina en `?offline=1`
   - ✅ Puede cargar formularios
   - ✅ Puede guardar reportes (se guardan local)

### Escenario 3: Reconexión

1. **Con dashboard abierto en modo offline**
2. Activar conexión nuevamente
3. Verificar en consola:
   ```
   [OfflineSync] Conexión restaurada, sincronizando...
   [OfflineSync] Token offline renovado
   ```
4. Reportes pendientes se sincronizan automáticamente

### Escenario 4: Token Expirado

1. **Modificar manualmente** el timestamp de expiración:
   ```javascript
   // En consola
   const token = localStorage.getItem('sve_offline_token');
   const decoded = JSON.parse(atob(token));
   decoded.expiresAt = Date.now() - 1000; // Ya expiró
   localStorage.setItem('sve_offline_token', btoa(JSON.stringify(decoded)));
   ```
2. Recargar página offline
3. Verificar:
   - ❌ No redirige al dashboard
   - ✅ Muestra mensaje: "Sin sesión offline válida"
   - ✅ Token expirado se elimina automáticamente

### Escenario 5: Logout

1. Con sesión offline guardada
2. Click en "Salir"
3. Aparece diálogo:
   ```
   ¿Deseas borrar también la sesión offline?

   • SÍ: No podrás trabajar sin conexión...
   • NO: Podrás seguir trabajando sin conexión.
   ```
4. Seleccionar "SÍ"
5. Verificar que se borraron las claves de localStorage

## Solución de Problemas

### No redirige automáticamente offline

**Causa**: No hay sesión guardada o token expiró

**Solución**:
1. Conectarse a internet
2. Iniciar sesión normalmente
3. Volver a probar offline

### Error "Sesión offline inválida"

**Causa**: Token corrupto o datos inconsistentes

**Solución**:
```javascript
// En consola
window.offlineSync.clearOfflineSession();
location.reload();
```

### Datos de sesión no coinciden

**Causa**: Sesión guardada es de otro usuario

**Solución**:
1. Cerrar sesión con "Borrar sesión offline"
2. Iniciar sesión con el usuario correcto

### Token no se renueva

**Causa**: Sistema no detecta reconexión

**Solución**:
```javascript
// Forzar renovación manual
window.offlineSync.renewOfflineToken();
```

## Limitaciones

1. **Solo un usuario por navegador**: Si se inicia sesión con otro usuario, se sobrescribe el token
2. **No funciona en modo incógnito**: localStorage no persiste
3. **Expira en 30 días**: Requiere reconexión al menos cada mes
4. **No sincroniza automáticamente** cambios del servidor (ej: datos de solicitudes actualizados)
5. **Navegadores sin localStorage**: No funciona en navegadores muy antiguos

## Próximas Mejoras

- [ ] Encriptación real del token (actualmente solo base64)
- [ ] Soporte multi-dispositivo con sincronización
- [ ] Configuración de días de expiración desde panel admin
- [ ] Notificación cuando el token está por expirar
- [ ] Opción de "recordarme en este dispositivo"
- [ ] Biometría para acceso offline (fingerprint/FaceID)

---

**Última actualización**: 2026-02-07
**Versión**: 1.0
**Requisitos**: Navegador con localStorage y Service Worker
