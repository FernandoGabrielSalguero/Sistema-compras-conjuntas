<?php
// reset_pass_productores.php (con frontend)
// ✔ Muestra formulario
// ✔ Dry-run / Apply
// ✔ Chunk configurable
// ✔ Log en <pre>
// ✔ Evita 403 (muestra error en pantalla)
// ❗ Borralo cuando termines y usa siempre un TOKEN fuerte.

ini_set('display_errors', 1);
error_reporting(E_ALL);
set_time_limit(0);

require_once __DIR__ . '/config.php'; // Debe definir $pdo (PDO)

$TOKEN = 'SVEbajosprecios'; // Cambiálo si querés evitar URL-encode

// util para imprimir seguro
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

function run_reset($pdo, $apply, $chunk, &$statsLog) {
    $role = 'productor';
    $log  = "";

    // En caso de que config.php no ponga exceptions
    try { $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); } catch (Exception $e) {}

    $log .= "=== Reset de contraseñas (rol='{$role}') ===\n";
    $log .= "Modo: " . ($apply ? "APLICAR CAMBIOS" : "DRY-RUN (no escribe)") . "\n";
    $log .= "Lote: {$chunk}\n";

    $total = (int)$pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol='productor'")->fetchColumn();
    $log .= "Productores detectados: {$total}\n";
    if ($total === 0) { $log .= "Nada para hacer.\n"; $statsLog = ['done'=>0,'updated'=>0,'skipped'=>0,'total'=>0]; return $log; }

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

    try {
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

                if (password_verify($usuario, $hashDB)) {
                    $skipped++;
                } else {
                    $newHash = password_hash($usuario, PASSWORD_DEFAULT);
                    if ($apply) {
                        $upd->execute([':hash' => $newHash, ':id' => $id]);
                        $updated++;
                    } else {
                        // En dry-run, logueo una línea corta (sin mostrar hash)
                        $log .= "[DRY] id={$id} usuario='{$usuario}' => se actualizaría\n";
                    }
                }

                $lastId = $id;
                $done++;
            }

            if ($apply) {
                $pdo->commit();
                $log .= "Lote aplicado. Avance: {$done}/{$total} (actualizados {$updated}, omitidos {$skipped})\n";
            } else {
                $log .= "Lote simulado. Avance: {$done}/{$total}\n";
            }
        }

        $log .= "=== FIN ===\n";
        $log .= "Procesados: {$done}\n";
        $log .= "Actualizados: {$updated}\n";
        $log .= "Omitidos (ya ok): {$skipped}\n";

    } catch (Exception $e) {
        if ($apply && $pdo->inTransaction()) $pdo->rollBack();
        $log .= "ERROR: " . $e->getMessage() . "\n";
    }

    $statsLog = ['done'=>$done,'updated'=>$updated,'skipped'=>$skipped,'total'=>$total];
    return $log;
}

// ---- UI ----
$isPost = ($_SERVER['REQUEST_METHOD'] === 'POST');

$defaultChunk = 300;
$apply  = false;
$chunk  = $defaultChunk;
$tokenOk = false;
$logOutput = '';
$stats = null;
$tokenError = '';

if ($isPost) {
    $tokenInput = isset($_POST['token']) ? (string)$_POST['token'] : '';
    $apply      = !empty($_POST['apply']);
    $chunk      = isset($_POST['chunk']) ? max(50, (int)$_POST['chunk']) : $defaultChunk;

    if ($tokenInput !== $TOKEN) {
        $tokenError = "Token inválido. Recordá que si tiene '$', en la URL debe ir como %24. En el formulario no hace falta.";
    } else {
        $tokenOk = true;
        $logOutput = run_reset($pdo, $apply, $chunk, $stats);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Reset masivo de contraseñas (productores)</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
  body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif;background:#f7f7fb;margin:0;padding:24px;color:#111}
  .card{max-width:900px;margin:0 auto;background:#fff;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,.08);padding:20px}
  h1{margin:0 0 16px;color:#4c1d95}
  .row{display:flex;gap:16px;flex-wrap:wrap}
  .col{flex:1 1 220px}
  label{display:block;font-size:14px;margin:6px 0}
  input[type=text],input[type=number]{width:100%;padding:10px;border:1px solid #ddd;border-radius:8px}
  .help{font-size:12px;color:#666}
  .actions{margin-top:12px;display:flex;gap:8px;align-items:center}
  button{background:#4c1d95;color:#fff;border:none;border-radius:8px;padding:10px 16px;cursor:pointer}
  button.secondary{background:#6b7280}
  .badge{display:inline-block;background:#eef2ff;color:#3730a3;border-radius:999px;padding:4px 10px;font-size:12px}
  .error{background:#fee2e2;color:#991b1b;padding:10px;border-radius:8px;margin:8px 0}
  pre{white-space:pre-wrap;background:#0b1020;color:#c9e1ff;border-radius:10px;padding:14px;max-height:460px;overflow:auto}
  .grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
</style>
</head>
<body>
  <div class="card">
    <h1>Reset masivo de contraseñas <span class="badge">rol: productor</span></h1>
    <form method="post">
      <?php if($tokenError): ?><div class="error"><?=h($tokenError)?></div><?php endif; ?>
      <div class="row">
        <div class="col">
          <label>Token</label>
          <input type="text" name="token" placeholder="Ingresá el token exacto" required>
          <div class="help">El token del servidor fue configurado en este archivo.</div>
        </div>
        <div class="col">
          <label>Chunk (lote)</label>
          <input type="number" name="chunk" min="50" step="50" value="<?=h($chunk)?>">
          <div class="help">Tamaño de lote por transacción (300–500 recomendado).</div>
        </div>
      </div>
      <div class="actions">
        <label><input type="checkbox" name="apply" value="1"> Aplicar cambios (si no, hace Dry-Run)</label>
        <button type="submit">Ejecutar</button>
        <button type="button" class="secondary" onclick="location.reload()">Limpiar</button>
      </div>
    </form>

    <?php if($isPost): ?>
      <h3>Resultado</h3>
      <?php if($tokenOk): ?>
        <?php if($stats): ?>
          <div class="grid">
            <div><strong>Total productores:</strong> <?=h($stats['total'])?></div>
            <div><strong>Procesados:</strong> <?=h($stats['done'])?></div>
            <div><strong>Actualizados:</strong> <?=h($stats['updated'])?></div>
            <div><strong>Omitidos (ya ok):</strong> <?=h($stats['skipped'])?></div>
          </div>
        <?php endif; ?>
        <pre><?=h($logOutput)?></pre>
      <?php else: ?>
        <div class="error">No se ejecutó porque el token no validó.</div>
      <?php endif; ?>
    <?php endif; ?>

    <p class="help">⚠️ Al finalizar, borra este archivo del servidor.</p>
  </div>
</body>
</html>
