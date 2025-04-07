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

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Error en la conexión: " . $conn->connect_error);
}

// Obtener productores por cooperativa (ajax)
if (isset($_POST['cooperativas'])) {
    $coops = $_POST['cooperativas'];
    if (!is_array($coops)) $coops = [$coops];
    $ids = implode(",", array_map('intval', $coops));

    $sql = "
        SELECT DISTINCT u.id, u.nombre
        FROM usuarios u
        INNER JOIN productores_cooperativas pc ON pc.id_productor = u.id
        WHERE pc.id_cooperativa IN ($ids)
          AND u.rol = 'productor'
    ";

    $res = $conn->query($sql);

    echo "<option value='all'>Todos</option>";
    while ($row = $res->fetch_assoc()) {
        echo "<option value='{$row['id']}'>{$row['nombre']}</option>";
    }

    exit;
}

// Crear nuevo operativo
if (isset($_POST['nombre'])) {
    $nombre = $_POST['nombre'];
    $cooperativas = is_array($_POST['cooperativas']) ? implode(",", $_POST['cooperativas']) : '';
    $productores = (isset($_POST['productores']) && $_POST['productores'][0] === 'all')
        ? 'all'
        : implode(",", $_POST['productores']);
    $productos = isset($_POST['productos']) ? implode(",", $_POST['productos']) : '';
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_cierre = $_POST['fecha_cierre'];

    $stmt = $conn->prepare("INSERT INTO operativos (nombre, fecha_inicio, fecha_cierre, cooperativas_ids, productores_ids, productos_ids) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $nombre, $fecha_inicio, $fecha_cierre, $cooperativas, $productores, $productos);

    echo json_encode(['success' => $stmt->execute()]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Operativos SVE</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />

    <style>
        body,
        html {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        #header {
            background-color: #ffffff;
            color: #333;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 10;
        }

        #sidebar {
            background-color: #ffffff;
            color: #333;
            padding: 1rem;
            width: 250px;
            height: calc(100vh - 60px);
            position: fixed;
            top: 60px;
            left: 0;
            overflow-y: auto;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
        }

        #body {
            margin-left: 250px;
            padding: 2rem;
            background-color: #F0F2F5;
            padding-top: 60px;
        }

        .card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .md-input {
            margin-bottom: 1.5rem;
            display: flex;
            flex-direction: column;
        }

        .md-input label {
            font-weight: bold;
            margin-bottom: 6px;
            color: #333;
        }

        .md-input input,
        .md-input select,
        .md-input textarea {
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 15px;
            background-color: #fff;
            outline: none;
            transition: border 0.3s ease, box-shadow 0.3s ease;
        }

        .md-input input:focus,
        .md-input select:focus,
        .md-input textarea:focus {
            border-color: #3f51b5;
            box-shadow: 0 0 5px rgba(63, 81, 181, 0.5);
        }

        .productos-box {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid #ccc;
            padding: 1rem;
            border-radius: 6px;
            background-color: #fff;
        }

        .productos-box label {
            display: flex;
            align-items: center;
            margin-bottom: 6px;
            cursor: pointer;
        }

        .productos-box input[type="checkbox"] {
            margin-right: 10px;
        }

        .submit-btn {
            background-color: #3f51b5;
            color: white;
            border: none;
            padding: 12px 18px;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .submit-btn:hover {
            background-color: #303f9f;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #f4f4f4;
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
</head>

<body>
    <div id="header">
        <div id="menu-icon" onclick="toggleSidebar()">☰</div>
        <div>Operativos</div>
    </div>

    <div id="sidebar">
        <nav>
            <a href="sve_dashboard.php"><i class="fa fa-home"></i> Inicio</a><br>
            <a href="alta_usuarios.php"><i class="fa fa-user-plus"></i> Alta Usuarios</a><br>
            <a href="relacionamiento.php"><i class="fa fa-user-plus"></i> Relacionamiento </a><br>
            <a href="alta_productos.php"><i class="fa fa-box"></i> Alta Productos</a><br>
            <a href="operativos.php"><i class="fa fa-box"></i> Operativos</a><br>
            <a href="mercado_digital.php"><i class="fa fa-shopping-cart"></i> Mercado Digital</a><br>
            <a href="pedidos.php"><i class="fa fa-list"></i> Pedidos</a><br>
            <a href="CargaMasivaUsuarios.php"><i class="fa fa-list"></i> Carga masiva de datos</a><br>
            <a href="base_datos.php"><i class="fa fa-list"></i> Base de datos </a><br>
            <a href="logout.php"><i class="fa fa-sign-out-alt"></i> Salir</a><br>
        </nav>
        <button id="close-menu-button" onclick="toggleSidebar()">Cerrar Menú</button>
    </div>

    <div id="body">
        <div class="card">
            <h2>Crear nuevo operativo</h2>
            <form id="form-operativo" method="POST">
                <div class="md-input">
                    <label>Nombre del operativo</label>
                    <input type="text" name="nombre" required>
                </div>

                <div class="md-input">
                    <label>Cooperativas</label>
                    <select id="cooperativas" name="cooperativas[]" multiple="multiple" style="width: 100%">
                        <?php
                        $res = $conn->query("SELECT id, nombre FROM cooperativas");
                        while ($row = $res->fetch_assoc()) {
                            echo "<option value='{$row['id']}'>{$row['nombre']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="md-input">
                    <label>Productores</label>
                    <select id="productores" name="productores[]" multiple="multiple" style="width: 100%">
                        <option value="all" selected>Todos</option>
                    </select>
                </div>

                <div class="md-input">
                    <label>Productos</label>
                    <div class="productos-box">
                        <?php
                        $categorias = $conn->query("SELECT DISTINCT categoria FROM productos ORDER BY categoria ASC");
                        while ($cat = $categorias->fetch_assoc()) {
                            echo "<strong>{$cat['categoria']}</strong><br>";
                            $prods = $conn->query("SELECT id, Nombre_producto FROM productos WHERE categoria = '{$cat['categoria']}'");
                            while ($prod = $prods->fetch_assoc()) {
                                echo "<label><input type='checkbox' name='productos[]' value='{$prod['id']}'> {$prod['Nombre_producto']}</label><br>";
                            }
                            echo "<br>";
                        }
                        ?>
                    </div>
                </div>

                <div class="md-input">
                    <label>Fecha de inicio</label>
                    <input type="date" name="fecha_inicio" required>
                </div>

                <div class="md-input">
                    <label>Fecha de cierre</label>
                    <input type="date" name="fecha_cierre" required>
                </div>

                <button type="submit" class="submit-btn">Crear Operativo</button>
            </form>
        </div>

        <div class="card">
            <h2>Operativos existentes</h2>
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Cooperativas</th>
                        <th>Productores</th>
                        <th>Productos</th>
                        <th>Fecha inicio</th>
                        <th>Fecha cierre</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $operativos = $conn->query("SELECT * FROM operativos");
                    while ($op = $operativos->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>{$op['nombre']}</td>";
                        echo "<td>{$op['cooperativas_ids']}</td>";
                        echo "<td>{$op['productores_ids']}</td>";
                        echo "<td>{$op['productos_ids']}</td>";
                        echo "<td>{$op['fecha_inicio']}</td>";
                        echo "<td>{$op['fecha_cierre']}</td>";
                        echo "<td><button onclick='editarOperativo({$op['id']})'>Editar</button></td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('show');
        }

        $(document).ready(function () {
            $('#cooperativas, #productores').select2();

            $('#cooperativas').on('change', function () {
                const coopIds = $(this).val();
                $.post('operativos.php', { cooperativas: coopIds }, function (data) {
                    $('#productores').html(data).trigger('change');
                });
            });

            $('#form-operativo').on('submit', function (e) {
                e.preventDefault();
                $.post('operativos.php', $(this).serialize(), function (res) {
                    if (res.success) {
                        toastr.success('✅ Operativo creado');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        toastr.error('❌ Error al crear operativo');
                    }
                }, 'json');
            });
        });

        function editarOperativo(id) {
            toastr.info("Funcionalidad de edición aún no implementada");
        }
    </script>
</body>
</html>
