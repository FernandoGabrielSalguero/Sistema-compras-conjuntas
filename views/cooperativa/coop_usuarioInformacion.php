<?php
// Mostrar errores en pantalla (útil en desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión y proteger acceso
session_start();

// ⚠️ Expiración por inactividad (20 minutos)
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1200)) {
    session_unset();
    session_destroy();
    header("Location: /index.php?expired=1");
    exit;
}
$_SESSION['LAST_ACTIVITY'] = time();

// 🚧 Protección de acceso general
if (!isset($_SESSION['cuit'])) {
    die("⚠️ Acceso denegado. No has iniciado sesión.");
}

// 🔐 Protección por rol
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'cooperativa') {
    die("🚫 Acceso restringido: esta página es solo para usuarios cooperativa.");
}

//Cargamos los operativos cerrados
$cierre_info = $_SESSION['cierre_info'] ?? null;
unset($_SESSION['cierre_info']);

// Datos del usuario en sesión
$nombre = $_SESSION['nombre'] ?? 'Sin nombre';
$correo = $_SESSION['correo'] ?? 'Sin correo';
$usuario = $_SESSION['usuario'] ?? 'Sin usuario';
$telefono = $_SESSION['telefono'] ?? 'Sin teléfono';

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SVE</title>

    <!-- Íconos de Material Design -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />

    <!-- Framework Success desde CDN -->
    <link rel="stylesheet" href="https://www.fernandosalguero.com/cdn/assets/css/framework.css">
    <script src="https://www.fernandosalguero.com/cdn/assets/javascript/framework.js" defer></script>
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
                        <span class="material-icons" style="color: #5b21b6;">shopping_cart</span><span class="link-text">Mercado Digital</span>
                    </li>
                    <li onclick="location.href='coop_listadoPedidos.php'">
                        <span class="material-icons" style="color: #5b21b6;">receipt_long</span><span class="link-text">Listado Pedidos</span>
                    </li>
                    <li onclick="location.href='coop_usuarioInformacion.php'">
                        <ure class="material-icons" style="color: #5b21b6;">agriculture</ure><span class="link-text">Productores</span>
                    </li>
                    <li onclick="location.href='coop_productores.php'">
                        <span class="material-icons" style="color: #5b21b6;">link</span><span class="link-text">Asociar Prod</span>
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
                <div class="navbar-title">Inicio</div>
            </header>

            <!-- 📦 CONTENIDO -->
            <section class="content">

                <!-- Bienvenida -->
                <div class="card">
                    <h4>Hola <?php echo htmlspecialchars($nombre); ?> 👋</h4>
                    <p>En esta página, vas a poder inscribir productores nuevos a tu cooperativa y vas a poder modificar su información</p>
                </div>

                <!-- Formulario -->
                <div class="card">
                    <h2>Crear nuevo usuario</h2>
                    <form class="form-modern" id="formUsuario">
                        <div class="form-grid grid-2">

                            <!-- Usuario -->
                            <div class="input-group">
                                <label for="usuario">Usuario</label>
                                <div class="input-icon">
                                    <span class="material-icons">person</span>
                                    <input type="text" id="usuario" name="usuario" placeholder="Asigna un usuario" autocomplete="Asigna un usuario" required>
                                </div>
                            </div>

                            <!-- Contraseña con ojo -->
                            <div class="input-group password-container">
                                <label for="contrasena">Contraseña</label>
                                <div class="input-icon">
                                    <span class="material-icons">lock</span>
                                    <input type="password" id="contrasena" name="contrasena" placeholder="Asigna una contraseña" autocomplete="Asigna una contraseña" required>
                                    <span class="material-icons toggle-password" onclick="togglePassword()">visibility</span>
                                </div>
                            </div>

                            <!-- CUIT -->
                            <div class="input-group">
                                <label for="cuit">CUIT</label>
                                <div class="input-icon">
                                    <span class="material-icons">fingerprint</span>
                                    <input type="text" id="cuit" name="cuit" inputmode="numeric" pattern="\d*" maxlength="11" placeholder="Coloca el CUIT sin guiones" oninput="this.value = this.value.replace(/\D/g, '')" required>
                                </div>
                            </div>

                            <!-- ID Real auto -->
                            <div class="input-group">
                                <label for="id_real">ID Real (auto)</label>
                                <div class="input-icon">
                                    <span class="material-icons">badge</span>
                                    <input type="text" id="id_real" name="id_real" readonly>
                                </div>
                            </div>

                            <!-- Rol fijo -->
                            <div class="input-group">
                                <label for="rol">Rol</label>
                                <div class="input-icon">
                                    <span class="material-icons">supervisor_account</span>
                                    <input type="text" id="rol" name="rol" value="productor" disabled>
                                </div>
                            </div>

                            <!-- Permiso fijo -->
                            <div class="input-group">
                                <label for="permiso_ingreso">Permiso</label>
                                <div class="input-icon">
                                    <span class="material-icons">check_circle</span>
                                    <input type="text" id="permiso_ingreso" name="permiso_ingreso" value="Habilitado" disabled>
                                </div>
                            </div>

                            <!-- Cooperativa -->
                            <div class="input-group">
                                <label for="cooperativa">Cooperativa</label>
                                <div class="input-icon">
                                    <span class="material-icons">store</span>
                                    <input type="text" id="cooperativa" name="cooperativa" value="<?php echo htmlspecialchars($nombre); ?>" readonly>
                                </div>
                            </div>
                        </div>

                        <!-- Botones -->
                        <div class="form-buttons">
                            <button class="btn btn-aceptar" type="submit">Crear productor</button>
                        </div>
                    </form>

                </div>

                <!-- Tarjeta de buscador -->
                <div class="card">
                    <h2>Busca usuarios</h2>

                    <form class="form-modern">
                        <div class="form-grid grid-2">
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
                        </div>
                    </form>
                </div>

                <!-- contenedor de productores -->
                <div class="card">
                    <h2>Productores asociados</h2>
                    <p>Listado de productores asociados a tu cooperativa. Podés editar su información.</p>
                    <br>
                    <div class="card-grid grid-4" id="contenedorProductores"></div>
                </div>

                <!-- Modal de edición -->
                <div class="modal" id="modalEditarProductor" style="display:none;">
                    <div class="modal-content">
                        <span class="material-icons close" onclick="cerrarModal()">close</span>
                        <h3>Editar productor</h3>
                        <form id="formEditarProductor">
                            <input type="hidden" name="usuario_id" id="usuario_id">
                            <div class="input-group">
                                <label for="nombre">Nombre</label>
                                <input type="text" id="nombre" name="nombre">
                            </div>
                            <div class="input-group">
                                <label for="telefono">Teléfono</label>
                                <input type="text" id="telefono" name="telefono">
                            </div>
                            <div class="input-group">
                                <label for="correo">Correo</label>
                                <input type="email" id="correo" name="correo">
                            </div>
                            <div class="input-group">
                                <label for="direccion">Dirección</label>
                                <input type="text" id="direccion" name="direccion">
                            </div>
                            <div class="form-buttons">
                                <button type="submit" class="btn btn-editar">Guardar cambios</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- contenedor del toastify -->
                <div id="toast-container"></div>
                <!-- Spinner Global -->
                <script src="../../views/partials/spinner-global.js"></script>

                <script>
                    // Funcion togglePassword
                    function togglePassword() {
                        const passwordInput = document.getElementById('contrasena');
                        const icon = document.querySelector('.toggle-password');
                        const isPassword = passwordInput.getAttribute('type') === 'password';
                        passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                        icon.textContent = isPassword ? 'visibility_off' : 'visibility';
                    }

                    document.addEventListener('DOMContentLoaded', () => {
                        const form = document.getElementById('formUsuario');
                        const idRealInput = document.getElementById('id_real');

                        // Obtener ID real disponible al cargar
                        fetch('../../controllers/coop_usuarioInformacionController.php')
                            .then(res => res.json())
                            .then(data => {
                                if (data.id_real) {
                                    idRealInput.value = data.id_real;
                                }
                            });

                        form.addEventListener('submit', async (e) => {
                            e.preventDefault();

                            const formData = new FormData(form);
                            const response = await fetch('../../controllers/coop_usuarioInformacionController.php', {
                                method: 'POST',
                                body: formData
                            });

                            const result = await response.json();

                            if (result.success) {
                                showAlert('success', result.message);
                                idRealInput.value = result.id_real;
                                form.reset();
                            } else {
                                showAlert('error', result.message);
                            }
                        });
                    });

                    // funcion para cargar productores
                    async function cargarProductores() {
                        const contenedor = document.getElementById('contenedorProductores');
                        contenedor.innerHTML = '<p>Cargando productores...</p>';

                        try {
                            const res = await fetch('../../controllers/coop_usuarioInformacionController.php?action=listar_productores');
                            const data = await res.json();
                            if (!data.success) throw new Error(data.message);

                            contenedor.innerHTML = '';

                            data.productores.forEach(p => {
                                const card = document.createElement('div');
                                card.className = 'user-card';

                                card.innerHTML = `
                <h3 class="user-name">${p.usuario}</h3>
                <div class="user-info">
                    <span class="material-icons">fingerprint</span>
                    <span class="user-email">${p.cuit}</span>
                </div>
                <button class="btn btn-editar" onclick='abrirModal(${JSON.stringify(p)})'>Editar</button>
            `;
                                contenedor.appendChild(card);
                            });

                        } catch (err) {
                            contenedor.innerHTML = `<p style="color:red;">${err.message}</p>`;
                        }
                    }

                    function abrirModal(prod) {
                        document.getElementById('modalEditarProductor').style.display = 'block';
                        document.getElementById('usuario_id').value = prod.usuario_id;
                        document.getElementById('nombre').value = prod.nombre || '';
                        document.getElementById('telefono').value = prod.telefono || '';
                        document.getElementById('correo').value = prod.correo || '';
                        document.getElementById('direccion').value = prod.direccion || '';
                    }

                    function cerrarModal() {
                        document.getElementById('modalEditarProductor').style.display = 'none';
                    }

                    document.getElementById('formEditarProductor').addEventListener('submit', async (e) => {
                        e.preventDefault();
                        const formData = new FormData(e.target);
                        formData.append('action', 'editar_productor');

                        const res = await fetch('../../controllers/coop_usuarioInformacionController.php', {
                            method: 'POST',
                            body: formData
                        });

                        const result = await res.json();
                        if (result.success) {
                            showAlert('success', result.message);
                            cerrarModal();
                            cargarProductores();
                        } else {
                            showAlert('error', result.message);
                        }
                    });

                    document.addEventListener('DOMContentLoaded', cargarProductores);
                </script>

            </section>

        </div>
    </div>

</body>

</html>