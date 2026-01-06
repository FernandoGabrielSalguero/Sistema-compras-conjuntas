<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Cargar tu archivo de conexión (usa $pdo)
require_once __DIR__ . '/config.php';

// Obtener el nombre de la base de datos actual
$baseDatos = $pdo->query("SELECT DATABASE()")->fetchColumn();

echo "<h1>📚 Estructura completa de la base de datos: <em>$baseDatos</em></h1>";

// Obtener todas las tablas
$tablesQuery = $pdo->query("SHOW TABLES");
$tables = $tablesQuery->fetchAll(PDO::FETCH_NUM);

// Construir un mapa de relaciones bidireccional entre tablas
$relations = [];
$fkQuery = $pdo->prepare(
    "SELECT TABLE_NAME, REFERENCED_TABLE_NAME
     FROM information_schema.KEY_COLUMN_USAGE
     WHERE TABLE_SCHEMA = :db
       AND REFERENCED_TABLE_NAME IS NOT NULL"
);
$fkQuery->execute([':db' => $baseDatos]);
$fkRows = $fkQuery->fetchAll(PDO::FETCH_ASSOC);
foreach ($fkRows as $row) {
    $t = $row['TABLE_NAME'];
    $r = $row['REFERENCED_TABLE_NAME'];
    $relations[$t][] = $r;
    $relations[$r][] = $t; // hacerla bidireccional para mostrar relaciones entrantes
}
foreach ($relations as $k => $arr) {
    $relations[$k] = array_values(array_unique($arr));
}

// Selector de tablas
echo '<div id="db-structure-controls" style="margin-bottom:16px;">';
echo '<label for="tablaSelector"><strong>Seleccionar tabla(s):</strong></label><br>';
echo '<select id="tablaSelector" multiple size="8" style="min-width:300px;margin-top:8px;">';
foreach ($tables as $t) {
    $name = $t[0];
    echo "<option value=\"" . htmlspecialchars($name, ENT_QUOTES) . "\">" . htmlspecialchars($name) . "</option>";
}
echo '</select> <button id="clearSelection" type="button">Limpiar</button>';
echo '<p style="font-size:0.9em;color:#666;margin-top:8px;">Haz clic en un título de tabla para seleccionarla o usa el selector de arriba. Mantén Ctrl/Cmd para seleccionar varias.</p>';
echo '</div>';

// Imprimir cada tabla dentro de un bloque con atributos data para relaciones
foreach ($tables as $table) {
    $tableName = $table[0];
    $relList = isset($relations[$tableName]) ? implode(',', $relations[$tableName]) : '';

    echo "<div class='table-block' data-table=\"" . htmlspecialchars($tableName, ENT_QUOTES) . "\" data-relations=\"" . htmlspecialchars($relList, ENT_QUOTES) . "\" style=\"margin-bottom:20px;border:1px solid #ddd;padding:10px;border-radius:6px;\">";

    echo "<h2>📄 Tabla: <strong>$tableName</strong></h2>";

    // Estructura de columnas (igual que antes)
    $columnsQuery = $pdo->query("SHOW COLUMNS FROM `$tableName`");
    $columns = $columnsQuery->fetchAll(PDO::FETCH_ASSOC);

    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>Columna</th><th>Tipo</th><th>Nulo</th><th>Clave</th><th>Default</th><th>Extra</th></tr>";

    foreach ($columns as $column) {
        echo "<tr>";
        foreach (['Field', 'Type', 'Null', 'Key', 'Default', 'Extra'] as $campo) {
            $value = $column[$campo] ?? '';
            echo "<td>" . htmlspecialchars((string) $value) . "</td>";
        }
        echo "</tr>";
    }
    echo "</table><br>";

    // Relaciones salientes (detalladas)
    $relacionesQuery = $pdo->prepare("\n        SELECT \n            COLUMN_NAME, \n            REFERENCED_TABLE_NAME, \n            REFERENCED_COLUMN_NAME \n        FROM information_schema.KEY_COLUMN_USAGE \n        WHERE TABLE_SCHEMA = :db \n            AND TABLE_NAME = :tabla \n            AND REFERENCED_TABLE_NAME IS NOT NULL\n    ");
    $relacionesQuery->execute([
        ':db' => $baseDatos,
        ':tabla' => $tableName
    ]);

    $relacionesDet = $relacionesQuery->fetchAll(PDO::FETCH_ASSOC);
    if (count($relacionesDet) > 0) {
        echo "<strong>🔗 Relaciones (salientes):</strong><ul>";
        foreach ($relacionesDet as $rel) {
            echo "<li>Columna <code>{$rel['COLUMN_NAME']}</code> referencia a <code>{$rel['REFERENCED_TABLE_NAME']}.{$rel['REFERENCED_COLUMN_NAME']}</code></li>";
        }
        echo "</ul>";
    }

    // Relaciones entrantes (tablas que referencian esta)
    $incoming = [];
    foreach ($relations as $k => $arr) {
        if (in_array($tableName, $arr)) $incoming[] = $k;
    }
    if (count($incoming) > 0) {
        echo "<strong>🔁 Relaciones (entrantes):</strong> <em>" . htmlspecialchars(implode(', ', $incoming)) . "</em>";
    }

    echo "</div>";
}

// Estilos y JavaScript para filtrar
echo "<style> .hidden{display:none!important;} .table-block h2{cursor:pointer;} </style>";

?>
<script>
(function(){
  const selector = document.getElementById('tablaSelector');
  const clearBtn = document.getElementById('clearSelection');
  const blocks = Array.from(document.querySelectorAll('.table-block'));

  function update(){
    const selected = Array.from(selector.selectedOptions).map(o=>o.value);
    if(selected.length === 0){
      blocks.forEach(b=>b.classList.remove('hidden'));
      return;
    }
    const toShow = new Set(selected);
    selected.forEach(s=>{
      const el = document.querySelector('.table-block[data-table="'+s+'"]');
      if(!el) return;
      const rels = el.dataset.relations ? el.dataset.relations.split(',').filter(Boolean) : [];
      rels.forEach(r=>toShow.add(r));
    });
    blocks.forEach(b=>{
      if(toShow.has(b.dataset.table)) b.classList.remove('hidden'); else b.classList.add('hidden');
    });
  }

  selector.addEventListener('change', update);
  clearBtn.addEventListener('click', ()=>{ Array.from(selector.options).forEach(o=>o.selected=false); update(); });

  blocks.forEach(b=>{
    const h = b.querySelector('h2');
    if(!h) return;
    h.addEventListener('click', ()=>{
      const val = b.dataset.table;
      const opt = Array.from(selector.options).find(o=>o.value===val);
      if(opt){ opt.selected = !opt.selected; update(); }
    });
  });
})();
</script>
<?php
