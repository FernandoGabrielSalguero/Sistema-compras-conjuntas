<?php
// Mostrar errores en pantalla (útil en desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión y proteger acceso
session_start();

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
                    <li onclick="location.href='sve_dashboard.php'">
                        <span class="material-icons">home</span><span class="link-text">Inicio</span>
                    </li>
                    <li onclick="location.href='sve_altausuarios.php'">
                        <span class="material-icons">person</span><span class="link-text">Alta usuarios</span>
                    </li>
                    <li onclick="location.href='sve_operativos.php'">
                        <span class="material-icons">assignment</span><span class="link-text">Operativos</span>
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
                <div class="navbar-title">Operativos</div>
            </header>

            <!-- 📦 CONTENIDO -->
            <section class="content">

                <!-- Bienvenida -->
                <div class="card">
                    <h2>Hola 👋</h2>
                    <p>En esta página vamos a crear y administrar los operativos de compras.</p>
                </div>

                <!-- Formulario -->
                <div class="card">
                    <h2>Formulario para crear un operativo nuevo</h2>
                    <form class="form-modern" id="formUsuario">
                        <div class="form-grid grid-4">

                            <!-- nombre -->
                            <div class="input-group">
                                <label for="nombre">Nombre</label>
                                <div class="input-icon">
                                    <span class="material-icons">person</span>
                                    <input type="text" id="nombre" name="nombre" placeholder="Juan Pérez" required
                                        minlength="2" maxlength="60" aria-required="true">
                                </div>
                                <small class="error-message" aria-live="polite"></small>
                            </div>

                            <!-- Fecha_inicio -->
                            <div class="input-group">
                                <label for="fecha">Fecha de inicio</label>
                                <span class="material-icons">info</span>
                                </span>
                                </label>
                                <div class="input-icon">
                                    <span class="material-icons">event</span>
                                    <input type="date" id="fecha" name="fecha" required>
                                </div>
                            </div>

                            <!-- Fecha_cierre -->
                            <div class="input-group">
                                <label for="fecha">Fecha de cierre</label>
                                <span class="material-icons">info</span>
                                </span>
                                </label>
                                <div class="input-icon">
                                    <span class="material-icons">event</span>
                                    <input type="date" id="fecha" name="fecha" required>
                                </div>
                            </div>

                            <!-- cooperativas_ids -->
                            <div class="input-group">
                                <label for="provincia">Cooperativas</label>
                                <div class="input-icon">
                                    <span class="material-icons">public</span>
                                    <select id="provincia" name="provincia" required>
                                        <option value="">Seleccionar</option>
                                        <option>Buenos Aires</option>
                                        <option>Córdoba</option>
                                        <option>Santa Fe</option>
                                    </select>
                                </div>
                            </div>

                            <!-- productores_ids -->
                            <div class="input-group">
                                <label for="provincia">Productores</label>
                                <div class="input-icon">
                                    <span class="material-icons">public</span>
                                    <select id="provincia" name="provincia" required>
                                        <option value="">Seleccionar</option>
                                        <option>Buenos Aires</option>
                                        <option>Córdoba</option>
                                        <option>Santa Fe</option>
                                    </select>
                                </div>
                            </div>


                            <!-- productos_ids -->
                            <div class="input-group">
                                <label for="provincia">Productos</label>
                                <div class="input-icon">
                                    <span class="material-icons">public</span>
                                    <select id="provincia" name="provincia" required>
                                        <option value="">Seleccionar</option>
                                        <option>Buenos Aires</option>
                                        <option>Córdoba</option>
                                        <option>Santa Fe</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Botones -->
                            <div class="form-buttons">
                                <button class="btn btn-aceptar" type="submit">Enviar</button>
                            </div>
                    </form>
                </div>

                <!-- Tarjeta de buscador -->
                <div class="card">
                    <h2>Busca un usuario por su CUIT</h2>
                    <div class="input-group">
                        <label for="buscarCuit">CUIT</label>
                        <div class="input-icon">
                            <span class="material-icons">fingerprint</span>
                            <input type="text" id="buscarCuit" name="buscarCuit" placeholder="20123456781">
                        </div>
                    </div>
                </div>


                <!-- Tabla -->
                <div class="card">
                    <h2>Listado de usuarios registrados</h2>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>id</th>
                                    <th>Nombre operativo</th>
                                    <th>Fecha Inicio</th>
                                    <th>Fecha Cierre</th>
                                    <th>Cooperativas</th>
                                    <th>Productores</th>
                                    <th>Productos</th>
                                    <th>Fecha de creación</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Alert -->
                <div class="alert-container" id="alertContainer"></div>
            </section>

        </div>
    </div>



    <script>

    </script>
</body>

</html>