<?php

/**
 * Vista parcial para el modal de Producción.
 * Muestra datos combinados de:
 *  - prod_fincas
 *  - prod_finca_direccion
 *  - prod_finca_superficie
 *  - prod_finca_cultivos
 *  - prod_finca_agua
 *  - prod_finca_maquinaria
 *  - prod_finca_gerencia
 *
 * Los campos marcados como "avanzados" se controlan con un switch
 * mediante data-advanced="1" y la clase CSS .relevamiento-advanced-hidden.
 */

function h($value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

$fincas = [];
if (is_array($datosProduccion) && isset($datosProduccion['fincas']) && is_array($datosProduccion['fincas'])) {
    $fincas = $datosProduccion['fincas'];
}
?>
<style>
    /* Títulos de secciones del relevamiento */
    .relevamiento-section-title {
        margin-top: 1.5rem;
        margin-bottom: 0.75rem;
        font-weight: 600;
        color: var(--sve-primary-color, #0d6efd);
    }

    /* Separadores visualmente más agradables */
    .relevamiento-section-divider {
        border: 0;
        height: 1px;
        margin: 0.75rem 0 1.25rem;
        background: linear-gradient(
            to right,
            transparent,
            rgba(0, 0, 0, 0.18),
            transparent
        );
    }

    .relevamiento-finca-block {
        padding: 1rem 0;
    }

    .relevamiento-finca-header {
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .relevamiento-finca-subtitle {
        font-size: 0.9rem;
        color: #666;
        margin-bottom: 0.75rem;
    }
</style>

<form id="produccion-form">
    <input type="hidden" name="productor_id_real" value="<?= h($productorIdReal) ?>">

    <p>
        Formulario de <strong>Producción</strong> para el productor
        <strong><?= h($productorIdReal) ?></strong>.
    </p>

    <!-- Switch para campos avanzados -->
    <div class="form-switch">
        <label>
            <input type="checkbox" data-role="produccion-advanced-toggle">
            Mostrar campos avanzados
        </label>
    </div>

    <?php if (empty($fincas)): ?>
        <p class="text-muted" style="margin-top: 1rem;">
            No se encontraron fincas asociadas a este productor.
        </p>
    <?php else: ?>
        <?php foreach ($fincas as $idx => $fila): ?>
            <?php
                $finca      = $fila['finca']      ?? [];
                $direccion  = $fila['direccion']  ?? [];
                $superficie = $fila['superficie'] ?? [];
                $cultivos   = $fila['cultivos']   ?? [];
                $agua       = $fila['agua']       ?? [];
                $maquinaria = $fila['maquinaria'] ?? [];
                $gerencia   = $fila['gerencia']   ?? [];
            ?>

            <div class="relevamiento-finca-block">
                <hr class="relevamiento-section-divider">

                <!-- IDs ocultos por finca y tablas relacionadas -->
                <input type="hidden" name="fincas[<?= $idx ?>][finca_id]" value="<?= h($finca['id'] ?? '') ?>">
                <input type="hidden" name="fincas[<?= $idx ?>][direccion_id]" value="<?= h($direccion['id'] ?? '') ?>">
                <input type="hidden" name="fincas[<?= $idx ?>][superficie_id]" value="<?= h($superficie['id'] ?? '') ?>">
                <input type="hidden" name="fincas[<?= $idx ?>][superficie_anio]" value="<?= h($superficie['anio'] ?? '') ?>">
                <input type="hidden" name="fincas[<?= $idx ?>][cultivos_id]" value="<?= h($cultivos['id'] ?? '') ?>">
                <input type="hidden" name="fincas[<?= $idx ?>][cultivos_anio]" value="<?= h($cultivos['anio'] ?? '') ?>">
                <input type="hidden" name="fincas[<?= $idx ?>][agua_id]" value="<?= h($agua['id'] ?? '') ?>">
                <input type="hidden" name="fincas[<?= $idx ?>][agua_anio]" value="<?= h($agua['anio'] ?? '') ?>">
                <input type="hidden" name="fincas[<?= $idx ?>][maquinaria_id]" value="<?= h($maquinaria['id'] ?? '') ?>">
                <input type="hidden" name="fincas[<?= $idx ?>][maquinaria_anio]" value="<?= h($maquinaria['anio'] ?? '') ?>">
                <input type="hidden" name="fincas[<?= $idx ?>][gerencia_id]" value="<?= h($gerencia['id'] ?? '') ?>">
                <input type="hidden" name="fincas[<?= $idx ?>][gerencia_anio]" value="<?= h($gerencia['anio'] ?? '') ?>">

                <div class="relevamiento-finca-header">
                    Finca <?= h($finca['codigo_finca'] ?? '') ?>
                </div>
                <div class="relevamiento-finca-subtitle">
                    <?= h($finca['nombre_finca'] ?? 'Sin nombre') ?>
                </div>

                <!-- Datos básicos de finca (prod_fincas) -->
                <h4 class="relevamiento-section-title">
                    Datos de finca (prod_fincas)
                </h4>

                <div class="input-group">
                    <label for="codigo_finca_<?= $idx ?>">Código finca</label>
                    <div class="input-icon input-icon-name">
                        <input
                            type="text"
                            id="codigo_finca_<?= $idx ?>"
                            name="fincas[<?= $idx ?>][codigo_finca]"
                            value="<?= h($finca['codigo_finca'] ?? '') ?>"
                            readonly
                        />
                    </div>
                </div>

                <div class="input-group">
                    <label for="nombre_finca_<?= $idx ?>">Nombre finca</label>
                    <div class="input-icon input-icon-name">
                        <input
                            type="text"
                            id="nombre_finca_<?= $idx ?>"
                            name="fincas[<?= $idx ?>][nombre_finca]"
                            value="<?= h($finca['nombre_finca'] ?? '') ?>"
                        />
                    </div>
                </div>

                <!-- Dirección (prod_finca_direccion) -->
                <hr class="relevamiento-section-divider">
                <h4 class="relevamiento-section-title">
                    Dirección (prod_finca_direccion)
                </h4>

                <div class="input-group">
                    <label for="departamento_<?= $idx ?>">Departamento</label>
                    <div class="input-icon input-icon-name">
                        <input
                            type="text"
                            id="departamento_<?= $idx ?>"
                            name="fincas[<?= $idx ?>][departamento]"
                            value="<?= h($direccion['departamento'] ?? '') ?>"
                        />
                    </div>
                </div>

                <div class="input-group">
                    <label for="localidad_<?= $idx ?>">Localidad</label>
                    <div class="input-icon input-icon-name">
                        <input
                            type="text"
                            id="localidad_<?= $idx ?>"
                            name="fincas[<?= $idx ?>][localidad]"
                            value="<?= h($direccion['localidad'] ?? '') ?>"
                        />
                    </div>
                </div>

                <div class="input-group">
                    <label for="calle_<?= $idx ?>">Calle</label>
                    <div class="input-icon input-icon-name">
                        <input
                            type="text"
                            id="calle_<?= $idx ?>"
                            name="fincas[<?= $idx ?>][calle]"
                            value="<?= h($direccion['calle'] ?? '') ?>"
                        />
                    </div>
                </div>

                <div class="input-group">
                    <label for="numero_<?= $idx ?>">Número</label>
                    <div class="input-icon input-icon-name">
                        <input
                            type="text"
                            id="numero_<?= $idx ?>"
                            name="fincas[<?= $idx ?>][numero]"
                            value="<?= h($direccion['numero'] ?? '') ?>"
                        />
                    </div>
                </div>

                <div class="input-group">
                    <label for="latitud_<?= $idx ?>">Latitud</label>
                    <div class="input-icon input-icon-name">
                        <input
                            type="text"
                            id="latitud_<?= $idx ?>"
                            name="fincas[<?= $idx ?>][latitud]"
                            value="<?= h($direccion['latitud'] ?? '') ?>"
                        />
                    </div>
                </div>

                <div class="input-group">
                    <label for="longitud_<?= $idx ?>">Longitud</label>
                    <div class="input-icon input-icon-name">
                        <input
                            type="text"
                            id="longitud_<?= $idx ?>"
                            name="fincas[<?= $idx ?>][longitud]"
                            value="<?= h($direccion['longitud'] ?? '') ?>"
                        />
                    </div>
                </div>

                <!-- Superficie (prod_finca_superficie) -->
                <hr class="relevamiento-section-divider">
                <h4 class="relevamiento-section-title">
                    Superficie (prod_finca_superficie)
                </h4>

                <div class="input-group">
                    <label for="sup_total_ha_<?= $idx ?>">Sup total (ha)</label>
                    <div class="input-icon input-icon-name">
                        <input
                            type="text"
                            id="sup_total_ha_<?= $idx ?>"
                            name="fincas[<?= $idx ?>][sup_total_ha]"
                            value="<?= h($superficie['sup_total_ha'] ?? '') ?>"
                        />
                    </div>
                </div>

                <div class="input-group">
                    <label for="sup_total_cultivada_ha_<?= $idx ?>">Sup total cultivada (ha)</label>
                    <div class="input-icon input-icon-name">
                        <input
                            type="text"
                            id="sup_total_cultivada_ha_<?= $idx ?>"
                            name="fincas[<?= $idx ?>][sup_total_cultivada_ha]"
                            value="<?= h($superficie['sup_total_cultivada_ha'] ?? '') ?>"
                        />
                    </div>
                </div>

                <div class="input-group">
                    <label for="sup_total_vid_ha_<?= $idx ?>">Sup total vid (ha)</label>
                    <div class="input-icon input-icon-name">
                        <input
                            type="text"
                            id="sup_total_vid_ha_<?= $idx ?>"
                            name="fincas[<?= $idx ?>][sup_total_vid_ha]"
                            value="<?= h($superficie['sup_total_vid_ha'] ?? '') ?>"
                        />
                    </div>
                </div>

                <div class="input-group">
                    <label for="sup_vid_destinada_coop_ha_<?= $idx ?>">Sup vid destinada coop (ha)</label>
                    <div class="input-icon input-icon-name">
                        <input
                            type="text"
                            id="sup_vid_destinada_coop_ha_<?= $idx ?>"
                            name="fincas[<?= $idx ?>][sup_vid_destinada_coop_ha]"
                            value="<?= h($superficie['sup_vid_destinada_coop_ha'] ?? '') ?>"
                        />
                    </div>
                </div>

                <div class="input-group">
                    <label for="sup_con_otros_cultivos_ha_<?= $idx ?>">Sup con otros cultivos (ha)</label>
                    <div class="input-icon input-icon-name">
                        <input
                            type="text"
                            id="sup_con_otros_cultivos_ha_<?= $idx ?>"
                            name="fincas[<?= $idx ?>][sup_con_otros_cultivos_ha]"
                            value="<?= h($superficie['sup_con_otros_cultivos_ha'] ?? '') ?>"
                        />
                    </div>
                </div>

                <div class="input-group">
                    <label for="clasificacion_riesgo_salinizacion_<?= $idx ?>">Clasificación riesgo salinización</label>
                    <div class="input-icon input-icon-name">
                        <input
                            type="text"
                            id="clasificacion_riesgo_salinizacion_<?= $idx ?>"
                            name="fincas[<?= $idx ?>][clasificacion_riesgo_salinizacion]"
                            value="<?= h($superficie['clasificacion_riesgo_salinizacion'] ?? '') ?>"
                        />
                    </div>
                </div>

                <div class="input-group">
                    <label for="analisis_suelo_completo_<?= $idx ?>">Análisis de suelo completo</label>
                    <div class="input-icon input-icon-name">
                        <input
                            type="text"
                            id="analisis_suelo_completo_<?= $idx ?>"
                            name="fincas[<?= $idx ?>][analisis_suelo_completo]"
                            value="<?= h($superficie['analisis_suelo_completo'] ?? '') ?>"
                        />
                    </div>
                </div>

                <!-- Cultivos (prod_finca_cultivos) - avanzados -->
                <hr class="relevamiento-section-divider">
                <h4 class="relevamiento-section-title relevamiento-advanced-hidden" data-advanced="1">
                    Cultivos (prod_finca_cultivos)
                </h4>

                <div class="input-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
                    <label for="sup_cultivo_horticola_ha_<?= $idx ?>">Sup cultivo hortícola (ha)</label>
                    <div class="input-icon input-icon-name">
                        <input
                            type="text"
                            id="sup_cultivo_horticola_ha_<?= $idx ?>"
                            name="fincas[<?= $idx ?>][sup_cultivo_horticola_ha]"
                            value="<?= h($cultivos['sup_cultivo_horticola_ha'] ?? '') ?>"
                        />
                    </div>
                </div>

                <div class="input-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
                    <label for="estado_cultivo_horticola_<?= $idx ?>">Estado cultivo hortícola</label>
                    <div class="input-icon input-icon-name">
                        <input
                            type="text"
                            id="estado_cultivo_horticola_<?= $idx ?>"
                            name="fincas[<?= $idx ?>][estado_cultivo_horticola]"
                            value="<?= h($cultivos['estado_cultivo_horticola'] ?? '') ?>"
                        />
                    </div>
                </div>

                <div class="input-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
                    <label for="sup_cultivo_fruticola_ha_<?= $idx ?>">Sup cultivo frutícola (ha)</label>
                    <div class="input-icon input-icon-name">
                        <input
                            type="text"
                            id="sup_cultivo_fruticola_ha_<?= $idx ?>"
                            name="fincas[<?= $idx ?>][sup_cultivo_fruticola_ha]"
                            value="<?= h($cultivos['sup_cultivo_fruticola_ha'] ?? '') ?>"
                        />
                    </div>
                </div>

                <div class="input-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
                    <label for="estado_cultivo_fruticola_<?= $idx ?>">Estado cultivo frutícola</label>
                    <div class="input-icon input-icon-name">
                        <input
                            type="text"
                            id="estado_cultivo_fruticola_<?= $idx ?>"
                            name="fincas[<?= $idx ?>][estado_cultivo_fruticola]"
                            value="<?= h($cultivos['estado_cultivo_fruticola'] ?? '') ?>"
                        />
                    </div>
                </div>

                <div class="input-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
                    <label for="sup_cultivo_forestal_otra_ha_<?= $idx ?>">Sup cultivo forestales/otros (ha)</label>
                    <div class="input-icon input-icon-name">
                        <input
                            type="text"
                            id="sup_cultivo_forestal_otra_ha_<?= $idx ?>"
                            name="fincas[<?= $idx ?>][sup_cultivo_forestal_otra_ha]"
                            value="<?= h($cultivos['sup_cultivo_forestal_otra_ha'] ?? '') ?>"
                        />
                    </div>
                </div>

                <div class="input-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
                    <label for="estado_cultivo_forestal_otra_<?= $idx ?>">Estado cultivo forestales/otros</label>
                    <div class="input-icon input-icon-name">
                        <input
                            type="text"
                            id="estado_cultivo_forestal_otra_<?= $idx ?>"
                            name="fincas[<?= $idx ?>][estado_cultivo_forestal_otra]"
                            value="<?= h($cultivos['estado_cultivo_forestal_otra'] ?? '') ?>"
                        />
                    </div>
                </div>

                <!-- Agua (prod_finca_agua) -->
                <hr class="relevamiento-section-divider">
                <h4 class="relevamiento-section-title">
                    Agua de riego (prod_finca_agua)
                </h4>

                <div class="input-group">
                    <label for="sup_agua_con_derecho_ha_<?= $idx ?>">Sup agua con derecho (ha)</label>
                    <div class="input-icon input-icon-name">
                        <input
                            type="text"
                            id="sup_agua_con_derecho_ha_<?= $idx ?>"
                            name="fincas[<?= $idx ?>][sup_agua_con_derecho_ha]"
                            value="<?= h($agua['sup_agua_con_derecho_ha'] ?? '') ?>"
                        />
                    </div>
                </div>

                <div class="input-group">
                    <label for="tipo_riego_<?= $idx ?>">Tipo de riego</label>
                    <div class="input-icon input-icon-name">
                        <input
                            type="text"
                            id="tipo_riego_<?= $idx ?>"
                            name="fincas[<?= $idx ?>][tipo_riego]"
                            value="<?= h($agua['tipo_riego'] ?? '') ?>"
                        />
                    </div>
                </div>

                <div class="input-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
                    <label for="sup_agua_sin_derecho_ha_<?= $idx ?>">Sup agua sin derecho (ha)</label>
                    <div class="input-icon input-icon-name">
                        <input
                            type="text"
                            id="sup_agua_sin_derecho_ha_<?= $idx ?>"
                            name="fincas[<?= $idx ?>][sup_agua_sin_derecho_ha]"
                            value="<?= h($agua['sup_agua_sin_derecho_ha'] ?? '') ?>"
                        />
                    </div>
                </div>

                <div class="input-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
                    <label for="estado_provision_agua_<?= $idx ?>">Estado provisión de agua</label>
                    <div class="input-icon input-icon-name">
                        <input
                            type="text"
                            id="estado_provision_agua_<?= $idx ?>"
                            name="fincas[<?= $idx ?>][estado_provision_agua]"
                            value="<?= h($agua['estado_provision_agua'] ?? '') ?>"
                        />
                    </div>
                </div>

                <div class="input-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
                    <label for="estado_asignacion_turnado_<?= $idx ?>">Estado asignación/turnado</label>
                    <div class="input-icon input-icon-name">
                        <input
                            type="text"
                            id="estado_asignacion_turnado_<?= $idx ?>"
                            name="fincas[<?= $idx ?>][estado_asignacion_turnado]"
                            value="<?= h($agua['estado_asignacion_turnado'] ?? '') ?>"
                        />
                    </div>
                </div>

                <div class="input-group">
                    <label for="estado_sistematizacion_vinedo_<?= $idx ?>">Estado sistematización viñedo</label>
                    <div class="input-icon input-icon-name">
                        <input
                            type="text"
                            id="estado_sistematizacion_vinedo_<?= $idx ?>"
                            name="fincas[<?= $idx ?>][estado_sistematizacion_vinedo]"
                            value="<?= h($agua['estado_sistematizacion_vinedo'] ?? '') ?>"
                        />
                    </div>
                </div>

                <div class="input-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
                    <label for="tiene_flexibilizacion_entrega_agua_<?= $idx ?>">Tiene flexibilización entrega agua</label>
                    <div class="input-icon input-icon-name">
                        <input
                            type="text"
                            id="tiene_flexibilizacion_entrega_agua_<?= $idx ?>"
                            name="fincas[<?= $idx ?>][tiene_flexibilizacion_entrega_agua]"
                            value="<?= h($agua['tiene_flexibilizacion_entrega_agua'] ?? '') ?>"
                        />
                    </div>
                </div>

                <div class="input-group">
                    <label for="riego_presurizado_toma_agua_de_<?= $idx ?>">Riego presurizado toma agua de</label>
                    <div class="input-icon input-icon-name">
                        <input
                            type="text"
                            id="riego_presurizado_toma_agua_de_<?= $idx ?>"
                            name="fincas[<?= $idx ?>][riego_presurizado_toma_agua_de]"
                            value="<?= h($agua['riego_presurizado_toma_agua_de'] ?? '') ?>"
                        />
                    </div>
                </div>

                <div class="input-group">
                    <label for="perforacion_activa_1_<?= $idx ?>">Perforación activa 1</label>
                    <div class="input-icon input-icon-name">
                        <input
                            type="text"
                            id="perforacion_activa_1_<?= $idx ?>"
                            name="fincas[<?= $idx ?>][perforacion_activa_1]"
                            value="<?= h($agua['perforacion_activa_1'] ?? '') ?>"
                        />
                    </div>
                </div>

                <div class="input-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
                    <label for="perforacion_activa_2_<?= $idx ?>">Perforación activa 2</label>
                    <div class="input-icon input-icon-name">
                        <input
                            type="text"
                            id="perforacion_activa_2_<?= $idx ?>"
                            name="fincas[<?= $idx ?>][perforacion_activa_2]"
                            value="<?= h($agua['perforacion_activa_2'] ?? '') ?>"
                        />
                    </div>
                </div>

                <div class="input-group">
                    <label for="agua_analizada_<?= $idx ?>">Agua analizada</label>
                    <div class="input-icon input-icon-name">
                        <input
                            type="text"
                            id="agua_analizada_<?= $idx ?>"
                            name="fincas[<?= $idx ?>][agua_analizada]"
                            value="<?= h($agua['agua_analizada'] ?? '') ?>"
                        />
                    </div>
                </div>

                <div class="input-group">
                    <label for="conductividad_mhos_cm_<?= $idx ?>">Conductividad (mhos/cm)</label>
                    <div class="input-icon input-icon-name">
                        <input
                            type="text"
                            id="conductividad_mhos_cm_<?= $idx ?>"
                            name="fincas[<?= $idx ?>][conductividad_mhos_cm]"
                            value="<?= h($agua['conductividad_mhos_cm'] ?? '') ?>"
                        />
                    </div>
                </div>

                <!-- Maquinaria (prod_finca_maquinaria) -->
                <hr class="relevamiento-section-divider">
                <h4 class="relevamiento-section-title">
                    Maquinaria (prod_finca_maquinaria)
                </h4>

                <div class="input-group">
                    <label for="clasificacion_estado_tractor_<?= $idx ?>">Clasificación estado tractor</label>
                    <div class="input-icon input-icon-name">
                        <input
                            type="text"
                            id="clasificacion_estado_tractor_<?= $idx ?>"
                            name="fincas[<?= $idx ?>][clasificacion_estado_tractor]"
                            value="<?= h($maquinaria['clasificacion_estado_tractor'] ?? '') ?>"
                        />
                    </div>
                </div>

                <div class="input-group">
                    <label for="estado_pulverizadora_<?= $idx ?>">Estado pulverizadora</label>
                    <div class="input-icon input-icon-name">
                        <input
                            type="text"
                            id="estado_pulverizadora_<?= $idx ?>"
                            name="fincas[<?= $idx ?>][estado_pulverizadora]"
                            value="<?= h($maquinaria['estado_pulverizadora'] ?? '') ?>"
                        />
                    </div>
                </div>

                <div class="input-group">
                    <label for="clasificacion_estado_implementos_<?= $idx ?>">Clasificación estado implementos</label>
                    <div class="input-icon input-icon-name">
                        <input
                            type="text"
                            id="clasificacion_estado_implementos_<?= $idx ?>"
                            name="fincas[<?= $idx ?>][clasificacion_estado_implementos]"
                            value="<?= h($maquinaria['clasificacion_estado_implementos'] ?? '') ?>"
                        />
                    </div>
                </div>

                <div class="input-group">
                    <label for="utiliza_empresa_servicios_<?= $idx ?>">Utiliza empresa de servicios</label>
                    <div class="input-icon input-icon-name">
                        <input
                            type="text"
                            id="utiliza_empresa_servicios_<?= $idx ?>"
                            name="fincas[<?= $idx ?>][utiliza_empresa_servicios]"
                            value="<?= h($maquinaria['utiliza_empresa_servicios'] ?? '') ?>"
                        />
                    </div>
                </div>

                <div class="input-group">
                    <label for="administracion_<?= $idx ?>">Administración</label>
                    <div class="input-icon input-icon-name">
                        <input
                            type="text"
                            id="administracion_<?= $idx ?>"
                            name="fincas[<?= $idx ?>][administracion]"
                            value="<?= h($maquinaria['administracion'] ?? '') ?>"
                        />
                    </div>
                </div>

                <div class="input-group">
                    <label for="trabajadores_permanentes_<?= $idx ?>">Trabajadores permanentes</label>
                    <div class="input-icon input-icon-name">
                        <input
                            type="number"
                            id="trabajadores_permanentes_<?= $idx ?>"
                            name="fincas[<?= $idx ?>][trabajadores_permanentes]"
                            value="<?= h($maquinaria['trabajadores_permanentes'] ?? '') ?>"
                        />
                    </div>
                </div>

                <div class="input-group">
                    <label for="posee_deposito_fitosanitarios_<?= $idx ?>">Posee depósito fitosanitarios</label>
                    <div class="input-icon input-icon-name">
                        <input
                            type="text"
                            id="posee_deposito_fitosanitarios_<?= $idx ?>"
                            name="fincas[<?= $idx ?>][posee_deposito_fitosanitarios]"
                            value="<?= h($maquinaria['posee_deposito_fitosanitarios'] ?? '') ?>"
                        />
                    </div>
                </div>

                <!-- Gerencia (prod_finca_gerencia) -->
                <hr class="relevamiento-section-divider">
                <h4 class="relevamiento-section-title">
                    Gerencia (prod_finca_gerencia)
                </h4>

                <div class="input-group">
                    <label for="problemas_gerencia_<?= $idx ?>">Problemas de gerencia</label>
                    <div class="input-icon input-icon-name">
                        <input
                            type="text"
                            id="problemas_gerencia_<?= $idx ?>"
                            name="fincas[<?= $idx ?>][problemas_gerencia]"
                            value="<?= h($gerencia['problemas_gerencia'] ?? '') ?>"
                        />
                    </div>
                </div>

                <!-- Campos avanzados gerencia -->
                <div class="input-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
                    <label for="prob_gerenciamiento_1_<?= $idx ?>">Prob gerenciamiento 1</label>
                    <div class="input-icon input-icon-name">
                        <input
                            type="text"
                            id="prob_gerenciamiento_1_<?= $idx ?>"
                            name="fincas[<?= $idx ?>][prob_gerenciamiento_1]"
                            value="<?= h($gerencia['prob_gerenciamiento_1'] ?? '') ?>"
                        />
                    </div>
                </div>

                <div class="input-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
                    <label for="prob_personal_1_<?= $idx ?>">Prob personal 1</label>
                    <div class="input-icon input-icon-name">
                        <input
                            type="text"
                            id="prob_personal_1_<?= $idx ?>"
                            name="fincas[<?= $idx ?>][prob_personal_1]"
                            value="<?= h($gerencia['prob_personal_1'] ?? '') ?>"
                        />
                    </div>
                </div>

                <div class="input-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
                    <label for="prob_tecnologicos_1_<?= $idx ?>">Prob tecnológicos 1</label>
                    <div class="input-icon input-icon-name">
                        <input
                            type="text"
                            id="prob_tecnologicos_1_<?= $idx ?>"
                            name="fincas[<?= $idx ?>][prob_tecnologicos_1]"
                            value="<?= h($gerencia['prob_tecnologicos_1'] ?? '') ?>"
                        />
                    </div>
                </div>

                <div class="input-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
                    <label for="prob_administracion_1_<?= $idx ?>">Prob administración 1</label>
                    <div class="input-icon input-icon-name">
                        <input
                            type="text"
                            id="prob_administracion_1_<?= $idx ?>"
                            name="fincas[<?= $idx ?>][prob_administracion_1]"
                            value="<?= h($gerencia['prob_administracion_1'] ?? '') ?>"
                        />
                    </div>
                </div>

                <div class="input-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
                    <label for="prob_medios_produccion_1_<?= $idx ?>">Prob medios producción 1</label>
                    <div class="input-icon input-icon-name">
                        <input
                            type="text"
                            id="prob_medios_produccion_1_<?= $idx ?>"
                            name="fincas[<?= $idx ?>][prob_medios_produccion_1]"
                            value="<?= h($gerencia['prob_medios_produccion_1'] ?? '') ?>"
                        />
                    </div>
                </div>

                <div class="input-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
                    <label for="prob_observacion_1_<?= $idx ?>">Prob observación 1</label>
                    <div class="input-icon input-icon-name">
                        <input
                            type="text"
                            id="prob_observacion_1_<?= $idx ?>"
                            name="fincas[<?= $idx ?>][prob_observacion_1]"
                            value="<?= h($gerencia['prob_observacion_1'] ?? '') ?>"
                        />
                    </div>
                </div>

                <div class="input-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
                    <label for="prob_gerenciamiento_2_<?= $idx ?>">Prob gerenciamiento 2</label>
                    <div class="input-icon input-icon-name">
                        <input
                            type="text"
                            id="prob_gerenciamiento_2_<?= $idx ?>"
                            name="fincas[<?= $idx ?>][prob_gerenciamiento_2]"
                            value="<?= h($gerencia['prob_gerenciamiento_2'] ?? '') ?>"
                        />
                    </div>
                </div>

                <div class="input-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
                    <label for="prob_personal_2_<?= $idx ?>">Prob personal 2</label>
                    <div class="input-icon input-icon-name">
                        <input
                            type="text"
                            id="prob_personal_2_<?= $idx ?>"
                            name="fincas[<?= $idx ?>][prob_personal_2]"
                            value="<?= h($gerencia['prob_personal_2'] ?? '') ?>"
                        />
                    </div>
                </div>

                <div class="input-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
                    <label for="prob_tecnologicos_2_<?= $idx ?>">Prob tecnológicos 2</label>
                    <div class="input-icon input-icon-name">
                        <input
                            type="text"
                            id="prob_tecnologicos_2_<?= $idx ?>"
                            name="fincas[<?= $idx ?>][prob_tecnologicos_2]"
                            value="<?= h($gerencia['prob_tecnologicos_2'] ?? '') ?>"
                        />
                    </div>
                </div>

                <div class="input-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
                    <label for="prob_administracion_2_<?= $idx ?>">Prob administración 2</label>
                    <div class="input-icon input-icon-name">
                        <input
                            type="text"
                            id="prob_administracion_2_<?= $idx ?>"
                            name="fincas[<?= $idx ?>][prob_administracion_2]"
                            value="<?= h($gerencia['prob_administracion_2'] ?? '') ?>"
                        />
                    </div>
                </div>

                <div class="input-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
                    <label for="prob_medios_produccion_2_<?= $idx ?>">Prob medios producción 2</label>
                    <div class="input-icon input-icon-name">
                        <input
                            type="text"
                            id="prob_medios_produccion_2_<?= $idx ?>"
                            name="fincas[<?= $idx ?>][prob_medios_produccion_2]"
                            value="<?= h($gerencia['prob_medios_produccion_2'] ?? '') ?>"
                        />
                    </div>
                </div>

                <div class="input-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
                    <label for="prob_observacion_2_<?= $idx ?>">Prob observación 2</label>
                    <div class="input-icon input-icon-name">
                        <input
                            type="text"
                            id="prob_observacion_2_<?= $idx ?>"
                            name="fincas[<?= $idx ?>][prob_observacion_2]"
                            value="<?= h($gerencia['prob_observacion_2'] ?? '') ?>"
                        />
                    </div>
                </div>

                <!-- Limitantes -->
                <div class="input-group">
                    <label for="limitante_1_<?= $idx ?>">Limitante 1</label>
                    <div class="input-icon input-icon-name">
                        <input
                            type="text"
                            id="limitante_1_<?= $idx ?>"
                            name="fincas[<?= $idx ?>][limitante_1]"
                            value="<?= h($gerencia['limitante_1'] ?? '') ?>"
                        />
                    </div>
                </div>

                <div class="input-group">
                    <label for="limitante_2_<?= $idx ?>">Limitante 2</label>
                    <div class="input-icon input-icon-name">
                        <input
                            type="text"
                            id="limitante_2_<?= $idx ?>"
                            name="fincas[<?= $idx ?>][limitante_2]"
                            value="<?= h($gerencia['limitante_2'] ?? '') ?>"
                        />
                    </div>
                </div>

                <div class="input-group">
                    <label for="limitante_3_<?= $idx ?>">Limitante 3</label>
                    <div class="input-icon input-icon-name">
                        <input
                            type="text"
                            id="limitante_3_<?= $idx ?>"
                            name="fincas[<?= $idx ?>][limitante_3]"
                            value="<?= h($gerencia['limitante_3'] ?? '') ?>"
                        />
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</form>
