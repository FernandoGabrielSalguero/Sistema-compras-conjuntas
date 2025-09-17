<?php
// reset_pass_productores_batches.php
// UI por bloques de 100 productores
// ⚠️ Borralo cuando termines

ini_set('display_errors', 1);
error_reporting(E_ALL);
set_time_limit(0);

require_once __DIR__ . '/config.php'; // $pdo debe estar definido

$TOKEN = 'SVEtokenSeguro2025'; // cámbialo por algo fuerte
$role  = 'productor';
$perPage = 100;

// --- Seguridad por token ---
$tokenInput = $_GET['token'] ?? '';
if ($tokenInput !== $TOKEN) {
    http_response_code(403);
    echo "Forbidden (token inválido)";
    exit;
}

// --- Parámetros ---
$page  = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$apply = isset($_GET['apply']) && $_GET['apply'] === '1';

// --- Conteo total ---
$total = (int)$pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol='productor'")->fetchColumn();
$pages = max(1, ceil($total / $perPage));
$offset = ($page - 1) * $perPage;

// --- Query lote actual ---
$stmt = $pdo->prepare("SELECT id, usuario, contrasena FROM usuarios WHERE rol=:rol ORDER BY id ASC LIMIT :lim OFFSET :off");
$stmt->bindValue(':rol', $role, PDO::PARAM_STR);
$stmt->bindValue(':lim', $perPage, PDO::PARAM_INT);
$stmt->bindValue(':off', $offset, PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$updated = 0;
$skipped = 0;

if ($apply) {
    $pdo->beginTransaction();
    $upd = $pdo->prepare("UPDATE usuarios SET contrasena=:hash WHERE id=:id");

    foreach ($rows as $r) {
        $usuario = (string)$r['usuario'];
        $hashDB  = (string)$r['contrasena'];
        if (password_verify($usuario, $hashDB)) {
            $skipped++;
        } else {
            $newHash = password_hash($usuario, PASSWORD_DEFAULT);
            $upd->execute([':hash' => $newHash, ':id' => (int)$r['id']]);
            $updated++;
        }
    }

    $pdo->commit();
}

// ---- HTML UI ----
function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Reset contraseñas productores (bloques de 100)</title>
<style>
body{font-family:Arial, sans-serif;background:#f9f9fb;padding:20px;}
table{border-collapse:collapse;width:100%;margin:16px 0;}
th,td{border:1px solid #ddd;padding:8px;text-align:left;}
th{background:#f3f4f6;}
tr:nth-child(even){background:#fafafa;}
.ok{color:green;font-weight:bold;}
.miss{color:red;font-weight:bold;}
nav{margin:10px 0;}
a{margin:0 4px;padding:4px 8px;text-decoration:none;background:#ddd;border-radius:4px;}
a.active{background:#4c1d95;color:#fff;}
button{padding:6px 12px;border:none;background:#4c1d95;color:#fff;border-radius:4px;cursor:pointer;}
.summary{padding:8px;background:#eef;border:1px solid #99c;margin:10px 0;}
</style>
</head>
<body>
<h1>Reset de contraseñas - Productores</h1>
<p>Total productores: <strong><?=$total?></strong>. Mostrando página <?=$page?> de <?=$pages?> (<?=$perPage?> por bloque).</p>

<?php if($apply): ?>
<div class="summary">
    ✅ Bloque actualizado. Cambiados: <?=$updated?> — Omitidos (ya ok): <?=$skipped?>
</div>
<?php endif; ?>

<form method="get">
  <input type="hidden" name="token" value="<?=h($TOKEN)?>">
  <input type="hidden" name="page" value="<?=h($page)?>">
  <input type="hidden" name="apply" value="1">
  <button type="submit">Aplicar cambios a este bloque</button>
</form>

<table>
<thead>
<tr><th>ID</th><th>Usuario</th><th>Estado contraseña</th></tr>
</thead>
<tbody>
<?php foreach($rows as $r):
    $ok = password_verify($r['usuario'], $r['contrasena']);
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
  <a href="?token=<?=urlencode($TOKEN)?>&page=<?=$i?>" class="<?=$i===$page?'active':''?>"><?=$i?></a>
<?php endfor; ?>
</nav>

<p style="color:#c00">⚠️ Importante: borrá este archivo cuando termines.</p>
</body>
</html>
