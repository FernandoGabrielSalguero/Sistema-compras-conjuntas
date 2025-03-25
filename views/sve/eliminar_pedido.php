<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $idPedido = $_POST['idPedido'];

    $dotenv = parse_ini_file("../../.env");
    $conn = new PDO("mysql:host={$dotenv['DB_HOST']};dbname={$dotenv['DB_NAME']}", $dotenv['DB_USER'], $dotenv['DB_PASS']);
    $stmt = $conn->prepare("DELETE FROM pedidos WHERE id = :idPedido");
    $stmt->bindParam(':idPedido', $idPedido);
    $stmt->execute();

    echo "Pedido eliminado correctamente.";
}
