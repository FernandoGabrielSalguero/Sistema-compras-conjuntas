<?php
session_start();

if (!isset($_SESSION['user_role'])) {
    header("Location: ../../index.php");
    exit();
}

$user_role = $_SESSION['user_role'] ?? '';

// Páginas disponibles para cada rol
$pages = [
    'SVE' => [
        'Dashboard' => '📊',
        'Pedidos' => '📋',
        'Mercado Digital' => '🛒',
        'Alta Usuarios' => '👥',
        'Alta Fincas' => '🏡',
        'Productos' => '📦',
        'Solicitudes de Modificación' => '📝'
    ]
];
$user_pages = $pages[$user_role] ?? [];
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sidebar Fijo</title>
    <link rel="stylesheet" href="../../assets/css/style.css"> <!-- Llamada al archivo CSS -->
</head>

<div class="sidebar">
    <ul>
        <?php foreach ($user_pages as $page_name => $icon): ?>
            <li><a href="#"><?php echo $icon . ' ' . $page_name; ?></a></li>
        <?php endforeach; ?>
    </ul>
</div>
