<?php
// Activar la visualización de errores en pantalla
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Cargar las variables del archivo .env manualmente
$env_path = __DIR__ . '/../../.env';
if (file_exists($env_path)) {
    $dotenv = parse_ini_file($env_path);
} else {
    die("❌ Error: El archivo .env no se encuentra en la carpeta del proyecto.");
}

// Conexión a la base de datos
$host = $dotenv['DB_HOST'];
$dbname = $dotenv['DB_NAME'];
$username = $dotenv['DB_USER'];
$password = $dotenv['DB_PASS'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("❌ Error de conexión a la base de datos: " . $e->getMessage());
}

// Procesar formulario de carga de productos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['agregar_producto'])) {
    $nombre = $_POST['Nombre_producto'];
    $foto = $_FILES['Foto_producto']['name'];
    $detalle = $_POST['Detalle_producto'];
    $precio = $_POST['Precio_producto'];
    $unidad = $_POST['Unidad_Medida_venta'];

    if (move_uploaded_file($_FILES['Foto_producto']['tmp_name'], "uploads/" . $foto)) {
        $sql = "INSERT INTO productos (Nombre_producto, Foto_producto, Detalle_producto, Precio_producto, Unidad_Medida_venta)
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nombre, $foto, $detalle, $precio, $unidad]);
        echo '<script>window.onload = function() { alert("✅ Producto agregado correctamente."); };</script>';
    } else {
        echo '<script>window.onload = function() { alert("❌ Error al subir la imagen."); };</script>';
    }
}

// Procesar modificación de productos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modificar_producto'])) {
    $id = $_POST['id_producto'];
    $nombre = $_POST['Nombre_producto'];
    $detalle = $_POST['Detalle_producto'];
    $precio = $_POST['Precio_producto'];
    $unidad = $_POST['Unidad_Medida_venta'];

    $sql = "UPDATE productos SET Nombre_producto = ?, Detalle_producto = ?, Precio_producto = ?, Unidad_Medida_venta = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nombre, $detalle, $precio, $unidad, $id]);

    echo '<script>window.onload = function() { alert("✅ Producto modificado correctamente."); };</script>';
}

// Búsqueda de productos
$productos = [];
if (isset($_GET['buscar'])) {
    $busqueda = $_GET['buscar'];
    $stmt = $pdo->prepare("SELECT * FROM productos WHERE Nombre_producto LIKE ?");
    $stmt->execute(['%' . $busqueda . '%']);
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alta de Productos</title>
</head>
<body>

<!-- Tarjeta 1: Formulario de registro de productos -->
<div class="tarjeta">
    <h2>Agregar Producto</h2>
    <form method="POST" enctype="multipart/form-data">
        <input type="text" name="Nombre_producto" placeholder="Nombre del Producto" required><br>
        <input type="file" name="Foto_producto" required><br>
        <textarea name="Detalle_producto" placeholder="Detalle del Producto" required></textarea><br>
        <input type="number" name="Precio_producto" placeholder="Precio del Producto" required><br>
        <input type="text" name="Unidad_Medida_venta" placeholder="Unidad de Medida" required><br>
        <button type="submit" name="agregar_producto">Agregar Producto</button>
    </form>
</div>

<!-- Tarjeta 2: Búsqueda y modificación de productos -->
<div class="tarjeta">
    <h2>Buscar Producto</h2>
    <form method="GET">
        <input type="text" name="buscar" placeholder="Buscar por nombre de producto">
        <button type="submit">Buscar</button>
    </form>
    
    <?php if (!empty($productos)) { ?>
    <table border="1">
        <tr>
            <th>Nombre</th>
            <th>Foto</th>
            <th>Detalle</th>
            <th>Precio</th>
            <th>Unidad</th>
            <th>Acciones</th>
        </tr>
        <?php foreach ($productos as $producto) { ?>
        <tr>
            <form method="POST">
                <input type="hidden" name="id_producto" value="<?= $producto['Id'] ?>">
                <td><input type="text" name="Nombre_producto" value="<?= $producto['Nombre_producto'] ?>"></td>
                <td><img src="uploads/<?= $producto['Foto_producto'] ?>" width="50"></td>
                <td><textarea name="Detalle_producto"><?= $producto['Detalle_producto'] ?></textarea></td>
                <td><input type="number" name="Precio_producto" value="<?= $producto['Precio_producto'] ?>"></td>
                <td><input type="text" name="Unidad_Medida_venta" value="<?= $producto['Unidad_Medida_venta'] ?>"></td>
                <td><button type="submit" name="modificar_producto">Modificar</button></td>
            </form>
        </tr>
        <?php } ?>
    </table>
    <?php } ?>
</div>

</body>
</html>
