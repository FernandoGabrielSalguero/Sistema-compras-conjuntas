<?php
session_start();

// Proteger la sesión
if (!isset($_SESSION['user_role'])) {
    header("Location: ../../index.php");
    exit();
}

$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : '';
$user_role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : '';

// Definimos las páginas que puede ver cada rol
$pages = [
    'SVE' => [
        'Dashboard' => ['url' => 'dashboard.php', 'icon' => '📊'],
        'Pedidos' => ['url' => 'pedidos.php', 'icon' => '📋'],
        'Mercado Digital' => ['url' => 'mercado_digital.php', 'icon' => '🛒'],
        'Alta Usuarios' => ['url' => 'alta_usuarios.php', 'icon' => '👤'],
        'Alta Fincas' => ['url' => 'alta_fincas.php', 'icon' => '🏡'],
        'Productos' => ['url' => 'productos.php', 'icon' => '📦'],
        'Solicitudes de Modificación' => ['url' => 'solicitudes_modificaciones.php', 'icon' => '📝']
    ],
    'Cooperativa' => [
        'Dashboard' => ['url' => 'dashboard.php', 'icon' => '📊'],
        'Mercado Digital' => ['url' => 'mercado_digital.php', 'icon' => '🛒'],
        'Alta Usuarios' => ['url' => 'alta_usuarios.php', 'icon' => '👥']
    ],
    'Productor' => [
        'Dashboard' => ['url' => 'dashboard.php', 'icon' => '📊'],
        'Mercado Digital' => ['url' => 'mercado_digital.php', 'icon' => '🛒'],
        'Perfil' => ['url' => 'perfil.php', 'icon' => '👤']
    ]
];

$user_pages = $pages[$user_role] ?? [];

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sidebar Fijo</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Roboto', sans-serif;
            background-color: #f4f4f4;
            display: flex;
            min-height: 100vh;
        }

        #header {
            background-color: white;
            width: calc(100% - 260px);
            padding: 10px 20px;
            position: fixed;
            top: 0;
            left: 260px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            z-index: 10;
        }

        #sidebar {
            background-color: white;
            width: 250px;
            position: fixed;
            top: 30px; 
            bottom: 60px;
            left: 10px;
            padding-top: 30px;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

        .sidebar-content a {
            display: flex;
            align-items: center;
            padding: 15px;
            text-decoration: none;
            color: #424242;
            transition: background-color 0.3s;
            font-size: 15px;
            margin-bottom: 5px;
        }

        .sidebar-content a:hover {
            background-color: #e0e0e0;
        }

        .content {
            margin-left: 260px;
            padding: 80px 20px;
            width: 100%;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div id="sidebar">
    <div class="sidebar-content">
        <?php foreach ($user_pages as $page_name => $page_data): ?>
            <a href="<?php echo $page_data['url']; ?>">
                <?php echo $page_data['icon'] . ' ' . $page_name; ?>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- Header -->
<div id="header">
    <div class="left-section">
        <button>⬒</button>
    </div>

    <div class="right-section">
        <div class="date-display">📅 <?php echo date('d F Y'); ?></div>
        <div class="user-info">
            <span><?php echo $user_role . ' - ' . $user_name; ?></span>
        </div>
    </div>
</div>

<!-- Contenido principal -->
<div class="content">
    <!-- Aquí va el contenido principal de cada página -->
</div>

</body>
</html>
