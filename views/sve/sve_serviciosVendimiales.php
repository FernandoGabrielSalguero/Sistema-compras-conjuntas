<?php
// Mostrar errores en pantalla (útil en desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión y configurar parámetros de seguridad
require_once '../../middleware/authMiddleware.php';
checkAccess('sve');

// Datos del usuario en sesión
$nombre = $_SESSION['nombre'] ?? 'Sin nombre';
$correo = $_SESSION['correo'] ?? 'Sin correo';
$cuit = $_SESSION['cuit'] ?? 'Sin CUIT';
$telefono = $_SESSION['telefono'] ?? 'Sin teléfono';
$observaciones = $_SESSION['observaciones'] ?? 'Sin observaciones';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SVE</title>

    <!-- Íconos de Material Design -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <!-- Framework Success desde CDN -->
    <link rel="stylesheet" href="https://framework.impulsagroup.com/assets/css/framework.css">
    <script src="https://framework.impulsagroup.com/assets/javascript/framework.js" defer></script>

    <!-- text Editor -->
    <link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">

    <style>
        .estado-pill {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 0.8rem;
            background: #eef2ff;
            color: #3730a3;
        }

        .empty-row {
            text-align: center;
            color: #6b7280;
            padding: 16px 8px;
        }

        .table-container {
            overflow-x: hidden;
        }

        #modalServiciosOfrecidos .modal-content,
        #modalCentrifugadoras .modal-content,
        #modalFiltracion .modal-content,
        #modalColumna .modal-content,
        #modalContratos .modal-content {
            width: 80vw;
            height: 80vh;
            max-width: none;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .modal-content {
    background: #fff;
    padding: 2rem;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    max-width: 800px;
    width: 90%;
    text-align: center;
    animation: fadeInModal 0.3s ease;
}

        #modalServiciosOfrecidos .table-container,
        #modalCentrifugadoras .table-container,
        #modalFiltracion .table-container,
        #modalColumna .table-container,
        #modalContratos .table-container {
            overflow-x: hidden;
        }

        #modalContratos .editor-card {
            margin-top: 0.25rem;
            background: #ffffff;
            border-radius: 0.75rem;
            border: 1px solid #e2e8f0;
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.06);
            overflow: hidden;
            width: 100%;
        }

        #modalContratos .editor-card .ql-toolbar.ql-snow {
            border: none;
            border-bottom: 1px solid #e2e8f0;
            background: #f8fafc;
            padding: 0.5rem 0.75rem;
        }

        #modalContratos .editor-card .ql-container.ql-snow {
            border: none;
        }

        #modalContratos .editor-card .ql-editor {
            min-height: 200px;
            font-size: 0.95rem;
            line-height: 1.5;
            padding: 0.75rem 0.9rem;
        }
    </style>
</head>

<body>

    <!-- 🔲 CONTENEDOR PRINCIPAL -->
    <div class="layout">

        <!-- 🧭 SIDEBAR -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <span class="material-icons logo-icon">dashboard</span>
                <span class="logo-text">SVE</span>
            </div>

            <nav class="sidebar-menu">
                <ul>
                    <li onclick="location.href='sve_dashboard.php'">
                        <span class="material-icons" style="color: #5b21b6;">home</span><span class="link-text">Inicio</span>
                    </li>
                    <li onclick="location.href='sve_consolidado.php'">
                        <span class="material-icons" style="color: #5b21b6;">analytics</span><span class="link-text">Consolidado</span>
                    </li>
                    <li onclick="location.href='sve_altausuarios.php'">
                        <span class="material-icons" style="color: #5b21b6;">person</span><span class="link-text">Alta usuarios</span>
                    </li>
                    <li onclick="location.href='sve_asociarProductores.php'">
                        <span class="material-icons" style="color: #5b21b6;">link</span><span class="link-text">Asociaciones</span>
                    </li>
                    <li onclick="location.href='sve_cargaMasiva.php'">
                        <span class="material-icons" style="color: #5b21b6;">upload_file</span><span class="link-text">Carga masiva</span>
                    </li>
                    <li onclick="location.href='sve_registro_login.php'">
                        <span class="material-icons" style="color: #5b21b6;">login</span><span class="link-text">Ingresos</span>
                    </li>
                    <li onclick="location.href='sve_operativos.php'">
                        <span class="material-icons" style="color: #5b21b6;">assignment</span><span class="link-text">Operativos</span>
                    </li>
                    <li onclick="location.href='sve_mercadodigital.php'">
                        <span class="material-icons" style="color: #5b21b6;">shopping_cart</span><span class="link-text">Mercado Digital</span>
                    </li>
                    <li onclick="location.href='sve_listadoPedidos.php'">
                        <span class="material-icons" style="color: #5b21b6;">assignment_turned_in</span><span class="link-text">Listado Pedidos</span>
                    </li>
                    <li onclick="location.href='sve_productos.php'">
                        <span class="material-icons" style="color: #5b21b6;">inventory</span><span class="link-text">Productos</span>
                    </li>
                    <li onclick="location.href='sve_pulverizacionDrone.php'">
                        <span class="material-symbols-outlined" style="color:#5b21b6;">drone</span>
                        <span class="link-text">Drones</span>
                    </li>
                    <li onclick="location.href='sve_cosechaMecanica.php'">
                        <span class="material-icons" style="color:#5b21b6;">agriculture</span>
                        <span class="link-text">Cosecha Mecánica</span>
                    </li>
                    <li onclick="location.href='sve_serviciosVendimiales.php'">
                        <span class="material-icons" style="color:#5b21b6;">wine_bar</span>
                        <span class="link-text">Servicios Auxiliares Enológicos</span>
                    </li>
                    <li onclick="location.href='sve_publicaciones.php'">
                        <span class="material-icons" style="color: #5b21b6;">menu_book</span><span class="link-text">Biblioteca Virtual</span>
                    </li>
                    <li onclick="location.href='../../../logout.php'">
                        <span class="material-icons" style="color: red;">logout</span><span class="link-text">Salir</span>
                    </li>
                </ul>
            </nav>

            <div class="sidebar-footer">
                <button class="btn-icon" onclick="toggleSidebar()">
                    <span class="material-icons" id="collapseIcon">chevron_left</span>
                </button>
            </div>
        </aside>

        <!-- 🧱 MAIN -->
        <div class="main">

            <!-- 🟪 NAVBAR -->
            <header class="navbar">
                <button class="btn-icon" onclick="toggleSidebar()">
                    <span class="material-icons">menu</span>
                </button>
                <div class="navbar-title">Servicios vendimiales</div>
            </header>

            <!-- 📦 CONTENIDO -->
            <section class="content">

                <div class="card">
                    <h2>Servicios vendimiales</h2>
                    <p>Administración de servicios vendimiales. Usá el botón para gestionar los servicios ofrecidos.</p>
                    <div class="form-buttons" style="margin-top: 16px;">
                        <button type="button" class="btn btn-aceptar" onclick="openModalServiciosOfrecidos()">Servicios ofrecidos</button>
                        <button type="button" class="btn btn-aceptar" onclick="openModalCentrifugadoras()">Centrifugadoras</button>
                        <button type="button" class="btn btn-aceptar" onclick="openModalFiltracion()">Filtración</button>
                        <button type="button" class="btn btn-aceptar" onclick="openModalColumna()">Columna</button>
                        <button type="button" class="btn btn-aceptar" onclick="openModalContratos()">Contratos</button>
                    </div>
                </div>

                <div class="card">
                    <h2>Servicios contratados</h2>
                    <div class="form-buttons" style="margin-top: 16px;">
                        <button type="button" class="btn btn-aceptar" onclick="openModalPedidoCreate()">Nuevo pedido</button>
                    </div>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Cooperativa</th>
                                    <th>Nombre</th>
                                    <th>Servicio</th>
                                    <th>Producto</th>
                                    <th>Volumen</th>
                                    <th>Equipo</th>
                                    <th>Estado</th>
                                    <th>Contrato</th>
                                    <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaPedidosBody">
                            <tr>
                                <td colspan="9" class="empty-row">Sin pedidos cargados.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            </section>
        </div>
    </div>

    <!-- Modal editar servicio contratado -->
    <div id="modalPedidoEdit" class="modal hidden">
        <div class="modal-content">
            <div style="display:flex; align-items:center; justify-content:space-between; gap:16px;">
                <h3 style="margin:0;">Editar servicio contratado</h3>
                <button class="btn-icon" onclick="closeModalPedidoEdit()" aria-label="Cerrar">
                    <span class="material-icons">close</span>
                </button>
            </div>

            <div class="card" style="margin-top: 16px;">
                <form class="form-modern" id="formPedidoEdit">
                    <input type="hidden" id="pedido_id" name="id">
                    <div class="form-grid grid-3">
                        <div class="input-group">
                            <label for="pedido_cooperativa">Cooperativa</label>
                            <div class="input-icon">
                                <span class="material-icons">apartment</span>
                                <select id="pedido_cooperativa" name="cooperativa" required></select>
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="pedido_nombre">Nombre</label>
                            <div class="input-icon">
                                <span class="material-icons">person</span>
                                <input type="text" id="pedido_nombre" name="nombre" required maxlength="160">
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="pedido_cargo">Cargo</label>
                            <div class="input-icon">
                                <span class="material-icons">badge</span>
                                <input type="text" id="pedido_cargo" name="cargo" maxlength="120">
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="pedido_servicio">Servicio</label>
                            <div class="input-icon">
                                <span class="material-icons">local_offer</span>
                                <select id="pedido_servicio" name="servicioAcontratar" required></select>
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="pedido_producto">Producto</label>
                            <div class="input-icon">
                                <span class="material-icons">inventory_2</span>
                                <select id="pedido_producto" name="producto_id"></select>
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="pedido_volumen">Volumen aproximado</label>
                            <div class="input-icon">
                                <span class="material-icons">scale</span>
                                <input type="number" id="pedido_volumen" name="volumenAproximado" min="0" step="0.001">
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="pedido_unidad_volumen">Unidad</label>
                            <div class="input-icon">
                                <span class="material-icons">straighten</span>
                                <input type="text" id="pedido_unidad_volumen" name="unidad_volumen" maxlength="20" placeholder="litros">
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="pedido_fecha_entrada">Fecha entrada equipo</label>
                            <div class="input-icon">
                                <span class="material-icons">event</span>
                                <input type="date" id="pedido_fecha_entrada" name="fecha_entrada_equipo">
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="pedido_equipo">Equipo centrifugadora</label>
                            <div class="input-icon">
                                <span class="material-icons">precision_manufacturing</span>
                                <select id="pedido_equipo" name="equipo_centrifugadora"></select>
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="pedido_estado">Estado</label>
                            <div class="input-icon">
                                <span class="material-icons">toggle_on</span>
                                <select id="pedido_estado" name="estado" required>
                                    <option value="SOLICITADO">SOLICITADO</option>
                                    <option value="BORRADOR">BORRADOR</option>
                                    <option value="CONFIRMADO">CONFIRMADO</option>
                                    <option value="CANCELADO">CANCELADO</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="input-group input-group-descripcion" style="margin-top: 16px;">
                        <label for="pedido_observaciones">Observaciones</label>
                        <textarea id="pedido_observaciones" name="observaciones" rows="4"></textarea>
                    </div>
                    <div class="form-buttons" style="margin-top: 16px;">
                        <button type="submit" class="btn btn-aceptar">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal servicios ofrecidos -->
    <div id="modalServiciosOfrecidos" class="modal hidden">
        <div class="modal-content">
            <div style="display:flex; align-items:center; justify-content:space-between; gap:16px;">
                <h3 style="margin:0;">Servicios ofrecidos</h3>
                <button class="btn-icon" onclick="closeModalServiciosOfrecidos()" aria-label="Cerrar">
                    <span class="material-icons">close</span>
                </button>
            </div>

            <div class="card" style="margin-top: 16px;">
                <h4>Nuevo servicio</h4>
                <form class="form-modern" id="formServicio">
                    <input type="hidden" id="servicio_id" name="id">
                    <div class="form-grid grid-3">
                        <div class="input-group">
                            <label for="nombre">Nombre</label>
                            <div class="input-icon">
                                <span class="material-icons">local_offer</span>
                                <input type="text" id="nombre" name="nombre" required maxlength="120" placeholder="Ej: Centrifugado">
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="activo">Activo</label>
                            <div class="input-icon">
                                <span class="material-icons">toggle_on</span>
                                <select id="activo" name="activo" required>
                                    <option value="1">Sí</option>
                                    <option value="0">No</option>
                                </select>
                            </div>
                        </div>
                        <div class="input-group" style="display:flex; align-items:flex-end;">
                            <button type="submit" class="btn btn-aceptar" style="width:100%;">Guardar</button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="card" style="margin-top: 16px;">
                <h4>Listado de servicios</h4>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Activo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaServiciosBody">
                            <tr>
                                <td colspan="3" class="empty-row">Sin servicios cargados.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal centrifugadoras -->
    <div id="modalCentrifugadoras" class="modal hidden">
        <div class="modal-content">
            <div style="display:flex; align-items:center; justify-content:space-between; gap:16px;">
                <h3 style="margin:0;">Centrifugadoras</h3>
                <button class="btn-icon" onclick="closeModalCentrifugadoras()" aria-label="Cerrar">
                    <span class="material-icons">close</span>
                </button>
            </div>

            <div class="card" style="margin-top: 16px;">
                <h4>Nueva centrifugadora</h4>
                <form class="form-modern" id="formCentrifugadora">
                    <input type="hidden" id="centrifugadora_id" name="id">
                    <div class="form-grid grid-4">
                        <div class="input-group">
                            <label for="centrifugadora_nombre">Nombre</label>
                            <div class="input-icon">
                                <span class="material-icons">precision_manufacturing</span>
                                <input type="text" id="centrifugadora_nombre" name="nombre" required maxlength="120" placeholder="Ej: Alfa 2200">
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="centrifugadora_precio">Precio</label>
                            <div class="input-icon">
                                <span class="material-icons">payments</span>
                                <input type="number" id="centrifugadora_precio" name="precio" required min="0" step="0.01" placeholder="0.00">
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="centrifugadora_moneda">Moneda</label>
                            <div class="input-icon">
                                <span class="material-icons">paid</span>
                                <input type="text" id="centrifugadora_moneda" name="moneda" required maxlength="3" placeholder="ARS">
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="centrifugadora_activo">Activo</label>
                            <div class="input-icon">
                                <span class="material-icons">toggle_on</span>
                                <select id="centrifugadora_activo" name="activo" required>
                                    <option value="1">Sí</option>
                                    <option value="0">No</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-buttons" style="margin-top: 16px;">
                        <button type="submit" class="btn btn-aceptar">Guardar</button>
                    </div>
                </form>
            </div>

            <div class="card" style="margin-top: 16px;">
                <h4>Listado de centrifugadoras</h4>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Precio</th>
                                <th>Moneda</th>
                                <th>Activo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaCentrifugadorasBody">
                            <tr>
                                <td colspan="5" class="empty-row">Sin centrifugadoras cargadas.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal filtración -->
    <div id="modalFiltracion" class="modal hidden">
        <div class="modal-content">
            <div style="display:flex; align-items:center; justify-content:space-between; gap:16px;">
                <h3 style="margin:0;">Filtración</h3>
                <button class="btn-icon" onclick="closeModalFiltracion()" aria-label="Cerrar">
                    <span class="material-icons">close</span>
                </button>
            </div>

            <div class="card" style="margin-top: 16px;">
                <h4>Nuevo producto de filtración</h4>
                <form class="form-modern" id="formFiltracion">
                    <input type="hidden" id="filtracion_id" name="id">
                    <div class="form-grid grid-4">
                        <div class="input-group">
                            <label for="filtracion_nombre">Nombre</label>
                            <div class="input-icon">
                                <span class="material-icons">precision_manufacturing</span>
                                <input type="text" id="filtracion_nombre" name="nombre" required maxlength="120" placeholder="Ej: Filtro X-200">
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="filtracion_precio">Precio</label>
                            <div class="input-icon">
                                <span class="material-icons">payments</span>
                                <input type="number" id="filtracion_precio" name="precio" required min="0" step="0.01" placeholder="0.00">
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="filtracion_moneda">Moneda</label>
                            <div class="input-icon">
                                <span class="material-icons">paid</span>
                                <input type="text" id="filtracion_moneda" name="moneda" required maxlength="3" placeholder="ARS">
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="filtracion_activo">Activo</label>
                            <div class="input-icon">
                                <span class="material-icons">toggle_on</span>
                                <select id="filtracion_activo" name="activo" required>
                                    <option value="1">Sí</option>
                                    <option value="0">No</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-buttons" style="margin-top: 16px;">
                        <button type="submit" class="btn btn-aceptar">Guardar</button>
                    </div>
                </form>
            </div>

            <div class="card" style="margin-top: 16px;">
                <h4>Listado de productos de filtración</h4>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Precio</th>
                                <th>Moneda</th>
                                <th>Activo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaFiltracionBody">
                            <tr>
                                <td colspan="5" class="empty-row">Sin productos cargados.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal columna -->
    <div id="modalColumna" class="modal hidden">
        <div class="modal-content">
            <div style="display:flex; align-items:center; justify-content:space-between; gap:16px;">
                <h3 style="margin:0;">Columna</h3>
                <button class="btn-icon" onclick="closeModalColumna()" aria-label="Cerrar">
                    <span class="material-icons">close</span>
                </button>
            </div>

            <div class="card" style="margin-top: 16px;">
                <h4>Nuevo producto de columna</h4>
                <form class="form-modern" id="formColumna">
                    <input type="hidden" id="columna_id" name="id">
                    <div class="form-grid grid-4">
                        <div class="input-group">
                            <label for="columna_nombre">Nombre</label>
                            <div class="input-icon">
                                <span class="material-icons">precision_manufacturing</span>
                                <input type="text" id="columna_nombre" name="nombre" required maxlength="120" placeholder="Ej: Columna Z-10">
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="columna_precio">Precio</label>
                            <div class="input-icon">
                                <span class="material-icons">payments</span>
                                <input type="number" id="columna_precio" name="precio" required min="0" step="0.01" placeholder="0.00">
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="columna_moneda">Moneda</label>
                            <div class="input-icon">
                                <span class="material-icons">paid</span>
                                <input type="text" id="columna_moneda" name="moneda" required maxlength="3" placeholder="ARS">
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="columna_activo">Activo</label>
                            <div class="input-icon">
                                <span class="material-icons">toggle_on</span>
                                <select id="columna_activo" name="activo" required>
                                    <option value="1">Sí</option>
                                    <option value="0">No</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-buttons" style="margin-top: 16px;">
                        <button type="submit" class="btn btn-aceptar">Guardar</button>
                    </div>
                </form>
            </div>

            <div class="card" style="margin-top: 16px;">
                <h4>Listado de productos de columna</h4>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Precio</th>
                                <th>Moneda</th>
                                <th>Activo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaColumnaBody">
                            <tr>
                                <td colspan="5" class="empty-row">Sin productos cargados.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal contratos -->
    <div id="modalContratos" class="modal hidden">
        <div class="modal-content">
            <div style="display:flex; align-items:center; justify-content:space-between; gap:16px;">
                <h3 style="margin:0;">Contratos</h3>
                <button class="btn-icon" onclick="closeModalContratos()" aria-label="Cerrar">
                    <span class="material-icons">close</span>
                </button>
            </div>

            <div class="card" style="margin-top: 16px;">
                <h4>Nuevo contrato</h4>
                <form class="form-modern" id="formContrato">
                    <input type="hidden" id="contrato_id" name="id">
                    <div class="form-grid grid-4">
                        <div class="input-group">
                            <label for="contrato_nombre">Nombre</label>
                            <div class="input-icon">
                                <span class="material-icons">assignment</span>
                                <input type="text" id="contrato_nombre" name="nombre" required maxlength="160" placeholder="Ej: Contrato vendimia 2026">
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="contrato_servicio">Servicio</label>
                            <div class="input-icon">
                                <span class="material-icons">local_offer</span>
                                <select id="contrato_servicio" name="servicio_id" required>
                                    <option value="">Seleccioná un servicio</option>
                                </select>
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="contrato_version">Versión</label>
                            <div class="input-icon">
                                <span class="material-icons">tag</span>
                                <input type="number" id="contrato_version" name="version" min="1" step="1" value="1">
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="contrato_vigente">Vigente</label>
                            <div class="input-icon">
                                <span class="material-icons">toggle_on</span>
                                <select id="contrato_vigente" name="vigente" required>
                                    <option value="1">Sí</option>
                                    <option value="0">No</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="input-group input-group-descripcion" style="margin-top: 16px;">
                        <label for="contrato_editor">Contenido del contrato</label>
                        <div id="contrato_editor_container" class="editor-card editor-card-full">
                            <div id="contrato_editor" class="quill-editor"></div>
                            <textarea id="contrato_contenido" name="contenido" style="display: none;"></textarea>
                        </div>
                    </div>

                    <div class="form-buttons" style="margin-top: 16px;">
                        <button type="submit" class="btn btn-aceptar">Guardar</button>
                    </div>
                </form>
            </div>

            <div class="card" style="margin-top: 16px;">
                <h4>Listado de contratos</h4>
                <div class="table-container">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Servicio</th>
                                <th>Versión</th>
                                <th>Vigente</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="tablaContratosBody">
                            <tr>
                                <td colspan="5" class="empty-row">Sin contratos cargados.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>

    <script>
        let quillContrato = null;

        function openModalServiciosOfrecidos() {
            const modal = document.getElementById('modalServiciosOfrecidos');
            if (modal) {
                modal.classList.remove('hidden');
                cargarServiciosVendimiales();
            }
        }

        function closeModalServiciosOfrecidos() {
            const modal = document.getElementById('modalServiciosOfrecidos');
            if (modal) {
                modal.classList.add('hidden');
            }
        }

        function openModalCentrifugadoras() {
            const modal = document.getElementById('modalCentrifugadoras');
            if (modal) {
                modal.classList.remove('hidden');
                cargarCentrifugadoras();
            }
        }

        function closeModalCentrifugadoras() {
            const modal = document.getElementById('modalCentrifugadoras');
            if (modal) {
                modal.classList.add('hidden');
            }
        }

        function openModalPedidoEdit() {
            const modal = document.getElementById('modalPedidoEdit');
            if (modal) {
                modal.classList.remove('hidden');
            }
        }

        function closeModalPedidoEdit() {
            const modal = document.getElementById('modalPedidoEdit');
            if (modal) {
                modal.classList.add('hidden');
            }
        }

        async function openModalPedidoCreate() {
            setPedidoForm(null);
            await cargarCooperativasSelectPedido();
            await cargarServiciosSelectPedido();
            await cargarCentrifugadorasSelectPedido();
            openModalPedidoEdit();
        }

        function openModalFiltracion() {
            const modal = document.getElementById('modalFiltracion');
            if (modal) {
                modal.classList.remove('hidden');
                cargarFiltracion();
            }
        }

        function closeModalFiltracion() {
            const modal = document.getElementById('modalFiltracion');
            if (modal) {
                modal.classList.add('hidden');
            }
        }

        function openModalColumna() {
            const modal = document.getElementById('modalColumna');
            if (modal) {
                modal.classList.remove('hidden');
                cargarColumna();
            }
        }

        function closeModalColumna() {
            const modal = document.getElementById('modalColumna');
            if (modal) {
                modal.classList.add('hidden');
            }
        }

        function openModalContratos() {
            const modal = document.getElementById('modalContratos');
            if (modal) {
                modal.classList.remove('hidden');
                cargarServiciosParaContratos();
                cargarContratos();
            }
        }

        function closeModalContratos() {
            const modal = document.getElementById('modalContratos');
            if (modal) {
                modal.classList.add('hidden');
            }
        }

        function setForm(servicio) {
            document.getElementById('servicio_id').value = servicio?.id ?? '';
            document.getElementById('nombre').value = servicio?.nombre ?? '';
            document.getElementById('activo').value = servicio?.activo ?? '1';
        }

        function setCentrifugadoraForm(item) {
            document.getElementById('centrifugadora_id').value = item?.id ?? '';
            document.getElementById('centrifugadora_nombre').value = item?.nombre ?? '';
            document.getElementById('centrifugadora_precio').value = item?.precio ?? '';
            document.getElementById('centrifugadora_moneda').value = item?.moneda ?? '';
            document.getElementById('centrifugadora_activo').value = item?.activo ?? '1';
        }

        function setPedidoForm(item) {
            document.getElementById('pedido_id').value = item?.id ?? '';
            document.getElementById('pedido_cooperativa').value = item?.cooperativa ?? '';
            document.getElementById('pedido_nombre').value = item?.nombre ?? '';
            document.getElementById('pedido_cargo').value = item?.cargo ?? '';
            document.getElementById('pedido_servicio').value = item?.servicioAcontratar ?? '';
            document.getElementById('pedido_producto').value = item?.producto_id ?? '';
            document.getElementById('pedido_volumen').value = item?.volumenAproximado ?? '';
            document.getElementById('pedido_unidad_volumen').value = item?.unidad_volumen ?? 'litros';
            document.getElementById('pedido_fecha_entrada').value = item?.fecha_entrada_equipo ?? '';
            document.getElementById('pedido_equipo').value = item?.equipo_centrifugadora ?? '';
            document.getElementById('pedido_estado').value = item?.estado ?? 'BORRADOR';
            document.getElementById('pedido_observaciones').value = item?.observaciones ?? '';
        }

        function setFiltracionForm(item) {
            document.getElementById('filtracion_id').value = item?.id ?? '';
            document.getElementById('filtracion_nombre').value = item?.nombre ?? '';
            document.getElementById('filtracion_precio').value = item?.precio ?? '';
            document.getElementById('filtracion_moneda').value = item?.moneda ?? '';
            document.getElementById('filtracion_activo').value = item?.activo ?? '1';
        }

        function setColumnaForm(item) {
            document.getElementById('columna_id').value = item?.id ?? '';
            document.getElementById('columna_nombre').value = item?.nombre ?? '';
            document.getElementById('columna_precio').value = item?.precio ?? '';
            document.getElementById('columna_moneda').value = item?.moneda ?? '';
            document.getElementById('columna_activo').value = item?.activo ?? '1';
        }

        function setContratoForm(item) {
            document.getElementById('contrato_id').value = item?.id ?? '';
            document.getElementById('contrato_nombre').value = item?.nombre ?? '';
            document.getElementById('contrato_version').value = item?.version ?? 1;
            document.getElementById('contrato_vigente').value = item?.vigente ?? '1';
            const servicioSelect = document.getElementById('contrato_servicio');
            if (servicioSelect) {
                servicioSelect.value = item?.servicio_id ?? '';
            }
            if (quillContrato) {
                quillContrato.root.innerHTML = item?.contenido ?? '';
            }
        }

        async function cargarServiciosVendimiales() {
            const tbody = document.getElementById('tablaServiciosBody');
            tbody.innerHTML = '<tr><td colspan="3" class="empty-row">Cargando...</td></tr>';

            try {
                const res = await fetch('/controllers/sve_serviciosVendimialesController.php');
                const data = await res.json();

                if (!data.success) {
                    throw new Error(data.message || 'No se pudo cargar la información.');
                }

                const servicios = Array.isArray(data.servicios) ? data.servicios : [];

                if (servicios.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="3" class="empty-row">Sin servicios cargados.</td></tr>';
                    return;
                }

                tbody.innerHTML = '';
                servicios.forEach((servicio) => {
                    const estado = Number(servicio.activo) === 1 ? 'Sí' : 'No';
                    const fila = document.createElement('tr');
                    fila.innerHTML = `
                        <td>${servicio.nombre ?? 'Sin nombre'}</td>
                        <td><span class="estado-pill">${estado}</span></td>
                        <td>
                            <button class="btn-icon" data-id="${servicio.id}" data-action="editar" data-tooltip="Editar">
                                <span class="material-icons">edit</span>
                            </button>
                            <button class="btn-icon" data-id="${servicio.id}" data-action="eliminar" data-tooltip="Eliminar" style="color: red;">
                                <span class="material-icons">delete</span>
                            </button>
                        </td>
                    `;
                    tbody.appendChild(fila);
                });
            } catch (error) {
                tbody.innerHTML = `<tr><td colspan="3" class="empty-row">${error.message}</td></tr>`;
            }
        }

        async function guardarServicio(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            const payload = new URLSearchParams(formData);

            const res = await fetch('/controllers/sve_serviciosVendimialesController.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
                },
                body: payload.toString()
            });

            const data = await res.json();
            if (!data.success) {
                alert(data.message || 'Error al guardar.');
                return;
            }

            setForm(null);
            await cargarServiciosVendimiales();
        }

        async function eliminarServicio(id) {
            if (!confirm('¿Eliminar servicio?')) return;

            const payload = new URLSearchParams();
            payload.append('_method', 'delete');
            payload.append('id', id);

            const res = await fetch('/controllers/sve_serviciosVendimialesController.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
                },
                body: payload.toString()
            });

            const data = await res.json();
            if (!data.success) {
                alert(data.message || 'Error al eliminar.');
                return;
            }

            await cargarServiciosVendimiales();
        }

        async function editarServicio(id) {
            const res = await fetch(`/controllers/sve_serviciosVendimialesController.php?id=${id}`);
            const data = await res.json();
            if (!data.success) {
                alert(data.message || 'No se pudo cargar el servicio.');
                return;
            }
            setForm(data.servicio);
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        async function cargarCentrifugadoras() {
            const tbody = document.getElementById('tablaCentrifugadorasBody');
            tbody.innerHTML = '<tr><td colspan="5" class="empty-row">Cargando...</td></tr>';

            try {
                const res = await fetch('/controllers/sve_centrifugadoresController.php');
                const data = await res.json();

                if (!data.success) {
                    throw new Error(data.message || 'No se pudo cargar la información.');
                }

                const items = Array.isArray(data.centrifugadoras) ? data.centrifugadoras : [];
                if (items.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" class="empty-row">Sin centrifugadoras cargadas.</td></tr>';
                    return;
                }

                tbody.innerHTML = '';
                items.forEach((item) => {
                    const estado = Number(item.activo) === 1 ? 'Sí' : 'No';
                    const fila = document.createElement('tr');
                    fila.innerHTML = `
                        <td>${item.nombre ?? 'Sin nombre'}</td>
                        <td>${item.precio ?? '0.00'}</td>
                        <td>${item.moneda ?? ''}</td>
                        <td><span class="estado-pill">${estado}</span></td>
                        <td>
                            <button class="btn-icon" data-id="${item.id}" data-action="editar" data-tooltip="Editar">
                                <span class="material-icons">edit</span>
                            </button>
                            <button class="btn-icon" data-id="${item.id}" data-action="eliminar" data-tooltip="Eliminar" style="color: red;">
                                <span class="material-icons">delete</span>
                            </button>
                        </td>
                    `;
                    tbody.appendChild(fila);
                });
            } catch (error) {
                tbody.innerHTML = `<tr><td colspan="5" class="empty-row">${error.message}</td></tr>`;
            }
        }

        async function guardarCentrifugadora(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            const payload = new URLSearchParams(formData);

            const res = await fetch('/controllers/sve_centrifugadoresController.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
                },
                body: payload.toString()
            });

            const data = await res.json();
            if (!data.success) {
                alert(data.message || 'Error al guardar.');
                return;
            }

            setCentrifugadoraForm(null);
            await cargarCentrifugadoras();
        }

        async function eliminarCentrifugadora(id) {
            if (!confirm('¿Eliminar centrifugadora?')) return;

            const payload = new URLSearchParams();
            payload.append('_method', 'delete');
            payload.append('id', id);

            const res = await fetch('/controllers/sve_centrifugadoresController.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
                },
                body: payload.toString()
            });

            const data = await res.json();
            if (!data.success) {
                alert(data.message || 'Error al eliminar.');
                return;
            }

            await cargarCentrifugadoras();
        }

        async function editarCentrifugadora(id) {
            const res = await fetch(`/controllers/sve_centrifugadoresController.php?id=${id}`);
            const data = await res.json();
            if (!data.success) {
                alert(data.message || 'No se pudo cargar la centrifugadora.');
                return;
            }
            setCentrifugadoraForm(data.centrifugadora);
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        async function cargarFiltracion() {
            const tbody = document.getElementById('tablaFiltracionBody');
            tbody.innerHTML = '<tr><td colspan="5" class="empty-row">Cargando...</td></tr>';

            try {
                const res = await fetch('/controllers/sve_filtracionController.php');
                const data = await res.json();

                if (!data.success) {
                    throw new Error(data.message || 'No se pudo cargar la información.');
                }

                const items = Array.isArray(data.filtracion) ? data.filtracion : [];
                if (items.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" class="empty-row">Sin productos cargados.</td></tr>';
                    return;
                }

                tbody.innerHTML = '';
                items.forEach((item) => {
                    const estado = Number(item.activo) === 1 ? 'Sí' : 'No';
                    const fila = document.createElement('tr');
                    fila.innerHTML = `
                        <td>${item.nombre ?? 'Sin nombre'}</td>
                        <td>${item.precio ?? '0.00'}</td>
                        <td>${item.moneda ?? ''}</td>
                        <td><span class="estado-pill">${estado}</span></td>
                        <td>
                            <button class="btn-icon" data-id="${item.id}" data-action="editar" data-tooltip="Editar">
                                <span class="material-icons">edit</span>
                            </button>
                            <button class="btn-icon" data-id="${item.id}" data-action="eliminar" data-tooltip="Eliminar" style="color: red;">
                                <span class="material-icons">delete</span>
                            </button>
                        </td>
                    `;
                    tbody.appendChild(fila);
                });
            } catch (error) {
                tbody.innerHTML = `<tr><td colspan="5" class="empty-row">${error.message}</td></tr>`;
            }
        }

        async function guardarFiltracion(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            const payload = new URLSearchParams(formData);

            const res = await fetch('/controllers/sve_filtracionController.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
                },
                body: payload.toString()
            });

            const data = await res.json();
            if (!data.success) {
                alert(data.message || 'Error al guardar.');
                return;
            }

            setFiltracionForm(null);
            await cargarFiltracion();
        }

        async function eliminarFiltracion(id) {
            if (!confirm('¿Eliminar producto?')) return;

            const payload = new URLSearchParams();
            payload.append('_method', 'delete');
            payload.append('id', id);

            const res = await fetch('/controllers/sve_filtracionController.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
                },
                body: payload.toString()
            });

            const data = await res.json();
            if (!data.success) {
                alert(data.message || 'Error al eliminar.');
                return;
            }

            await cargarFiltracion();
        }

        async function editarFiltracion(id) {
            const res = await fetch(`/controllers/sve_filtracionController.php?id=${id}`);
            const data = await res.json();
            if (!data.success) {
                alert(data.message || 'No se pudo cargar el producto.');
                return;
            }
            setFiltracionForm(data.producto);
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        async function cargarColumna() {
            const tbody = document.getElementById('tablaColumnaBody');
            tbody.innerHTML = '<tr><td colspan="5" class="empty-row">Cargando...</td></tr>';

            try {
                const res = await fetch('/controllers/sve_columnaController.php');
                const data = await res.json();

                if (!data.success) {
                    throw new Error(data.message || 'No se pudo cargar la información.');
                }

                const items = Array.isArray(data.columna) ? data.columna : [];
                if (items.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" class="empty-row">Sin productos cargados.</td></tr>';
                    return;
                }

                tbody.innerHTML = '';
                items.forEach((item) => {
                    const estado = Number(item.activo) === 1 ? 'Sí' : 'No';
                    const fila = document.createElement('tr');
                    fila.innerHTML = `
                        <td>${item.nombre ?? 'Sin nombre'}</td>
                        <td>${item.precio ?? '0.00'}</td>
                        <td>${item.moneda ?? ''}</td>
                        <td><span class="estado-pill">${estado}</span></td>
                        <td>
                            <button class="btn-icon" data-id="${item.id}" data-action="editar" data-tooltip="Editar">
                                <span class="material-icons">edit</span>
                            </button>
                            <button class="btn-icon" data-id="${item.id}" data-action="eliminar" data-tooltip="Eliminar" style="color: red;">
                                <span class="material-icons">delete</span>
                            </button>
                        </td>
                    `;
                    tbody.appendChild(fila);
                });
            } catch (error) {
                tbody.innerHTML = `<tr><td colspan="5" class="empty-row">${error.message}</td></tr>`;
            }
        }

        async function guardarColumna(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            const payload = new URLSearchParams(formData);

            const res = await fetch('/controllers/sve_columnaController.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
                },
                body: payload.toString()
            });

            const data = await res.json();
            if (!data.success) {
                alert(data.message || 'Error al guardar.');
                return;
            }

            setColumnaForm(null);
            await cargarColumna();
        }

        async function eliminarColumna(id) {
            if (!confirm('¿Eliminar producto?')) return;

            const payload = new URLSearchParams();
            payload.append('_method', 'delete');
            payload.append('id', id);

            const res = await fetch('/controllers/sve_columnaController.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
                },
                body: payload.toString()
            });

            const data = await res.json();
            if (!data.success) {
                alert(data.message || 'Error al eliminar.');
                return;
            }

            await cargarColumna();
        }

        async function editarColumna(id) {
            const res = await fetch(`/controllers/sve_columnaController.php?id=${id}`);
            const data = await res.json();
            if (!data.success) {
                alert(data.message || 'No se pudo cargar el producto.');
                return;
            }
            setColumnaForm(data.producto);
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        async function cargarContratos() {
            const tbody = document.getElementById('tablaContratosBody');
            tbody.innerHTML = '<tr><td colspan="5" class="empty-row">Cargando...</td></tr>';

            try {
                const res = await fetch('/controllers/sve_contratosVendimialesController.php');
                const data = await res.json();

                if (!data.success) {
                    throw new Error(data.message || 'No se pudo cargar la información.');
                }

                const items = Array.isArray(data.contratos) ? data.contratos : [];
                if (items.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" class="empty-row">Sin contratos cargados.</td></tr>';
                    return;
                }

                tbody.innerHTML = '';
                items.forEach((item) => {
                    const estado = Number(item.vigente) === 1 ? 'Sí' : 'No';
                    const fila = document.createElement('tr');
                    fila.innerHTML = `
                        <td>${item.nombre ?? 'Sin nombre'}</td>
                        <td>${item.servicio_nombre ?? '-'}</td>
                        <td>${item.version ?? 1}</td>
                        <td><span class="estado-pill">${estado}</span></td>
                        <td>
                            <button class="btn-icon" data-id="${item.id}" data-action="editar" data-tooltip="Editar">
                                <span class="material-icons">edit</span>
                            </button>
                            <button class="btn-icon" data-id="${item.id}" data-action="eliminar" data-tooltip="Eliminar" style="color: red;">
                                <span class="material-icons">delete</span>
                            </button>
                        </td>
                    `;
                    tbody.appendChild(fila);
                });
            } catch (error) {
                tbody.innerHTML = `<tr><td colspan="4" class="empty-row">${error.message}</td></tr>`;
            }
        }

        async function guardarContrato(e) {
            e.preventDefault();
            const form = e.target;
            const hidden = document.getElementById('contrato_contenido');
            if (quillContrato && hidden) {
                hidden.value = quillContrato.root.innerHTML;
            }
            const formData = new FormData(form);
            const payload = new URLSearchParams(formData);

            const res = await fetch('/controllers/sve_contratosVendimialesController.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
                },
                body: payload.toString()
            });

            const data = await res.json();
            if (!data.success) {
                alert(data.message || 'Error al guardar.');
                return;
            }

            setContratoForm(null);
            await cargarContratos();
        }

        async function eliminarContrato(id) {
            if (!confirm('¿Eliminar contrato?')) return;

            const payload = new URLSearchParams();
            payload.append('_method', 'delete');
            payload.append('id', id);

            const res = await fetch('/controllers/sve_contratosVendimialesController.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
                },
                body: payload.toString()
            });

            const data = await res.json();
            if (!data.success) {
                alert(data.message || 'Error al eliminar.');
                return;
            }

            await cargarContratos();
        }

        async function editarContrato(id) {
            const res = await fetch(`/controllers/sve_contratosVendimialesController.php?id=${id}`);
            const data = await res.json();
            if (!data.success) {
                alert(data.message || 'No se pudo cargar el contrato.');
                return;
            }
            setContratoForm(data.contrato);
            await cargarServiciosParaContratos(data.contrato?.servicio_id ?? '');
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }

        async function cargarServiciosParaContratos(selectedId = '') {
            const select = document.getElementById('contrato_servicio');
            if (!select) return;
            select.innerHTML = '<option value="">Cargando...</option>';

            try {
                const res = await fetch('/controllers/sve_serviciosVendimialesController.php');
                const data = await res.json();

                if (!data.success) {
                    throw new Error(data.message || 'No se pudo cargar servicios.');
                }

                const servicios = Array.isArray(data.servicios) ? data.servicios : [];
                if (servicios.length === 0) {
                    select.innerHTML = '<option value="">Sin servicios disponibles</option>';
                    return;
                }

                select.innerHTML = '<option value="">Seleccioná un servicio</option>';
                servicios.forEach((servicio) => {
                    const option = document.createElement('option');
                    option.value = servicio.id;
                    option.textContent = servicio.nombre ?? 'Sin nombre';
                    select.appendChild(option);
                });

                if (selectedId !== '') {
                    select.value = String(selectedId);
                }
            } catch (error) {
                select.innerHTML = `<option value="">${error.message}</option>`;
            }
        }

        async function cargarServiciosContratados() {
            const tbody = document.getElementById('tablaPedidosBody');
            tbody.innerHTML = '<tr><td colspan="9" class="empty-row">Cargando...</td></tr>';

            try {
                const res = await fetch('/controllers/sve_serviciosVendimialesPedidosController.php');
                const data = await res.json();

                if (!data.success) {
                    throw new Error(data.message || 'No se pudo cargar la información.');
                }

                const pedidos = Array.isArray(data.pedidos) ? data.pedidos : [];
                if (pedidos.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="9" class="empty-row">Sin pedidos cargados.</td></tr>';
                    return;
                }

                tbody.innerHTML = '';
                pedidos.forEach((p) => {
                    const volumen = p.volumenAproximado ? `${p.volumenAproximado} ${p.unidad_volumen ?? ''}` : '-';
                    const contrato = p.contrato_aceptado === null ? 'Sin firma' : (Number(p.contrato_aceptado) === 1 ? 'Firmado' : 'No aceptado');
                    const fila = document.createElement('tr');
                    fila.innerHTML = `
                        <td>${p.cooperativa ?? '-'}</td>
                        <td>
                            <div>${p.nombre ?? '-'}</div>
                            <div style="color: #5b21b6; font-size: 0.85rem;">${p.cargo ?? ''}</div>
                        </td>
                        <td>${p.servicio_nombre ?? '-'}</td>
                        <td>${p.producto_nombre ?? '-'}</td>
                        <td>${volumen}</td>
                        <td>${p.centrifugadora_nombre ?? '-'}</td>
                        <td>${p.estado ?? '-'}</td>
                        <td><span class="estado-pill">${contrato}</span></td>
                        <td>
                            <button class="btn-icon" data-id="${p.id}" data-action="editar" data-tooltip="Editar">
                                <span class="material-icons">edit</span>
                            </button>
                            <button class="btn-icon" data-id="${p.id}" data-action="eliminar" data-tooltip="Eliminar" style="color: red;">
                                <span class="material-icons">delete</span>
                            </button>
                        </td>
                    `;
                    tbody.appendChild(fila);
                });
            } catch (error) {
                tbody.innerHTML = `<tr><td colspan="9" class="empty-row">${error.message}</td></tr>`;
            }
        }

        async function cargarServiciosSelectPedido(selectedId = '') {
            const select = document.getElementById('pedido_servicio');
            if (!select) return;
            select.innerHTML = '<option value="">Cargando...</option>';

            try {
                const res = await fetch('/controllers/sve_serviciosVendimialesController.php');
                const data = await res.json();
                if (!data.success) {
                    throw new Error(data.message || 'No se pudo cargar servicios.');
                }
                const servicios = Array.isArray(data.servicios) ? data.servicios : [];
                if (servicios.length === 0) {
                    select.innerHTML = '<option value="">Sin servicios disponibles</option>';
                    return;
                }
                select.innerHTML = '<option value="">Seleccioná un servicio</option>';
                servicios.forEach((servicio) => {
                    const option = document.createElement('option');
                    option.value = servicio.id;
                    option.textContent = servicio.nombre ?? 'Sin nombre';
                    select.appendChild(option);
                });
                if (selectedId !== '') {
                    select.value = String(selectedId);
                }
                await cargarProductosSelectPedido(select.value);
            } catch (error) {
                select.innerHTML = `<option value="">${error.message}</option>`;
            }
        }

        async function cargarProductosSelectPedido(servicioId, selectedId = '') {
            const select = document.getElementById('pedido_producto');
            if (!select) return;
            if (!servicioId) {
                select.innerHTML = '<option value="">Seleccioná un servicio primero</option>';
                return;
            }

            select.innerHTML = '<option value="">Cargando...</option>';

            try {
                const res = await fetch(`/controllers/sve_productosVendimialesController.php?servicio_id=${servicioId}`);
                const data = await res.json();
                if (!data.success) {
                    throw new Error(data.message || 'No se pudo cargar productos.');
                }
                const items = Array.isArray(data.productos) ? data.productos : [];
                if (items.length === 0) {
                    select.innerHTML = '<option value="">Sin productos</option>';
                    return;
                }
                select.innerHTML = '<option value="">Seleccioná un producto</option>';
                items.forEach((item) => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = `${item.nombre ?? 'Sin nombre'} (${item.moneda ?? ''} ${item.precio ?? ''})`;
                    select.appendChild(option);
                });
                if (selectedId !== '') {
                    select.value = String(selectedId);
                }
            } catch (error) {
                select.innerHTML = `<option value="">${error.message}</option>`;
            }
        }

        async function cargarCentrifugadorasSelectPedido(selectedId = '') {
            const select = document.getElementById('pedido_equipo');
            if (!select) return;
            select.innerHTML = '<option value="">Cargando...</option>';

            try {
                const res = await fetch('/controllers/sve_centrifugadoresController.php');
                const data = await res.json();
                if (!data.success) {
                    throw new Error(data.message || 'No se pudo cargar centrifugadoras.');
                }
                const items = Array.isArray(data.centrifugadoras) ? data.centrifugadoras : [];
                select.innerHTML = '<option value="">Sin seleccionar</option>';
                items.forEach((item) => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = `${item.nombre ?? 'Sin nombre'} (${item.moneda ?? ''} ${item.precio ?? ''})`;
                    select.appendChild(option);
                });
                if (selectedId !== '') {
                    select.value = String(selectedId);
                }
            } catch (error) {
                select.innerHTML = `<option value="">${error.message}</option>`;
            }
        }

        async function editarPedido(id) {
            const res = await fetch(`/controllers/sve_serviciosVendimialesPedidosController.php?id=${id}`);
            const data = await res.json();
            if (!data.success) {
                alert(data.message || 'No se pudo cargar el pedido.');
                return;
            }
            await cargarCooperativasSelectPedido(data.pedido?.cooperativa ?? '');
            await cargarServiciosSelectPedido(data.pedido?.servicioAcontratar ?? '');
            await cargarCentrifugadorasSelectPedido(data.pedido?.equipo_centrifugadora ?? '');
            await cargarProductosSelectPedido(data.pedido?.servicioAcontratar ?? '', data.pedido?.producto_id ?? '');
            setPedidoForm(data.pedido);
            openModalPedidoEdit();
        }

        async function eliminarPedido(id) {
            if (!confirm('¿Eliminar servicio contratado?')) return;

            const payload = new URLSearchParams();
            payload.append('_method', 'delete');
            payload.append('id', id);

            const res = await fetch('/controllers/sve_serviciosVendimialesPedidosController.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
                },
                body: payload.toString()
            });

            const data = await res.json();
            if (!data.success) {
                alert(data.message || 'Error al eliminar.');
                return;
            }

            await cargarServiciosContratados();
        }

        async function guardarPedidoEdit(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            const payload = new URLSearchParams(formData);

            const res = await fetch('/controllers/sve_serviciosVendimialesPedidosController.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8'
                },
                body: payload.toString()
            });

            const data = await res.json();
            if (!data.success) {
                alert(data.message || 'Error al guardar.');
                return;
            }

            closeModalPedidoEdit();
            await cargarServiciosContratados();
        }

        async function cargarCooperativasSelectPedido(selectedValue = '') {
            const select = document.getElementById('pedido_cooperativa');
            if (!select) return;
            select.innerHTML = '<option value="">Cargando...</option>';

            try {
                const res = await fetch('/controllers/sve_serviciosVendimialesPedidosController.php?action=cooperativas');
                const data = await res.json();
                if (!data.success) {
                    throw new Error(data.message || 'No se pudo cargar cooperativas.');
                }
                const cooperativas = Array.isArray(data.cooperativas) ? data.cooperativas : [];
                if (cooperativas.length === 0) {
                    select.innerHTML = '<option value="">Sin cooperativas</option>';
                    return;
                }
                select.innerHTML = '<option value="">Seleccioná cooperativa</option>';
                cooperativas.forEach((coop) => {
                    const option = document.createElement('option');
                    option.value = coop.valor ?? '';
                    option.textContent = coop.texto ?? coop.valor ?? 'Sin nombre';
                    select.appendChild(option);
                });
                if (selectedValue !== '') {
                    select.value = String(selectedValue);
                }
            } catch (error) {
                select.innerHTML = `<option value="">${error.message}</option>`;
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            setForm(null);
            document.getElementById('formServicio').addEventListener('submit', guardarServicio);

            document.getElementById('tablaServiciosBody').addEventListener('click', (e) => {
                const btn = e.target.closest('button[data-action]');
                if (!btn) return;
                const id = btn.getAttribute('data-id');
                const action = btn.getAttribute('data-action');
                if (action === 'editar') {
                    editarServicio(id);
                }
                if (action === 'eliminar') {
                    eliminarServicio(id);
                }
            });

            const modalServicios = document.getElementById('modalServiciosOfrecidos');
            if (modalServicios) {
                modalServicios.addEventListener('click', (e) => {
                    if (e.target === modalServicios) {
                        closeModalServiciosOfrecidos();
                    }
                });
            }

            setCentrifugadoraForm(null);
            document.getElementById('formCentrifugadora').addEventListener('submit', guardarCentrifugadora);

            document.getElementById('tablaCentrifugadorasBody').addEventListener('click', (e) => {
                const btn = e.target.closest('button[data-action]');
                if (!btn) return;
                const id = btn.getAttribute('data-id');
                const action = btn.getAttribute('data-action');
                if (action === 'editar') {
                    editarCentrifugadora(id);
                }
                if (action === 'eliminar') {
                    eliminarCentrifugadora(id);
                }
            });

            const modalCentrifugadoras = document.getElementById('modalCentrifugadoras');
            if (modalCentrifugadoras) {
                modalCentrifugadoras.addEventListener('click', (e) => {
                    if (e.target === modalCentrifugadoras) {
                        closeModalCentrifugadoras();
                    }
                });
            }

            setFiltracionForm(null);
            document.getElementById('formFiltracion').addEventListener('submit', guardarFiltracion);

            document.getElementById('tablaFiltracionBody').addEventListener('click', (e) => {
                const btn = e.target.closest('button[data-action]');
                if (!btn) return;
                const id = btn.getAttribute('data-id');
                const action = btn.getAttribute('data-action');
                if (action === 'editar') {
                    editarFiltracion(id);
                }
                if (action === 'eliminar') {
                    eliminarFiltracion(id);
                }
            });

            const modalFiltracion = document.getElementById('modalFiltracion');
            if (modalFiltracion) {
                modalFiltracion.addEventListener('click', (e) => {
                    if (e.target === modalFiltracion) {
                        closeModalFiltracion();
                    }
                });
            }

            setColumnaForm(null);
            document.getElementById('formColumna').addEventListener('submit', guardarColumna);

            document.getElementById('tablaColumnaBody').addEventListener('click', (e) => {
                const btn = e.target.closest('button[data-action]');
                if (!btn) return;
                const id = btn.getAttribute('data-id');
                const action = btn.getAttribute('data-action');
                if (action === 'editar') {
                    editarColumna(id);
                }
                if (action === 'eliminar') {
                    eliminarColumna(id);
                }
            });

            const modalColumna = document.getElementById('modalColumna');
            if (modalColumna) {
                modalColumna.addEventListener('click', (e) => {
                    if (e.target === modalColumna) {
                        closeModalColumna();
                    }
                });
            }

            const editorContainer = document.getElementById('contrato_editor');
            if (editorContainer) {
                quillContrato = new Quill('#contrato_editor', {
                    theme: 'snow',
                    modules: {
                        toolbar: [
                            ['bold', 'underline'],
                            [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                            [{ 'indent': '-1' }, { 'indent': '+1' }]
                        ]
                    }
                });
            }

            setContratoForm(null);
            cargarServiciosParaContratos();
            document.getElementById('formContrato').addEventListener('submit', guardarContrato);

            document.getElementById('tablaContratosBody').addEventListener('click', (e) => {
                const btn = e.target.closest('button[data-action]');
                if (!btn) return;
                const id = btn.getAttribute('data-id');
                const action = btn.getAttribute('data-action');
                if (action === 'editar') {
                    editarContrato(id);
                }
                if (action === 'eliminar') {
                    eliminarContrato(id);
                }
            });

            const modalContratos = document.getElementById('modalContratos');
            if (modalContratos) {
                modalContratos.addEventListener('click', (e) => {
                    if (e.target === modalContratos) {
                        closeModalContratos();
                    }
                });
            }

            document.getElementById('formPedidoEdit').addEventListener('submit', guardarPedidoEdit);

            document.getElementById('tablaPedidosBody').addEventListener('click', (e) => {
                const btn = e.target.closest('button[data-action]');
                if (!btn) return;
                const id = btn.getAttribute('data-id');
                const action = btn.getAttribute('data-action');
                if (action === 'editar') {
                    editarPedido(id);
                }
                if (action === 'eliminar') {
                    eliminarPedido(id);
                }
            });

            const modalPedido = document.getElementById('modalPedidoEdit');
            if (modalPedido) {
                modalPedido.addEventListener('click', (e) => {
                    if (e.target === modalPedido) {
                        closeModalPedidoEdit();
                    }
                });
            }

            const servicioSelectPedido = document.getElementById('pedido_servicio');
            if (servicioSelectPedido) {
                servicioSelectPedido.addEventListener('change', (e) => {
                    cargarProductosSelectPedido(e.target.value);
                });
            }

            cargarServiciosContratados();
        });
    </script>

</body>

</html>
