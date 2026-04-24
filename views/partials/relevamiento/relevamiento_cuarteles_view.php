<?php

function h_cuartel($value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function input_cuartel(int $idx, string $field, string $label, array $data, bool $readonly = false): void
{
    $id = 'cuartel_' . $field . '_' . $idx;
    ?>
    <div class="input-group">
        <label for="<?= h_cuartel($id) ?>"><?= h_cuartel($label) ?></label>
        <div class="input-icon input-icon-name">
            <input
                type="text"
                id="<?= h_cuartel($id) ?>"
                name="cuarteles[<?= $idx ?>][<?= h_cuartel($field) ?>]"
                value="<?= h_cuartel($data[$field] ?? '') ?>"
                <?= $readonly ? 'readonly' : '' ?>
            />
        </div>
    </div>
    <?php
}

function textarea_cuartel(int $idx, string $field, string $label, array $data): void
{
    $id = 'cuartel_' . $field . '_' . $idx;
    ?>
    <div class="input-group">
        <label for="<?= h_cuartel($id) ?>"><?= h_cuartel($label) ?></label>
        <div class="input-icon input-icon-name">
            <textarea id="<?= h_cuartel($id) ?>" name="cuarteles[<?= $idx ?>][<?= h_cuartel($field) ?>]"><?= h_cuartel($data[$field] ?? '') ?></textarea>
        </div>
    </div>
    <?php
}

function cuartel_variedad_display(array $cuartel): string
{
    $display = trim((string)($cuartel['variedad_display'] ?? ''));
    if ($display !== '') {
        return $display;
    }

    $codigo = trim((string)($cuartel['variedad'] ?? ''));
    $nombre = trim((string)($cuartel['nombre_variedad'] ?? ''));
    if ($codigo !== '' && $nombre !== '') {
        return $codigo . ' - ' . $nombre;
    }

    return $codigo !== '' ? $codigo : 'Sin variedad';
}

$cuarteles = [];
if (is_array($datosCuarteles) && isset($datosCuarteles['cuarteles']) && is_array($datosCuarteles['cuarteles'])) {
    $cuarteles = $datosCuarteles['cuarteles'];
}
?>

<form id="cuarteles-form">
    <input type="hidden" name="productor_id_real" value="<?= h_cuartel($productorIdReal) ?>">

    <?php if (empty($cuarteles)): ?>
        <p class="text-muted" style="margin-top: 1rem;">No se encontraron cuarteles asociados a este productor.</p>
    <?php else: ?>
        <?php foreach ($cuarteles as $idx => $fila): ?>
            <?php
                $cuartel = $fila['cuartel'] ?? [];
                $limitantes = $fila['limitantes'] ?? [];
                $rendimientos = $fila['rendimientos'] ?? [];
                $riesgos = $fila['riesgos'] ?? [];
            ?>

            <div class="relevamiento-cuartel-block">
                <input type="hidden" name="cuarteles[<?= $idx ?>][cuartel_id]" value="<?= h_cuartel($cuartel['id'] ?? '') ?>">
                <input type="hidden" name="cuarteles[<?= $idx ?>][limitantes_id]" value="<?= h_cuartel($limitantes['id'] ?? '') ?>">
                <input type="hidden" name="cuarteles[<?= $idx ?>][rendimientos_id]" value="<?= h_cuartel($rendimientos['id'] ?? '') ?>">
                <input type="hidden" name="cuarteles[<?= $idx ?>][riesgos_id]" value="<?= h_cuartel($riesgos['id'] ?? '') ?>">

                <div class="relevamiento-cuartel-header">
                    Cuartel <?= h_cuartel($cuartel['codigo_cuartel'] ?? $cuartel['id'] ?? '') ?>
                </div>
                <div class="relevamiento-cuartel-subtitle">
                    Finca <?= h_cuartel($cuartel['codigo_finca'] ?? 'Sin finca') ?> - <?= h_cuartel(cuartel_variedad_display($cuartel)) ?>
                </div>

                <h4 class="relevamiento-section-title">Datos basicos (prod_cuartel)</h4>
                <?php input_cuartel($idx, 'codigo_cuartel', 'Codigo cuartel', $cuartel, true); ?>
                <?php input_cuartel($idx, 'variedad', 'Codigo variedad', $cuartel); ?>
                <?php input_cuartel($idx, 'nombre_variedad', 'Nombre variedad', $cuartel, true); ?>
                <?php input_cuartel($idx, 'numero_inv', 'Numero INV', $cuartel); ?>
                <?php input_cuartel($idx, 'sistema_conduccion', 'Sistema de conduccion', $cuartel); ?>
                <?php input_cuartel($idx, 'superficie_ha', 'Superficie (ha)', $cuartel); ?>
                <?php input_cuartel($idx, 'porcentaje_cepas_produccion', '% cepas en produccion', $cuartel); ?>
                <?php input_cuartel($idx, 'forma_cosecha_actual', 'Forma cosecha actual', $cuartel); ?>
                <?php input_cuartel($idx, 'porcentaje_malla_buen_estado', '% malla buen estado', $cuartel); ?>
                <?php input_cuartel($idx, 'edad_promedio_encepado_anios', 'Edad promedio encepado', $cuartel); ?>
                <?php input_cuartel($idx, 'estado_estructura_sistema', 'Estado estructura/sistema', $cuartel); ?>
                <?php textarea_cuartel($idx, 'labores_mecanizables', 'Labores mecanizables', $cuartel); ?>

                <h4 class="relevamiento-section-title">Limitantes (prod_cuartel_limitantes)</h4>
                <?php textarea_cuartel($idx, 'limitantes_suelo', 'Limitantes suelo', $limitantes); ?>
                <?php textarea_cuartel($idx, 'observaciones', 'Observaciones', $limitantes); ?>
                <?php input_cuartel($idx, 'categoria_1', 'Categoria 1', $limitantes); ?>
                <?php input_cuartel($idx, 'limitante_1', 'Limitante 1', $limitantes); ?>
                <?php textarea_cuartel($idx, 'inversion_accion1_1', 'Inversion accion 1.1', $limitantes); ?>
                <?php textarea_cuartel($idx, 'obs_inversion_accion1_1', 'Obs inversion accion 1.1', $limitantes); ?>
                <?php input_cuartel($idx, 'ciclo_agricola1_1', 'Ciclo agricola 1.1', $limitantes); ?>
                <?php textarea_cuartel($idx, 'inversion_accion2_1', 'Inversion accion 2.1', $limitantes); ?>
                <?php textarea_cuartel($idx, 'obs_inversion_accion2_1', 'Obs inversion accion 2.1', $limitantes); ?>
                <?php input_cuartel($idx, 'ciclo_agricola2_1', 'Ciclo agricola 2.1', $limitantes); ?>
                <?php input_cuartel($idx, 'categoria_2', 'Categoria 2', $limitantes); ?>
                <?php input_cuartel($idx, 'limitante_2', 'Limitante 2', $limitantes); ?>
                <?php textarea_cuartel($idx, 'inversion_accion1_2', 'Inversion accion 1.2', $limitantes); ?>
                <?php textarea_cuartel($idx, 'obs_inversion_accion1_2', 'Obs inversion accion 1.2', $limitantes); ?>
                <?php input_cuartel($idx, 'ciclo_agricola1_2', 'Ciclo agricola 1.2', $limitantes); ?>
                <?php textarea_cuartel($idx, 'inversion_accion2_2', 'Inversion accion 2.2', $limitantes); ?>
                <?php textarea_cuartel($idx, 'obs_inversion_accion2_2', 'Obs inversion accion 2.2', $limitantes); ?>
                <?php input_cuartel($idx, 'ciclo_agricola2_2', 'Ciclo agricola 2.2', $limitantes); ?>

                <h4 class="relevamiento-section-title">Rendimientos (prod_cuartel_rendimientos)</h4>
                <?php input_cuartel($idx, 'rend_2020_qq_ha', 'Rend 2020 (qq/ha)', $rendimientos); ?>
                <?php input_cuartel($idx, 'rend_2021_qq_ha', 'Rend 2021 (qq/ha)', $rendimientos); ?>
                <?php input_cuartel($idx, 'rend_2022_qq_ha', 'Rend 2022 (qq/ha)', $rendimientos); ?>
                <?php input_cuartel($idx, 'ing_2023_kg', 'Ingreso 2023 (kg)', $rendimientos); ?>
                <?php input_cuartel($idx, 'rend_2023_qq_ha', 'Rend 2023 (qq/ha)', $rendimientos); ?>
                <?php input_cuartel($idx, 'ing_2024_kg', 'Ingreso 2024 (kg)', $rendimientos); ?>
                <?php input_cuartel($idx, 'rend_2024_qq_ha', 'Rend 2024 (qq/ha)', $rendimientos); ?>
                <?php input_cuartel($idx, 'ing_2025_kg', 'Ingreso 2025 (kg)', $rendimientos); ?>
                <?php input_cuartel($idx, 'rend_2025_qq_ha', 'Rend 2025 (qq/ha)', $rendimientos); ?>
                <?php input_cuartel($idx, 'rend_promedio_5anios_qq_ha', 'Rend promedio 5 anos', $rendimientos); ?>

                <h4 class="relevamiento-section-title">Riesgos (prod_cuartel_riesgos)</h4>
                <?php input_cuartel($idx, 'tiene_seguro_agricola', 'Tiene seguro agricola', $riesgos); ?>
                <?php input_cuartel($idx, 'porcentaje_dano_granizo', '% dano granizo', $riesgos); ?>
                <?php input_cuartel($idx, 'heladas_dano_promedio_5anios', 'Heladas dano promedio 5 anos', $riesgos); ?>
                <?php input_cuartel($idx, 'presencia_freatica', 'Presencia freatica', $riesgos); ?>
                <?php textarea_cuartel($idx, 'plagas_no_convencionales', 'Plagas no convencionales', $riesgos); ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</form>
