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

    exit; // 🚨 Detiene el script para que no siga al HTML
}



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
    <title>Dashboard SVE</title>
    <style>
        body,
        html {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: #F0F2F5;
        }

        /* General Styles */
        * {
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        /* Header */
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

        /* Sidebar */
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

        /* Body */
        #body {
            margin-left: 250px;
            padding: 2rem;
            background-color: #F0F2F5;
            height: 100vh;
            overflow-y: auto;
            padding-top: 60px;
        }

        /* Card */
        .card {
            background: white;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        /* Mobile Adjustments */
        @media (max-width: 768px) {
            #sidebar {
                transform: translateX(-100%);
                position: fixed;
                top: 0;
                height: 100vh;
                z-index: 9;
            }

            #sidebar.show {
                transform: translateX(0);
            }

            #body {
                margin-left: 0;
            }
        }

        /* Botón de cerrar menú */
        #close-menu-button {
            display: block;
            /* Visible por defecto */
        }

        /* Ocultar el botón en pantallas grandes */
        @media (min-width: 769px) {
            #close-menu-button {
                display: none;
            }
        }

        /* Estilo de botones del menú */
        #sidebar nav a {
            display: flex;
            align-items: center;
            padding: 10px;
            margin: 5px 0;
            background-color: white;
            color: #333;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            transition: background-color 0.3s, box-shadow 0.3s;
        }

        #sidebar nav a:hover {
            background-color: #E0E0E0;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        /* Iconos */
        #sidebar nav a i {
            margin-right: 8px;
        }

        /* Estilo del botón de cerrar menú */
        #close-menu-button {
            display: block;
            /* Visible por defecto */
            padding: 10px;
            margin-top: 20px;
            border: none;
            background-color: #ff5e57;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        #close-menu-button:hover {
            background-color: #ff3d3d;
        }

        /* Ocultar el botón de cerrar menú en pantallas grandes */
        @media (min-width: 769px) {
            #close-menu-button {
                display: none;
            }
        }

        /* Ajuste para que el sidebar no se superponga al header en móviles */
        @media (max-width: 768px) {
            #sidebar {
                top: 55PX;
                /* Para que ocupe todo el alto de la pantalla */
                height: 100vh;
            }
        }
    </style>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>



</head>

<body>

    <!-- Header -->
    <div id="header">
        <div id="menu-icon" onclick="toggleSidebar()">☰</div>
        <div>Inicio SVE</div>
    </div>

    <!-- Sidebar -->
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

    <!-- Body -->
    <div id="body">
        <div class="card">
            <h2>Crear nuevo operativo</h2>
            <form id="form-operativo" method="POST">
                <!-- Nombre -->
                <div class="md-input">
                    <label>Nombre del operativo</label>
                    <input type="text" name="nombre" required>
                </div>

                <!-- Cooperativas -->
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

                <!-- Productores -->
                <div class="md-input">
                    <label>Productores</label>
                    <select id="productores" name="productores[]" multiple="multiple" style="width: 100%">
                        <option value="all" selected>Todos</option>
                        <?php
                        $res = $conn->query("SELECT id, nombre FROM productores");
                        while ($row = $res->fetch_assoc()) {
                            echo "<option value='{$row['id']}'>{$row['nombre']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- Productos -->
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

                <!-- Fechas -->
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
            <table border="1" width="100%" cellpadding="10" id="tabla-operativos">
                <tr>
                    <th>Nombre</th>
                    <th>Cooperativas</th>
                    <th>Productores</th>
                    <th>Productos</th>
                    <th>Fecha inicio</th>
                    <th>Fecha cierre</th>
                    <th>Acción</th>
                </tr>
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
            </table>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('show');
        }

        $(document).ready(function() {
            $('#cooperativas, #productores').select2();

            $('#cooperativas').on('change', function() {
                const coopIds = $(this).val();
                $.post('get_productores_por_coop.php', {
                    cooperativas: coopIds
                }, function(data) {
                    $('#productores').html(data).trigger('change');
                });
            });

            $('#form-operativo').on('submit', function(e) {
                e.preventDefault();
                $.post('crear_operativo.php', $(this).serialize(), function(res) {
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