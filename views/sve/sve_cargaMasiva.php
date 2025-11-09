<?php
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
                <div class="navbar-title">Carga masiva de usuarios</div>
            </header>

            <!-- 📦 CONTENIDO -->
            <section class="content">

                <!-- Bienvenida -->
                <div class="card">
                    <h2>Hola 👋</h2>
                    <p>En esta página vamos a cargar masivamente los usuarios en nuestro sistema. Recordá que solo podemos cargar archivos con extensión CSV.</p>
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

                    <!-- Tarjeta: Carga de relaciones -->
                    <div class="card">
                        <h3>Cargar relaciones productores ↔ cooperativas</h3>
                        <input type="file" id="csvRelaciones" accept=".csv" />
                        <button class="btn btn-info" onclick="previewCSV('relaciones')">Previsualizar</button>
                        <div id="previewRelaciones" class="csv-preview"></div>
                        <button class="btn btn-aceptar" onclick="confirmarCarga('relaciones')">Confirmar carga</button>
                    </div>
                </div>
            </section>

        </div>
    </div>

    <!-- script principal  -->
    <script>
        window.previewCSV = function(tipo) {
            const inputFile = document.getElementById('csv' + capitalize(tipo));
            const previewDiv = document.getElementById('preview' + capitalize(tipo));

            if (!inputFile.files.length) {
                alert("Por favor seleccioná un archivo CSV.");
                return;
            }

            const file = inputFile.files[0];
            const reader = new FileReader();

            reader.onload = function(e) {
                const contenido = e.target.result;
                const filas = contenido.split('\n').map(fila => fila.split(';'));
                renderPreview(filas, previewDiv);
            };

            reader.readAsText(file);
        }

        function renderPreview(filas, container) {
            if (!filas.length) {
                container.innerHTML = "<p>No se pudo leer el archivo.</p>";
                return;
            }

            let html = '<table class="table"><thead><tr>';
            filas[0].forEach(col => {
                html += '<th>' + escapeHtml(col) + '</th>';
            });
            html += '</tr></thead><tbody>';

            for (let i = 1; i < filas.length; i++) {
                if (filas[i].length === 1 && filas[i][0].trim() === '') continue;
                html += '<tr>';
                filas[i].forEach(col => {
                    html += '<td>' + escapeHtml(col) + '</td>';
                });
                html += '</tr>';
            }

            html += '</tbody></table>';
            container.innerHTML = html;
        }

        window.confirmarCarga = function(tipo) {
            const inputFile = document.getElementById('csv' + capitalize(tipo));
            if (!inputFile.files.length) {
                alert("Seleccioná un archivo para cargar.");
                return;
            }

            const file = inputFile.files[0];
            const formData = new FormData();
            formData.append('archivo', file);
            formData.append('tipo', tipo);

            // 🔍 NUEVO: mostrar contenido del archivo CSV antes de enviar
            const reader = new FileReader();
            reader.onload = function(e) {
                console.log("📦 CSV a enviar (tipo:", tipo, "):");
                console.log(e.target.result);
            };
            reader.readAsText(file);

            // Enviar al servidor
            fetch('../../controllers/sve_cargaMasivaController.php', {
                    method: 'POST',
                    body: formData
                })
                .then(async resp => {
                    const text = await resp.text();
                    console.log("🔎 Respuesta cruda del servidor:", text);
                    try {
                        const data = JSON.parse(text);
                        alert(data.mensaje || "Carga completada.");
                    } catch (e) {
                        console.error("❌ Error al parsear JSON:", e);
                        alert("El servidor devolvió una respuesta inválida.");
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert("Ocurrió un error al subir el archivo.");
                });
        };



        function capitalize(str) {
            return str.charAt(0).toUpperCase() + str.slice(1);
        }

        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) {
                return map[m];
            });
        }
    </script>

    <!-- Spinner Global -->
    <script src="../../views/partials/spinner-global.js"></script>
</body>

</html>