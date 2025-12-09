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

$usuario        = $datosFamilia['usuario'] ?? [];
$usuariosInfo   = $datosFamilia['usuarios_info'] ?? [];
$contactosAlt   = $datosFamilia['contactos_alternos'] ?? [];
$infoProd       = $datosFamilia['info_productor'] ?? [];
$colaboradores  = $datosFamilia['colaboradores'] ?? [];
$hijos          = $datosFamilia['hijos'] ?? [];

function h($value): string
{
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}
?>
<div>
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

        <div class="form-group">
            <label>Nombre</label>
            <input type="text" name="nombre" value="<?= h($usuariosInfo['nombre'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label>Teléfono</label>
            <input type="text" name="telefono" value="<?= h($usuariosInfo['telefono'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label>Correo</label>
            <input type="email" name="correo" value="<?= h($usuariosInfo['correo'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label>Fecha de nacimiento</label>
            <input type="date" name="fecha_nacimiento" value="<?= h($usuariosInfo['fecha_nacimiento'] ?? '') ?>">
        </div>

        <div class="form-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label>Categorización</label>
            <input type="text" name="categorizacion" value="<?= h($usuariosInfo['categorizacion'] ?? '') ?>">
        </div>

        <div class="form-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label>Tipo de relación</label>
            <input type="text" name="tipo_relacion" value="<?= h($usuariosInfo['tipo_relacion'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label>CUIT</label>
            <input type="text" name="cuit" value="<?= h($usuario['cuit'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label>Razón social</label>
            <input type="text" name="razon_social" value="<?= h($usuario['razon_social'] ?? '') ?>">
        </div>

        <hr>

        <h4>Contactos alternos (productores_contactos_alternos)</h4>

        <div class="form-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label>Contacto preferido</label>
            <input type="text" name="contacto_preferido" value="<?= h($contactosAlt['contacto_preferido'] ?? '') ?>">
        </div>

        <div class="form-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label>Celular alternativo</label>
            <input type="text" name="celular_alternativo" value="<?= h($contactosAlt['celular_alternativo'] ?? '') ?>">
        </div>

        <div class="form-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label>Teléfono fijo</label>
            <input type="text" name="telefono_fijo" value="<?= h($contactosAlt['telefono_fijo'] ?? '') ?>">
        </div>

        <div class="form-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label>Mail alternativo</label>
            <input type="email" name="mail_alternativo" value="<?= h($contactosAlt['mail_alternativo'] ?? '') ?>">
        </div>

        <hr>

        <h4>Información del productor (info_productor)</h4>

        <div class="form-group">
            <label>Acceso a internet</label>
            <input type="text" name="acceso_internet" value="<?= h($infoProd['acceso_internet'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label>Vive en la finca</label>
            <input type="text" name="vive_en_finca" value="<?= h($infoProd['vive_en_finca'] ?? '') ?>">
        </div>

        <div class="form-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label>Tiene otra finca</label>
            <input type="text" name="tiene_otra_finca" value="<?= h($infoProd['tiene_otra_finca'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label>Condición en la cooperativa</label>
            <input type="text" name="condicion_cooperativa" value="<?= h($infoProd['condicion_cooperativa'] ?? '') ?>">
        </div>

        <div class="form-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label>Año de asociación</label>
            <input type="text" name="anio_asociacion" value="<?= h($infoProd['anio_asociacion'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label>Actividad principal</label>
            <input type="text" name="actividad_principal" value="<?= h($infoProd['actividad_principal'] ?? '') ?>">
        </div>

        <div class="form-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label>Actividad secundaria</label>
            <input type="text" name="actividad_secundaria" value="<?= h($infoProd['actividad_secundaria'] ?? '') ?>">
        </div>

        <div class="form-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label>% aporte vitivinícola</label>
            <input type="text" name="porcentaje_aporte_vitivinicola" value="<?= h($infoProd['porcentaje_aporte_vitivinicola'] ?? '') ?>">
        </div>

        <hr>

        <h4>Colaboradores (prod_colaboradores)</h4>

        <div class="form-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label>Hijos/sobrinos participan</label>
            <input type="text" name="hijos_sobrinos_participan" value="<?= h($colaboradores['hijos_sobrinos_participan'] ?? '') ?>">
        </div>

        <div class="form-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label>Mujeres tiempo completo</label>
            <input type="text" name="mujeres_tc" value="<?= h($colaboradores['mujeres_tc'] ?? '') ?>">
        </div>

        <div class="form-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label>Hombres tiempo completo</label>
            <input type="text" name="hombres_tc" value="<?= h($colaboradores['hombres_tc'] ?? '') ?>">
        </div>

        <div class="form-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label>Mujeres tiempo parcial</label>
            <input type="text" name="mujeres_tp" value="<?= h($colaboradores['mujeres_tp'] ?? '') ?>">
        </div>

        <div class="form-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label>Hombres tiempo parcial</label>
            <input type="text" name="hombres_tp" value="<?= h($colaboradores['hombres_tp'] ?? '') ?>">
        </div>

        <div class="form-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label>Probabilidad de que hijos trabajen</label>
            <input type="text" name="prob_hijos_trabajen" value="<?= h($colaboradores['prob_hijos_trabajen'] ?? '') ?>">
        </div>

        <hr>

        <h4>Hijos (prod_hijos)</h4>

        <div class="form-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label>Motivo de no trabajar</label>
            <input type="text" name="motivo_no_trabajar" value="<?= h($hijos['motivo_no_trabajar'] ?? '') ?>">
        </div>

        <div class="form-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label>Rango etario</label>
            <input type="text" name="rango_etario" value="<?= h($hijos['rango_etario'] ?? '') ?>">
        </div>

        <div class="form-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label>Sexo</label>
            <input type="text" name="sexo" value="<?= h($hijos['sexo'] ?? '') ?>">
        </div>

        <div class="form-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label>Cantidad</label>
            <input type="text" name="cantidad" value="<?= h($hijos['cantidad'] ?? '') ?>">
        </div>

        <div class="form-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label>Nivel de estudio</label>
            <input type="text" name="nivel_estudio" value="<?= h($hijos['nivel_estudio'] ?? '') ?>">
        </div>

        <!-- Hijo 1 -->
        <div class="form-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label>Nombre hijo 1</label>
            <input type="text" name="nom_hijo_1" value="<?= h($hijos['nom_hijo_1'] ?? '') ?>">
        </div>

        <div class="form-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label>Fecha nacimiento hijo 1</label>
            <input type="date" name="fecha_nacimiento_1" value="<?= h($hijos['fecha_nacimiento_1'] ?? '') ?>">
        </div>

        <div class="form-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label>Sexo hijo 1</label>
            <input type="text" name="sexo1" value="<?= h($hijos['sexo1'] ?? '') ?>">
        </div>

        <div class="form-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label>Nivel estudio hijo 1</label>
            <input type="text" name="nivel_estudio1" value="<?= h($hijos['nivel_estudio1'] ?? '') ?>">
        </div>

        <!-- Hijo 2 -->
        <div class="form-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label>Nombre hijo 2</label>
            <input type="text" name="nom_hijo_2" value="<?= h($hijos['nom_hijo_2'] ?? '') ?>">
        </div>

        <div class="form-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label>Fecha nacimiento hijo 2</label>
            <input type="date" name="fecha_nacimiento_2" value="<?= h($hijos['fecha_nacimiento_2'] ?? '') ?>">
        </div>

        <div class="form-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label>Sexo hijo 2</label>
            <input type="text" name="sexo2" value="<?= h($hijos['sexo2'] ?? '') ?>">
        </div>

        <div class="form-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label>Nivel estudio hijo 2</label>
            <input type="text" name="nive_estudio2" value="<?= h($hijos['nive_estudio2'] ?? '') ?>">
        </div>

        <!-- Hijo 3 -->
        <div class="form-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label>Nombre hijo 3</label>
            <input type="text" name="nom_hijo_3" value="<?= h($hijos['nom_hijo_3'] ?? '') ?>">
        </div>

        <div class="form-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label>Fecha nacimiento hijo 3</label>
            <input type="date" name="fecha_nacimiento_3" value="<?= h($hijos['fecha_nacimiento_3'] ?? '') ?>">
        </div>

        <div class="form-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label>Sexo hijo 3</label>
            <input type="text" name="sexo3" value="<?= h($hijos['sexo3'] ?? '') ?>">
        </div>

        <div class="form-group campo-avanzado relevamiento-advanced-hidden" data-advanced="1">
            <label>Nivel estudio hijo 3</label>
            <input type="text" name="nivel_estudio3" value="<?= h($hijos['nivel_estudio3'] ?? '') ?>">
        </div>

    </form>
</div>