<?php
// Mostrar errores en pantalla (útil en desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/// Iniciar sesión y configurar parámetros de seguridad
require_once '../../middleware/authMiddleware.php';
checkAccess('cooperativa');

// Datos del usuario en sesión
$nombre = $_SESSION['nombre'] ?? 'Sin nombre';
$correo = $_SESSION['correo'] ?? 'Sin correo';
$cuit = $_SESSION['cuit'] ?? 'Sin CUIT';
$telefono = $_SESSION['telefono'] ?? 'Sin teléfono';
$observaciones = $_SESSION['observaciones'] ?? 'Sin observaciones';

// Campos adicionales para cooperativa
$id_cooperativa_real = $_SESSION['id_real'] ?? null; // usamos el ID real de la sesión
$id_cooperativa = $_SESSION['id_cooperativa'] ?? null;
$id_productor = $_SESSION['id_productor'] ?? null;
$direccion = $_SESSION['direccion'] ?? 'Sin dirección';
$id_finca_asociada = $_SESSION['id_finca_asociada'] ?? null;

// Verificar si el ID de la cooperativa está disponible
echo "<script>console.log('🟣 id_cooperativa desde PHP: " . $id_cooperativa_real . "');</script>";
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
                    <li onclick="location.href='coop_dashboard.php'">
                        <span class="material-icons" style="color: #5b21b6;">home</span><span class="link-text">Inicio</span>
                    </li>
                    <li onclick="location.href='coop_mercadoDigital.php'">
                        <span class="material-icons"
                            style="color: #5b21b6;">shopping_cart</span><span class="link-text">Mercado Digital</span>
                    </li>
                    <li onclick="location.href='coop_listadoPedidos.php'">
                        <span class="material-icons"
                            style="color: #5b21b6;">receipt_long</span><span class="link-text">Listado Pedidos</span>
                    </li>
                    <li onclick="location.href='coop_consolidado.php'">
                        <span class="material-icons"
                            style="color: #5b21b6;">analytics</span><span class="link-text">Consolidado</span>
                    </li>
                    <li onclick="location.href='coop_pulverizacion.php'">
                        <span class="material-symbols-outlined"
                            style="color:#5b21b6;">drone</span><span class="link-text">Pulverización con Drone</span>
                    </li>
                    <li onclick="location.href='coop_usuarioInformacion.php'">
                        <span class="material-icons" style="color: #5b21b6;">person</span><span
                            class="link-text">Productores</span>
                    </li>
                    <li onclick="location.href='coop_cosechaMecanicaView.php'">
                        <span class="material-icons"
                            style="color: #5b21b6;">agriculture</span><span class="link-text">Cosecha Mecanica</span>
                    </li>
                    <li onclick="location.href='coop_serviciosVendimiales.php'">
                        <span class="material-icons" style="color:#5b21b6;">wine_bar</span>
                        <span class="link-text">Servicios Auxiliares Enológicos</span>
                    </li>
                    <li onclick="location.href='../../../logout.php'">
                        <span class="material-icons" style="color: red;">logout</span><span
                            class="link-text">Salir</span>
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
                <div class="navbar-title">Productores</div>
            </header>

            <!-- 📦 CONTENIDO -->
            <section class="content">

                <!-- Bienvenida -->
                <div class="card">
                    <h4><?php echo htmlspecialchars($nombre); ?>, estas es la página "Productores"</h4>
                    <p>Podes inscribir a un nuevo productor a tu cooperativa y además, modificar sus datos</p>
                </div>

                <!-- Tarjeta de buscador -->
                <div class="card">
                    <h2>Busca productores</h2>
                    <form class="form-modern">
                        <div class="form-grid grid-3">
                            <!-- Buscar por CUIT -->
                            <div class="input-group">
                                <label for="buscarCuit">Podes buscar por CUIT</label>
                                <div class="input-icon">
                                    <span class="material-icons">fingerprint</span>
                                    <input type="text" id="buscarCuit" name="buscarCuit" placeholder="20123456781">
                                </div>
                            </div>

                            <!-- Buscar por Nombre -->
                            <div class="input-group">
                                <label for="buscarNombre">Podes buscar por nombre</label>
                                <div class="input-icon">
                                    <span class="material-icons">person</span>
                                    <input type="text" id="buscarNombre" name="buscarNombre" placeholder="Ej: Juan Pérez">
                                </div>
                            </div>

                            <!-- Filtro por asociación -->
                            <div class="input-group">
                                <label for="filtroAsociacion">Filtrar por asociación</label>
                                <div class="input-icon">
                                    <span class="material-icons">filter_list</span>
                                    <select id="filtroAsociacion">
                                        <option value="">Todos</option>
                                        <option value="asociado">Asociados</option>
                                        <option value="no_asociado">No asociados</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Tabla -->
                <div class="card">
                    <h2>Asociar productores con cooperativas</h2>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID Real</th>
                                    <th>Nombre</th>
                                    <th>CUIT</th>
                                    <th>Cooperativa</th>
                                </tr>
                            </thead>
                            <tbody id="tablaAsociaciones">
                                <!-- Contenido dinámico -->
                            </tbody>
                        </table>
                    </div>
                </div>

            </section>

        </div>
    </div>

    <script>
        function asociarProductor(select, id_productor) {
            const id_cooperativa = select.value;

            if (!id_cooperativa) return;

            // 📌 Mostrar lo que se va a enviar
            const payload = {
                id_productor: id_productor,
                id_cooperativa: id_cooperativa
            };
            console.log("📤 Enviando al controlador:", payload);

            fetch('/controllers/coop_asociarProductoresController.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(payload)
                })
                .then(res => res.json())
                .then(data => {
                    console.log("📥 Respuesta del controlador:", data);
                    if (data.success) {
                        showAlert('success', data.message);
                    } else {
                        showAlert('error', data.message);
                    }
                })
                .catch(err => {
                    console.error('❌ Error en la asociación:', err);
                    showAlert('error', 'Error inesperado al asociar productor.');
                });
        }


        // cargar la tabla
        document.addEventListener('DOMContentLoaded', () => {
            fetch('/controllers/coop_asociarProductoresController.php')
                .then(res => res.text())
                .then(html => {
                    document.getElementById('tablaAsociaciones').innerHTML = html;
                })
                .catch(err => {
                    console.error('❌ Error al cargar asociaciones:', err);
                    document.getElementById('tablaAsociaciones').innerHTML = "<tr><td colspan='4'>Error al cargar datos</td></tr>";
                });

            // filtro para los productores que tienen una cooperativa asociada
            document.getElementById('filtroAsociacion').addEventListener('change', cargarProductores);

        });

        async function cargarProductores() {
            const tabla = document.getElementById('tablaAsociaciones');
            try {
                const res = await fetch('/controllers/coop_asociarProductoresController.php');
                const html = await res.text();
                tabla.innerHTML = html;
            } catch (err) {
                tabla.innerHTML = '<tr><td colspan="4">Error al cargar datos.</td></tr>';
                console.error('Error al cargar productores:', err);
            }
        }

        document.addEventListener('DOMContentLoaded', cargarProductores);


        // buscador por cuit o nombre de productor
        document.addEventListener('DOMContentLoaded', () => {
            // Cargar tabla al inicio
            cargarProductores();

            // Escuchar inputs
            document.getElementById('buscarCuit').addEventListener('input', cargarProductores);
            document.getElementById('buscarNombre').addEventListener('input', cargarProductores);
        });

        function cargarProductores() {
            const cuit = document.getElementById('buscarCuit').value.trim();
            const nombre = document.getElementById('buscarNombre').value.trim();
            const filtroAsociacion = document.getElementById('filtroAsociacion').value;

            fetch(`/controllers/coop_asociarProductoresController.php?cuit=${encodeURIComponent(cuit)}&nombre=${encodeURIComponent(nombre)}&filtro=${encodeURIComponent(filtroAsociacion)}`)
                .then(res => res.text())
                .then(html => {
                    document.getElementById('tablaAsociaciones').innerHTML = html;
                })
                .catch(err => {
                    console.error('❌ Error al cargar productores:', err);
                    document.getElementById('tablaAsociaciones').innerHTML = '<tr><td colspan="6">Error al cargar datos.</td></tr>';
                });
        }
    </script>


    <!-- Spinner Global -->
    <script src="../../views/partials/spinner-global.js"></script>

</body>

</html>