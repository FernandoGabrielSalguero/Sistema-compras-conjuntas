# Sistema de Correos Electrónicos - SVE

Documentación completa del sistema de envío de correos electrónicos del proyecto SVE (Sistema de Compras Conjuntas).

---

## Índice

1. [Configuración General](#configuración-general)
2. [Arquitectura del Sistema](#arquitectura-del-sistema)
3. [Proveedor de Correo](#proveedor-de-correo)
4. [Tipos de Correos](#tipos-de-correos)
5. [Plantillas HTML](#plantillas-html)
6. [Flujos de Envío](#flujos-de-envío)
7. [Destinatarios y Reglas](#destinatarios-y-reglas)
8. [Registro y Auditoría](#registro-y-auditoría)

---

## Configuración General

### Variables de Entorno

Configuración en `config.php`:

```php
// SMTP (Hostinger) - Para PHPMailer
define('MAIL_HOST', 'smtp.hostinger.com');
define('MAIL_PORT', 465);
define('MAIL_SECURE', 'ssl');
define('MAIL_USER', 'contacto@sve.com.ar');
define('MAIL_PASS', 'W]17i|5HsTTk');
define('MAIL_FROM', 'contacto@sve.com.ar');
define('MAIL_FROM_NAME', 'SVE Notificaciones');
```

### Ubicación de Archivos

```
mail/
├── Mail.php                          # Clase principal PHPMailer
├── lib/
│   ├── PHPMailer.php                # Librería PHPMailer
│   ├── SMTP.php
│   ├── Exception.php
│   └── OAuthTokenProvider.php
└── template/
    ├── base.html                     # Plantilla base genérica
    ├── dron_solicitud.html          # Solicitud de drones
    ├── dron_actualizada.html        # Actualización de solicitud
    ├── pedido_creado.html           # Nuevo pedido creado
    └── solicitud_actualizada.html   # (no utilizada actualmente)
```

---

## Arquitectura del Sistema

### Clase Principal: `SVE\Mail\Maill`

**Archivo:** `mail/Mail.php`

Métodos públicos estáticos:

1. `enviarCierreCosechaMecanica(array $data): array`
2. `enviarPedidoCreado(array $data): array`
3. `enviarSolicitudDron(array $data): array`
4. `enviarSolicitudDronActualizada(array $data): array`

Todos retornan: `['ok' => bool, 'error' => ?string]`

---

## Proveedor de Correo

### PHPMailer (SMTP Hostinger)

**Configuración:**
- Host: `smtp.hostinger.com`
- Puerto: `465` (SSL)
- Usuario/From: `contacto@sve.com.ar`
- Autenticación: SMTP Auth habilitada

**Uso:** Todos los correos del sistema

**Características:**
- Soporte HTML completo
- Adjuntos (no usado actualmente)
- CC/BCC
- Codificación UTF-8 con base64

---

## Tipos de Correos

### 1. Cierre de Cosecha Mecánica

**Método:** `Maill::enviarCierreCosechaMecanica()`

**Cuándo se envía:**
- Automáticamente por **CRON** cuando un operativo de Cosecha Mecánica alcanza su fecha de cierre
- Manualmente desde el panel de cooperativa al firmar contrato
- Manualmente desde el panel de cooperativa al cerrar operativo

**Plantilla:** `mail/template/base.html`

**Destinatarios:**
- Cooperativa que firmó el contrato (1 correo por cooperativa participante)

**Datos incluidos:**
```php
[
  'cooperativa_nombre' => string,
  'cooperativa_correo' => string,
  'operativo' => [
    'id' => int,
    'nombre' => string,
    'fecha_apertura' => date,
    'fecha_cierre' => date,
    'descripcion' => string (HTML permitido),
    'estado' => 'cerrado'
  ],
  'participaciones' => [
    [
      'productor' => string,
      'finca_id' => int,
      'superficie' => float,
      'variedad' => string,
      'prod_estimada' => float,
      'fecha_estimada' => date,
      'km_finca' => float,
      'flete' => int,
      'seguro_flete' => 'si'|'no'|'sin_definir'
    ],
    ...
  ],
  'firma_fecha' => ?date
]
```

**Contenido:**
- Datos del contrato firmado
- Tabla con productores inscriptos y sus fincas
- Detalles de superficie, producción estimada, logística

**Asunto:** "SVE: Cierre de operativo de Cosecha Mecanica"

---

### 2. Pedido Creado (Mercado Digital)

**Método:** `Maill::enviarPedidoCreado()`

**Cuándo se envía:**
- Al crear un nuevo pedido en el Mercado Digital (compras conjuntas)

**Archivo invocador:** `controllers/coop_MercadoDigitalController.php`

**Plantilla:** `mail/template/pedido_creado.html`

**Destinatarios:**
- Cooperativa que creó el pedido (opcional, si tiene correo)
- **SIEMPRE:** `lacruzg@coopsve.com` (hardcoded)

**Datos incluidos:**
```php
[
  'cooperativa_nombre' => string,
  'cooperativa_correo' => ?string,
  'operativo_nombre' => string,
  'items' => [
    [
      'nombre' => string,
      'cantidad' => float,
      'unidad' => string,
      'precio' => float,
      'alicuota' => float (porcentaje IVA),
      'subtotal' => float,
      'total' => float
    ],
    ...
  ],
  'totales' => [
    'sin_iva' => float,
    'iva' => float,
    'con_iva' => float
  ]
]
```

**Contenido:**
- Tabla de productos con cantidades, precios e IVA
- Totales desglosados (sin IVA, IVA, total con IVA)

**Asunto:** "🟣 SVE: Nuevo pedido creado"

---

### 3. Solicitud de Dron (Nueva)

**Método:** `Maill::enviarSolicitudDron()`

**Cuándo se envía:**
- Al crear una nueva solicitud de servicio de pulverización con dron

**Archivo invocador:** `controllers/prod_dronesController.php`

**Plantilla:** `mail/template/dron_solicitud.html`

**Versiones del correo:**
El método genera **DOS versiones** del correo:

#### Versión Productor (simple)
- **Destinatario:** Productor solicitante
- **Contenido:** Datos básicos de la solicitud
- **Asunto:** "🟣 SVE: Solicitaste un nuevo servicio de pulverización con drones"

#### Versión Cooperativa/Drones (con acciones)
- **Destinatarios:**
  - **SIEMPRE:** `dronesvecoop@gmail.com` (hardcoded)
  - Cooperativa seleccionada (si el pago es por cooperativa y tiene correo)
- **Contenido adicional:** Si `pago_por_coop = true`:
  - Texto especial para cooperativa
  - Botones de acción: "Aprobar Solicitud" / "Declinar Solicitud"
  - URLs firmadas con tokens de seguridad (TTL configurable)
- **Asunto:** "🟣 SVE: Nueva solicitud de pulverización con dron"

**Datos incluidos:**
```php
[
  'solicitud_id' => int,
  'productor' => [
    'nombre' => string,
    'correo' => string
  ],
  'cooperativa' => [
    'nombre' => string,
    'correo' => string
  ],
  'superficie_ha' => float,
  'forma_pago' => string,
  'motivos' => [string, ...],          // Patologías/motivos
  'rangos' => [string, ...],            // Rangos de fechas tentativas
  'productos' => [
    [
      'patologia' => string,
      'fuente' => 'sve'|'yo',           // Producto SVE o del productor
      'detalle' => string
    ],
    ...
  ],
  'direccion' => [
    'provincia' => string,
    'localidad' => string,
    'calle' => string,
    'numero' => string
  ],
  'ubicacion' => [
    'en_finca' => 'si'|'no',
    'lat' => float,
    'lng' => float,
    'acc' => float,
    'timestamp' => datetime
  ],
  'costos' => [
    'moneda' => string,
    'base' => float,
    'productos' => float,
    'total' => float,
    'costo_ha' => float
  ],
  'pago_por_coop' => bool,
  'cta_url' => string,                  // URL base del sistema
  'cta_approve_url' => ?string,         // URL con token firmado
  'cta_decline_url' => ?string,         // URL con token firmado
  'coop_texto_extra' => string          // Texto adicional para cooperativa
]
```

**Contenido:**
- Datos del productor y cooperativa
- Superficie, forma de pago, motivos, rangos
- Tabla de productos seleccionados (SVE o propios)
- Dirección y ubicación GPS
- Desglose de costos (base + productos = total)
- **Si pago por cooperativa:** Bloque especial con botones de acción

**Sistema de Tokens de Seguridad:**
- Se generan tokens firmados con `COOP_ACTION_SECRET` (definido en `config.php` o `.env`)
- Tokens incluyen: solicitud_id, cooperativa_id, acción ('approve'/'decline'), expiración
- TTL: 7 días (604800 segundos) por defecto
- URLs: `<APP_URL>/views/partials/drones/coop_action_handler.php?t=<token>`

---

### 4. Solicitud de Dron Actualizada

**Método:** `Maill::enviarSolicitudDronActualizada()`

**Cuándo se envía:**
- Al actualizar una solicitud de dron existente desde el panel de gestión

**Archivo invocador:** `views/partials/drones/controller/drone_drawerListado_controller.php`

**Plantilla:** `mail/template/dron_actualizada.html`

**Destinatarios:**
- **SIEMPRE:** `dronesvecoop@gmail.com`
- Productor solicitante (si tiene correo)
- Cooperativas asociadas al productor (si tienen correo, evita duplicados)

**Datos incluidos:**
```php
[
  'solicitud_id' => int,
  'estado_anterior' => ?string,
  'estado_actual' => ?string,
  'productor' => [
    'nombre' => string,
    'correo' => string
  ],
  'cooperativas' => [
    [
      'usuario' => string,
      'correo' => string
    ],
    ...
  ],
  'cambios' => [
    [
      'campo' => string,
      'antes' => string,
      'despues' => string
    ],
    ...
  ],
  'costos' => [
    'moneda' => string,
    'base_total' => float,
    'productos_total' => float,
    'total' => float
  ]
]
```

**Cambios detectados:**
- Estado
- Fecha y hora de visita
- Piloto asignado
- Forma de pago
- Superficie
- Observaciones
- Costos (base, productos, total)
- Lista de productos
- Motivos (patologías)
- Rangos de fecha

**Contenido:**
- Estado anterior → Estado actual (con badges visuales)
- Tabla de cambios campo por campo
- Snapshot de costos actuales

**Asunto:** "🟣 SVE: Solicitud de dron actualizada"

**Manejo de errores:**
El envío de correo está envuelto en try-catch y los errores se silencian en la respuesta HTTP (solo se loguean).

---

## Plantillas HTML

### Estructura General

Todas las plantillas usan:
- Diseño responsive
- Tipografía: `Arial, Helvetica, sans-serif`
- Colores corporativos SVE (violeta `#5b21b6`, grises)
- Fondo gris claro, tarjeta blanca central
- Footer con texto "correo automático"

### Plantilla Base (`base.html`)

Plantilla genérica con placeholders:
- `{{title}}` - Título del documento
- `{{content}}` - Contenido dinámico generado en PHP

Usada por:
- Cierre de Cosecha Mecánica

### Plantilla Dron Solicitud (`dron_solicitud.html`)

Placeholder único:
- `{CONTENT}` - Contenido generado en PHP

Características:
- Máximo 760px de ancho
- Tarjeta blanca con bordes redondeados
- Footer automático

### Plantilla Dron Actualizada (`dron_actualizada.html`)

Similar a `dron_solicitud.html`:
- Placeholder `{CONTENT}`
- Diseño consistente con solicitud nueva

### Plantilla Pedido Creado (`pedido_creado.html`)

Placeholder único:
- `{CONTENT}` - Tabla de productos y totales

Características:
- Máximo 680px de ancho
- Optimizada para tablas de productos

---

## Flujos de Envío

### 1. Cierre Automático de Cosecha Mecánica (CRON)

**Archivo:** `cron/cerrar_cosecha_mecanica.php`

**Frecuencia:** Ejecutado por cron (programar en servidor)

**Flujo:**
1. Busca operativos con `estado <> 'cerrado'` y `fecha_cierre <= HOY`
2. Valida que la fecha de cierre haya llegado (incluyendo hora 23:39)
3. Actualiza estado a 'cerrado'
4. Por cada cooperativa que firmó el contrato:
   - Verifica que tenga correo válido
   - Verifica que no se haya enviado ya (tabla `cosechaMecanica_coop_correo_log`)
   - Obtiene participaciones de productores de esa cooperativa
   - Envía correo con `enviarCierreCosechaMecanica()`
   - Registra envío en log con `registrarCorreoCierre()`
5. Errores se loguean vía `error_log()`

**Modelo:** `coop_cosechaMecanicaModel.php`
- `correoCierreEnviado(contrato_id, cooperativa_id_real): bool`
- `registrarCorreoCierre(contrato_id, cooperativa_id_real, correo, tipo='cron')`

### 2. Cierre Manual de Cosecha Mecánica (Panel Cooperativa)

**Archivo:** `controllers/coop_cosechaMecanicaController.php`

**Acciones:**
- `action=firmar_contrato`: Cooperativa firma contrato, se envía correo
- `action=cerrar_operativo`: Cooperativa cierra operativo, se envía correo

**Flujo similar al CRON:**
- Verifica permisos de cooperativa
- Actualiza base de datos
- Envía correo con `enviarCierreCosechaMecanica()`
- Registra en log con tipo `'cooperativa'` en lugar de `'cron'`

### 3. Creación de Pedido en Mercado Digital

**Archivo:** `controllers/coop_MercadoDigitalController.php`

**Acción:** `action=create_pedido`

**Flujo:**
1. Valida datos del pedido y productos
2. Calcula totales (sin IVA, IVA, con IVA) por producto
3. Inserta en tabla `pedidos` y `detalle_pedidos`
4. Construye payload con items y totales
5. Envía correo con `enviarPedidoCreado()`
6. **Nota:** El envío falla silenciosamente, no afecta la respuesta al cliente

**Destinatarios fijos:**
- Correo cooperativa (si existe)
- `lacruzg@coopsve.com` (hardcoded, SIEMPRE se envía)

### 4. Solicitud de Dron Nueva

**Archivo:** `controllers/prod_dronesController.php`

**Acción:** `action=crear_solicitud`

**Flujo:**
1. Valida datos completos del formulario (productor, superficie, motivos, productos, ubicación, etc.)
2. Inserta en tabla `drones_solicitud`
3. Inserta relaciones: motivos, rangos, productos/items, parámetros
4. Calcula costos totales y los guarda en `drones_solicitud_costos`
5. Registra evento en `drones_solicitud_evento`
6. **Si pago por cooperativa:**
   - Genera tokens firmados con `signCoopActionToken()`
   - Construye URLs de aprobación/rechazo
7. Construye payload completo con todos los datos
8. Envía **DOS correos** con `enviarSolicitudDron()`:
   - Versión simple al productor
   - Versión completa (con botones si aplica) a drones + cooperativa
9. Loguea resultado en `drones_solicitud_evento`

**Función de tokens:**
```php
function signCoopActionToken(array $data): ?string
```
- Usa `hash_hmac('sha256', json_encode($data), COOP_ACTION_SECRET)`
- Retorna: `base64(json(data)) . '.' . signature`

**Manejo de errores de correo:**
- Se capturan excepciones del mailer
- Se registran en eventos pero no bloquean la creación de la solicitud
- Se retorna error de correo en respuesta JSON pero con `ok: true` (solicitud creada)

### 5. Actualización de Solicitud de Dron

**Archivo:** `views/partials/drones/controller/drone_drawerListado_controller.php`

**Acción:** `action=update_solicitud`

**Flujo:**
1. Obtiene snapshot ANTES de actualizar
2. Actualiza solicitud con `actualizarSolicitud()` del modelo
3. Obtiene snapshot DESPUÉS de actualizar
4. Compara campo por campo para detectar cambios
5. Construye array de cambios con formato `[campo, antes, después]`
6. Envía correo con `enviarSolicitudDronActualizada()`
7. **Nota:** Errores de correo se silencian (try-catch vacío)

**Campos comparados:**
- Estado, fecha_visita, hora_visita_desde, hora_visita_hasta
- piloto_id, forma_pago_id, superficie_ha, observaciones
- Costos (base_total, productos_total, total)
- Lista de productos (items)
- Motivos (patologías)
- Rangos de fecha

---

## Destinatarios y Reglas

### Direcciones Hardcoded

**Crítico:** El sistema tiene direcciones de correo hardcoded en el código:

1. **Drones SVE** (SIEMPRE en solicitudes de drones):
   - `dronesvecoop@gmail.com`
   - **Archivos:**
     - `mail/Mail.php:418` (nueva solicitud)
     - `mail/Mail.php:534` (solicitud actualizada)

2. **La Cruz** (SIEMPRE en pedidos de Mercado Digital):
   - `lacruzg@coopsve.com`
   - **Archivo:** `mail/Mail.php:264`

### Reglas de Envío por Tipo

| Tipo de Correo | Destinatarios | Condiciones |
|---|---|---|
| Cierre Cosecha Mecánica | Cooperativa que firmó | Solo si tiene correo válido y no se envió antes |
| Pedido Creado | Cooperativa + lacruzg@ | lacruzg siempre, cooperativa opcional |
| Solicitud Dron Nueva | Productor + Drones + Cooperativa | Productor: versión simple<br>Drones: SIEMPRE<br>Cooperativa: solo si pago_por_coop |
| Solicitud Dron Actualizada | Productor + Drones + Cooperativas | Drones: SIEMPRE<br>Productor: si tiene correo<br>Cooperativas: todas las asociadas |

### Validación de Correos

**Método usado:** `filter_var($email, FILTER_VALIDATE_EMAIL)`

**Ubicaciones:**
- `cron/cerrar_cosecha_mecanica.php:67`
- Validaciones implícitas en PHPMailer al agregar destinatarios

---

## Registro y Auditoría

### Tabla: `cosechaMecanica_coop_correo_log`

**Columnas:**
- `id` - Auto-increment
- `contrato_id` - ID del operativo de cosecha mecánica
- `cooperativa_id_real` - ID real de la cooperativa
- `correo` - Email enviado
- `tipo` - 'cron' | 'cooperativa' | otros
- `enviado_por` - Usuario que envió (si manual)
- `created_at` - Timestamp

**Uso:**
- Evitar duplicados en cierres automáticos
- Auditoría de envíos de cierre de cosecha

**Modelo:** `coop_cosechaMecanicaModel.php`
```php
correoCierreEnviado(int $contratoId, string $coopIdReal): bool
registrarCorreoCierre(int $contratoId, string $coopIdReal, string $correo, string $tipo, ?string $enviadoPor = null): void
```

### Tabla: `drones_solicitud_evento`

**Columnas:**
- `id` - Auto-increment
- `solicitud_id` - ID de la solicitud de dron
- `tipo` - Tipo de evento (ej: 'correo_enviado', 'correo_error')
- `detalle` - Descripción textual
- `payload` - JSON con datos adicionales
- `actor` - Usuario/sistema que generó el evento
- `created_at` - Timestamp

**Uso:**
- Registro de envíos de correo (exitosos y fallidos)
- Trazabilidad completa de solicitudes de dron
- Debugging de problemas de correo

**Archivo:** `controllers/prod_dronesController.php`

**Eventos registrados:**
```php
// Éxito
$model->registrarEvento($id, 'correo_enviado', 'Notificación enviada exitosamente', [
    'destinatarios' => [...],
    'pago_por_coop' => $esPagoCoop
], $actor);

// Error
$model->registrarEvento($id, 'correo_error', 'Error al enviar notificación', [
    'error' => $mailErr
], $actor);
```

### Error Logging del Sistema

**Función:** `error_log()`

**Ubicaciones:**
- `cron/cerrar_cosecha_mecanica.php:111` - Error enviando cierre
- `cron/cerrar_cosecha_mecanica.php:119` - Error general en cron
- `controllers/prod_dronesController.php:258` - Advertencia: COOP_ACTION_SECRET ausente

**Logs van a:** PHP error log del servidor (configurado en `php.ini`)

---

## Resumen de Archivos Clave

### Archivos que ENVÍAN correos:

1. **`mail/Mail.php`** - Clase principal con 4 métodos de envío
2. **`controllers/prod_dronesController.php`** - Solicitudes de dron nuevas
3. **`controllers/coop_MercadoDigitalController.php`** - Pedidos de compra
4. **`controllers/coop_cosechaMecanicaController.php`** - Cierre manual de cosecha
5. **`cron/cerrar_cosecha_mecanica.php`** - Cierre automático de cosecha (CRON)
6. **`views/partials/drones/controller/drone_drawerListado_controller.php`** - Actualización de solicitudes

### Archivos de CONFIGURACIÓN:

1. **`config.php`** - Constantes SMTP
2. **`.env`** - Variables de entorno (COOP_ACTION_SECRET)

### Plantillas HTML:

1. `mail/template/base.html`
2. `mail/template/dron_solicitud.html`
3. `mail/template/dron_actualizada.html`
4. `mail/template/pedido_creado.html`

### Librería PHPMailer:

1. `mail/lib/PHPMailer.php`
2. `mail/lib/SMTP.php`
3. `mail/lib/Exception.php`

---

## Consideraciones Importantes

### Seguridad

1. **Tokens firmados:** Las URLs de acción para cooperativas usan HMAC-SHA256
2. **Validación de correos:** `filter_var()` antes de enviar
3. **Sanitización HTML:** `htmlspecialchars()` en todos los datos de usuario
4. **Secret keys:** COOP_ACTION_SECRET debe estar en `.env` y ser secreto

### Performance

1. **Envíos sincrónicos:** Todos los correos se envían en el mismo request (no hay cola)
2. **Timeout SMTP:** Por defecto configurable en PHPMailer
3. **CRON:** El cierre automático puede tomar tiempo si hay muchas cooperativas

### Mantenimiento

1. **Direcciones hardcoded:** Cambiar `dronesvecoop@gmail.com` y `lacruzg@coopsve.com` requiere editar código
2. **Plantillas:** Cambios de diseño requieren editar HTML
3. **Textos:** Los asuntos y mensajes están hardcoded en español

### Fallbacks

1. **Plantillas faltantes:** El código tiene fallbacks HTML inline si no encuentra archivos
2. **Errores de correo:** No bloquean operaciones principales (solicitud se crea aunque falle el email)
3. **Correos inválidos:** Se validan y se omiten sin fallar todo el proceso

---

## Próximos Pasos Recomendados

1. **Configurar CRON:** Asegurar que `cron/cerrar_cosecha_mecanica.php` se ejecute diariamente
2. **Mover direcciones hardcoded a config:** Crear constantes para `dronesvecoop@gmail.com` y `lacruzg@coopsve.com`
3. **Implementar cola de correos:** Usar Redis/RabbitMQ para envíos asíncronos
4. **Logs centralizados:** Usar Monolog o similar en lugar de `error_log()`
5. **Testing:** Crear suite de tests para verificar envíos en staging
6. **Métricas:** Implementar tracking de tasa de entrega/apertura

---

**Última actualización:** 2026-01-30
