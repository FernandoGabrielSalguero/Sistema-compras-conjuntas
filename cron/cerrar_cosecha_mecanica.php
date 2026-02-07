<?php

declare(strict_types=1);

ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL);

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../models/coop_cosechaMecanicaModel.php';
require_once __DIR__ . '/../mail/Mail.php';

use SVE\Mail\Mail;

$now = new DateTimeImmutable('now');
$hoy = $now->format('Y-m-d');

try {
    $model = new CoopCosechaMecanicaModel($pdo);

    $stmt = $pdo->prepare(
        "SELECT id, nombre, fecha_apertura, fecha_cierre, descripcion, estado
         FROM CosechaMecanica
         WHERE estado <> 'cerrado'
           AND fecha_cierre <= :hoy"
    );
    $stmt->execute([':hoy' => $hoy]);
    $operativos = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

    foreach ($operativos as $op) {
        $fechaCierreRaw = (string)($op['fecha_cierre'] ?? '');
        $soloFecha = explode(' ', $fechaCierreRaw)[0];
        $fechaCierre = DateTimeImmutable::createFromFormat('Y-m-d H:i', $soloFecha . ' 23:39');
        if (!$fechaCierre || $now < $fechaCierre) {
            continue;
        }

        $upd = $pdo->prepare("UPDATE CosechaMecanica SET estado = 'cerrado' WHERE id = :id AND estado <> 'cerrado'");
        $upd->execute([':id' => $op['id']]);

        if ($upd->rowCount() === 0) {
            continue;
        }

        $op['estado'] = 'cerrado';

        $coopStmt = $pdo->prepare(
            "SELECT f.cooperativa_id_real AS id_real,
                    f.fecha_firma,
                    u.usuario,
                    ui.nombre,
                    ui.correo
             FROM cosechaMecanica_coop_contrato_firma f
             INNER JOIN usuarios u
                 ON u.id_real = f.cooperativa_id_real
                AND u.rol = 'cooperativa'
             LEFT JOIN usuarios_info ui
                 ON ui.usuario_id = u.id
             WHERE f.contrato_id = :contrato_id
               AND f.acepto = 1"
        );
        $coopStmt->execute([':contrato_id' => $op['id']]);
        $cooperativas = $coopStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        foreach ($cooperativas as $coop) {
            $correo = trim((string)($coop['correo'] ?? ''));
            if ($correo === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            if ($model->correoCierreEnviado((int) $op['id'], (string) $coop['id_real'])) {
                continue;
            }

            $nombreCoop = trim((string)($coop['nombre'] ?? ''));
            $usuarioCoop = trim((string)($coop['usuario'] ?? ''));
            $idRealCoop = trim((string)($coop['id_real'] ?? ''));

            $nomBusqueda = $nombreCoop !== '' ? $nombreCoop : ($usuarioCoop !== '' ? $usuarioCoop : $idRealCoop);
            if ($nomBusqueda === '') {
                $nomBusqueda = 'Cooperativa';
            }

            $partStmt = $pdo->prepare(
                "SELECT productor, superficie, variedad, prod_estimada, fecha_estimada, km_finca, flete, seguro_flete, finca_id
                 FROM cosechaMecanica_cooperativas_participacion
                 WHERE contrato_id = :contrato_id
                   AND (nom_cooperativa = :nom
                        OR nom_cooperativa = :usuario
                        OR nom_cooperativa = :id_real)
                 ORDER BY id ASC"
            );
            $partStmt->execute([
                ':contrato_id' => $op['id'],
                ':nom' => $nomBusqueda,
                ':usuario' => $usuarioCoop,
                ':id_real' => $idRealCoop,
            ]);
            $participaciones = $partStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            $mailResp = Mail::enviarCierreCosechaMecanica([
                'cooperativa_nombre' => $nombreCoop !== '' ? $nombreCoop : ($usuarioCoop !== '' ? $usuarioCoop : 'Cooperativa'),
                'cooperativa_correo' => $correo,
                'cooperativa_id_real' => (string) $coop['id_real'],
                'operativo' => $op,
                'participaciones' => $participaciones,
                'firma_fecha' => $coop['fecha_firma'] ?? null,
                'enviado_por' => 'cron',
            ]);

            if (!($mailResp['ok'] ?? false)) {
                $err = $mailResp['error'] ?? 'Error desconocido';
                error_log('[CosechaMecanica] Error enviando cierre a ' . $correo . ': ' . $err);
                continue;
            }

        }
    }
} catch (Throwable $e) {
    error_log('[CosechaMecanica] Error en cron cierre: ' . $e->getMessage());
}
