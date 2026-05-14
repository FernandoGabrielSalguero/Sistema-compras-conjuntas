<?php


require_once __DIR__ . '/../config.php';

final class SveRelevamientoModel
{
    private PDO $pdo;

    private const RELEVAMIENTO_CAMPOS = [
        ['tabla' => 'usuarios_info', 'campo' => 'nombre', 'etiqueta' => 'Nombre', 'grupo' => 'Datos del productor', 'alcance' => 'productor'],
        ['tabla' => 'usuarios_info', 'campo' => 'telefono', 'etiqueta' => 'Telefono', 'grupo' => 'Datos del productor', 'alcance' => 'productor'],
        ['tabla' => 'usuarios_info', 'campo' => 'correo', 'etiqueta' => 'Correo', 'grupo' => 'Datos del productor', 'alcance' => 'productor'],
        ['tabla' => 'usuarios_info', 'campo' => 'fecha_nacimiento', 'etiqueta' => 'Fecha de nacimiento', 'grupo' => 'Datos del productor', 'alcance' => 'productor'],
        ['tabla' => 'usuarios_info', 'campo' => 'categorizacion', 'etiqueta' => 'Categorizacion', 'grupo' => 'Datos del productor', 'alcance' => 'productor'],
        ['tabla' => 'usuarios_info', 'campo' => 'tipo_relacion', 'etiqueta' => 'Tipo de relacion', 'grupo' => 'Datos del productor', 'alcance' => 'productor'],
        ['tabla' => 'usuarios', 'campo' => 'cuit', 'etiqueta' => 'CUIT', 'grupo' => 'Datos del productor', 'alcance' => 'productor'],
        ['tabla' => 'usuarios', 'campo' => 'razon_social', 'etiqueta' => 'Razon social', 'grupo' => 'Datos del productor', 'alcance' => 'productor'],
        ['tabla' => 'productores_contactos_alternos', 'campo' => 'telefono_fijo', 'etiqueta' => 'Telefono fijo', 'grupo' => 'Contactos alternativos', 'alcance' => 'productor'],
        ['tabla' => 'productores_contactos_alternos', 'campo' => 'celular_alternativo', 'etiqueta' => 'Celular alternativo', 'grupo' => 'Contactos alternativos', 'alcance' => 'productor'],
        ['tabla' => 'productores_contactos_alternos', 'campo' => 'mail_alternativo', 'etiqueta' => 'Mail alternativo', 'grupo' => 'Contactos alternativos', 'alcance' => 'productor'],
        ['tabla' => 'info_productor', 'campo' => 'acceso_internet', 'etiqueta' => 'Acceso a internet', 'grupo' => 'Informacion productiva', 'alcance' => 'productor'],
        ['tabla' => 'info_productor', 'campo' => 'vive_en_finca', 'etiqueta' => 'Vive en la finca', 'grupo' => 'Informacion productiva', 'alcance' => 'productor'],
        ['tabla' => 'info_productor', 'campo' => 'tiene_otra_finca', 'etiqueta' => 'Tiene otra finca', 'grupo' => 'Informacion productiva', 'alcance' => 'productor'],
        ['tabla' => 'info_productor', 'campo' => 'condicion_cooperativa', 'etiqueta' => 'Condicion en cooperativa', 'grupo' => 'Informacion productiva', 'alcance' => 'productor'],
        ['tabla' => 'info_productor', 'campo' => 'anio_asociacion', 'etiqueta' => 'Anio de asociacion', 'grupo' => 'Informacion productiva', 'alcance' => 'productor'],
        ['tabla' => 'info_productor', 'campo' => 'actividad_principal', 'etiqueta' => 'Actividad principal', 'grupo' => 'Informacion productiva', 'alcance' => 'productor'],
        ['tabla' => 'info_productor', 'campo' => 'actividad_secundaria', 'etiqueta' => 'Actividad secundaria', 'grupo' => 'Informacion productiva', 'alcance' => 'productor'],
        ['tabla' => 'info_productor', 'campo' => 'porcentaje_aporte_vitivinicola', 'etiqueta' => 'Porcentaje aporte vitivinicola', 'grupo' => 'Informacion productiva', 'alcance' => 'productor'],
        ['tabla' => 'prod_colaboradores', 'campo' => 'hijos_sobrinos_participan', 'etiqueta' => 'Hijos/sobrinos participan', 'grupo' => 'Familia y colaboradores', 'alcance' => 'productor'],
        ['tabla' => 'prod_colaboradores', 'campo' => 'mujeres_tc', 'etiqueta' => 'Mujeres tiempo completo', 'grupo' => 'Familia y colaboradores', 'alcance' => 'productor'],
        ['tabla' => 'prod_colaboradores', 'campo' => 'hombres_tc', 'etiqueta' => 'Hombres tiempo completo', 'grupo' => 'Familia y colaboradores', 'alcance' => 'productor'],
        ['tabla' => 'prod_colaboradores', 'campo' => 'mujeres_tp', 'etiqueta' => 'Mujeres tiempo parcial', 'grupo' => 'Familia y colaboradores', 'alcance' => 'productor'],
        ['tabla' => 'prod_colaboradores', 'campo' => 'hombres_tp', 'etiqueta' => 'Hombres tiempo parcial', 'grupo' => 'Familia y colaboradores', 'alcance' => 'productor'],
        ['tabla' => 'prod_colaboradores', 'campo' => 'prob_hijos_trabajen', 'etiqueta' => 'Probabilidad de que hijos trabajen', 'grupo' => 'Familia y colaboradores', 'alcance' => 'productor'],
        ['tabla' => 'prod_hijos', 'campo' => 'motivo_no_trabajar', 'etiqueta' => 'Motivo de no trabajar', 'grupo' => 'Hijos', 'alcance' => 'productor'],
        ['tabla' => 'prod_hijos', 'campo' => 'rango_etario', 'etiqueta' => 'Rango etario', 'grupo' => 'Hijos', 'alcance' => 'productor'],
        ['tabla' => 'prod_hijos', 'campo' => 'sexo', 'etiqueta' => 'Sexo', 'grupo' => 'Hijos', 'alcance' => 'productor'],
        ['tabla' => 'prod_hijos', 'campo' => 'cantidad', 'etiqueta' => 'Cantidad', 'grupo' => 'Hijos', 'alcance' => 'productor'],
        ['tabla' => 'prod_hijos', 'campo' => 'nivel_estudio', 'etiqueta' => 'Nivel de estudio', 'grupo' => 'Hijos', 'alcance' => 'productor'],
        ['tabla' => 'prod_hijos', 'campo' => 'nom_hijo_1', 'etiqueta' => 'Nombre hijo 1', 'grupo' => 'Hijos', 'alcance' => 'productor'],
        ['tabla' => 'prod_hijos', 'campo' => 'fecha_nacimiento_1', 'etiqueta' => 'Fecha nacimiento hijo 1', 'grupo' => 'Hijos', 'alcance' => 'productor'],
        ['tabla' => 'prod_hijos', 'campo' => 'sexo1', 'etiqueta' => 'Sexo hijo 1', 'grupo' => 'Hijos', 'alcance' => 'productor'],
        ['tabla' => 'prod_hijos', 'campo' => 'nivel_estudio1', 'etiqueta' => 'Nivel estudio hijo 1', 'grupo' => 'Hijos', 'alcance' => 'productor'],
        ['tabla' => 'prod_hijos', 'campo' => 'nom_hijo_2', 'etiqueta' => 'Nombre hijo 2', 'grupo' => 'Hijos', 'alcance' => 'productor'],
        ['tabla' => 'prod_hijos', 'campo' => 'fecha_nacimiento_2', 'etiqueta' => 'Fecha nacimiento hijo 2', 'grupo' => 'Hijos', 'alcance' => 'productor'],
        ['tabla' => 'prod_hijos', 'campo' => 'sexo2', 'etiqueta' => 'Sexo hijo 2', 'grupo' => 'Hijos', 'alcance' => 'productor'],
        ['tabla' => 'prod_hijos', 'campo' => 'nivel_estudio2', 'etiqueta' => 'Nivel estudio hijo 2', 'grupo' => 'Hijos', 'alcance' => 'productor'],
        ['tabla' => 'prod_hijos', 'campo' => 'nom_hijo_3', 'etiqueta' => 'Nombre hijo 3', 'grupo' => 'Hijos', 'alcance' => 'productor'],
        ['tabla' => 'prod_hijos', 'campo' => 'fecha_nacimiento_3', 'etiqueta' => 'Fecha nacimiento hijo 3', 'grupo' => 'Hijos', 'alcance' => 'productor'],
        ['tabla' => 'prod_hijos', 'campo' => 'sexo3', 'etiqueta' => 'Sexo hijo 3', 'grupo' => 'Hijos', 'alcance' => 'productor'],
        ['tabla' => 'prod_hijos', 'campo' => 'nivel_estudio3', 'etiqueta' => 'Nivel estudio hijo 3', 'grupo' => 'Hijos', 'alcance' => 'productor'],
        ['tabla' => 'prod_fincas', 'campo' => 'nombre_finca', 'etiqueta' => 'Nombre finca', 'grupo' => 'Finca', 'alcance' => 'finca'],
        ['tabla' => 'prod_finca_direccion', 'campo' => 'departamento', 'etiqueta' => 'Departamento', 'grupo' => 'Direccion finca', 'alcance' => 'finca'],
        ['tabla' => 'prod_finca_direccion', 'campo' => 'localidad', 'etiqueta' => 'Localidad', 'grupo' => 'Direccion finca', 'alcance' => 'finca'],
        ['tabla' => 'prod_finca_direccion', 'campo' => 'calle', 'etiqueta' => 'Calle', 'grupo' => 'Direccion finca', 'alcance' => 'finca'],
        ['tabla' => 'prod_finca_direccion', 'campo' => 'numero', 'etiqueta' => 'Numero', 'grupo' => 'Direccion finca', 'alcance' => 'finca'],
        ['tabla' => 'prod_finca_direccion', 'campo' => 'latitud', 'etiqueta' => 'Latitud', 'grupo' => 'Direccion finca', 'alcance' => 'finca'],
        ['tabla' => 'prod_finca_direccion', 'campo' => 'longitud', 'etiqueta' => 'Longitud', 'grupo' => 'Direccion finca', 'alcance' => 'finca'],
        ['tabla' => 'prod_finca_superficie', 'campo' => 'sup_total_ha', 'etiqueta' => 'Sup total ha', 'grupo' => 'Superficie', 'alcance' => 'finca'],
        ['tabla' => 'prod_finca_superficie', 'campo' => 'sup_total_cultivada_ha', 'etiqueta' => 'Sup total cultivada ha', 'grupo' => 'Superficie', 'alcance' => 'finca'],
        ['tabla' => 'prod_finca_superficie', 'campo' => 'sup_total_vid_ha', 'etiqueta' => 'Sup total vid ha', 'grupo' => 'Superficie', 'alcance' => 'finca'],
        ['tabla' => 'prod_finca_superficie', 'campo' => 'sup_vid_destinada_coop_ha', 'etiqueta' => 'Sup vid destinada coop ha', 'grupo' => 'Superficie', 'alcance' => 'finca'],
        ['tabla' => 'prod_finca_superficie', 'campo' => 'sup_con_otros_cultivos_ha', 'etiqueta' => 'Sup con otros cultivos ha', 'grupo' => 'Superficie', 'alcance' => 'finca'],
        ['tabla' => 'prod_finca_superficie', 'campo' => 'clasificacion_riesgo_salinizacion', 'etiqueta' => 'Riesgo salinizacion', 'grupo' => 'Superficie', 'alcance' => 'finca'],
        ['tabla' => 'prod_finca_superficie', 'campo' => 'analisis_suelo_completo', 'etiqueta' => 'Analisis suelo completo', 'grupo' => 'Superficie', 'alcance' => 'finca'],
        ['tabla' => 'prod_finca_cultivos', 'campo' => 'sup_cultivo_horticola_ha', 'etiqueta' => 'Sup cultivo horticola ha', 'grupo' => 'Cultivos', 'alcance' => 'finca'],
        ['tabla' => 'prod_finca_cultivos', 'campo' => 'estado_cultivo_horticola', 'etiqueta' => 'Estado cultivo horticola', 'grupo' => 'Cultivos', 'alcance' => 'finca'],
        ['tabla' => 'prod_finca_cultivos', 'campo' => 'sup_cultivo_fruticola_ha', 'etiqueta' => 'Sup cultivo fruticola ha', 'grupo' => 'Cultivos', 'alcance' => 'finca'],
        ['tabla' => 'prod_finca_cultivos', 'campo' => 'estado_cultivo_fruticola', 'etiqueta' => 'Estado cultivo fruticola', 'grupo' => 'Cultivos', 'alcance' => 'finca'],
        ['tabla' => 'prod_finca_cultivos', 'campo' => 'sup_cultivo_forestal_otra_ha', 'etiqueta' => 'Sup forestal/otra ha', 'grupo' => 'Cultivos', 'alcance' => 'finca'],
        ['tabla' => 'prod_finca_cultivos', 'campo' => 'estado_cultivo_forestal_otra', 'etiqueta' => 'Estado forestal/otra', 'grupo' => 'Cultivos', 'alcance' => 'finca'],
        ['tabla' => 'prod_finca_agua', 'campo' => 'sup_agua_con_derecho_ha', 'etiqueta' => 'Sup agua con derecho ha', 'grupo' => 'Agua', 'alcance' => 'finca'],
        ['tabla' => 'prod_finca_agua', 'campo' => 'tipo_riego', 'etiqueta' => 'Tipo de riego', 'grupo' => 'Agua', 'alcance' => 'finca'],
        ['tabla' => 'prod_finca_agua', 'campo' => 'sup_agua_sin_derecho_ha', 'etiqueta' => 'Sup agua sin derecho ha', 'grupo' => 'Agua', 'alcance' => 'finca'],
        ['tabla' => 'prod_finca_agua', 'campo' => 'estado_provision_agua', 'etiqueta' => 'Estado provision agua', 'grupo' => 'Agua', 'alcance' => 'finca'],
        ['tabla' => 'prod_finca_agua', 'campo' => 'estado_asignacion_turnado', 'etiqueta' => 'Estado asignacion/turnado', 'grupo' => 'Agua', 'alcance' => 'finca'],
        ['tabla' => 'prod_finca_agua', 'campo' => 'estado_sistematizacion_vinedo', 'etiqueta' => 'Estado sistematizacion vinedo', 'grupo' => 'Agua', 'alcance' => 'finca'],
        ['tabla' => 'prod_finca_agua', 'campo' => 'tiene_flexibilizacion_entrega_agua', 'etiqueta' => 'Flexibilizacion entrega agua', 'grupo' => 'Agua', 'alcance' => 'finca'],
        ['tabla' => 'prod_finca_agua', 'campo' => 'riego_presurizado_toma_agua_de', 'etiqueta' => 'Riego presurizado toma agua de', 'grupo' => 'Agua', 'alcance' => 'finca'],
        ['tabla' => 'prod_finca_agua', 'campo' => 'perforacion_activa_1', 'etiqueta' => 'Perforacion activa 1', 'grupo' => 'Agua', 'alcance' => 'finca'],
        ['tabla' => 'prod_finca_agua', 'campo' => 'perforacion_activa_2', 'etiqueta' => 'Perforacion activa 2', 'grupo' => 'Agua', 'alcance' => 'finca'],
        ['tabla' => 'prod_finca_agua', 'campo' => 'agua_analizada', 'etiqueta' => 'Agua analizada', 'grupo' => 'Agua', 'alcance' => 'finca'],
        ['tabla' => 'prod_finca_agua', 'campo' => 'conductividad_mhos_cm', 'etiqueta' => 'Conductividad', 'grupo' => 'Agua', 'alcance' => 'finca'],
        ['tabla' => 'prod_finca_maquinaria', 'campo' => 'clasificacion_estado_tractor', 'etiqueta' => 'Estado tractor', 'grupo' => 'Maquinaria', 'alcance' => 'finca'],
        ['tabla' => 'prod_finca_maquinaria', 'campo' => 'estado_pulverizadora', 'etiqueta' => 'Estado pulverizadora', 'grupo' => 'Maquinaria', 'alcance' => 'finca'],
        ['tabla' => 'prod_finca_maquinaria', 'campo' => 'clasificacion_estado_implementos', 'etiqueta' => 'Estado implementos', 'grupo' => 'Maquinaria', 'alcance' => 'finca'],
        ['tabla' => 'prod_finca_maquinaria', 'campo' => 'utiliza_empresa_servicios', 'etiqueta' => 'Utiliza empresa servicios', 'grupo' => 'Maquinaria', 'alcance' => 'finca'],
        ['tabla' => 'prod_finca_maquinaria', 'campo' => 'administracion', 'etiqueta' => 'Administracion', 'grupo' => 'Maquinaria', 'alcance' => 'finca'],
        ['tabla' => 'prod_finca_maquinaria', 'campo' => 'trabajadores_permanentes', 'etiqueta' => 'Trabajadores permanentes', 'grupo' => 'Maquinaria', 'alcance' => 'finca'],
        ['tabla' => 'prod_finca_maquinaria', 'campo' => 'posee_deposito_fitosanitarios', 'etiqueta' => 'Deposito fitosanitarios', 'grupo' => 'Maquinaria', 'alcance' => 'finca'],
        ['tabla' => 'prod_finca_gerencia', 'campo' => 'problemas_gerencia', 'etiqueta' => 'Problemas de gerencia', 'grupo' => 'Gerencia', 'alcance' => 'finca'],
        ['tabla' => 'prod_finca_gerencia', 'campo' => 'prob_gerenciamiento_1', 'etiqueta' => 'Prob gerenciamiento 1', 'grupo' => 'Gerencia', 'alcance' => 'finca'],
        ['tabla' => 'prod_finca_gerencia', 'campo' => 'prob_personal_1', 'etiqueta' => 'Prob personal 1', 'grupo' => 'Gerencia', 'alcance' => 'finca'],
        ['tabla' => 'prod_finca_gerencia', 'campo' => 'prob_tecnologicos_1', 'etiqueta' => 'Prob tecnologicos 1', 'grupo' => 'Gerencia', 'alcance' => 'finca'],
        ['tabla' => 'prod_finca_gerencia', 'campo' => 'prob_administracion_1', 'etiqueta' => 'Prob administracion 1', 'grupo' => 'Gerencia', 'alcance' => 'finca'],
        ['tabla' => 'prod_finca_gerencia', 'campo' => 'prob_medios_produccion_1', 'etiqueta' => 'Prob medios produccion 1', 'grupo' => 'Gerencia', 'alcance' => 'finca'],
        ['tabla' => 'prod_finca_gerencia', 'campo' => 'prob_observacion_1', 'etiqueta' => 'Prob observacion 1', 'grupo' => 'Gerencia', 'alcance' => 'finca'],
        ['tabla' => 'prod_finca_gerencia', 'campo' => 'prob_gerenciamiento_2', 'etiqueta' => 'Prob gerenciamiento 2', 'grupo' => 'Gerencia', 'alcance' => 'finca'],
        ['tabla' => 'prod_finca_gerencia', 'campo' => 'prob_personal_2', 'etiqueta' => 'Prob personal 2', 'grupo' => 'Gerencia', 'alcance' => 'finca'],
        ['tabla' => 'prod_finca_gerencia', 'campo' => 'prob_tecnologicos_2', 'etiqueta' => 'Prob tecnologicos 2', 'grupo' => 'Gerencia', 'alcance' => 'finca'],
        ['tabla' => 'prod_finca_gerencia', 'campo' => 'prob_administracion_2', 'etiqueta' => 'Prob administracion 2', 'grupo' => 'Gerencia', 'alcance' => 'finca'],
        ['tabla' => 'prod_finca_gerencia', 'campo' => 'prob_medios_produccion_2', 'etiqueta' => 'Prob medios produccion 2', 'grupo' => 'Gerencia', 'alcance' => 'finca'],
        ['tabla' => 'prod_finca_gerencia', 'campo' => 'prob_observacion_2', 'etiqueta' => 'Prob observacion 2', 'grupo' => 'Gerencia', 'alcance' => 'finca'],
        ['tabla' => 'prod_finca_gerencia', 'campo' => 'limitante_1', 'etiqueta' => 'Limitante 1', 'grupo' => 'Gerencia', 'alcance' => 'finca'],
        ['tabla' => 'prod_finca_gerencia', 'campo' => 'limitante_2', 'etiqueta' => 'Limitante 2', 'grupo' => 'Gerencia', 'alcance' => 'finca'],
        ['tabla' => 'prod_finca_gerencia', 'campo' => 'limitante_3', 'etiqueta' => 'Limitante 3', 'grupo' => 'Gerencia', 'alcance' => 'finca'],
        ['tabla' => 'prod_cuartel', 'campo' => 'codigo_cuartel', 'etiqueta' => 'Codigo cuartel', 'grupo' => 'Cuartel', 'alcance' => 'cuartel'],
        ['tabla' => 'prod_cuartel', 'campo' => 'variedad', 'etiqueta' => 'Variedad', 'grupo' => 'Cuartel', 'alcance' => 'cuartel'],
        ['tabla' => 'prod_cuartel', 'campo' => 'numero_inv', 'etiqueta' => 'Numero INV', 'grupo' => 'Cuartel', 'alcance' => 'cuartel'],
        ['tabla' => 'prod_cuartel', 'campo' => 'sistema_conduccion', 'etiqueta' => 'Sistema conduccion', 'grupo' => 'Cuartel', 'alcance' => 'cuartel'],
        ['tabla' => 'prod_cuartel', 'campo' => 'superficie_ha', 'etiqueta' => 'Superficie ha', 'grupo' => 'Cuartel', 'alcance' => 'cuartel'],
        ['tabla' => 'prod_cuartel', 'campo' => 'porcentaje_cepas_produccion', 'etiqueta' => 'Porcentaje cepas produccion', 'grupo' => 'Cuartel', 'alcance' => 'cuartel'],
        ['tabla' => 'prod_cuartel', 'campo' => 'forma_cosecha_actual', 'etiqueta' => 'Forma cosecha actual', 'grupo' => 'Cuartel', 'alcance' => 'cuartel'],
        ['tabla' => 'prod_cuartel', 'campo' => 'porcentaje_malla_buen_estado', 'etiqueta' => 'Porcentaje malla buen estado', 'grupo' => 'Cuartel', 'alcance' => 'cuartel'],
        ['tabla' => 'prod_cuartel', 'campo' => 'edad_promedio_encepado_anios', 'etiqueta' => 'Edad promedio encepado', 'grupo' => 'Cuartel', 'alcance' => 'cuartel'],
        ['tabla' => 'prod_cuartel', 'campo' => 'estado_estructura_sistema', 'etiqueta' => 'Estado estructura sistema', 'grupo' => 'Cuartel', 'alcance' => 'cuartel'],
        ['tabla' => 'prod_cuartel', 'campo' => 'labores_mecanizables', 'etiqueta' => 'Labores mecanizables', 'grupo' => 'Cuartel', 'alcance' => 'cuartel'],
        ['tabla' => 'prod_cuartel_limitantes', 'campo' => 'limitantes_suelo', 'etiqueta' => 'Limitantes suelo', 'grupo' => 'Limitantes cuartel', 'alcance' => 'cuartel'],
        ['tabla' => 'prod_cuartel_limitantes', 'campo' => 'observaciones', 'etiqueta' => 'Observaciones limitantes', 'grupo' => 'Limitantes cuartel', 'alcance' => 'cuartel'],
        ['tabla' => 'prod_cuartel_limitantes', 'campo' => 'categoria_1', 'etiqueta' => 'Categoria 1', 'grupo' => 'Limitantes cuartel', 'alcance' => 'cuartel'],
        ['tabla' => 'prod_cuartel_limitantes', 'campo' => 'limitante_1', 'etiqueta' => 'Limitante 1', 'grupo' => 'Limitantes cuartel', 'alcance' => 'cuartel'],
        ['tabla' => 'prod_cuartel_limitantes', 'campo' => 'inversion_accion1_1', 'etiqueta' => 'Inversion accion 1.1', 'grupo' => 'Limitantes cuartel', 'alcance' => 'cuartel'],
        ['tabla' => 'prod_cuartel_limitantes', 'campo' => 'ciclo_agricola1_1', 'etiqueta' => 'Ciclo agricola 1.1', 'grupo' => 'Limitantes cuartel', 'alcance' => 'cuartel'],
        ['tabla' => 'prod_cuartel_limitantes', 'campo' => 'categoria_2', 'etiqueta' => 'Categoria 2', 'grupo' => 'Limitantes cuartel', 'alcance' => 'cuartel'],
        ['tabla' => 'prod_cuartel_limitantes', 'campo' => 'limitante_2', 'etiqueta' => 'Limitante 2', 'grupo' => 'Limitantes cuartel', 'alcance' => 'cuartel'],
        ['tabla' => 'prod_cuartel_rendimientos', 'campo' => 'rend_2020_qq_ha', 'etiqueta' => 'Rend 2020 qq/ha', 'grupo' => 'Rendimientos cuartel', 'alcance' => 'cuartel'],
        ['tabla' => 'prod_cuartel_rendimientos', 'campo' => 'rend_2021_qq_ha', 'etiqueta' => 'Rend 2021 qq/ha', 'grupo' => 'Rendimientos cuartel', 'alcance' => 'cuartel'],
        ['tabla' => 'prod_cuartel_rendimientos', 'campo' => 'rend_2022_qq_ha', 'etiqueta' => 'Rend 2022 qq/ha', 'grupo' => 'Rendimientos cuartel', 'alcance' => 'cuartel'],
        ['tabla' => 'prod_cuartel_rendimientos', 'campo' => 'ing_2023_kg', 'etiqueta' => 'Ingreso 2023 kg', 'grupo' => 'Rendimientos cuartel', 'alcance' => 'cuartel'],
        ['tabla' => 'prod_cuartel_rendimientos', 'campo' => 'rend_2023_qq_ha', 'etiqueta' => 'Rend 2023 qq/ha', 'grupo' => 'Rendimientos cuartel', 'alcance' => 'cuartel'],
        ['tabla' => 'prod_cuartel_rendimientos', 'campo' => 'ing_2024_kg', 'etiqueta' => 'Ingreso 2024 kg', 'grupo' => 'Rendimientos cuartel', 'alcance' => 'cuartel'],
        ['tabla' => 'prod_cuartel_rendimientos', 'campo' => 'rend_2024_qq_ha', 'etiqueta' => 'Rend 2024 qq/ha', 'grupo' => 'Rendimientos cuartel', 'alcance' => 'cuartel'],
        ['tabla' => 'prod_cuartel_rendimientos', 'campo' => 'ing_2025_kg', 'etiqueta' => 'Ingreso 2025 kg', 'grupo' => 'Rendimientos cuartel', 'alcance' => 'cuartel'],
        ['tabla' => 'prod_cuartel_rendimientos', 'campo' => 'rend_2025_qq_ha', 'etiqueta' => 'Rend 2025 qq/ha', 'grupo' => 'Rendimientos cuartel', 'alcance' => 'cuartel'],
        ['tabla' => 'prod_cuartel_rendimientos', 'campo' => 'rend_promedio_5anios_qq_ha', 'etiqueta' => 'Rend promedio 5 anios qq/ha', 'grupo' => 'Rendimientos cuartel', 'alcance' => 'cuartel'],
        ['tabla' => 'prod_cuartel_riesgos', 'campo' => 'tiene_seguro_agricola', 'etiqueta' => 'Tiene seguro agricola', 'grupo' => 'Riesgos cuartel', 'alcance' => 'cuartel'],
        ['tabla' => 'prod_cuartel_riesgos', 'campo' => 'porcentaje_dano_granizo', 'etiqueta' => 'Porcentaje dano granizo', 'grupo' => 'Riesgos cuartel', 'alcance' => 'cuartel'],
        ['tabla' => 'prod_cuartel_riesgos', 'campo' => 'heladas_dano_promedio_5anios', 'etiqueta' => 'Dano heladas promedio 5 anios', 'grupo' => 'Riesgos cuartel', 'alcance' => 'cuartel'],
        ['tabla' => 'prod_cuartel_riesgos', 'campo' => 'presencia_freatica', 'etiqueta' => 'Presencia freatica', 'grupo' => 'Riesgos cuartel', 'alcance' => 'cuartel'],
        ['tabla' => 'prod_cuartel_riesgos', 'campo' => 'plagas_no_convencionales', 'etiqueta' => 'Plagas no convencionales', 'grupo' => 'Riesgos cuartel', 'alcance' => 'cuartel'],
    ];

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function obtenerResumenCooperativas(string $q = ''): array
    {
        $whereSql = '';
        $params = [];

        if ($q !== '') {
            $whereSql = "
                WHERE (
                    COALESCE(NULLIF(TRIM(cui.nombre), ''), NULLIF(TRIM(cu.razon_social), ''), NULLIF(TRIM(cu.usuario), ''), rpc.cooperativa_id_real) LIKE :q
                    OR CAST(COALESCE(cu.cuit, '') AS CHAR) LIKE :q
                    OR rpc.cooperativa_id_real LIKE :q
                )
            ";
            $params[':q'] = '%' . $q . '%';
        }

        $sql = "
            SELECT
                rpc.cooperativa_id_real,
                COALESCE(
                    NULLIF(TRIM(cui.nombre), ''),
                    NULLIF(TRIM(cu.razon_social), ''),
                    NULLIF(TRIM(cu.usuario), ''),
                    rpc.cooperativa_id_real
                ) AS cooperativa_nombre,
                COUNT(DISTINCT rpc.productor_id_real) AS productores_total,
                SUM(CASE WHEN COALESCE(pf_stats.fincas_count, 0) > 0 THEN 1 ELSE 0 END) AS productores_con_fincas,
                SUM(CASE WHEN COALESCE(pc_stats.cuarteles_count, 0) > 0 THEN 1 ELSE 0 END) AS productores_con_cuarteles
            FROM rel_productor_coop rpc
            LEFT JOIN usuarios cu
                ON cu.id_real = rpc.cooperativa_id_real
               AND cu.rol = 'cooperativa'
            LEFT JOIN usuarios_info cui
                ON cui.usuario_id = cu.id
            LEFT JOIN (
                SELECT
                    pf.productor_id_real,
                    COUNT(*) AS fincas_count
                FROM prod_fincas pf
                GROUP BY pf.productor_id_real
            ) pf_stats
                ON pf_stats.productor_id_real = rpc.productor_id_real
            LEFT JOIN (
                SELECT
                    z.productor_id_real,
                    COUNT(DISTINCT z.cuartel_id) AS cuarteles_count
                FROM (
                    SELECT
                        pf.productor_id_real,
                        pc.id AS cuartel_id
                    FROM prod_cuartel pc
                    INNER JOIN prod_fincas pf
                        ON pf.id = pc.finca_id

                    UNION

                    SELECT
                        pc.id_responsable_real AS productor_id_real,
                        pc.id AS cuartel_id
                    FROM prod_cuartel pc
                    WHERE pc.id_responsable_real IS NOT NULL
                      AND pc.id_responsable_real <> ''
                ) z
                GROUP BY z.productor_id_real
            ) pc_stats
                ON pc_stats.productor_id_real = rpc.productor_id_real
            {$whereSql}
            GROUP BY rpc.cooperativa_id_real, cooperativa_nombre
            ORDER BY cooperativa_nombre ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll() ?: [];
    }

    /**
     * @return array{rows: array<int, array<string, mixed>>, total: int}
     */
    public function obtenerListadoProductores(string $coopIdReal, string $q, int $page, int $perPage): array
    {
        $where = [];
        $params = [];

        if ($coopIdReal !== '') {
            $where[] = 'rpc.cooperativa_id_real = :coop_id_real';
            $params[':coop_id_real'] = $coopIdReal;
        }

        if ($q !== '') {
            $where[] = "(
                COALESCE(NULLIF(TRIM(pui.nombre), ''), NULLIF(TRIM(pu.razon_social), ''), NULLIF(TRIM(pu.usuario), ''), rpc.productor_id_real) LIKE :q
                OR CAST(COALESCE(pu.cuit, '') AS CHAR) LIKE :q
                OR rpc.productor_id_real LIKE :q
                OR COALESCE(NULLIF(TRIM(cui.nombre), ''), NULLIF(TRIM(cu.razon_social), ''), NULLIF(TRIM(cu.usuario), ''), rpc.cooperativa_id_real) LIKE :q
            )";
            $params[':q'] = '%' . $q . '%';
        }

        $whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
        $offset = max(0, ($page - 1) * $perPage);

        $sqlCount = "
            SELECT COUNT(*)
            FROM rel_productor_coop rpc
            LEFT JOIN usuarios pu
                ON pu.id_real = rpc.productor_id_real
               AND pu.rol = 'productor'
            LEFT JOIN usuarios_info pui
                ON pui.usuario_id = pu.id
            LEFT JOIN usuarios cu
                ON cu.id_real = rpc.cooperativa_id_real
               AND cu.rol = 'cooperativa'
            LEFT JOIN usuarios_info cui
                ON cui.usuario_id = cu.id
            {$whereSql}
        ";

        $stmtCount = $this->pdo->prepare($sqlCount);
        foreach ($params as $key => $value) {
            $stmtCount->bindValue($key, $value, PDO::PARAM_STR);
        }
        $stmtCount->execute();
        $total = (int) ($stmtCount->fetchColumn() ?: 0);

        $sql = "
            SELECT
                rpc.id AS relacion_id,
                rpc.cooperativa_id_real,
                COALESCE(
                    NULLIF(TRIM(cui.nombre), ''),
                    NULLIF(TRIM(cu.razon_social), ''),
                    NULLIF(TRIM(cu.usuario), ''),
                    rpc.cooperativa_id_real
                ) AS cooperativa_nombre,
                rpc.productor_id_real,
                COALESCE(
                    NULLIF(TRIM(pui.nombre), ''),
                    NULLIF(TRIM(pu.razon_social), ''),
                    NULLIF(TRIM(pu.usuario), ''),
                    rpc.productor_id_real
                ) AS productor_nombre,
                NULLIF(NULLIF(TRIM(CAST(pu.cuit AS CHAR)), ''), '0') AS productor_cuit,
                COALESCE(pf_stats.fincas_count, 0) AS fincas_count,
                COALESCE(pc_stats.cuarteles_count, 0) AS cuarteles_count
            FROM rel_productor_coop rpc
            LEFT JOIN usuarios pu
                ON pu.id_real = rpc.productor_id_real
               AND pu.rol = 'productor'
            LEFT JOIN usuarios_info pui
                ON pui.usuario_id = pu.id
            LEFT JOIN usuarios cu
                ON cu.id_real = rpc.cooperativa_id_real
               AND cu.rol = 'cooperativa'
            LEFT JOIN usuarios_info cui
                ON cui.usuario_id = cu.id
            LEFT JOIN (
                SELECT
                    pf.productor_id_real,
                    COUNT(*) AS fincas_count
                FROM prod_fincas pf
                GROUP BY pf.productor_id_real
            ) pf_stats
                ON pf_stats.productor_id_real = rpc.productor_id_real
            LEFT JOIN (
                SELECT
                    z.productor_id_real,
                    COUNT(DISTINCT z.cuartel_id) AS cuarteles_count
                FROM (
                    SELECT
                        pf.productor_id_real,
                        pc.id AS cuartel_id
                    FROM prod_cuartel pc
                    INNER JOIN prod_fincas pf
                        ON pf.id = pc.finca_id

                    UNION

                    SELECT
                        pc.id_responsable_real AS productor_id_real,
                        pc.id AS cuartel_id
                    FROM prod_cuartel pc
                    WHERE pc.id_responsable_real IS NOT NULL
                      AND pc.id_responsable_real <> ''
                ) z
                GROUP BY z.productor_id_real
            ) pc_stats
                ON pc_stats.productor_id_real = rpc.productor_id_real
            {$whereSql}
            ORDER BY cooperativa_nombre ASC, productor_nombre ASC
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'rows' => $stmt->fetchAll() ?: [],
            'total' => $total,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listarCodigosVariedades(string $q = ''): array
    {
        $where = '';
        $params = [];

        if ($q !== '') {
            $where = "WHERE CAST(codigo_variedad AS CHAR) LIKE :q OR nombre_variedad LIKE :q";
            $params[':q'] = '%' . $q . '%';
        }

        $sql = "
            SELECT
                id,
                codigo_variedad,
                nombre_variedad,
                created_at,
                updated_at
            FROM codigo_variedades_fincas
            {$where}
            ORDER BY codigo_variedad ASC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll() ?: [];
    }

    /**
     * @return array<string, mixed>
     */
    public function crearCodigoVariedad(int $codigoVariedad, string $nombreVariedad): array
    {
        $sql = "
            INSERT INTO codigo_variedades_fincas (codigo_variedad, nombre_variedad)
            VALUES (:codigo_variedad, :nombre_variedad)
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':codigo_variedad' => $codigoVariedad,
            ':nombre_variedad' => $nombreVariedad,
        ]);

        return $this->obtenerCodigoVariedadPorId((int) $this->pdo->lastInsertId());
    }

    /**
     * @return array<string, mixed>
     */
    public function actualizarCodigoVariedad(int $id, int $codigoVariedad, string $nombreVariedad): array
    {
        $sql = "
            UPDATE codigo_variedades_fincas
            SET
                codigo_variedad = :codigo_variedad,
                nombre_variedad = :nombre_variedad
            WHERE id = :id
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':id' => $id,
            ':codigo_variedad' => $codigoVariedad,
            ':nombre_variedad' => $nombreVariedad,
        ]);

        return $this->obtenerCodigoVariedadPorId($id);
    }

    public function eliminarCodigoVariedad(int $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM codigo_variedades_fincas WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listarCamposOperativoRelevamiento(): array
    {
        return array_map(static function (array $field): array {
            $field['key'] = $field['tabla'] . '.' . $field['campo'];
            return $field;
        }, self::RELEVAMIENTO_CAMPOS);
    }

    /**
     * @param array<int, string> $fieldKeys
     * @return array<string, mixed>
     */
    public function crearOperativoRelevamiento(
        string $nombre,
        string $fechaInicio,
        string $fechaFin,
        string $estado,
        array $fieldKeys,
        ?string $createdByReal
    ): array {
        $nombre = trim($nombre);
        $fechaInicio = trim($fechaInicio);
        $fechaFin = trim($fechaFin);
        $estado = in_array($estado, ['borrador', 'abierto', 'cerrado'], true) ? $estado : 'borrador';

        if ($nombre === '') {
            throw new InvalidArgumentException('El nombre del operativo es obligatorio');
        }
        if (!$this->isValidDate($fechaInicio) || !$this->isValidDate($fechaFin)) {
            throw new InvalidArgumentException('Las fechas del operativo son obligatorias');
        }
        if ($fechaFin < $fechaInicio) {
            throw new InvalidArgumentException('La fecha de finalizacion no puede ser anterior a la fecha de inicio');
        }

        $catalogByKey = [];
        foreach ($this->listarCamposOperativoRelevamiento() as $field) {
            $catalogByKey[$field['key']] = $field;
        }

        $fieldKeys = array_values(array_unique(array_filter(array_map('strval', $fieldKeys))));
        if (empty($fieldKeys)) {
            throw new InvalidArgumentException('Selecciona al menos un campo para el operativo');
        }

        $selectedFields = [];
        foreach ($fieldKeys as $key) {
            if (isset($catalogByKey[$key])) {
                $selectedFields[] = $catalogByKey[$key];
            }
        }

        if (empty($selectedFields)) {
            throw new InvalidArgumentException('Los campos seleccionados no son validos');
        }

        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO relevamiento_operativos
                    (nombre, fecha_inicio, fecha_fin, estado, created_by_real)
                VALUES
                    (:nombre, :fecha_inicio, :fecha_fin, :estado, :created_by_real)
            ");
            $stmt->execute([
                ':nombre' => $nombre,
                ':fecha_inicio' => $fechaInicio,
                ':fecha_fin' => $fechaFin,
                ':estado' => $estado,
                ':created_by_real' => $createdByReal,
            ]);

            $operativoId = (int)$this->pdo->lastInsertId();
            $insertCampo = $this->pdo->prepare("
                INSERT INTO relevamiento_operativo_campos
                    (operativo_id, tabla, campo, etiqueta, grupo, alcance, obligatorio, orden)
                VALUES
                    (:operativo_id, :tabla, :campo, :etiqueta, :grupo, :alcance, :obligatorio, :orden)
            ");

            foreach ($selectedFields as $index => $field) {
                $insertCampo->execute([
                    ':operativo_id' => $operativoId,
                    ':tabla' => $field['tabla'],
                    ':campo' => $field['campo'],
                    ':etiqueta' => $field['etiqueta'],
                    ':grupo' => $field['grupo'],
                    ':alcance' => $field['alcance'],
                    ':obligatorio' => 0,
                    ':orden' => $index + 1,
                ]);
            }

            $this->pdo->commit();

            return [
                'id' => $operativoId,
                'nombre' => $nombre,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'estado' => $estado,
                'campos_count' => count($selectedFields),
            ];
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listarOperativosRelevamiento(): array
    {
        $stmt = $this->pdo->query("
            SELECT
                ro.id,
                ro.nombre,
                ro.fecha_inicio,
                ro.fecha_fin,
                ro.estado,
                COUNT(roc.id) AS campos_count
            FROM relevamiento_operativos ro
            LEFT JOIN relevamiento_operativo_campos roc
                ON roc.operativo_id = ro.id
            GROUP BY ro.id, ro.nombre, ro.fecha_inicio, ro.fecha_fin, ro.estado
            ORDER BY ro.fecha_inicio DESC, ro.id DESC
        ");

        return $stmt->fetchAll() ?: [];
    }

    /**
     * @return array<string, mixed>
     */
    public function obtenerOperativoRelevamiento(int $id): array
    {
        $stmt = $this->pdo->prepare("
            SELECT id, nombre, fecha_inicio, fecha_fin, estado
            FROM relevamiento_operativos
            WHERE id = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => $id]);
        $operativo = $stmt->fetch();

        if (!$operativo) {
            throw new InvalidArgumentException('Operativo no encontrado');
        }

        $camposStmt = $this->pdo->prepare("
            SELECT tabla, campo, etiqueta, grupo, alcance, obligatorio, orden
            FROM relevamiento_operativo_campos
            WHERE operativo_id = :id
            ORDER BY orden ASC, id ASC
        ");
        $camposStmt->execute([':id' => $id]);
        $campos = $camposStmt->fetchAll() ?: [];

        foreach ($campos as &$campo) {
            $campo['key'] = $campo['tabla'] . '.' . $campo['campo'];
        }
        unset($campo);

        $operativo['campos'] = $campos;
        return $operativo;
    }

    /**
     * @param array<int, string> $fieldKeys
     * @return array<string, mixed>
     */
    public function actualizarOperativoRelevamiento(
        int $id,
        string $nombre,
        string $fechaInicio,
        string $fechaFin,
        string $estado,
        array $fieldKeys
    ): array {
        if ($id <= 0) {
            throw new InvalidArgumentException('ID de operativo invalido');
        }

        $this->obtenerOperativoRelevamiento($id);

        $nombre = trim($nombre);
        $fechaInicio = trim($fechaInicio);
        $fechaFin = trim($fechaFin);
        $estado = in_array($estado, ['borrador', 'abierto', 'cerrado'], true) ? $estado : 'borrador';

        if ($nombre === '') {
            throw new InvalidArgumentException('El nombre del operativo es obligatorio');
        }
        if (!$this->isValidDate($fechaInicio) || !$this->isValidDate($fechaFin)) {
            throw new InvalidArgumentException('Las fechas del operativo son obligatorias');
        }
        if ($fechaFin < $fechaInicio) {
            throw new InvalidArgumentException('La fecha de finalizacion no puede ser anterior a la fecha de inicio');
        }

        $selectedFields = $this->resolverCamposSeleccionados($fieldKeys);

        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("
                UPDATE relevamiento_operativos
                SET nombre = :nombre,
                    fecha_inicio = :fecha_inicio,
                    fecha_fin = :fecha_fin,
                    estado = :estado
                WHERE id = :id
            ");
            $stmt->execute([
                ':id' => $id,
                ':nombre' => $nombre,
                ':fecha_inicio' => $fechaInicio,
                ':fecha_fin' => $fechaFin,
                ':estado' => $estado,
            ]);

            $delete = $this->pdo->prepare("DELETE FROM relevamiento_operativo_campos WHERE operativo_id = :id");
            $delete->execute([':id' => $id]);

            $insertCampo = $this->pdo->prepare("
                INSERT INTO relevamiento_operativo_campos
                    (operativo_id, tabla, campo, etiqueta, grupo, alcance, obligatorio, orden)
                VALUES
                    (:operativo_id, :tabla, :campo, :etiqueta, :grupo, :alcance, :obligatorio, :orden)
            ");

            foreach ($selectedFields as $index => $field) {
                $insertCampo->execute([
                    ':operativo_id' => $id,
                    ':tabla' => $field['tabla'],
                    ':campo' => $field['campo'],
                    ':etiqueta' => $field['etiqueta'],
                    ':grupo' => $field['grupo'],
                    ':alcance' => $field['alcance'],
                    ':obligatorio' => 0,
                    ':orden' => $index + 1,
                ]);
            }

            $this->pdo->commit();

            return [
                'id' => $id,
                'nombre' => $nombre,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'estado' => $estado,
                'campos_count' => count($selectedFields),
            ];
        } catch (Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function actualizarEstadoOperativoRelevamiento(int $id, string $estado): void
    {
        if ($id <= 0) {
            throw new InvalidArgumentException('ID de operativo invalido');
        }
        if (!in_array($estado, ['borrador', 'abierto', 'cerrado'], true)) {
            throw new InvalidArgumentException('Estado invalido');
        }

        $stmt = $this->pdo->prepare("
            UPDATE relevamiento_operativos
            SET estado = :estado
            WHERE id = :id
        ");
        $stmt->execute([':id' => $id, ':estado' => $estado]);

        if ($stmt->rowCount() === 0) {
            $this->obtenerOperativoRelevamiento($id);
        }
    }

    public function eliminarOperativoRelevamiento(int $id): void
    {
        if ($id <= 0) {
            throw new InvalidArgumentException('ID de operativo invalido');
        }

        $stmt = $this->pdo->prepare("DELETE FROM relevamiento_operativos WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }

    /**
     * @param array<int, string> $fieldKeys
     * @return array<int, array<string, mixed>>
     */
    private function resolverCamposSeleccionados(array $fieldKeys): array
    {
        $catalogByKey = [];
        foreach ($this->listarCamposOperativoRelevamiento() as $field) {
            $catalogByKey[$field['key']] = $field;
        }

        $fieldKeys = array_values(array_unique(array_filter(array_map('strval', $fieldKeys))));
        if (empty($fieldKeys)) {
            throw new InvalidArgumentException('Selecciona al menos un campo para el operativo');
        }

        $selectedFields = [];
        foreach ($fieldKeys as $key) {
            if (isset($catalogByKey[$key])) {
                $selectedFields[] = $catalogByKey[$key];
            }
        }

        if (empty($selectedFields)) {
            throw new InvalidArgumentException('Los campos seleccionados no son validos');
        }

        return $selectedFields;
    }

    private function isValidDate(string $value): bool
    {
        $dt = DateTime::createFromFormat('Y-m-d', $value);
        return $dt instanceof DateTime && $dt->format('Y-m-d') === $value;
    }

    /**
     * @return array<string, mixed>
     */
    private function obtenerCodigoVariedadPorId(int $id): array
    {
        $stmt = $this->pdo->prepare("
            SELECT id, codigo_variedad, nombre_variedad, created_at, updated_at
            FROM codigo_variedades_fincas
            WHERE id = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return is_array($row) ? $row : [];
    }
}
