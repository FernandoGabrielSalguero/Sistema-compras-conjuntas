<?php
// reset_pass_productores.php
// MODO DE USO (desde navegador):
//   1) Dry-run (no escribe):     /reset_pass_productores.php?token=TU_TOKEN
//   2) Aplicar cambios (escribe): /reset_pass_productores.php?token=TU_TOKEN&apply=1&chunk=400
//
// IMPORTANTE: Cambiá TU_TOKEN por algo largo/aleatorio y borra este archivo al terminar.

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config.php'; // Debe definir $pdo (PDO)

$TOKEN = 'SVEbajosprecios$';

if (!isset($_GET['token']) || $_GET['token'] !== $TOKEN) {
    http_response_code(403);
    echo "Forbidden";
    exit;
}

$apply = isset($_GET['apply']);
$chunk = isset($_GET['chunk']) ? max(50, (int)$_GET['chunk']) : 300;
$role  = 'productor';

header('Content-Type: text/plain; charset=utf-8');

if (!isset($pdo) || !($pdo instanceof PDO)) {
    echo "ERROR: config.php no define \$pdo (PDO)\n";
    exit(1);
}

echo "=== Reset de contraseñas (solo rol='{$role}') ===\n";
echo "Modo: " . ($apply ? "APLICAR CAMBIOS" : "DRY-RUN (no escribe)") . "\n";
echo "Lote: {$chunk}\n";

try {
    $total = (int)$pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol='productor'")->fetchColumn();
    echo "Productores detectados: {$total}\n";
    if ($total === 0) { echo "Nada para hacer.\n"; exit; }

    $sel = $pdo->prepare("
        SELECT id, usuario, contrasena
        FROM usuarios
        WHERE rol = :rol AND id > :last_id
        ORDER BY id ASC
        LIMIT :lim
    ");
    $upd = $pdo->prepare("UPDATE usuarios SET contrasena = :hash WHERE id = :id");

    $lastId  = 0;
    $done    = 0;
    $updated = 0;
    $skipped = 0;

    while (true) {
        $sel->bindValue(':rol', $role, PDO::PARAM_STR);
        $sel->bindValue(':last_id', $lastId, PDO::PARAM_INT);
        $sel->bindValue(':lim', $chunk, PDO::PARAM_INT);
        $sel->execute();

        $rows = $sel->fetchAll(PDO::FETCH_ASSOC);
        if (!$rows) break;

        if ($apply) $pdo->beginTransaction();

        foreach ($rows as $r) {
            $id      = (int)$r['id'];
            $usuario = (string)$r['usuario'];
            $hashDB  = (string)$r['contrasena'];

            // Si YA es password=usuario, no toco
            if (password_verify($usuario, $hashDB)) {
                $skipped++;
            } else {
                $newHash = password_hash($usuario, PASSWORD_DEFAULT);
                if ($apply) {
                    $upd->execute([':hash' => $newHash, ':id' => $id]);
                    $updated++;
                } else {
                    echo "[DRY] id={$id} usuario='{$usuario}' => se actualizaría\n";
                }
            }

            $lastId = $id;
            $done++;
        }

        if ($apply) {
            $pdo->commit();
            echo "Lote aplicado. Avance: {$done}/{$total} (actualizados {$updated}, omitidos {$skipped})\n";
        } else {
            echo "Lote simulado. Avance: {$done}/{$total}\n";
        }
    }

    echo "=== FIN ===\n";
    echo "Procesados: {$done}\n";
    echo "Actualizados: {$updated}\n";
    echo "Omitidos (ya ok): {$skipped}\n";

} catch (Exception $e) {
    if ($apply && $pdo->inTransaction()) $pdo->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
