<?php
// Mostrar errores en pantalla (Ãºtil en desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sesiÃ³n y configurar parÃ¡metros de seguridad
require_once '../../middleware/authMiddleware.php';
checkAccess('sve');

// Datos del usuario en sesiÃ³n
$nombre = $_SESSION['nombre'] ?? 'Sin nombre';
$correo = $_SESSION['correo'] ?? 'Sin correo';
$cuit = $_SESSION['cuit'] ?? 'Sin CUIT';
$telefono = $_SESSION['telefono'] ?? 'Sin telÃ©fono';
$observaciones = $_SESSION['observaciones'] ?? 'Sin observaciones';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SVE</title>

    <!-- Ãconos de Material Design -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <!-- Framework Success desde CDN -->
    <link rel="stylesheet" href="https://framework.impulsagroup.com/assets/css/framework.css">
    <script src="https://framework.impulsagroup.com/assets/javascript/framework.js" defer></script>
</head>

<style>
    .badge-chip {
        display: inline-flex;
        align-items: center;
        gap: .25rem;
        padding: .25rem .5rem;
        border-radius: 9999px;
        background: var(--clr-surface-200, #f2f2f2);
        margin: .125rem .25rem;
    }

    .badge-chip .remove {
        border: none;
        background: transparent;
        cursor: pointer;
        font-size: 0.9rem;
        line-height: 1;
    }

    .badge-chip .remove:focus {
        outline: 2px solid #5b21b6;
        outline-offset: 2px;
        border-radius: 4px;
    }
</style>


<body>

    <!-- ðŸ”² CONTENEDOR PRINCIPAL -->
    <div class="layout">

        <!-- ðŸ§­ SIDEBAR -->
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
                    <li onclick="location.href='sve_relevamiento.php'">
                        <span class="material-icons" style="color:#5b21b6;">fact_check</span>
                        <span class="link-text">Relevamiento</span>
                    </li>
                    <li onclick="location.href='sve_cosechaMecanica.php'">
                        <span class="material-icons" style="color:#5b21b6;">agriculture</span>
                        <span class="link-text">Cosecha MecÃ¡nica</span>
                    </li>
                    <li onclick="location.href='sve_serviciosVendimiales.php'">
                        <span class="material-icons" style="color:#5b21b6;">wine_bar</span>
                        <span class="link-text">Servicios Auxiliares EnolÃ³gicos</span>
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

        <!-- ðŸ§± MAIN -->
        <div class="main">

            <!-- ðŸŸª NAVBAR -->
            <header class="navbar">
                <button class="btn-icon" onclick="toggleSidebar()">
                    <span class="material-icons">menu</span>
                </button>
                <div class="navbar-title">Asociaciones</div>
            </header>

            <!-- ðŸ“¦ CONTENIDO -->
            <section class="content">

                <!-- Bienvenida -->
                <div class="card">
                    <h2>Hola ðŸ‘‹</h2>
                    <p>En esta pÃ¡gina, vamos a asignar a los usuarios productores, sus ingenieros, tecnicos, cooperativas, etc.</p>
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
                                    <input type="text" id="buscarNombre" name="buscarNombre" placeholder="Ej: Juan PÃ©rez">
                                </div>
                            </div>

                            <!-- Filtro por asociaciÃ³n -->
                            <div class="input-group">
                                <label for="filtroAsociacion">Filtrar por asociaciÃ³n</label>
                                <div class="input-icon">
                                    <span class="material-icons">filter_list</span>
                                    <select id="filtroAsociacion">
                                        <option value="">Todos</option>
                                        <option value="asociado">Solo asociados</option>
                                        <option value="no_asociado">Solo no asociados</option>
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
                                <!-- Contenido dinÃ¡mico -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ## ingenieros cooperativa ## -->

                <!-- Tarjeta de filtros Coop â‡„ Ing -->
                <div class="card" id="cardFiltrosCoopIng">
                    <h2>Filtros Cooperativas / Ingenieros</h2>
                    <form class="form-modern" aria-labelledby="filtros-coop-ing">
                        <div class="form-grid grid-3">
                            <div class="input-group">
                                <label for="buscarCuitCoop">CUIT Cooperativa</label>
                                <div class="input-icon">
                                    <span class="material-icons">badge</span>
                                    <input type="text" id="buscarCuitCoop" name="buscarCuitCoop" placeholder="3070..." autocomplete="off">
                                </div>
                            </div>
                            <div class="input-group">
                                <label for="buscarNombreCoop">Nombre Cooperativa</label>
                                <div class="input-icon">
                                    <span class="material-icons">group</span>
                                    <input type="text" id="buscarNombreCoop" name="buscarNombreCoop" placeholder="Ej: Coop. Valle" autocomplete="off">
                                </div>
                            </div>
                            <div class="input-group">
                                <label for="buscarNombreIng">Nombre Ingeniero</label>
                                <div class="input-icon">
                                    <span class="material-icons">engineering</span>
                                    <input type="text" id="buscarNombreIng" name="buscarNombreIng" placeholder="Ej: Ana GÃ³mez" autocomplete="off">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Tabla Cooperativas â†” Ingenieros -->
                <div class="card" id="cardCoopIng">
                    <h2>Asociar cooperativas con ingenieros</h2>
                    <div class="table-container" aria-live="polite">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID Real</th>
                                    <th>Cooperativa</th>
                                    <th>Ingenieros vinculados</th>
                                    <th>Agregar ingeniero</th>
                                </tr>
                            </thead>
                            <tbody id="tablaCoopIng">
                                <!-- Contenido dinÃ¡mico -->
                            </tbody>
                        </table>
                    </div>
                </div>


                <!-- Alert -->
                <div class="alert-container" id="alertContainer"></div>
            </section>

        </div>
    </div>

    <!-- javascrip -->

    <!-- javascript -->
    <script>
        // Utilidades
        const debounce = (fn, wait = 300) => {
            let t;
            return (...args) => {
                clearTimeout(t);
                t = setTimeout(() => fn.apply(null, args), wait);
            };
        };

        // ------- Productor â†” Cooperativa (existente) -------
        async function asociarProductor(select, id_productor) {
            const id_cooperativa = select.value;
            if (!id_cooperativa) return;

            select.disabled = true;
            try {
                const res = await fetch('/controllers/sve_asociarProductoresController.php?action=asociar_prod_coop', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        id_productor,
                        id_cooperativa
                    })
                });
                const data = await res.json();
                if (data.ok) {
                    showAlert('success', 'AsociaciÃ³n guardada correctamente.');
                } else {
                    showAlert('error', data.error || 'No se pudo guardar la asociaciÃ³n.');
                }
            } catch (err) {
                console.error('âŒ Error en la asociaciÃ³n productor-coop:', err);
                showAlert('error', 'Error inesperado al asociar productor.');
            } finally {
                select.disabled = false;
            }
        }

        async function cargarProductores() {
            const cuit = document.getElementById('buscarCuit').value.trim();
            const nombre = document.getElementById('buscarNombre').value.trim();
            const filtroAsociacion = document.getElementById('filtroAsociacion').value;
            const tbody = document.getElementById('tablaAsociaciones');
            tbody.setAttribute('aria-busy', 'true');
            try {
                const res = await fetch(`/controllers/sve_asociarProductoresController.php?cuit=${encodeURIComponent(cuit)}&nombre=${encodeURIComponent(nombre)}&filtro=${encodeURIComponent(filtroAsociacion)}`);
                const html = await res.text();
                tbody.innerHTML = html;
            } catch (err) {
                console.error('âŒ Error al cargar productores:', err);
                tbody.innerHTML = "<tr><td colspan='4'>Error al cargar datos.</td></tr>";
            } finally {
                tbody.removeAttribute('aria-busy');
            }
        }

        // ------- Cooperativa â†” Ingeniero (nuevo) -------
        async function cargarCoopIng() {
            const cuitCoop = document.getElementById('buscarCuitCoop').value.trim();
            const nombreCoop = document.getElementById('buscarNombreCoop').value.trim();
            const nombreIng = document.getElementById('buscarNombreIng').value.trim();
            const tbody = document.getElementById('tablaCoopIng');
            tbody.setAttribute('aria-busy', 'true');
            try {
                const url = `/controllers/sve_asociarProductoresController.php?action=coop_ing&cuit_coop=${encodeURIComponent(cuitCoop)}&nombre_coop=${encodeURIComponent(nombreCoop)}&nombre_ing=${encodeURIComponent(nombreIng)}`;
                const res = await fetch(url);
                const html = await res.text();
                tbody.innerHTML = html;
            } catch (err) {
                console.error('âŒ Error al cargar coop/ing:', err);
                tbody.innerHTML = "<tr><td colspan='4'>Error al cargar datos.</td></tr>";
            } finally {
                tbody.removeAttribute('aria-busy');
            }
        }

        async function addCoopIng(select, coopIdReal) {
            const ingIdReal = select.value;
            if (!ingIdReal) return;
            select.disabled = true;
            try {
                const res = await fetch('/controllers/sve_asociarProductoresController.php?action=add_coop_ing', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        cooperativa_id_real: coopIdReal,
                        ingeniero_id_real: ingIdReal
                    })
                });
                const data = await res.json();
                if (data.ok) {
                    showAlert('success', 'Ingeniero vinculado.');
                    await cargarCoopIng();
                } else {
                    showAlert('error', data.error || 'No se pudo vincular.');
                }
            } catch (err) {
                console.error('âŒ Error al vincular coop/ing:', err);
                showAlert('error', 'Error inesperado al vincular.');
            } finally {
                select.disabled = false;
                select.value = '';
            }
        }

        async function delCoopIng(coopIdReal, ingIdReal, btn) {
            if (!confirm('Â¿Quitar esta vinculaciÃ³n?')) return;
            btn.disabled = true;
            try {
                const res = await fetch('/controllers/sve_asociarProductoresController.php?action=del_coop_ing', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        cooperativa_id_real: coopIdReal,
                        ingeniero_id_real: ingIdReal
                    })
                });
                const data = await res.json();
                if (data.ok) {
                    showAlert('success', 'VinculaciÃ³n eliminada.');
                    await cargarCoopIng();
                } else {
                    showAlert('error', data.error || 'No se pudo eliminar.');
                }
            } catch (err) {
                console.error('âŒ Error al eliminar vinculaciÃ³n:', err);
                showAlert('error', 'Error inesperado al eliminar.');
            } finally {
                btn.disabled = false;
            }
        }

        // ------- Init & Listeners (Ãºnicos) -------
        document.addEventListener('DOMContentLoaded', () => {
            // Inicial
            cargarProductores();
            cargarCoopIng();

            // Productores (con debounce)
            const debouncedProd = debounce(cargarProductores, 300);
            document.getElementById('buscarCuit').addEventListener('input', debouncedProd);
            document.getElementById('buscarNombre').addEventListener('input', debouncedProd);
            document.getElementById('filtroAsociacion').addEventListener('change', cargarProductores);

            // Coop/Ing (con debounce)
            const debouncedCoopIng = debounce(cargarCoopIng, 300);
            document.getElementById('buscarCuitCoop').addEventListener('input', debouncedCoopIng);
            document.getElementById('buscarNombreCoop').addEventListener('input', debouncedCoopIng);
            document.getElementById('buscarNombreIng').addEventListener('input', debouncedCoopIng);
        });
    </script>

    <!-- Spinner Global -->
    <script src="../../views/partials/spinner-global.js"></script>
</body>

</html>

