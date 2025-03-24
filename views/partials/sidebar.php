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
        'Dashboard' => ['url' => 'dashboard.php', 'icon' => 'fa fa-tachometer-alt'],
        'Pedidos' => ['url' => 'pedidos.php', 'icon' => 'fa fa-clipboard-list'],
        'Mercado Digital' => ['url' => 'mercado_digital.php', 'icon' => 'fa fa-shopping-cart'],
        'Alta Usuarios' => ['url' => 'alta_usuarios.php', 'icon' => 'fa fa-user-plus'],
        'Alta Fincas' => ['url' => 'alta_fincas.php', 'icon' => 'fa fa-leaf'],
        'Productos' => ['url' => 'productos.php', 'icon' => 'fa fa-box'],
        'Solicitudes de Modificación' => ['url' => 'solicitudes_modificaciones.php', 'icon' => 'fa fa-edit'],
        'Salir' => ['url' => '../../logout.php', 'icon' => 'fa fa-sign-out']
    ],
    'Cooperativa' => [
        'Dashboard' => ['url' => 'dashboard.php', 'icon' => 'fa fa-tachometer-alt'],
        'Mercado Digital' => ['url' => 'mercado_digital.php', 'icon' => 'fa fa-shopping-cart'],
        'Alta Usuarios' => ['url' => 'alta_usuarios.php', 'icon' => 'fa fa-users']
    ],
    'Productor' => [
        'Dashboard' => ['url' => 'dashboard.php', 'icon' => 'fa fa-tachometer-alt'],
        'Mercado Digital' => ['url' => 'mercado_digital.php', 'icon' => 'fa fa-shopping-cart'],
        'Perfil' => ['url' => 'perfil.php', 'icon' => 'fa fa-user']
    ]
];

$user_pages = $pages[$user_role] ?? [];
?>

<!-- Incluir Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<!-- Sidebar -->
<div id="sidebar">
    <div class="sidebar-content">
        <?php foreach ($user_pages as $page_name => $page_data): ?>
            <a href="<?php echo $page_data['url']; ?>">
                <i class="<?php echo $page_data['icon']; ?>"></i>
                <span><?php echo $page_name; ?></span>
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

    .sidebar-content i {
        margin-right: 10px;
        font-size: 16px;
    }
</style>
