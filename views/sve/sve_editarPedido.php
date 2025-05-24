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
    <title>Editar Pedido</title>
    <!-- Íconos de Material Design -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />

    <!-- Framework Success desde CDN -->
    <link rel="stylesheet" href="https://www.fernandosalguero.com/cdn/assets/css/framework.css">
    <script src="https://www.fernandosalguero.com/cdn/assets/javascript/framework.js" defer></script>
</head>

<body style="padding: 2rem; background: #f9fafb;">

    <h2 style="margin-bottom: 1rem;">Editar Pedido #<?= $pedido_id ?></h2>

    <form id="formEditarPedido" class="form-modern">
        <input type="hidden" name="id" value="<?= $pedido_id ?>">

        <div class="form-grid grid-3">
            <div class="input-group">
                <label for="cooperativa">Cooperativa</label>
                <div class="input-icon">
                    <span class="material-icons">business</span>
                    <input type="text" id="cooperativa" class="input" value="<?= htmlspecialchars($pedido['nombre_cooperativa']) ?>" disabled>
                </div>
            </div>

            <div class="input-group">
                <label for="productor">Productor</label>
                <div class="input-icon">
                    <span class="material-icons">person</span>
                    <input type="text" id="productor" class="input" value="<?= htmlspecialchars($pedido['nombre_productor']) ?>" disabled>
                </div>
            </div>

            <div class="input-group">
                <label for="persona_facturacion">A nombre de</label>
                <div class="input-icon">
                    <span class="material-icons">badge</span>
                    <select id="persona_facturacion" name="persona_facturacion" class="input">
                        <option value="cooperativa">Cooperativa</option>
                        <option value="productor">Productor</option>
                    </select>
                </div>
            </div>

            <div class="input-group">
                <label for="condicion_facturacion">Condición</label>
                <div class="input-icon">
                    <span class="material-icons">verified_user</span>
                    <select id="condicion_facturacion" name="condicion_facturacion" class="input">
                        <option value="responsable inscripto">Responsable Inscripto</option>
                        <option value="monotributista">Monotributista</option>
                    </select>
                </div>
            </div>

            <div class="input-group">
                <label for="afiliacion">Afiliación</label>
                <div class="input-icon">
                    <span class="material-icons">groups</span>
                    <select id="afiliacion" name="afiliacion" class="input">
                        <option value="socio">Socio</option>
                        <option value="tercero">Tercero</option>
                    </select>
                </div>
            </div>

            <div class="input-group">
                <label for="hectareas">Ha. cooperativa</label>
                <div class="input-icon">
                    <span class="material-icons">agriculture</span>
                    <input type="number" name="hectareas" id="hectareas" step="0.01" class="input" placeholder="Cantidad de hectáreas...">
                </div>
            </div>
        </div>
        <div class="input-group">
            <label for="observaciones">Observaciones</label>
            <div class="input-icon">
                <span class="material-icons">notes</span>
                <textarea id="observaciones" name="observaciones" rows="2" class="input" placeholder="Notas adicionales..."></textarea>
            </div>
        </div>

        <hr style="margin: 2rem 0;">

        <h3>Agregar producto</h3>
        <div class="form-grid grid-2">
            <div class="input-group">
                <label for="selectorProducto">Seleccioná un producto</label>
                <div class="input-icon">
                    <span class="material-icons">add_shopping_cart</span>
                    <select id="selectorProducto" class="input">
                        <option disabled selected>Seleccioná un producto</option>
                        <?php foreach ($productosDisponibles as $categoria => $items): ?>
                            <optgroup label="<?= htmlspecialchars($categoria) ?>">
                                <?php foreach ($items as $prod): ?>
                                    <option
                                        value="<?= $prod['producto_id'] ?>"
                                        data-json='<?= json_encode($prod) ?>'>
                                        <?= htmlspecialchars($prod['Nombre_producto']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="input-group" style="align-self: flex-end;">
                <button type="button" class="btn btn-info" onclick="agregarProducto()">+ Agregar</button>
            </div>
        </div>

        <h3 style="margin-top: 2rem;">Productos del pedido</h3>
        <table class="data-table" id="tablaProductos">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Categoría</th>
                    <th>Unidad</th>
                    <th>Cantidad</th>
                    <th>Precio</th>
                    <th>IVA</th>
                    <th>Subtotal</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($productos as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['nombre_producto']) ?><input type="hidden" name="productos[][id]" value="<?= $p['producto_id'] ?>"></td>
                        <td><?= $p['categoria'] ?></td>
                        <td><?= $p['unidad_medida_venta'] ?></td>
                        <td>
                            <div class="input-icon">
                                <span class="material-icons">123</span>
                                <input type="number" class="input" name="productos[][cantidad]" value="<?= $p['cantidad'] ?>" onchange="actualizarTotales()">
                            </div>
                        </td>
                        <td>$<?= number_format($p['precio_producto'], 2) ?></td>
                        <td><?= $p['alicuota'] ?>%</td>
                        <td class="subtotal">$<?= number_format($p['precio_producto'] * $p['cantidad'], 2) ?></td>
                        <td>
                            <button type="button" class="btn-icon" onclick="this.closest('tr').remove(); actualizarTotales()">
                                <span class="material-icons" style="color: red;">delete</span>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="form-grid grid-3" style="margin-top: 1rem;">
            <p><strong>Total sin IVA:</strong> $<span id="totalSinIva">0.00</span></p>
            <p><strong>IVA:</strong> $<span id="totalIva">0.00</span></p>
            <p><strong>Total Pedido:</strong> $<span id="totalConIva">0.00</span></p>
        </div>

        <div class="modal-actions" style="margin-top: 1.5rem;">
            <button type="submit" class="btn btn-aceptar">Guardar cambios</button>
            <button type="button" class="btn btn-cancelar" onclick="window.parent.document.getElementById('iframeEditarModal').style.display='none'">Cancelar</button>
        </div>
    </form>

    <script>
        function actualizarTotales() {
            let subtotal = 0;
            let iva = 0;
            document.querySelectorAll('#tablaProductos tbody tr').forEach(tr => {
                const cantidadInput = tr.querySelector('input[name="productos[][cantidad]"]');
                const cantidad = parseFloat(cantidadInput.value) || 0;
                const precio = parseFloat(tr.children[4].textContent.replace('$', '')) || 0;
                const alicuota = parseFloat(tr.children[5].textContent.replace('%', '')) || 0;
                const sub = cantidad * precio;
                const ivaCalc = sub * (alicuota / 100);
                subtotal += sub;
                iva += ivaCalc;
                tr.querySelector('.subtotal').textContent = "$" + sub.toFixed(2);
            });
            document.getElementById('totalSinIva').textContent = subtotal.toFixed(2);
            document.getElementById('totalIva').textContent = iva.toFixed(2);
            document.getElementById('totalConIva').textContent = (subtotal + iva).toFixed(2);
        }

        function agregarProducto() {
            const selector = document.getElementById('selectorProducto');
            const option = selector.options[selector.selectedIndex];
            if (!option || !option.dataset.json) return;

            const p = JSON.parse(option.dataset.json);
            const tr = document.createElement('tr');
            tr.innerHTML = `
        <td>${p.Nombre_producto}<input type="hidden" name="productos[][id]" value="${p.producto_id}"></td>
        <td>${p.categoria}</td>
        <td>${p.Unidad_Medida_venta}</td>
<td>
  <div class="input-icon">
    <span class="material-icons">123</span>
    <input type="number" class="input" name="productos[][cantidad]" value="1" onchange="actualizarTotales()">
  </div>
</td>        <td>$${parseFloat(p.Precio_producto).toFixed(2)}</td>
        <td>${p.alicuota}%</td>
        <td class="subtotal">$${parseFloat(p.Precio_producto).toFixed(2)}</td>
        <button type="button" class="btn-icon" onclick="this.closest('tr').remove(); actualizarTotales()">
        <span class="material-icons" style="color:red;">delete</span>
        </button>
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
            document.querySelectorAll('#tablaProductos tbody tr').forEach(tr => {
                productos.push({
                    id: tr.querySelector('input[type=\"hidden\"]').value,
                    nombre: tr.children[0].textContent.trim(),
                    cantidad: tr.querySelector('input').value
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