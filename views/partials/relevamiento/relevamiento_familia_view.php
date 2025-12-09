<?php

/**
 * Vista parcial para el modal de Familia.
 * Muestra un formulario con datos combinados de:
 * - usuarios / usuarios_info
 * - productores_contactos_alternos
 * - info_productor
 * - prod_colaboradores
 * - prod_hijos
 *
 * Los campos marcados como "avanzados" se controlan con un switch
 * mediante data-advanced="1" y la clase CSS .relevamiento-advanced-hidden.
 */

function h($value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

// Evitamos errores cuando $datosFamilia es null o no es array
$usuario       = [];
$usuariosInfo  = [];
$contactosAlt  = [];
$infoProd      = [];
$colaboradores = [];
$hijos         = [];

if (is_array($datosFamilia)) {
    $usuario       = $datosFamilia['usuario']            ?? [];
    $usuariosInfo  = $datosFamilia['usuarios_info']      ?? [];
    $contactosAlt  = $datosFamilia['contactos_alternos'] ?? [];
    $infoProd      = $datosFamilia['info_productor']     ?? [];
    $colaboradores = $datosFamilia['colaboradores']      ?? [];
    $hijos         = $datosFamilia['hijos']              ?? [];
}
?>
<div>
    <?php if (!empty($errorBackend)): ?>
        <p class="text-danger">
            Error al cargar datos de familia (backend): <?= h($errorBackend) ?>
        </p>
    <?php endif; ?>

    <p>
        Formulario de <strong>Familia</strong> para el productor
        <strong><?= h($productorIdReal) ?></strong>.
    </p>

    <!-- Switch para campos avanzados -->
    <div class="form-switch">
        <label>
            <input type="checkbox" data-role="familia-advanced-toggle">
            Mostrar campos avanzados
        </label>
    </div>

    <form id="familia-form">
        <h4>Datos personales (usuarios / usuarios_info)</h4>

        <div class="input-group">
            <label for="nombre">Nombre</label>
            <div class="input-icon input-icon-name">
                <input
                    type="text"
                    id="nombre"
                    name="nombre"
                    placeholder="…"
                    value="<?= h($usuariosInfo['nombre'] ?? '') ?>" />
            </div>
        </div>

        <div class="input-group">
            <label for="telefono">Teléfono</label>
            <div class="input-icon input-icon-name">
                <input
                    type="text"
                    id="telefono"
                    name="telefono"
                    placeholder="…"
                    value="<?= h($usuariosInfo['telefono'] ?? '') ?>" />
            </div>
        </div>

        <div class="input-group">
            <label for="correo">Correo</label>
            <div class="input-icon input-icon-name">
                <input
                    type="email"
                    id="correo"
                    name="correo"
                    placeholder="…"
                    value="<?= h($usuariosInfo['correo'] ?? '') ?>" />
            </div>
        </div>

        <div class="input-group">
            <label for="fecha_nacimiento">Fecha de nacimiento</label>
            <div class="input-icon input-icon-name">
                <input
                    type="date"
                    id="fecha_nacimiento"
                    name="fecha_nacimiento"
                    placeholder="…"
                    value="<?= h($usuariosInfo['fecha_nacimiento'] ?? '') ?>" />
            </div>
        </div>

        <div class="input-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label for="categorizacion">Categorización</label>
            <div class="input-icon input-icon-name">
                <input
                    type="text"
                    id="categorizacion"
                    name="categorizacion"
                    placeholder="…"
                    value="<?= h($usuariosInfo['categorizacion'] ?? '') ?>" />
            </div>
        </div>

        <div class="input-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label for="tipo_relacion">Tipo de relación</label>
            <div class="input-icon input-icon-name">
                <input
                    type="text"
                    id="tipo_relacion"
                    name="tipo_relacion"
                    placeholder="…"
                    value="<?= h($usuariosInfo['tipo_relacion'] ?? '') ?>" />
            </div>
        </div>

        <div class="input-group">
            <label for="cuit">CUIT</label>
            <div class="input-icon input-icon-name">
                <input
                    type="text"
                    id="cuit"
                    name="cuit"
                    placeholder="…"
                    value="<?= h($usuario['cuit'] ?? '') ?>" />
            </div>
        </div>

        <div class="input-group">
            <label for="razon_social">Razón social</label>
            <div class="input-icon input-icon-name">
                <input
                    type="text"
                    id="razon_social"
                    name="razon_social"
                    placeholder="…"
                    value="<?= h($usuario['razon_social'] ?? '') ?>" />
            </div>
        </div>

        <hr>

        <h4 class="Contactos alternos (productores_contactos_alternos)">Contactos alternos (productores_contactos_alternos)</h4>

        <div class="input-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label for="telefono_fijo">Teléfono fijo</label>
            <div class="input-icon input-icon-name">
                <input
                    type="text"
                    id="telefono_fijo"
                    name="telefono_fijo"
                    placeholder="…"
                    value="<?= h($contactosAlt['telefono_fijo'] ?? '') ?>"
                />
            </div>
        </div>

        <div class="input-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label for="celular_alternativo">Celular alternativo</label>
            <div class="input-icon input-icon-name">
                <input
                    type="text"
                    id="celular_alternativo"
                    name="celular_alternativo"
                    placeholder="…"
                    value="<?= h($contactosAlt['celular_alternativo'] ?? '') ?>" />
            </div>
        </div>

        <div class="input-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label for="telefono_fijo">Teléfono fijo</label>
            <div class="input-icon input-icon-name">
                <input
                    type="text"
                    id="telefono_fijo"
                    name="telefono_fijo"
                    placeholder="…"
                    value><?= h($contactosAlt['telefono_fijo'] ?? '') ?>"
                />
            </div>
        </div>

        <div class="input-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label for="mail_alternativo">Mail alternativo</label>
            <div class="input-icon input-icon-name">
                <input
                    type="email"
                    id="mail_alternativo"
                    name="mail_alternativo"
                    placeholder="…"
                    value="<?= h($contactosAlt['mail_alternativo'] ?? '') ?>" />
            </div>
        </div>

        <hr>

        <h4>Información del productor (info_productor)</h4>

        <div class="input-group">
            <label for="acceso_internet">Acceso a internet</label>
            <div class="input-icon input-icon-name">
                <input
                    type="text"
                    id="acceso_internet"
                    name="acceso_internet"
                    placeholder="…"
                    value="<?= h($infoProd['acceso_internet'] ?? '') ?>" />
            </div>
        </div>

        <div class="input-group">
            <label for="vive_en_finca">Vive en la finca</label>
            <div class="input-icon input-icon-name">
                <input
                    type="text"
                    id="vive_en_finca"
                    name="vive_en_finca"
                    placeholder="…"
                    value="<?= h($infoProd['vive_en_finca'] ?? '') ?>" />
            </div>
        </div>

        <div class="input-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label for="tiene_otra_finca">Tiene otra finca</label>
            <div class="input-icon input-icon-name">
                <input
                    type="text"
                    id="tiene_otra_finca"
                    name="tiene_otra_finca"
                    placeholder="…"
                    value="<?= h($infoProd['tiene_otra_finca'] ?? '') ?>" />
            </div>
        </div>

        <div class="input-group">
            <label for="condicion_cooperativa">Condición en la cooperativa</label>
            <div class="input-icon input-icon-name">
                <input
                    type="text"
                    id="condicion_cooperativa"
                    name="condicion_cooperativa"
                    placeholder="…"
                    value="<?= h($infoProd['condicion_cooperativa'] ?? '') ?>" />
            </div>
        </div>

        <div class="input-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label for="anio_asociacion">Año de asociación</label>
            <div class="input-icon input-icon-name">
                <input
                    type="text"
                    id="anio_asociacion"
                    name="anio_asociacion"
                    placeholder="…"
                    value="<?= h($infoProd['anio_asociacion'] ?? '') ?>" />
            </div>
        </div>

        <div class="input-group">
            <label for="actividad_principal">Actividad principal</label>
            <div class="input-icon input-icon-name">
                <input
                    type="text"
                    id="actividad_principal"
                    name="actividad_principal"
                    placeholder="…"
                    value="<?= h($infoProd['actividad_principal'] ?? '') ?>" />
            </div>
        </div>

        <div class="input-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label for="actividad_secundaria">Actividad secundaria</label>
            <div class="input-icon input-icon-name">
                <input
                    type="text"
                    id="actividad_secundaria"
                    name="actividad_secundaria"
                    placeholder="…"
                    value="<?= h($infoProd['actividad_secundaria'] ?? '') ?>" />
            </div>
        </div>

        <div class="input-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label for="porcentaje_aporte_vitivinicola">% aporte vitivinícola</label>
            <div class="input-icon input-icon-name">
                <input
                    type="text"
                    id="porcentaje_aporte_vitivinicola"
                    name="porcentaje_aporte_vitivinicola"
                    placeholder="…"
                    value="<?= h($infoProd['porcentaje_aporte_vitivinicola'] ?? '') ?>" />
            </div>
        </div>

        <hr>

        <h4>Colaboradores (prod_colaboradores)</h4>

        <div class="input-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label for="hijos_sobrinos_participan">Hijos/sobrinos participan</label>
            <div class="input-icon input-icon-name">
                <input
                    type="text"
                    id="hijos_sobrinos_participan"
                    name="hijos_sobrinos_participan"
                    placeholder="…"
                    value="<?= h($colaboradores['hijos_sobrinos_participan'] ?? '') ?>" />
            </div>
        </div>

        <div class="input-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label for="mujeres_tc">Mujeres tiempo completo</label>
            <div class="input-icon input-icon-name">
                <input
                    type="text"
                    id="mujeres_tc"
                    name="mujeres_tc"
                    placeholder="…"
                    value="<?= h($colaboradores['mujeres_tc'] ?? '') ?>" />
            </div>
        </div>

        <div class="input-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label for="hombres_tc">Hombres tiempo completo</label>
            <div class="input-icon input-icon-name">
                <input
                    type="text"
                    id="hombres_tc"
                    name="hombres_tc"
                    placeholder="…"
                    value="<?= h($colaboradores['hombres_tc'] ?? '') ?>" />
            </div>
        </div>

        <div class="input-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label for="mujeres_tp">Mujeres tiempo parcial</label>
            <div class="input-icon input-icon-name">
                <input
                    type="text"
                    id="mujeres_tp"
                    name="mujeres_tp"
                    placeholder="…"
                    value="<?= h($colaboradores['mujeres_tp'] ?? '') ?>" />
            </div>
        </div>

        <div class="input-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label for="hombres_tp">Hombres tiempo parcial</label>
            <div class="input-icon input-icon-name">
                <input
                    type="text"
                    id="hombres_tp"
                    name="hombres_tp"
                    placeholder="…"
                    value="<?= h($colaboradores['hombres_tp'] ?? '') ?>" />
            </div>
        </div>

        <div class="input-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label for="prob_hijos_trabajen">Probabilidad de que hijos trabajen</label>
            <div class="input-icon input-icon-name">
                <input
                    type="text"
                    id="prob_hijos_trabajen"
                    name="prob_hijos_trabajen"
                    placeholder="…"
                    value="<?= h($colaboradores['prob_hijos_trabajen'] ?? '') ?>" />
            </div>
        </div>

        <hr>

        <h4>Hijos (prod_hijos)</h4>

        <div class="input-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label for="motivo_no_trabajar">Motivo de no trabajar</label>
            <div class="input-icon input-icon-name">
                <input
                    type="text"
                    id="motivo_no_trabajar"
                    name="motivo_no_trabajar"
                    placeholder="…"
                    value="<?= h($hijos['motivo_no_trabajar'] ?? '') ?>" />
            </div>
        </div>

        <div class="input-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label for="rango_etario">Rango etario</label>
            <div class="input-icon input-icon-name">
                <input
                    type="text"
                    id="rango_etario"
                    name="rango_etario"
                    placeholder="…"
                    value="<?= h($hijos['rango_etario'] ?? '') ?>" />
            </div>
        </div>

        <div class="input-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label for="sexo">Sexo</label>
            <div class="input-icon input-icon-name">
                <input
                    type="text"
                    id="sexo"
                    name="sexo"
                    placeholder="…"
                    value="<?= h($hijos['sexo'] ?? '') ?>" />
            </div>
        </div>

        <div class="input-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label for="cantidad">Cantidad</label>
            <div class="input-icon input-icon-name">
                <input
                    type="text"
                    id="cantidad"
                    name="cantidad"
                    placeholder="…"
                    value="<?= h($hijos['cantidad'] ?? '') ?>" />
            </div>
        </div>

        <div class="input-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label for="nivel_estudio">Nivel de estudio</label>
            <div class="input-icon input-icon-name">
                <input
                    type="text"
                    id="nivel_estudio"
                    name="nivel_estudio"
                    placeholder="…"
                    value="<?= h($hijos['nivel_estudio'] ?? '') ?>" />
            </div>
        </div>

        <!-- Hijo 1 -->
        <div class="input-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label for="nom_hijo_1">Nombre hijo 1</label>
            <div class="input-icon input-icon-name">
                <input
                    type="text"
                    id="nom_hijo_1"
                    name="nom_hijo_1"
                    placeholder="…"
                    value="<?= h($hijos['nom_hijo_1'] ?? '') ?>" />
            </div>
        </div>

        <div class="input-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label for="fecha_nacimiento_1">Fecha nacimiento hijo 1</label>
            <div class="input-icon input-icon-name">
                <input
                    type="date"
                    id="fecha_nacimiento_1"
                    name="fecha_nacimiento_1"
                    placeholder="…"
                    value="<?= h($hijos['fecha_nacimiento_1'] ?? '') ?>" />
            </div>
        </div>

        <div class="input-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label for="sexo1">Sexo hijo 1</label>
            <div class="input-icon input-icon-name">
                <input
                    type="text"
                    id="sexo1"
                    name="sexo1"
                    placeholder="…"
                    value="<?= h($hijos['sexo1'] ?? '') ?>" />
            </div>
        </div>

        <div class="input-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label for="nivel_estudio1">Nivel estudio hijo 1</label>
            <div class="input-icon input-icon-name">
                <input
                    type="text"
                    id="nivel_estudio1"
                    name="nivel_estudio1"
                    placeholder="…"
                    value="<?= h($hijos['nivel_estudio1'] ?? '') ?>" />
            </div>
        </div>

        <!-- Hijo 2 -->
        <div class="input-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label for="nom_hijo_2">Nombre hijo 2</label>
            <div class="input-icon input-icon-name">
                <input
                    type="text"
                    id="nom_hijo_2"
                    name="nom_hijo_2"
                    placeholder="…"
                    value="<?= h($hijos['nom_hijo_2'] ?? '') ?>" />
            </div>
        </div>

        <div class="input-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label for="fecha_nacimiento_2">Fecha nacimiento hijo 2</label>
            <div class="input-icon input-icon-name">
                <input
                    type="date"
                    id="fecha_nacimiento_2"
                    name="fecha_nacimiento_2"
                    placeholder="…"
                    value="<?= h($hijos['fecha_nacimiento_2'] ?? '') ?>" />
            </div>
        </div>

        <div class="input-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label for="sexo2">Sexo hijo 2</label>
            <div class="input-icon input-icon-name">
                <input
                    type="text"
                    id="sexo2"
                    name="sexo2"
                    placeholder="…"
                    value="<?= h($hijos['sexo2'] ?? '') ?>" />
            </div>
        </div>

        <div class="input-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label for="nive_estudio2">Nivel estudio hijo 2</label>
            <div class="input-icon input-icon-name">
                <input
                    type="text"
                    id="nive_estudio2"
                    name="nive_estudio2"
                    placeholder="…"
                    value="<?= h($hijos['nive_estudio2'] ?? '') ?>" />
            </div>
        </div>

        <!-- Hijo 3 -->
        <div class="input-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label for="nom_hijo_3">Nombre hijo 3</label>
            <div class="input-icon input-icon-name">
                <input
                    type="text"
                    id="nom_hijo_3"
                    name="nom_hijo_3"
                    placeholder="…"
                    value="<?= h($hijos['nom_hijo_3'] ?? '') ?>" />
            </div>
        </div>

        <div class="input-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label for="fecha_nacimiento_3">Fecha nacimiento hijo 3</label>
            <div class="input-icon input-icon-name">
                <input
                    type="date"
                    id="fecha_nacimiento_3"
                    name="fecha_nacimiento_3"
                    placeholder="…"
                    value="<?= h($hijos['fecha_nacimiento_3'] ?? '') ?>" />
            </div>
        </div>

        <div class="input-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label for="sexo3">Sexo hijo 3</label>
            <div class="input-icon input-icon-name">
                <input
                    type="text"
                    id="sexo3"
                    name="sexo3"
                    placeholder="…"
                    value="<?= h($hijos['sexo3'] ?? '') ?>" />
            </div>
        </div>

        <div class="input-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label for="nivel_estudio3">Nivel estudio hijo 3</label>
            <div class="input-icon input-icon-name">
                <input
                    type="text"
                    id="nivel_estudio3"
                    name="nivel_estudio3"
                    placeholder="…"
                    value="<?= h($hijos['nivel_estudio3'] ?? '') ?>" />
            </div>
        </div>

    </form>

</div>