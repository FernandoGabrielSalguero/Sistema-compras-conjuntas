<?php
// Mostrar errores en pantalla (útil en desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión y configurar parámetros de seguridad
require_once '../../middleware/authMiddleware.php';
checkAccess('cooperativa');

// Datos del usuario en sesión
$nombre = $_SESSION['nombre'] ?? 'Sin nombre';
$correo = $_SESSION['correo'] ?? 'Sin correo';
$cuit = $_SESSION['cuit'] ?? 'Sin CUIT';
$telefono = $_SESSION['telefono'] ?? 'Sin teléfono';
$observaciones = $_SESSION['observaciones'] ?? 'Sin observaciones';

//Cargamos los operativos cerrados
$cierre_info = $_SESSION['cierre_info'] ?? null;
unset($_SESSION['cierre_info']);
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

    <style>
        /* Estilos tarjetas */
        .user-card {
            border: 2px solid #5b21b6;
            border-radius: 12px;
            padding: 1rem;
            transition: border 0.3s ease;
        }

        .user-card.completo {
            border: 2px solid green;
        }

        .user-card.incompleto {
            border: 2px solid red;
        }

        /* ocultar imputs */
        .oculto {
            display: none !important;
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
                        <span class="link-text">Servicios Vendimiales</span>
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
                <div class="navbar-title">Inicio</div>
            </header>

            <!-- 📦 CONTENIDO -->
            <section class="content">

                <!-- Bienvenida -->
                <div class="card">
                    <h4>Hola <?php echo htmlspecialchars($nombre); ?> 👋</h4>
                    <p>En esta página, vas a poder inscribir productores nuevos a tu cooperativa y vas a poder modificar su información</p>
                    <br>
                    <!-- Boton de tutorial -->
                    <button id="btnIniciarTutorial" class="btn btn-aceptar">
                        Tutorial
                    </button>
                </div>

                <!-- Formulario -->
                <div class="card tutorial-formulario">
                    <h2>Asignemos un productor a tu cooperativa</h2>
<form class="form-modern" id="formUsuario">
    <div class="form-grid grid-2">

        <!-- ✅ Nombre del productor (usuario) -->
        <div class="input-group">
            <label for="usuario">Nombre del productor</label>
            <div class="input-icon">
                <span class="material-icons">person</span>
                <input type="text" id="usuario" name="usuario" placeholder="¿Cuál es el nombre del productor que vas a asociar?" autocomplete="off" required>
            </div>
        </div>

        <!-- ✅ Contraseña -->
        <div class="input-group password-container">
            <label for="contrasena">Contraseña</label>
            <div class="input-icon">
                <span class="material-icons">lock</span>
                <input type="password" id="contrasena" name="contrasena" placeholder="Asignale una contraseña a este nuevo productor" autocomplete="new-password" required>
                <span class="material-icons toggle-password" onclick="togglePassword()">visibility</span>
            </div>
        </div>

        <!-- CUIT -->
        <div class="input-group">
            <label for="cuit">CUIT</label>
            <div class="input-icon input-icon-cuit">
                <span class="material-icons">fingerprint</span>
                <input type="text" id="cuit" name="cuit" inputmode="numeric" pattern="\d*" maxlength="11" placeholder="Coloca el CUIT sin guiones" oninput="this.value = this.value.replace(/\D/g, '')" required>
            </div>
        </div>

        <!-- ID Real auto --> 
        <div class="input-group tutorial-id_real">
            <label for="id_real">ID Real (Automático)</label>
            <div class="input-icon">
                <span class="material-icons">badge</span>
                <input type="text" id="id_real" name="id_real" readonly>
            </div>
        </div>

        <!-- Rol fijo -->
        <div class="input-group oculto">
            <label for="rol">Rol</label>
            <div class="input-icon">
                <span class="material-icons">supervisor_account</span>
                <input type="text" id="rol" name="rol" value="productor" readonly>
            </div>
        </div>

        <!-- Permiso fijo -->
        <div class="input-group oculto">
            <label for="permiso_ingreso">Permiso</label>
            <div class="input-icon">
                <span class="material-icons">check_circle</span>
                <input type="text" id="permiso_ingreso" name="permiso_ingreso" value="Habilitado" readonly>
            </div>
        </div>

        <!-- Cooperativa -->
        <div class="input-group oculto">
            <label for="cooperativa">Cooperativa</label>
            <div class="input-icon">
                <span class="material-icons">store</span>
                <input type="text" id="cooperativa" name="cooperativa" value="<?php echo htmlspecialchars($nombre); ?>" readonly>
            </div>
        </div>
    </div>

    <!-- Botones -->
    <div class="form-buttons tutorial-Boton">
        <button class="btn btn-aceptar" type="submit">Asociar nuevo productor</button>
    </div>
</form>


                </div>

                <!-- Tarjeta de buscador -->
                <div class="card tutorial-buscar">
                    <h2>Busca usuarios</h2>

                    <form class="form-modern">
                        <div class="form-grid grid-2">
                            <!-- Buscar por CUIT -->
                            <div class="input-group">
                                <label for="buscarCuit">Podes buscar por CUIT</label>
                                <div class="input-icon">
                                    <span class="material-icons">fingerprint</span>
                                    <input type="number" id="buscarCuit" name="buscarCuit" placeholder="20123456781">
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
                <div class="card tutorial-listadoProductores">
                    <h2>Productores asociados</h2>
                    <p>Listado de productores asociados a tu cooperativa. Podés editar su información.</p>
                    <br>
                    <div class="card-grid grid-4" id="contenedorProductores"></div>
                </div>

                <!-- Modal editar productor -->
                <div id="modalEditarProductor" class="modal hidden">
                    <div class="modal-content">
                        <h3>Editar Productor</h3>
                        <form id="formEditarProductor">
                            <input type="hidden" name="usuario_id" id="usuario_id">

                            <div class="input-group">
                                <label for="nombre">Nombre</label>
                                <div class="input-icon">
                                    <span class="material-icons">badge</span>
                                    <input type="text" id="nombre" name="nombre" required>
                                </div>
                            </div>

                            <div class="input-group">
                                <label for="cuitEditar">CUIT</label>
                                <div class="input-icon input-icon-cuit">
                                    <span class="material-icons">fingerprint</span>
                                    <input type="text" id="cuitEditar" name="cuit" placeholder="Ej: 20123456789" maxlength="11" inputmode="numeric" pattern="^[0-9]{11}$">
                                </div>
                            </div>

                            <div class="input-group">
                                <label for="telefono">Teléfono</label>
                                <div class="input-icon">
                                    <span class="material-icons">call</span>
                                    <input type="text" id="telefono" name="telefono">
                                </div>
                            </div>

                            <div class="input-group">
                                <label for="correo">Correo</label>
                                <div class="input-icon">
                                    <span class="material-icons">email</span>
                                    <input type="email" id="correo" name="correo">
                                </div>
                            </div>

                            <div class="input-group">
                                <label for="direccion">Dirección</label>
                                <div class="input-icon">
                                    <span class="material-icons">home</span>
                                    <input type="text" id="direccion" name="direccion">
                                </div>
                            </div>

                            <div class="form-buttons" style="margin-top: 20px;">
                                <button type="submit" class="btn btn-aceptar">Guardar</button>
                                <button type="button" class="btn btn-cancelar" onclick="cerrarModal()">Cancelar</button>
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
                                    idRealInput.value = data.id_real; // ✅ Ya viene con la 'P'
                                }
                            });

                        form.addEventListener('submit', async (e) => {
                            e.preventDefault();

                            const formData = new FormData(form);

                            // 🟡 DEBUG: Log de los datos enviados
                            console.log('📦 Datos enviados por FormData:');
                            for (let [key, value] of formData.entries()) {
                                console.log(`${key}: ${value}`);
                            }

                            const response = await fetch('../../controllers/coop_usuarioInformacionController.php', {
                                method: 'POST',
                                body: formData
                            });

                            const result = await response.json();

                            if (result.success) {
                                showAlert('success', result.message);

                                // Obtener nuevo ID real tras creación
                                fetch('../../controllers/coop_usuarioInformacionController.php')
                                    .then(r => r.json())
                                    .then(d => {
                                        if (d.id_real) idRealInput.value = d.id_real;
                                    });

                                form.reset();
                                cargarProductores(); // 👈 Actualiza tarjetas en tiempo real
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
                                const datosCompletos = (
                                    p.nombre && p.nombre.trim() !== '' &&
                                    p.telefono && p.telefono.trim() !== '' && p.telefono.trim().toLowerCase() !== 'sin teléfono' &&
                                    p.correo && p.correo.trim() !== '' && p.correo.trim().toLowerCase() !== 'sin-correo@sve.com' &&
                                    p.direccion && p.direccion.trim() !== '' && p.direccion.trim().toLowerCase() !== 'sin dirección'
                                );
                                card.className = `user-card ${datosCompletos ? 'completo' : 'incompleto'}`;



                                card.innerHTML = `
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h4 class="user-name">${p.usuario}</h4>
<span class="material-icons" title="${datosCompletos ? 'Datos completos' : 'Datos incompletos'}"
    style="color: ${datosCompletos ? 'green' : 'orange'};">
    ${datosCompletos ? 'check_circle' : 'error_outline'}
</span>
    </div>

    <div class="user-info">
        <span class="material-icons">badge</span>
        <span class="user-email"><strong>ID:</strong> ${p.id_real}</span>
    </div>

    <div class="user-info">
        <span class="material-icons">fingerprint</span>
        <span class="user-email">${p.cuit}</span>
    </div>

<button class="btn-icon tooltip-icon tutorial-EditarProductor"
        data-tooltip="Editar productor"
        onclick='abrirModal(${JSON.stringify(p)})'>
    <span class="material-icons" style="color: blue;" >edit</span>
</button>`;
                                contenedor.appendChild(card);
                            });

                        } catch (err) {
                            contenedor.innerHTML = `<p style="color:red;">${err.message}</p>`;
                        }
                    }

                    function abrirModal(prod) {
                        const modal = document.getElementById('modalEditarProductor');
                        modal.classList.remove('hidden');

                        document.getElementById('usuario_id').value = prod.usuario_id;
                        document.getElementById('nombre').value = prod.nombre || '';
                        document.getElementById('cuitEditar').value = prod.cuit || '';
                        document.getElementById('telefono').value = prod.telefono || '';
                        document.getElementById('correo').value = prod.correo || '';
                        document.getElementById('direccion').value = prod.direccion || '';
                    }

                    function cerrarModal() {
                        document.getElementById('modalEditarProductor').classList.add('hidden');
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

                    // Filtro por nombre o ID Real
                    document.getElementById('buscarNombre').addEventListener('input', filtrarProductores);
                    document.getElementById('buscarCuit').addEventListener('input', filtrarProductores);

                    function filtrarProductores() {
                        const nombre = document.getElementById('buscarNombre').value.toLowerCase();
                        const cuit = document.getElementById('buscarCuit').value;

                        document.querySelectorAll('#contenedorProductores .user-card').forEach(card => {
                            const texto = card.innerText.toLowerCase().replace(/\s+/g, ' ');
                            if (
                                (!nombre || texto.includes(nombre)) &&
                                (!cuit || texto.includes(cuit))
                            ) {
                                card.style.display = '';
                            } else {
                                card.style.display = 'none';
                            }
                        });
                    }
                </script>

            </section>

        </div>
    </div>

    <!-- llamada de tutorial -->
    <script src="../partials/tutorials/cooperativas/productores.js?v=<?= time() ?>" defer></script>
</body>

</html>