<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $idPedido = $_POST['idPedido'];
    $nuevoEstado = $_POST['nuevoEstado'];

    $dotenv = parse_ini_file("../../.env");
    $conn = new PDO("mysql:host={$dotenv['DB_HOST']};dbname={$dotenv['DB_NAME']}", $dotenv['DB_USER'], $dotenv['DB_PASS']);
    $stmt = $conn->prepare("UPDATE pedidos SET estado_compra = :nuevoEstado WHERE id = :idPedido");
    $stmt->bindParam(':nuevoEstado', $nuevoEstado);
    $stmt->bindParam(':idPedido', $idPedido);
    $stmt->execute();

    echo "Estado actualizado correctamente.";
}
