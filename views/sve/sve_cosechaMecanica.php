<?php
declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
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
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <!-- Framework Success desde CDN -->
    <link rel="stylesheet" href="https://framework.impulsagroup.com/assets/css/framework.css">
    <script src="https://framework.impulsagroup.com/assets/javascript/framework.js" defer></script>

    <!-- Estilos específicos Cosecha Mecánica -->
    <style>
        /* Altura máxima de la tabla de contratos - ajustar manualmente si es necesario */
        .tabla-wrapper.cosecha-table {
            max-height: 480px;
            overflow-y: auto;
        }

        .cosecha-filtros-card {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .cosecha-filtros-row {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: flex-end;
        }

        .cosecha-filtros-row .input-group {
            flex: 1 1 220px;
        }

        .cosecha-filtros-row .btn-nuevo-contrato {
            flex: 0 0 auto;
        }

        @media (max-width: 768px) {
            .cosecha-filtros-row {
                flex-direction: column;
                align-items: stretch;
            }

            .cosecha-filtros-row .btn-nuevo-contrato {
                width: 100%;
            }
        }

        .cosecha-acciones {
            display: flex;
            gap: 0.25rem;
            justify-content: center;
            align-items: center;
        }

        .cosecha-btn-icon {
            border: none;
            background: transparent;
            cursor: pointer;
            padding: 0.35rem;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.1s ease-out, box-shadow 0.1s ease-out, background-color 0.1s ease-out;
        }

        .cosecha-btn-icon:focus-visible {
            outline: 2px solid #4f46e5;
            outline-offset: 2px;
        }

        .cosecha-btn-icon:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.15);
        }

        .cosecha-btn-icon.view {
            color: #2563eb;
            background-color: rgba(37, 99, 235, 0.06);
        }

        .cosecha-btn-icon.coop {
            color: #16a34a;
            background-color: rgba(22, 163, 74, 0.06);
        }

        .cosecha-btn-icon.delete {
            color: #dc2626;
            background-color: rgba(220, 38, 38, 0.06);
        }

        .cosecha-btn-icon .material-icons,
        .cosecha-btn-icon .material-symbols-outlined {
            font-size: 20px;
        }

        .badge.estado-borrador {
            background-color: #e5e7eb;
            color: #374151;
        }

        .badge.estado-abierto {
            background-color: #dcfce7;
            color: #166534;
        }

        .badge.estado-cerrado {
            background-color: #e0f2fe;
            color: #1d4ed8;
        }

        .badge.estado-cancelado {
            background-color: #fee2e2;
            color: #b91c1c;
        }

        /* Modales de Cosecha Mecánica: permiten que el contenido controle el alto */
        .modal .modal-content {
            max-width: 720px;
            width: 100%;
        }

        @media (max-width: 640px) {
            .modal .modal-content {
                max-width: 100%;
                margin: 1.5rem;
            }
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
                    <span class="material-symbols-outlined" style="color:#5b21b6;">drone</span>
                    <span class="link-text">Cosecha Mecánica</span>
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
                <div class="navbar-title">Cosecha Mecánica</div>
            </header>

            <!-- 📦 CONTENIDO -->
            <section class="content">

                <!-- Bienvenida -->
                <div class="card">
                    <h2>Hola 👋</h2>
                    <p>En esta página vamos a crear los contratos y vamos a poder visualizar las cooperativas que confirmaron asistencia con sus respectivos productores</p>
                </div>

                <div class="card-grid grid-2">
                    <!-- Tarjeta: Carga de Cooperativas -->
                    <div class="card">
                        <h3>Cargar Usuarios</h3>
                        <input type="file" id="csvCooperativas" accept=".csv" />
                        <button class="btn btn-info" onclick="previewCSV('cooperativas')">Previsualizar</button>
                        <div id="previewCooperativas" class="csv-preview"></div>
                        <button class="btn btn-aceptar" onclick="confirmarCarga('cooperativas')">Confirmar carga</button>
                    </div>
                </div>
            </section>

        </div>
    </div>

    <!-- script principal  -->
    <script>
        window.confirmarCarga = function(tipo) {
                   // Enviar al servidor
            fetch('../../controllers/sve_cosechaMecanicaController.php', {
                    method: 'POST',
                    body: formData
                })
        };
    </script>

    <!-- Spinner Global -->
    <script src="../../views/partials/spinner-global.js"></script>
</body>

</html>