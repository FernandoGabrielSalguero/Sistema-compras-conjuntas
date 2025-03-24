<?php
session_start();

if (!isset($_SESSION['user_role'])) {
    header("Location: ../../index.php");
    exit();
}

$user_role = $_SESSION['user_role'] ?? '';

// Páginas permitidas por rol
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

<!-- Estilos del Sidebar -->
<style>
    #sidebar {
        background-color: white;
        width: 250px;
        position: fixed;
        top: 60px; 
        left: 0;
        height: calc(100vh - 60px);
        padding-top: 20px;
        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
        border-radius: 10px;
        overflow-y: auto;
    }

    .sidebar-content a {
        display: flex;
        align-items: center;
        padding: 10px 15px;
        text-decoration: none;
        color: #424242;
        transition: background-color 0.3s;
    }

    .sidebar-content a:hover {
        background-color: #f0f0f0;
    }
</style>
