<?php
// reset_pass_productores.php
// - Vista "bloques": recorre productores de a 100 y permite actualizar contraseñas por bloque.
// - Vista "limpieza": lista usuarios con "?" (y caracteres no ASCII comunes) para corregir nombre y (opcional) sincronizar contraseña.
// ⚠️ Borrar el archivo cuando termines.

ini_set('display_errors', 1);
error_reporting(E_ALL);
set_time_limit(0);

require_once __DIR__ . '/config.php'; // Debe definir $pdo (PDO conectado a la DB)

$TOKEN   = 'SVEtokenSeguro2025';     // cambiá por algo fuerte (solo letras/números)
$ROLE    = 'productor';
$PERPAGE = 100;

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

// ---------- parámetros ----------
$tokenInput = isset($_GET['token']) ? (string)$_GET['token'] : '';
$view       = isset($_GET['view']) ? (string)$_GET['view'] : 'blocks'; // blocks | cleanup
$page       = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$applyBlock = isset($_GET['apply']) && $_GET['apply'] === '1';

// acciones limpieza (todas por GET para evitar WAF/POST)
$saveOne    = isset($_GET['save']) && $_GET['save'] === '1';
$editId     = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$newUser    = isset($_GET['new_usuario']) ? trim((string)$_GET['new_usuario']) : '';
$syncPass   = isset($_GET['sync']) && $_GET['sync'] === '1';

// ---------- acceso por token ----------
if ($tokenInput === '' || $tokenInput !== $TOKEN) {
    $err = ($tokenInput !== '' && $tokenInput !== $TOKEN) ? 'Token inválido' : '';
    ?>
    <!doctype html>
    <html lang="es">
    <head>
      <meta charset="utf-8">
      <title>Acceso – herramienta por bloques</title>
      <style>
        body{font-family:system-ui,Arial;background:#f6f7fb;margin:0;padding:40px;color:#111}
        .card{max-width:420px;margin:0 auto;background:#fff;border-radius:12px;box-shadow:0 2px 12px rgba(0,0,0,.08);padding:20px}
        h1{margin:0 0 12px;color:#4c1d95}
        .error{background:#fee2e2;color:#991b1b;padding:10px;border-radius:8px;margin:8px 0}
        input,button{width:100%;padding:10px;border-radius:8px;border:1px solid #ddd}
        button{background:#4c1d95;border:none;color:#fff;margin-top:10px;cursor:pointer}
        .help{font-size:12px;color:#666;margin-top:6px}
      </style>
    </head>
    <body>
      <div class="card">
        <h1>Validar token</h1>
        <?php if($err): ?><div class="error"><?=h($err)?></div><?php endif; ?>
        <form method="get">
          <input type="text" name="token" placeholder="Ingresá el token exacto" required>
          <button type="submit">Entrar</button>
          <div class="help">Luego podés navegar entre “Bloques” y “Limpieza”.</div>
        </form>
      </div>
    </body>
    </html>
    <?php
    exit;
}

// ---------- estilo base ----------
$style = <<<CSS
body{font-family:Arial, sans-serif;background:#f9f9fb;padding:20px;}
h1{color:#111}
.tabs{display:flex;gap:8px;margin-bottom:12px}
.tabs a{padding:8px 12px;background:#e5e7eb;text-decoration:none;color:#111;border-radius:8px}
.tabs a.active{background:#4c1d95;color:#fff}
table{border-collapse:collapse;width:100%;margin:16px 0;}
th,td{border:1px solid #ddd;padding:8px;text-align:left;}
th{background:#f3f4f6;}
tr:nth-child(even){background:#fafafa;}
.ok{color:green;font-weight:bold;}
.miss{color:#b91c1c;font-weight:bold;}
nav{margin:10px 0;}
nav a{margin:0 4px;padding:4px 8px;text-decoration:none;background:#ddd;border-radius:4px}
nav a.active{background:#4c1d95;color:#fff;}
button,.btn{padding:8px 12px;border:none;background:#4c1d95;color:#fff;border-radius:6px;cursor:pointer;text-decoration:none;display:inline-block}
.summary{padding:8px;background:#eef;border:1px solid #99c;margin:10px 0;}
.warn{padding:8px;background:#fff7ed;border:1px solid #fdba74;margin:10px 0;}
.error{padding:8px;background:#fee2e2;border:1px solid #ef4444;margin:10px 0;color:#991b1b}
input[type=text]{padding:6px 8px;border:1px solid #ddd;border-radius:6px;width:100%}
.controls{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
.small{font-size:12px;color:#555}
CSS;

// ---------- helpers ----------
function paga($q){ echo $q; }

// ---------- VISTA BLOQUES ----------
if ($view === 'blocks') {
    $total = (int)$pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol='productor'")->fetchColumn();
    $pages = max(1, (int)ceil($total / $PERPAGE));
    $offset = ($page - 1) * $PERPAGE;

    $stmt = $pdo->prepare("
      SELECT id, usuario, contrasena
      FROM usuarios
      WHERE rol=:rol
      ORDER BY id ASC
      LIMIT :lim OFFSET :off
    ");
    $stmt->bindValue(':rol', $ROLE, PDO::PARAM_STR);
    $stmt->bindValue(':lim', $PERPAGE, PDO::PARAM_INT);
    $stmt->bindValue(':off', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $updated = 0; $skipped = 0;
    if ($applyBlock) {
        $pdo->beginTransaction();
        $upd = $pdo->prepare("UPDATE usuarios SET contrasena=:hash WHERE id=:id");
        foreach ($rows as $r) {
            $usuario = (string)$r['usuario'];
            $hashDB  = (string)$r['contrasena'];
            if (password_verify($usuario, $hashDB)) { $skipped++; }
            else {
                $newHash = password_hash($usuario, PASSWORD_DEFAULT);
                $upd->execute([':hash'=>$newHash, ':id'=>(int)$r['id']]);
                $updated++;
            }
        }
        $pdo->commit();
    }

    ?>
    <!doctype html>
    <html lang="es">
    <head><meta charset="utf-8"><title>Reset contraseñas – Bloques</title><style><?=$style?></style></head>
    <body>
      <h1>Reset de contraseñas - Productores</h1>

      <div class="tabs">
        <a class="active" href="?token=<?=urlencode($tokenInput)?>&view=blocks">Bloques</a>
        <a href="?token=<?=urlencode($tokenInput)?>&view=cleanup">Limpieza</a>
      </div>

      <p>Total productores: <strong><?=number_format($total,0,',','.')?></strong>. Mostrando página <?=$page?> de <?=$pages?> (<?=$PERPAGE?> por bloque).</p>

      <?php if($applyBlock): ?>
        <div class="summary">✅ Bloque actualizado. Cambiados: <?=$updated?> — Omitidos (ya ok): <?=$skipped?></div>
      <?php endif; ?>

      <div class="controls">
        <a class="btn" href="?token=<?=urlencode($tokenInput)?>&view=blocks&page=<?=$page?>&apply=1">Aplicar cambios a este bloque</a>
        <span class="small">Esto solo re-hashea <em>contraseñas</em> para que sean iguales al usuario en esta página (<?=$PERPAGE?> registros).</span>
      </div>

      <table>
        <thead><tr><th>ID</th><th>Usuario</th><th>Estado contraseña</th></tr></thead>
        <tbody>
          <?php foreach($rows as $r):
              $ok = password_verify($r['usuario'],$r['contrasena']);
          ?>
          <tr>
            <td><?=h($r['id'])?></td>
            <td><?=h($r['usuario'])?></td>
            <td class="<?=$ok?'ok':'miss'?>"><?=$ok?'✔ ya coincide':'✘ distinta'?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

      <nav>
        <?php for($i=1;$i<=$pages;$i++): ?>
          <a href="?token=<?=urlencode($tokenInput)?>&view=blocks&page=<?=$i?>" class="<?=$i===$page?'active':''?>"><?=$i?></a>
        <?php endfor; ?>
      </nav>

      <p class="warn">⚠️ Al finalizar, borrá este archivo del servidor.</p>
    </body>
    </html>
    <?php
    exit;
}

// ---------- VISTA LIMPIEZA (usuarios con "?" o caracteres fuera del set común) ----------
$offset = ($page - 1) * $PERPAGE;

// Criterio de detección:
// - Contiene '?' explícito
// - o bien caracteres fuera del rango ASCII imprimible habitual y letras acentuadas/ñ más comunes
//   (esto ayuda a encontrar nombres dañados por encoding).
$whereBad = "usuario LIKE '%?%' OR usuario REGEXP '[^0-9A-Za-z ÁÉÍÓÚÜÑáéíóúüñ._-]'";

$totalStmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE rol=:rol AND ($whereBad)");
$totalStmt->execute([':rol'=>$ROLE]);
$totalBad = (int)$totalStmt->fetchColumn();

$pagesBad = max(1, (int)ceil($totalBad / $PERPAGE));

$listStmt = $pdo->prepare("
  SELECT id, usuario, contrasena
  FROM usuarios
  WHERE rol=:rol AND ($whereBad)
  ORDER BY id ASC
  LIMIT :lim OFFSET :off
");
$listStmt->bindValue(':rol', $ROLE, PDO::PARAM_STR);
$listStmt->bindValue(':lim', $PERPAGE, PDO::PARAM_INT);
$listStmt->bindValue(':off', $offset, PDO::PARAM_INT);
$listStmt->execute();
$badRows = $listStmt->fetchAll(PDO::FETCH_ASSOC);

$flash = '';
$errMsg = '';

// Guardar un registro individual (cambio de usuario y opcionalmente contraseña)
if ($saveOne && $editId > 0 && $newUser !== '') {
    try {
        // ¿existe otro igual?
        $chk = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE usuario=:u AND id<>:id");
        $chk->execute([':u'=>$newUser, ':id'=>$editId]);
        if ((int)$chk->fetchColumn() > 0) {
            $errMsg = "El usuario '{$newUser}' ya existe. Elegí otro.";
        } else {
            $pdo->beginTransaction();
            // actualizar nombre
            $updU = $pdo->prepare("UPDATE usuarios SET usuario=:u WHERE id=:id");
            $updU->execute([':u'=>$newUser, ':id'=>$editId]);

            // opcional: sincronizar contraseña
            if ($syncPass) {
                $updP = $pdo->prepare("UPDATE usuarios SET contrasena=:h WHERE id=:id");
                $updP->execute([':h'=>password_hash($newUser, PASSWORD_DEFAULT), ':id'=>$editId]);
            }
            $pdo->commit();
            $flash = "Usuario ID {$editId} actualizado correctamente".($syncPass?" (y contraseña sincronizada)":"").".";
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        $errMsg = "Error al actualizar: ".$e->getMessage();
    }

    // volver a recalcular la página (el registro pudo salir del filtro)
    $listStmt->execute();
    $badRows = $listStmt->fetchAll(PDO::FETCH_ASSOC);
}

?>
<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><title>Limpieza de nombres – Productores</title><style><?=$style?></style></head>
<body>
  <h1>Limpieza de nombres (caracteres rotos)</h1>

  <div class="tabs">
    <a href="?token=<?=urlencode($tokenInput)?>&view=blocks" >Bloques</a>
    <a class="active" href="?token=<?=urlencode($tokenInput)?>&view=cleanup">Limpieza</a>
  </div>

  <p>Detectados con posibles caracteres dañados: <strong><?=number_format($totalBad,0,',','.')?></strong>. Mostrando página <?=$page?> de <?=$pagesBad?> (<?=$PERPAGE?> por bloque).</p>

  <?php if($flash): ?><div class="summary">✅ <?=h($flash)?></div><?php endif; ?>
  <?php if($errMsg): ?><div class="error">❌ <?=h($errMsg)?></div><?php endif; ?>

  <table>
    <thead>
      <tr><th style="width:80px">ID</th><th>Usuario (actual)</th><th style="width:360px">Nuevo usuario</th><th style="width:180px">Acción</th></tr>
    </thead>
    <tbody>
      <?php if (!$badRows): ?>
        <tr><td colspan="4">No hay usuarios con “?” u otros caracteres fuera del set esperado en esta página.</td></tr>
      <?php endif; ?>
      <?php foreach($badRows as $r): ?>
        <tr>
          <td><?=h($r['id'])?></td>
          <td><?=h($r['usuario'])?></td>
          <td>
            <form class="controls" method="get" style="margin:0;">
              <input type="hidden" name="token" value="<?=h($tokenInput)?>">
              <input type="hidden" name="view" value="cleanup">
              <input type="hidden" name="page" value="<?=h($page)?>">
              <input type="hidden" name="save" value="1">
              <input type="hidden" name="id" value="<?=h($r['id'])?>">
              <input type="text" name="new_usuario" placeholder="Escribí el nuevo usuario" value="<?=h($r['usuario'])?>" required>
          </td>
          <td>
              <label><input type="checkbox" name="sync" value="1" checked> sincronizar contraseña</label>
              <button type="submit" class="btn">Guardar</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <nav>
    <?php for($i=1;$i<=$pagesBad;$i++): ?>
      <a href="?token=<?=urlencode($tokenInput)?>&view=cleanup&page=<?=$i?>" class="<?=$i===$page?'active':''?>"><?=$i?></a>
    <?php endfor; ?>
  </nav>

  <p class="warn">Sugerencia: corregí y sincronizá de a pocos para minimizar carga. Al terminar, borra este archivo.</p>
</body>
</html>
