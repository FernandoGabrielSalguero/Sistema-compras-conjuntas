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

if (!isset($_GET['id'])) {
die("Falta ID de pedido");
}

$pedido_id = (int) $_GET['id'];
$model = new SveMercadoDigitalModel($pdo);

// Obtener pedido y productos
$stmt = $pdo->prepare("SELECT p.*, i1.nombre AS nombre_cooperativa, i2.nombre AS nombre_productor
FROM pedidos p
JOIN usuarios u1 ON u1.id_real = p.cooperativa
JOIN usuarios_info i1 ON i1.usuario_id = u1.id
JOIN usuarios u2 ON u2.id_real = p.productor
JOIN usuarios_info i2 ON i2.usuario_id = u2.id
WHERE p.id = ?");
$stmt->execute([$pedido_id]);
$pedido = $stmt->fetch(PDO::FETCH_ASSOC);

$stmtProd = $pdo->prepare("SELECT * FROM detalle_pedidos WHERE pedido_id = ?");
$stmtProd->execute([$pedido_id]);
$productos = $stmtProd->fetchAll(PDO::FETCH_ASSOC);

$productosDisponibles = $model->obtenerProductosAgrupadosPorCategoria();
?>

<!DOCTYPE html>
<html lang="es">
    
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Editar Pedido</title>
        <link rel="stylesheet" href="https://www.fernandosalguero.com/cdn/assets/css/framework.css">
    <style>
        body {
            padding: 2rem;
            background: #f3f4f6;
            font-family: sans-serif;
        }

        table th,
        table td {
            text-align: left;
            padding: 6px;
        }

        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }
    </style>
</head>

<body>
    <h2>Editar Pedido #<?= $pedido_id ?></h2>

    <form id="formEditarPedido" class="form-modern">
        <input type="hidden" name="id" value="<?= $pedido_id ?>">

        <div class="grid">
            <div>
                <label>Cooperativa:</label>
                <input class="input" value="<?= htmlspecialchars($pedido['nombre_cooperativa']) ?>" disabled>
            </div>
            <div>
                <label>Productor:</label>
                <input class="input" value="<?= htmlspecialchars($pedido['nombre_productor']) ?>" disabled>
            </div>

            <div>
                <label>A nombre de:</label>
                <select class="input" name="persona_facturacion">
                    <option value="cooperativa" <?= $pedido['persona_facturacion'] === 'cooperativa' ? 'selected' : '' ?>>Cooperativa</option>
                    <option value="productor" <?= $pedido['persona_facturacion'] === 'productor' ? 'selected' : '' ?>>Productor</option>
                </select>
            </div>
            <div>
                <label>Condición:</label>
                <select class="input" name="condicion_facturacion">
                    <option value="responsable inscripto" <?= $pedido['condicion_facturacion'] === 'responsable inscripto' ? 'selected' : '' ?>>Responsable inscripto</option>
                    <option value="monotributista" <?= $pedido['condicion_facturacion'] === 'monotributista' ? 'selected' : '' ?>>Monotributista</option>
                </select>
            </div>

            <div>
                <label>Afiliación:</label>
                <select class="input" name="afiliacion">
                    <option value="socio" <?= $pedido['afiliacion'] === 'socio' ? 'selected' : '' ?>>Socio</option>
                    <option value="tercero" <?= $pedido['afiliacion'] === 'tercero' ? 'selected' : '' ?>>Tercero</option>
                </select>
            </div>
            <div>
                <label>Ha. cooperativa:</label>
                <input type="number" step="0.01" class="input" name="hectareas" value="<?= $pedido['ha_cooperativa'] ?>">
            </div>
        </div>

        <div class="input-group">
            <label>Observaciones:</label>
            <textarea class="input" name="observaciones" rows="2"><?= htmlspecialchars($pedido['observaciones']) ?></textarea>
        </div>

        <hr>
        <h3>Agregar producto</h3>
        <div class="grid">
            <select class="input" id="selectorProducto">
                <option disabled selected>Seleccione un producto</option>
                <?php foreach ($productosDisponibles as $categoria => $grupo): ?>
                    <optgroup label="<?= $categoria ?>">
                        <?php foreach ($grupo as $prod): ?>
                            <option value="<?= $prod['producto_id'] ?>" data-json='<?= json_encode($prod) ?>'><?= $prod['Nombre_producto'] ?></option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endforeach; ?>
            </select>
            <button type="button" class="btn btn-info" onclick="agregarProducto()">+ Agregar</button>
        </div>

        <h3>Productos del pedido</h3>
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
                <?php foreach ($productos as $p):
                    $subtotal = $p['precio_producto'] * $p['cantidad']; ?>
                    <tr>
                        <td><?= htmlspecialchars($p['nombre_producto']) ?><input type="hidden" name="productos[][id]" value="<?= $p['producto_id'] ?>"></td>
                        <td><?= $p['categoria'] ?></td>
                        <td><?= $p['unidad_medida_venta'] ?></td>
                        <td><input type="number" class="input" name="productos[][cantidad]" value="<?= $p['cantidad'] ?>" onchange="actualizarTotales()"></td>
                        <td>$<?= number_format($p['precio_producto'], 2) ?></td>
                        <td><?= $p['alicuota'] ?>%</td>
                        <td class="subtotal">$<?= number_format($subtotal, 2) ?></td>
                        <td><button type="button" onclick="this.closest('tr').remove(); actualizarTotales()">❌</button></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <p><strong>Total sin IVA:</strong> $<span id="totalSinIva">0.00</span></p>
        <p><strong>IVA:</strong> $<span id="totalIva">0.00</span></p>
        <p><strong>Total Pedido:</strong> $<span id="totalConIva">0.00</span></p>

        <div class="modal-actions">
            <button type="submit" class="btn btn-aceptar">Guardar cambios</button>
            <button type="button" class="btn btn-cancelar" onclick="window.parent.document.getElementById('iframeEditarModal').style.display='none'">Cancelar</button>
        </div>
    </form>

    <script>
        function actualizarTotales() {
            let subtotal = 0;
            let iva = 0;
            document.querySelectorAll('#tablaProductos tbody tr').forEach(tr => {
                const cantidad = parseFloat(tr.querySelector('input[name*="cantidad"]').value) || 0;
                const precio = parseFloat(tr.children[4].textContent.replace('$', '')) || 0;
                const alicuota = parseFloat(tr.children[5].textContent.replace('%', '')) || 0;
                const sub = cantidad * precio;
                const ivaCalc = sub * (alicuota / 100);
                subtotal += sub;
                iva += ivaCalc;
                tr.querySelector('.subtotal').textContent = "$" + (sub).toFixed(2);
            });
            document.getElementById('totalSinIva').textContent = subtotal.toFixed(2);
            document.getElementById('totalIva').textContent = iva.toFixed(2);
            document.getElementById('totalConIva').textContent = (subtotal + iva).toFixed(2);
        }

        function agregarProducto() {
            const selector = document.getElementById('selectorProducto');
            const option = selector.options[selector.selectedIndex];
            const prod = JSON.parse(option.dataset.json);
            const tr = document.createElement('tr');
            tr.innerHTML = `
        <td>${prod.Nombre_producto}<input type="hidden" name="productos[][id]" value="${prod.producto_id}"></td>
        <td>${prod.categoria}</td>
        <td>${prod.Unidad_Medida_venta}</td>
        <td><input type="number" class="input" name="productos[][cantidad]" value="1" onchange="actualizarTotales()"></td>
        <td>$${parseFloat(prod.Precio_producto).toFixed(2)}</td>
        <td>${prod.alicuota}%</td>
        <td class="subtotal">$${parseFloat(prod.Precio_producto).toFixed(2)}</td>
        <td><button type="button" onclick="this.closest('tr').remove(); actualizarTotales()">❌</button></td>
      `;
            document.querySelector('#tablaProductos tbody').appendChild(tr);
            selector.selectedIndex = 0;
            actualizarTotales();
        }

        actualizarTotales();

        document.getElementById('formEditarPedido').addEventListener('submit', async function(e) {
            e.preventDefault();
            const data = new FormData(this);

            const productos = [];
            const filas = document.querySelectorAll('#tablaProductos tbody tr');
            filas.forEach(tr => {
                productos.push({
                    id: tr.querySelector('input[type="hidden"]').value,
                    nombre: tr.children[0].textContent.trim(),
                    cantidad: tr.querySelector('input[name*="cantidad"]').value
                });
            });

            const payload = {
                accion: 'editar_pedido',
                id: parseInt(data.get('id')),
                persona_facturacion: data.get('persona_facturacion'),
                condicion_facturacion: data.get('condicion_facturacion'),
                afiliacion: data.get('afiliacion'),
                hectareas: parseFloat(data.get('hectareas')),
                observaciones: data.get('observaciones'),
                productos
            };

            try {
                const res = await fetch('/controllers/sve_listadoPedidosController.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const json = await res.json();
                if (!json.success) throw new Error(json.message);

                alert('Pedido actualizado correctamente');
                window.parent.location.reload();
            } catch (err) {
                alert('Error al guardar: ' + err.message);
            }
        });
    </script>
</body>

</html>