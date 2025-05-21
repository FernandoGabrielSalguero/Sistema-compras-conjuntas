<?php
require_once __DIR__ . '/../../config.php';

function cerrarOperativosVencidos(PDO $pdo): array {
    $hoy = new DateTime();
    $cerrados = 0;
    $abiertos = 0;
    $totales = 0;
    $pendientes = [];

    // Obtener todos los operativos
    $sql = "SELECT id, nombre, fecha_cierre, estado FROM operativos";
    $stmt = $pdo->query($sql);
    $operativos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $totales = count($operativos);

    foreach ($operativos as $op) {
        $fechaCierre = new DateTime($op['fecha_cierre']);
        $dias = (int)$hoy->diff($fechaCierre)->format('%r%a');

        if ($op['estado'] === 'cerrado') {
            $cerrados++;
        } elseif ($op['estado'] === 'abierto') {
            $abiertos++;

            if ($fechaCierre < $hoy) {
                // Cerrar operativo vencido
                $update = $pdo->prepare("UPDATE operativos SET estado = 'cerrado' WHERE id = ?");
                $update->execute([$op['id']]);
                $cerrados++;
                $abiertos--;
            } else {
                // Guardar info de los que faltan cerrar
                $pendientes[] = [
                    'id' => $op['id'],
                    'nombre' => $op['nombre'],
                    'dias_faltantes' => $dias
                ];
            }
        }
    }

    return [
        'total_operativos' => $totales,
        'cerrados' => $cerrados,
        'abiertos' => $abiertos,
        'pendientes' => $pendientes
    ];
}
