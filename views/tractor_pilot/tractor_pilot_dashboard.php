<?php
// Mostrar errores en pantalla (útil en desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../../middleware/authMiddleware.php';
checkAccess('piloto_tractor');

$nombre = $_SESSION['nombre'] ?? 'Piloto de tractor';
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SVE - Piloto Tractor</title>

    <!-- Íconos de Material Design -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <!-- Framework Success desde CDN -->
    <link rel="stylesheet" href="https://framework.impulsagroup.com/assets/css/framework.css">
    <script src="https://framework.impulsagroup.com/assets/javascript/framework.js" defer></script>
</head>

<body>
    <div class="layout">

        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <span class="material-icons logo-icon">dashboard</span>
                <span class="logo-text">SVE</span>
            </div>

            <nav class="sidebar-menu">
                <ul>
                    <li onclick="location.href='tractor_pilot_dashboard.php'">
                        <span class="material-symbols-outlined" style="color:#16a34a;">agriculture</span>
                        <span class="link-text">Fincas</span>
                    </li>
                    <li onclick="location.href='../../../logout.php'">
                        <span class="material-icons" style="color: red;">logout</span>
                        <span class="link-text">Salir</span>
                    </li>
                </ul>
            </nav>

            <div class="sidebar-footer">
                <button class="btn-icon" onclick="toggleSidebar()">
                    <span class="material-icons" id="collapseIcon">chevron_left</span>
                </button>
            </div>
        </aside>

        <div class="main">
            <header class="navbar">
                <button class="btn-icon" onclick="toggleSidebar()">
                    <span class="material-icons">menu</span>
                </button>
                <div class="navbar-title">Fincas</div>
            </header>

            <section class="content">
                <div class="card" id="estado-card">
                    <h2>Panel piloto tractor</h2>
                    <p>Hola, <?php echo htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8'); ?>.</p>
                    <p id="estado-msg">Cargando estado...</p>
                </div>
            </section>
        </div>
    </div>

    <script>
        async function cargarEstado() {
            const el = document.getElementById('estado-msg');
            try {
                const res = await fetch('../../controllers/tractor_pilot_dashboardController.php?action=estado', {
                    credentials: 'same-origin'
                });
                const payload = await res.json();
                if (!res.ok || !payload.ok) {
                    throw new Error(payload.message || 'Error');
                }
                el.textContent = payload.data?.message || 'Estado OK.';
            } catch (e) {
                console.error(e);
                el.textContent = 'No se pudo cargar el estado.';
            }
        }

        document.addEventListener('DOMContentLoaded', cargarEstado);
    </script>
</body>

</html>
