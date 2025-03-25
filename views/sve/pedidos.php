<?php

// Habilitar la visualización de errores en pantalla
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir la conexión a la base de datos
include('conexion.php');

// Consulta de pedidos
$query = "SELECT p.id_pedido, pr.nombre AS productor, c.nombre AS cooperativa, pr.rol, p.fecha_compra, p.valor_total, p.estado, p.factura
          FROM pedidos p
          JOIN productores pr ON p.id_productor = pr.id_productor
          JOIN cooperativas c ON p.id_cooperativa = c.id_cooperativa";

$result = $conn->query($query);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos SVE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <style>
        .table-container {
            margin: 20px;
            padding: 20px;
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>

<div class="table-container">
    <div class="mb-3">
        <label>Filtrar por Productor:</label>
        <input type="text" id="filterProductor" class="form-control" placeholder="Nombre del productor">
        <label>Filtrar por Cooperativa:</label>
        <input type="text" id="filterCooperativa" class="form-control" placeholder="Nombre de la cooperativa">
        <label>Filtrar por Fecha:</label>
        <input type="date" id="filterDate" class="form-control">
    </div>

    <table class="table table-hover">
        <thead>
            <tr>
                <th>Productor</th>
                <th>Cooperativa</th>
                <th>Rol</th>
                <th>Fecha de Compra</th>
                <th>Valor Total</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['productor'] ?></td>
                    <td><?= $row['cooperativa'] ?></td>
                    <td><?= $row['rol'] ?></td>
                    <td><?= $row['fecha_compra'] ?></td>
                    <td><?= $row['valor_total'] ?></td>
                    <td>
                        <select class="form-select" aria-label="Estado del Pedido">
                            <option value="Pedido recibido">Pedido recibido</option>
                            <option value="Pedido cancelado">Pedido cancelado</option>
                            <option value="Pedido OK pendiente de factura">Pedido OK pendiente de factura</option>
                            <option value="Pedido OK FACTURADO">Pedido OK FACTURADO</option>
                            <option value="Pedido pendiente de retito">Pedido pendiente de retito</option>
                            <option value="Pedido en camino al productor">Pedido en camino al productor</option>
                            <option value="Pedido en camino a la cooperativa">Pedido en camino a la cooperativa</option>
                        </select>
                    </td>
                    <td>
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#viewDetailModal">Ver Detalle</button>
                        <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteConfirmModal">Eliminar</button>
                        <?php if ($row['factura']): ?>
                            <button class="btn btn-success btn-sm">Ver Factura</button>
                        <?php else: ?>
                            <button class="btn btn-info btn-sm">Añadir Factura</button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Modal para Ver Detalle / Modificar Pedido -->
<div class="modal fade" id="viewDetailModal" tabindex="-1" aria-labelledby="viewDetailModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewDetailModalLabel">Detalle del Pedido</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <!-- Aquí se mostrará el detalle del pedido -->
        <form>
            <div class="mb-3">
                <label for="idProducto" class="form-label">ID Producto</label>
                <input type="text" class="form-control" id="idProducto" disabled>
            </div>
            <div class="mb-3">
                <label for="cantidad" class="form-label">Cantidad</label>
                <input type="number" class="form-control" id="cantidad">
            </div>
            <div class="mb-3">
                <label for="precioUnitario" class="form-label">Precio Unitario</label>
                <input type="text" class="form-control" id="precioUnitario" disabled>
            </div>
            <div class="mb-3">
                <label for="subtotal" class="form-label">Subtotal</label>
                <input type="text" class="form-control" id="subtotal" disabled>
            </div>
            <div class="mb-3">
                <label for="total" class="form-label">Total</label>
                <input type="text" class="form-control" id="total" disabled>
            </div>
            <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
