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
