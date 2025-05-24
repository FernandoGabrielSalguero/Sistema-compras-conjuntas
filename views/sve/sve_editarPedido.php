<?php
// Mostrar errores en pantalla (útil en desarrollo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../models/sve_MercadoDigitalModel.php';

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'sve') {
    die("Acceso denegado");
}

$model = new SveMercadoDigitalModel($pdo);

// ID del pedido
$id = intval($_GET['id'] ?? 0);
if (!$id) die("ID de pedido inválido");

$pedido = $model->obtenerListadoPedidos('', 0, 9999);
$pedido = array_filter($pedido, fn($p) => $p['id'] == $id);
$pedido = array_values($pedido)[0] ?? null;

if (!$pedido) die("Pedido no encontrado");

// Productos del pedido
$stmt = $pdo->prepare("SELECT * FROM detalle_pedidos WHERE pedido_id = ?");
$stmt->execute([$id]);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">

    <link rel="stylesheet" href="https://www.fernandosalguero.com/cdn/assets/css/framework.css">

    <title>Editar Pedido</title>
    <style>
        body {
            margin: 0;
        }

        .modal {
            position: fixed;
            top: 0;
            left: 0;
            background: rgba(0, 0, 0, 0.6);
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            max-width: 800px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        table td,
        table th {
            border: 1px solid #ccc;
            padding: 8px;
        }

        .acciones {
            margin-top: 1rem;
            text-align: right;
        }

        .acciones button {
            padding: 8px 12px;
        }

        input,
        select,
        textarea {
            width: 100%;
            margin-top: 4px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
    </style>
</head>

<body>

    <div class="modal" id="editarModal">
        <div class="modal-content">
            <h2>Editar Pedido #<?= $id ?></h2>
            <!-- Dentro de .modal-content -->
            <form id="formEditarPedido" class="form-modern">

                <div class="form-grid grid-2">
                    <div>
                        <label>Cooperativa</label>
                        <input class="input" disabled value="<?= htmlspecialchars($pedido['nombre_cooperativa']) ?>">
                    </div>
                    <div>
                        <label>Productor</label>
                        <input class="input" disabled value="<?= htmlspecialchars($pedido['nombre_productor']) ?>">
                    </div>

                    <div>
                        <label>A nombre de:</label>
                        <select class="input" name="persona_facturacion">...</select>
                    </div>
                    <div>
                        <label>Condición:</label>
                        <select class="input" name="condicion_facturacion">...</select>
                    </div>
                    <div>
                        <label>Afiliación:</label>
                        <select class="input" name="afiliacion">...</select>
                    </div>
                    <div>
                        <label>Ha. cooperativa:</label>
                        <input type="number" class="input" name="hectareas" step="0.01" value="<?= $pedido['ha_cooperativa'] ?>">
                    </div>
                </div>

                <div class="input-group">
                    <label>Observaciones:</label>
                    <textarea class="input" name="observaciones"><?= htmlspecialchars($pedido['observaciones']) ?></textarea>
                </div>

                <!-- Selector para nuevo producto -->
                <h4>Agregar producto</h4>
                <div class="form-grid grid-2">
                    <select class="input" id="selectorProducto">
                        <option disabled selected>Seleccioná un producto</option>
                        <?php
                        $productosDisponibles = $model->obtenerProductosAgrupadosPorCategoria();
                        foreach ($productosDisponibles as $categoria => $productos):
                            echo "<optgroup label='$categoria'>";
                            foreach ($productos as $p):
                        ?>
                                <option value="<?= $p['producto_id'] ?>" data-json='<?= json_encode($p) ?>'>
                                    <?= $p['Nombre_producto'] ?>
                                </option>
                        <?php
                            endforeach;
                            echo "</optgroup>";
                        endforeach;
                        ?>
                    </select>
                    <button type="button" class="btn btn-info" onclick="agregarProducto()">➕ Agregar</button>
                </div>

                <!-- Tabla de productos -->
                <h4>Productos del pedido</h4>
                <table class="data-table" id="tablaProductos">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Unidad</th>
                            <th>Cantidad</th>
                            <th>Precio</th>
                            <th>Alicuota</th>
                            <th>Subtotal</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productos as $prod): ?>
                            <tr>
                                <td>
                                    <?= htmlspecialchars($prod['nombre_producto']) ?>
                                    <input type="hidden" name="productos[][id]" value="<?= $prod['producto_id'] ?>">
                                    <input type="hidden" name="productos[][nombre]" value="<?= $prod['nombre_producto'] ?>">
                                </td>
                                <td><?= $prod['categoria'] ?></td>
                                <td><?= $prod['unidad_medida_venta'] ?></td>
                                <td><input type="number" class="input" name="productos[][cantidad]" value="<?= $prod['cantidad'] ?>"></td>
                                <td>$<?= number_format($prod['precio_producto'], 2) ?></td>
                                <td><?= $prod['alicuota'] ?>%</td>
                                <td>$<?= number_format($prod['precio_producto'] * $prod['cantidad'], 2) ?></td>
                                <td><button type="button" onclick="this.closest('tr').remove()">❌</button></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="modal-actions">
                    <button type="submit" class="btn btn-aceptar">Guardar cambios</button>
                    <button type="button" class="btn btn-cancelar" onclick="window.parent.document.getElementById('iframeEditarModal').style.display='none'">Cancelar</button>
                </div>

            </form>
        </div>
    </div>

    <script>
        document.getElementById('formEditarPedido').addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);

            // convertir FormData a objeto limpio
            const data = {
                accion: 'editar_pedido',
                id: parseInt(formData.get('id')),
                persona_facturacion: formData.get('persona_facturacion'),
                condicion_facturacion: formData.get('condicion_facturacion'),
                afiliacion: formData.get('afiliacion'),
                hectareas: parseFloat(formData.get('hectareas')),
                observaciones: formData.get('observaciones'),
                productos: []
            };

            const rows = document.querySelectorAll('#tablaProductos tbody tr');
            rows.forEach(row => {
                const id = row.querySelector('input[name="productos[][id]"]')?.value;
                const nombre = row.querySelector('input[name="productos[][nombre]"]')?.value;
                const cantidad = row.querySelector('input[name="productos[][cantidad]"]')?.value;

                data.productos.push({
                    id: id ? parseInt(id) : null,
                    nombre,
                    cantidad: parseInt(cantidad)
                });
            });

            try {
                const res = await fetch('/controllers/sve_listadoPedidosController.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                const json = await res.json();

                if (json.success) {
                    alert("✅ Pedido actualizado");
                    window.location.href = 'sve_listadoPedidos.php';
                } else {
                    alert("❌ Error: " + json.message);
                }
            } catch (err) {
                console.error(err);
                alert("❌ Error al guardar");
            }

            function agregarProducto() {
                const select = document.getElementById('selectorProducto');
                const selectedOption = select.options[select.selectedIndex];
                if (!selectedOption || !selectedOption.dataset.json) return;

                const prod = JSON.parse(selectedOption.dataset.json);
                const tbody = document.querySelector('#tablaProductos tbody');

                const row = document.createElement('tr');
                row.innerHTML = `
        <td>${prod.Nombre_producto}
            <input type="hidden" name="productos[][id]" value="${prod.producto_id}">
            <input type="hidden" name="productos[][nombre]" value="${prod.Nombre_producto}">
        </td>
        <td>${prod.categoria}</td>
        <td>${prod.Unidad_Medida_venta}</td>
        <td><input type="number" class="input" name="productos[][cantidad]" value="1"></td>
        <td>$${parseFloat(prod.Precio_producto).toFixed(2)}</td>
        <td>${prod.alicuota}%</td>
        <td>$0.00</td>
        <td><button type="button" onclick="this.closest('tr').remove()">❌</button></td>
    `;
                tbody.appendChild(row);

                select.selectedIndex = 0;
            }
        });
    </script>

</body>

</html>