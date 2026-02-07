📄 Tabla: CosechaMecanica
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(10) unsigned	NO	PRI		auto_increment
nombre	varchar(150)	NO			
fecha_apertura	date	NO	MUL		
fecha_cierre	date	NO			
descripcion	text	YES			
estado	enum('borrador','abierto','cerrado')	NO	MUL	borrador	
costo_base	decimal(10,2)	NO		0.00	
bon_optima	decimal(5,2)	NO		0.00	
bon_muy_buena	decimal(5,2)	NO		0.00	
bon_buena	decimal(5,2)	NO		0.00	
anticipo	decimal(10,2)	NO		0.00	
created_at	timestamp	NO		current_timestamp()	
updated_at	timestamp	YES		current_timestamp()	on update current_timestamp()

🔁 Relaciones (entrantes): cosechaMecanica_coop_contrato_firma, cosechaMecanica_cooperativas_participacion
📄 Tabla: categorias_publicaciones
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(11)	NO	PRI		auto_increment
nombre	varchar(100)	NO			

🔁 Relaciones (entrantes): publicaciones, subcategorias_publicaciones
📄 Tabla: cooperativas_rangos
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(11)	NO	PRI		auto_increment
cooperativa_id_real	varchar(11)	NO			
nombre_cooperativa	varchar(100)	NO			
rango_productores_inicio	int(11)	NO			
rango_productores_fin	int(11)	NO			
rango_fincas_inicio	int(11)	NO			
rango_fincas_fin	int(11)	NO			
rango_cuarteles_inicio	int(11)	NO			
rango_cuarteles_fin	int(11)	NO			

📄 Tabla: cosechaMecanica_coop_contrato_firma
Columna	Tipo	Nulo	Clave	Default	Extra
id	bigint(20) unsigned	NO	PRI		auto_increment
contrato_id	int(10) unsigned	NO	MUL		
cooperativa_id_real	varchar(11)	NO			
acepto	tinyint(1)	NO		1	
fecha_firma	timestamp	NO		current_timestamp()	
created_at	timestamp	NO		current_timestamp()	

🔗 Relaciones (salientes):
Columna contrato_id referencia a CosechaMecanica.id
🔁 Relaciones (entrantes): CosechaMecanica
📄 Tabla: cosechaMecanica_coop_correo_log
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(11)	NO	PRI		auto_increment
contrato_id	int(11)	NO	MUL		
cooperativa_id_real	varchar(11)	NO			
correo	varchar(190)	NO			
tipo	varchar(20)	NO			
enviado_por	varchar(20)	NO			
created_at	datetime	NO		current_timestamp()	

📄 Tabla: cosechaMecanica_cooperativas_participacion
Columna	Tipo	Nulo	Clave	Default	Extra
id	bigint(20) unsigned	NO	PRI		auto_increment
contrato_id	int(10) unsigned	NO	MUL		
nom_cooperativa	varchar(255)	NO	MUL		
firma	tinyint(1)	NO		0	
productor	varchar(255)	NO	MUL		
finca_id	int(10) unsigned	YES			
superficie	decimal(10,2)	NO			
variedad	varchar(255)	NO			
prod_estimada	decimal(10,2)	NO			
fecha_estimada	varchar(60)	YES			
km_finca	decimal(10,2)	NO			
flete	tinyint(1)	NO		0	
seguro_flete	enum('sin_definir','si','no')	NO		sin_definir	
created_at	timestamp	NO		current_timestamp()	

🔗 Relaciones (salientes):
Columna contrato_id referencia a CosechaMecanica.id
🔁 Relaciones (entrantes): CosechaMecanica, cosechaMecanica_relevamiento_finca
📄 Tabla: cosechaMecanica_relevamiento_finca
Columna	Tipo	Nulo	Clave	Default	Extra
id	bigint(20) unsigned	NO	PRI		auto_increment
productor_id	int(11)	YES			
finca_id	int(10) unsigned	YES			
participacion_id	bigint(20) unsigned	NO	MUL		
ancho_callejon_norte	int(11)	NO			
ancho_callejon_sur	int(11)	NO			
promedio_callejon	decimal(6,2)	YES			
ancho_callejon	enum('Mayor a 6 metros','Mayor a 5.7 metros','Mayor a 5.3 metros','Mayor a 5 metros','Menor a 5 metros')	NO			
interfilar	enum('Mayor a 2,5 metros','Mayor a 2,3 metros','Mayor a 2.2 metros','Mayor a 2 metros','Menor a 2 metros')	NO			
cantidad_postes	int(11)	NO			
postes_mal_estado	int(11)	NO			
porcentaje_postes_mal_estado	decimal(5,2)	YES			
estructura_postes	enum('Menos del 5%','Menos de 10%','Menos de 25%','Menos de 40%','Más de 40%')	NO			
estructura_separadores	enum('Todos asegurados y tensados firmemente','Asegurados y tensados, algunos olvidados','Sin atar o tensar')	NO			
agua_lavado	enum('Suficiente y cercanda','Suficiente a mas de 1km','Insuficiente pero cercana','Insuficiente a mas de 1km','No tiene')	NO			
preparacion_acequias	enum('Acequias borradas y sin impedimentos','Acequias suavizadas de facil transito','Acequias con dificultades para el transito','Profundas sin borrar')	NO			
preparacion_obstaculos	enum('Ausencia de malesas','Ausencia en la mayoria de las superficies','Malezas menores a 40cm','Suelo enmalezado','Obstaculos o malesas sobre el alambre')	NO			
observaciones	text	YES			
created_at	timestamp	NO		current_timestamp()	
updated_at	timestamp	YES		current_timestamp()	on update current_timestamp()

🔗 Relaciones (salientes):
Columna participacion_id referencia a cosechaMecanica_cooperativas_participacion.id
🔁 Relaciones (entrantes): cosechaMecanica_cooperativas_participacion
📄 Tabla: detalle_pedidos
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(11)	NO	PRI		auto_increment
pedido_id	int(11)	NO	MUL		
nombre_producto	varchar(255)	NO			
detalle_producto	text	YES			
precio_producto	decimal(10,2)	NO			
unidad_medida_venta	varchar(100)	YES			
categoria	varchar(100)	YES			
producto_id	int(11)	YES			
cantidad	int(11)	NO		1	
alicuota	decimal(5,2)	YES		0.00	

🔗 Relaciones (salientes):
Columna pedido_id referencia a pedidos.id
🔁 Relaciones (entrantes): pedidos
📄 Tabla: dron_costo_hectarea
Columna	Tipo	Nulo	Clave	Default	Extra
id	tinyint(1)	NO	PRI		
costo	decimal(10,2)	NO			
moneda	varchar(20)	YES		Pesos	
updated_at	timestamp	YES		current_timestamp()	on update current_timestamp()

📄 Tabla: dron_formas_pago
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(11)	NO	PRI		auto_increment
nombre	varchar(100)	NO	UNI		
descripcion	varchar(255)	YES			
activo	enum('si','no')	NO	MUL	si	
created_at	timestamp	NO	MUL	current_timestamp()	
updated_at	timestamp	YES			on update current_timestamp()

🔁 Relaciones (entrantes): drones_solicitud
📄 Tabla: dron_patologias
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(11)	NO	PRI		auto_increment
nombre	varchar(100)	NO	UNI		
descripcion	varchar(255)	YES			
activo	enum('si','no')	NO	MUL	si	
created_at	timestamp	NO	MUL	current_timestamp()	
updated_at	timestamp	YES			on update current_timestamp()

🔁 Relaciones (entrantes): drones_solicitud_motivo, drones_solicitud_item, dron_productos_stock_patologias
📄 Tabla: dron_pilotos
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(11)	NO	PRI		auto_increment
nombre	varchar(100)	NO	MUL		
telefono	varchar(20)	YES			
zona_asignada	varchar(100)	YES			
correo	varchar(100)	NO	UNI		
activo	enum('si','no')	NO	MUL	si	
created_at	timestamp	NO		current_timestamp()	
updated_at	timestamp	YES			on update current_timestamp()

📄 Tabla: dron_produccion
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(11)	NO	PRI		auto_increment
nombre	varchar(100)	NO	UNI		
descripcion	varchar(255)	YES			
activo	enum('si','no')	NO	MUL	si	
created_at	timestamp	NO	MUL	current_timestamp()	
updated_at	timestamp	YES			on update current_timestamp()

📄 Tabla: dron_productos_stock
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(11)	NO	PRI		auto_increment
nombre	varchar(150)	NO	UNI		
detalle	text	YES			
principio_activo	varchar(150)	YES			
cantidad_deposito	int(11)	NO		0	
costo_hectarea	decimal(10,2)	NO		0.00	
activo	enum('si','no')	NO	MUL	si	
created_at	timestamp	NO		current_timestamp()	
updated_at	timestamp	YES			on update current_timestamp()
tiempo_carencia	varchar(100)	YES			

🔁 Relaciones (entrantes): drones_solicitud_item, dron_productos_stock_patologias
📄 Tabla: dron_productos_stock_patologias
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(11)	NO	PRI		auto_increment
producto_id	int(11)	NO	MUL		
patologia_id	int(11)	NO	MUL		
created_at	timestamp	NO		current_timestamp()	

🔗 Relaciones (salientes):
Columna patologia_id referencia a dron_patologias.id
Columna producto_id referencia a dron_productos_stock.id
🔁 Relaciones (entrantes): dron_patologias, dron_productos_stock
📄 Tabla: drones_calendario_notas
Columna	Tipo	Nulo	Clave	Default	Extra
id	bigint(20) unsigned	NO	PRI		auto_increment
fecha	date	NO	MUL		
texto	varchar(500)	NO			
piloto_id	int(11)	YES	MUL		
zona	varchar(100)	YES	MUL		
created_by	varchar(100)	YES			
created_at	timestamp	NO		current_timestamp()	
updated_at	timestamp	YES			on update current_timestamp()

🔗 Relaciones (salientes):
Columna piloto_id referencia a usuarios.id
🔁 Relaciones (entrantes): usuarios
📄 Tabla: drones_solicitud
Columna	Tipo	Nulo	Clave	Default	Extra
id	bigint(20) unsigned	NO	PRI		auto_increment
productor_id_real	varchar(20)	NO	MUL		
representante	enum('si','no')	NO			
linea_tension	enum('si','no')	NO			
zona_restringida	enum('si','no')	NO			
corriente_electrica	enum('si','no')	NO			
agua_potable	enum('si','no')	NO			
libre_obstaculos	enum('si','no')	NO			
area_despegue	enum('si','no')	NO			
superficie_ha	decimal(10,2)	NO			
fecha_visita	date	YES			
hora_visita_desde	time	YES			
hora_visita_hasta	time	YES			
piloto_id	int(11)	YES	MUL		
forma_pago_id	int(11)	NO	MUL		
coop_descuento_nombre	varchar(100)	YES			
dir_provincia	varchar(100)	YES			
dir_localidad	varchar(100)	YES			
dir_calle	varchar(150)	YES			
dir_numero	varchar(20)	YES			
en_finca	enum('si','no')	NO		no	
ubicacion_lat	decimal(10,7)	YES			
ubicacion_lng	decimal(10,7)	YES			
ubicacion_acc	decimal(10,2)	YES			
ubicacion_ts	datetime	YES			
observaciones	text	YES			
ses_usuario	varchar(100)	YES			
ses_rol	varchar(30)	YES			
ses_nombre	varchar(100)	YES			
ses_correo	varchar(100)	YES			
ses_telefono	varchar(30)	YES			
ses_direccion	varchar(255)	YES			
ses_cuit	bigint(20)	YES			
ses_last_activity_ts	datetime	YES			
estado	enum('ingresada','procesando','aprobada_coop','cancelada','completada','visita_realizada')	NO	MUL	ingresada	
motivo_cancelacion	varchar(100)	YES			
created_at	timestamp	NO		current_timestamp()	
updated_at	timestamp	YES			on update current_timestamp()

🔗 Relaciones (salientes):
Columna piloto_id referencia a usuarios.id
Columna forma_pago_id referencia a dron_formas_pago.id
Columna productor_id_real referencia a usuarios.id_real
🔁 Relaciones (entrantes): drones_solicitud_parametros, drones_solicitud_motivo, usuarios, drones_solicitud_Reporte, drones_solicitud_item, drones_solicitud_evento, drones_solicitud_costos, dron_formas_pago, drones_solicitud_rango
📄 Tabla: drones_solicitud_Reporte
Columna	Tipo	Nulo	Clave	Default	Extra
id	bigint(20) unsigned	NO	PRI		auto_increment
solicitud_id	bigint(20) unsigned	NO	MUL		
nom_cliente	varchar(150)	NO			
nom_piloto	varchar(150)	NO			
nom_encargado	varchar(150)	YES			
fecha_visita	date	NO			
hora_ingreso	time	NO			
hora_egreso	time	NO			
nombre_finca	varchar(150)	YES			
cultivo_pulverizado	varchar(150)	YES			
cuadro_cuartel	varchar(150)	YES			
sup_pulverizada	decimal(10,2)	YES			
vol_aplicado	decimal(10,2)	YES			
vel_viento	decimal(10,2)	YES			
temperatura	decimal(10,2)	YES			
humedad_relativa	decimal(10,2)	YES			
lavado_dron_miner	varchar(20)	NO		Sin definir	
triple_lavado_envases	varchar(20)	NO		Sin definir	
observaciones	text	YES			
created_at	timestamp	NO		current_timestamp()	

🔗 Relaciones (salientes):
Columna solicitud_id referencia a drones_solicitud.id
🔁 Relaciones (entrantes): drones_solicitud, drones_solicitud_reporte_media
📄 Tabla: drones_solicitud_costos
Columna	Tipo	Nulo	Clave	Default	Extra
id	bigint(20) unsigned	NO	PRI		auto_increment
solicitud_id	bigint(20) unsigned	NO	UNI		
moneda	varchar(20)	NO		Pesos	
costo_base_por_ha	decimal(10,2)	NO			
base_ha	decimal(10,2)	NO			
base_total	decimal(12,2)	NO			
productos_total	decimal(12,2)	NO			
total	decimal(12,2)	NO			
desglose_json	longtext	YES			
created_at	timestamp	NO		current_timestamp()	

🔗 Relaciones (salientes):
Columna solicitud_id referencia a drones_solicitud.id
🔁 Relaciones (entrantes): drones_solicitud
📄 Tabla: drones_solicitud_evento
Columna	Tipo	Nulo	Clave	Default	Extra
id	bigint(20) unsigned	NO	PRI		auto_increment
solicitud_id	bigint(20) unsigned	NO	MUL		
tipo	varchar(50)	NO	MUL		
detalle	text	YES			
payload	longtext	YES			
actor	varchar(100)	YES			
created_at	timestamp	NO		current_timestamp()	

🔗 Relaciones (salientes):
Columna solicitud_id referencia a drones_solicitud.id
🔁 Relaciones (entrantes): drones_solicitud
📄 Tabla: drones_solicitud_item
Columna	Tipo	Nulo	Clave	Default	Extra
id	bigint(20) unsigned	NO	PRI		auto_increment
solicitud_id	bigint(20) unsigned	NO	MUL		
patologia_id	int(11)	NO	MUL		
fuente	enum('sve','productor')	NO	MUL		
producto_id	int(11)	YES	MUL		
costo_hectarea_snapshot	decimal(10,2)	YES			
total_producto_snapshot	decimal(12,2)	YES			
nombre_producto	varchar(150)	YES			
created_at	timestamp	NO		current_timestamp()	
updated_at	timestamp	YES			on update current_timestamp()

🔗 Relaciones (salientes):
Columna patologia_id referencia a dron_patologias.id
Columna producto_id referencia a dron_productos_stock.id
Columna solicitud_id referencia a drones_solicitud.id
🔁 Relaciones (entrantes): drones_solicitud, dron_patologias, drones_solicitud_item_receta, dron_productos_stock
📄 Tabla: drones_solicitud_item_receta
Columna	Tipo	Nulo	Clave	Default	Extra
id	bigint(20) unsigned	NO	PRI		auto_increment
solicitud_item_id	bigint(20) unsigned	NO	MUL		
principio_activo	varchar(150)	YES			
dosis	decimal(10,3)	YES			
cant_prod_usado	decimal(10,2)	YES			
fecha_vencimiento	date	YES			
unidad	varchar(30)	YES			
orden_mezcla	smallint(6)	YES			
notas	text	YES			
created_by	varchar(100)	YES			
updated_by	varchar(100)	YES			
created_at	timestamp	NO		current_timestamp()	
updated_at	timestamp	YES			on update current_timestamp()

🔗 Relaciones (salientes):
Columna solicitud_item_id referencia a drones_solicitud_item.id
🔁 Relaciones (entrantes): drones_solicitud_item
📄 Tabla: drones_solicitud_motivo
Columna	Tipo	Nulo	Clave	Default	Extra
id	bigint(20) unsigned	NO	PRI		auto_increment
solicitud_id	bigint(20) unsigned	NO	MUL		
patologia_id	int(11)	YES	MUL		
es_otros	tinyint(1)	NO		0	
otros_text	varchar(255)	YES			
created_at	timestamp	NO		current_timestamp()	

🔗 Relaciones (salientes):
Columna patologia_id referencia a dron_patologias.id
Columna solicitud_id referencia a drones_solicitud.id
🔁 Relaciones (entrantes): drones_solicitud, dron_patologias
📄 Tabla: drones_solicitud_parametros
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(10) unsigned	NO	PRI		auto_increment
solicitud_id	bigint(20) unsigned	NO	MUL		
volumen_ha	decimal(10,2)	YES			
velocidad_vuelo	decimal(10,2)	YES			
alto_vuelo	decimal(10,2)	YES			
ancho_pasada	decimal(10,2)	YES			
tamano_gota	varchar(50)	YES			
observaciones	text	YES			
created_at	timestamp	NO		current_timestamp()	
updated_at	timestamp	YES			on update current_timestamp()
observaciones_agua	text	YES			

🔗 Relaciones (salientes):
Columna solicitud_id referencia a drones_solicitud.id
🔁 Relaciones (entrantes): drones_solicitud
📄 Tabla: drones_solicitud_rango
Columna	Tipo	Nulo	Clave	Default	Extra
id	bigint(20) unsigned	NO	PRI		auto_increment
solicitud_id	bigint(20) unsigned	NO	MUL		
rango	enum('enero_q1','enero_q2','febrero_q1','febrero_q2','octubre_q1','octubre_q2','noviembre_q1','noviembre_q2','diciembre_q1','diciembre_q2')	NO			
created_at	timestamp	NO		current_timestamp()	

🔗 Relaciones (salientes):
Columna solicitud_id referencia a drones_solicitud.id
🔁 Relaciones (entrantes): drones_solicitud
📄 Tabla: drones_solicitud_reporte_media
Columna	Tipo	Nulo	Clave	Default	Extra
id	bigint(20) unsigned	NO	PRI		auto_increment
reporte_id	bigint(20) unsigned	NO	MUL		
tipo	enum('foto','firma_cliente','firma_piloto')	NO			
ruta	varchar(255)	NO			
created_at	timestamp	NO		current_timestamp()	

🔗 Relaciones (salientes):
Columna reporte_id referencia a drones_solicitud_Reporte.id
🔁 Relaciones (entrantes): drones_solicitud_Reporte
📄 Tabla: factura_pedidos
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(11)	NO	PRI		auto_increment
pedido_id	int(11)	NO	MUL		
nombre_archivo	varchar(255)	NO			
extension	varchar(10)	YES			
fecha_subida	timestamp	YES		current_timestamp()	

🔗 Relaciones (salientes):
Columna pedido_id referencia a pedidos.id
🔁 Relaciones (entrantes): pedidos
📄 Tabla: info_productor
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(10) unsigned	NO	PRI		auto_increment
productor_id	int(11)	NO	MUL		
anio	smallint(5) unsigned	NO			
acceso_internet	enum('si','no','nsnc')	YES			
vive_en_finca	enum('si','no','nsnc')	YES			
tiene_otra_finca	enum('si','no','nsnc')	YES			
condicion_cooperativa	varchar(100)	YES			
anio_asociacion	smallint(5) unsigned	YES			
actividad_principal	varchar(150)	YES			
actividad_secundaria	varchar(150)	YES			
porcentaje_aporte_vitivinicola	decimal(5,2)	YES			
created_at	timestamp	NO		current_timestamp()	

🔗 Relaciones (salientes):
Columna productor_id referencia a usuarios.id
🔁 Relaciones (entrantes): usuarios
📄 Tabla: log_correos
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(11)	NO	PRI		auto_increment
tipo	varchar(100)	YES			
template	varchar(190)	YES			
subject	varchar(255)	YES			
from_email	varchar(190)	YES			
from_name	varchar(190)	YES			
reply_to	varchar(190)	YES			
to_emails	longtext	YES			
cc_emails	longtext	YES			
bcc_emails	longtext	YES			
body_html	longtext	YES			
body_text	longtext	YES			
enviado_ok	tinyint(1)	NO		0	
error_msg	text	YES			
created_at	datetime	NO		current_timestamp()	
contrato_id	int(11)	YES			
cooperativa_id_real	varchar(20)	YES			
correo	varchar(190)	YES			
enviado_por	varchar(50)	YES			

📄 Tabla: login_auditoria
Columna	Tipo	Nulo	Clave	Default	Extra
id	bigint(20) unsigned	NO	PRI		auto_increment
usuario_input	varchar(100)	YES	MUL		
usuario_id_real	varchar(20)	YES	MUL		
rol	enum('ingeniero','cooperativa','productor','sve')	YES	MUL		
resultado	enum('ok','error')	NO	MUL		
motivo	varchar(200)	YES			
ip	varchar(45)	YES			
user_agent	varchar(255)	YES			
created_at	timestamp	NO		current_timestamp()	

📄 Tabla: operativos
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(11)	NO	PRI		auto_increment
nombre	varchar(255)	NO			
fecha_inicio	date	NO			
fecha_cierre	date	NO			
created_at	timestamp	YES		current_timestamp()	
estado	enum('abierto','cerrado')	NO		abierto	
descripcion	varchar(255)	NO		Sin descripción	

🔁 Relaciones (entrantes): operativos_productos, operativos_cooperativas_participacion
📄 Tabla: operativos_cooperativas_participacion
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(11)	NO	PRI		auto_increment
operativo_id	int(11)	NO	MUL		
cooperativa_id_real	varchar(20)	NO	MUL		
participa	enum('si','no')	NO		no	
fecha_registro	timestamp	YES		current_timestamp()	

🔗 Relaciones (salientes):
Columna cooperativa_id_real referencia a usuarios.id_real
Columna operativo_id referencia a operativos.id
🔁 Relaciones (entrantes): usuarios, operativos
📄 Tabla: operativos_productos
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(11)	NO	PRI		auto_increment
operativo_id	int(11)	NO	MUL		
producto_id	int(11)	NO	MUL		
fecha_registro	timestamp	YES		current_timestamp()	

🔗 Relaciones (salientes):
Columna operativo_id referencia a operativos.id
Columna producto_id referencia a productos.Id
🔁 Relaciones (entrantes): operativos, productos
📄 Tabla: pedidos
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(11)	NO	PRI		auto_increment
cooperativa	varchar(255)	NO			
productor	varchar(255)	NO			
fecha_pedido	date	NO			
persona_facturacion	enum('productor','cooperativa')	NO			
condicion_facturacion	enum('responsable inscripto','monotributista')	NO			
afiliacion	enum('socio','tercero')	NO			
ha_cooperativa	decimal(10,2)	YES			
total_sin_iva	decimal(10,2)	YES			
total_iva	decimal(10,2)	YES			
factura	varchar(255)	YES			
total_pedido	decimal(10,2)	NO			
observaciones	text	NO			
operativo_id	int(11)	YES			

🔁 Relaciones (entrantes): detalle_pedidos, factura_pedidos
📄 Tabla: prod_colaboradores
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(10) unsigned	NO	PRI		auto_increment
productor_id	int(11)	NO	MUL		
anio	smallint(5) unsigned	NO			
hijos_sobrinos_participan	enum('si','no','nsnc')	YES			
mujeres_tc	int(11)	YES			
hombres_tc	int(11)	YES			
mujeres_tp	int(11)	YES			
hombres_tp	int(11)	YES			
prob_hijos_trabajen	varchar(100)	YES			
created_at	timestamp	NO		current_timestamp()	

🔗 Relaciones (salientes):
Columna productor_id referencia a usuarios.id
🔁 Relaciones (entrantes): usuarios
📄 Tabla: prod_cuartel
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(10) unsigned	NO	PRI		auto_increment
id_responsable_real	varchar(20)	YES			
cooperativa_id_real	varchar(20)	NO	MUL		
codigo_finca	varchar(20)	NO	MUL		
nombre_finca	varchar(150)	YES			
codigo_cuartel	varchar(20)	YES			
variedad	varchar(150)	YES			
numero_inv	varchar(50)	YES			
sistema_conduccion	varchar(150)	YES			
superficie_ha	decimal(10,2)	YES			
porcentaje_cepas_produccion	decimal(5,2)	YES			
forma_cosecha_actual	varchar(150)	YES			
porcentaje_malla_buen_estado	decimal(5,2)	YES			
edad_promedio_encepado_anios	smallint(6)	YES			
estado_estructura_sistema	varchar(150)	YES			
labores_mecanizables	text	YES			
finca_id	int(10) unsigned	YES	MUL		
created_at	timestamp	NO		current_timestamp()	

🔗 Relaciones (salientes):
Columna cooperativa_id_real referencia a usuarios.id_real
Columna finca_id referencia a prod_fincas.id
🔁 Relaciones (entrantes): prod_fincas, usuarios, prod_cuartel_limitantes, prod_cuartel_rendimientos, prod_cuartel_riesgos
📄 Tabla: prod_cuartel_limitantes
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(10) unsigned	NO	PRI		auto_increment
cuartel_id	int(10) unsigned	NO	UNI		
limitantes_suelo	text	YES			
observaciones	text	YES			
categoria_1	varchar(100)	YES			
limitante_1	varchar(255)	YES			
inversion_accion1_1	text	YES			
obs_inversion_accion1_1	text	YES			
ciclo_agricola1_1	varchar(100)	YES			
inversion_accion2_1	text	YES			
obs_inversion_accion2_1	text	YES			
ciclo_agricola2_1	varchar(100)	YES			
categoria_2	varchar(100)	YES			
limitante_2	varchar(255)	YES			
inversion_accion1_2	text	YES			
obs_inversion_accion1_2	text	YES			
ciclo_agricola1_2	varchar(100)	YES			
inversion_accion2_2	text	YES			
obs_inversion_accion2_2	text	YES			
ciclo_agricola2_2	varchar(100)	YES			
created_at	timestamp	NO		current_timestamp()	

🔗 Relaciones (salientes):
Columna cuartel_id referencia a prod_cuartel.id
🔁 Relaciones (entrantes): prod_cuartel
📄 Tabla: prod_cuartel_rendimientos
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(10) unsigned	NO	PRI		auto_increment
cuartel_id	int(10) unsigned	NO	UNI		
rend_2020_qq_ha	decimal(10,2)	YES			
rend_2021_qq_ha	decimal(10,2)	YES			
rend_2022_qq_ha	decimal(10,2)	YES			
ing_2023_kg	decimal(12,2)	YES			
rend_2023_qq_ha	decimal(10,2)	YES			
ing_2024_kg	decimal(12,2)	YES			
rend_2024_qq_ha	decimal(10,2)	YES			
ing_2025_kg	decimal(12,2)	YES			
rend_2025_qq_ha	decimal(10,2)	YES			
rend_promedio_5anios_qq_ha	decimal(10,2)	YES			
created_at	timestamp	NO		current_timestamp()	

🔗 Relaciones (salientes):
Columna cuartel_id referencia a prod_cuartel.id
🔁 Relaciones (entrantes): prod_cuartel
📄 Tabla: prod_cuartel_riesgos
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(10) unsigned	NO	PRI		auto_increment
cuartel_id	int(10) unsigned	NO	UNI		
tiene_seguro_agricola	enum('si','no','nsnc')	YES			
porcentaje_dano_granizo	decimal(5,2)	YES			
heladas_dano_promedio_5anios	decimal(5,2)	YES			
presencia_freatica	varchar(150)	YES			
plagas_no_convencionales	text	YES			
created_at	timestamp	NO		current_timestamp()	

🔗 Relaciones (salientes):
Columna cuartel_id referencia a prod_cuartel.id
🔁 Relaciones (entrantes): prod_cuartel
📄 Tabla: prod_finca_agua
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(10) unsigned	NO	PRI		auto_increment
finca_id	int(10) unsigned	NO	MUL		
anio	smallint(5) unsigned	NO			
sup_agua_con_derecho_ha	decimal(10,2)	YES			
tipo_riego	varchar(150)	YES			
sup_agua_sin_derecho_ha	decimal(10,2)	YES			
estado_provision_agua	varchar(150)	YES			
estado_asignacion_turnado	varchar(150)	YES			
estado_sistematizacion_vinedo	varchar(150)	YES			
tiene_flexibilizacion_entrega_agua	enum('si','no','nsnc')	YES			
riego_presurizado_toma_agua_de	varchar(150)	YES			
perforacion_activa_1	enum('si','no','nsnc')	YES			
perforacion_activa_2	enum('si','no','nsnc')	YES			
agua_analizada	enum('si','no','nsnc')	YES			
conductividad_mhos_cm	decimal(10,3)	YES			
created_at	timestamp	NO		current_timestamp()	

🔗 Relaciones (salientes):
Columna finca_id referencia a prod_fincas.id
🔁 Relaciones (entrantes): prod_fincas
📄 Tabla: prod_finca_cultivos
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(10) unsigned	NO	PRI		auto_increment
finca_id	int(10) unsigned	NO	MUL		
anio	smallint(5) unsigned	NO			
sup_cultivo_horticola_ha	decimal(10,2)	YES			
estado_cultivo_horticola	varchar(150)	YES			
sup_cultivo_fruticola_ha	decimal(10,2)	YES			
estado_cultivo_fruticola	varchar(150)	YES			
sup_cultivo_forestal_otra_ha	decimal(10,2)	YES			
estado_cultivo_forestal_otra	varchar(150)	YES			
created_at	timestamp	NO		current_timestamp()	

🔗 Relaciones (salientes):
Columna finca_id referencia a prod_fincas.id
🔁 Relaciones (entrantes): prod_fincas
📄 Tabla: prod_finca_direccion
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(10) unsigned	NO	PRI		auto_increment
finca_id	int(10) unsigned	NO	UNI		
departamento	varchar(100)	YES			
localidad	varchar(100)	YES			
calle	varchar(150)	YES			
numero	varchar(20)	YES			
latitud	decimal(10,7)	YES			
longitud	decimal(10,7)	YES			
created_at	timestamp	NO		current_timestamp()	

🔗 Relaciones (salientes):
Columna finca_id referencia a prod_fincas.id
🔁 Relaciones (entrantes): prod_fincas
📄 Tabla: prod_finca_gerencia
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(10) unsigned	NO	PRI		auto_increment
finca_id	int(10) unsigned	NO	MUL		
anio	smallint(5) unsigned	NO			
problemas_gerencia	varchar(255)	YES			
prob_gerenciamiento_1	text	YES			
prob_personal_1	text	YES			
prob_tecnologicos_1	text	YES			
prob_administracion_1	text	YES			
prob_medios_produccion_1	text	YES			
prob_observacion_1	text	YES			
prob_gerenciamiento_2	text	YES			
prob_personal_2	text	YES			
prob_tecnologicos_2	text	YES			
prob_administracion_2	text	YES			
prob_medios_produccion_2	text	YES			
prob_observacion_2	text	YES			
limitante_1	varchar(255)	YES			
limitante_2	varchar(255)	YES			
limitante_3	varchar(255)	YES			
created_at	timestamp	NO		current_timestamp()	

🔗 Relaciones (salientes):
Columna finca_id referencia a prod_fincas.id
🔁 Relaciones (entrantes): prod_fincas
📄 Tabla: prod_finca_maquinaria
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(10) unsigned	NO	PRI		auto_increment
finca_id	int(10) unsigned	NO	MUL		
anio	smallint(5) unsigned	NO			
clasificacion_estado_tractor	varchar(150)	YES			
estado_pulverizadora	varchar(150)	YES			
clasificacion_estado_implementos	varchar(150)	YES			
utiliza_empresa_servicios	enum('si','no','nsnc')	YES			
administracion	varchar(150)	YES			
trabajadores_permanentes	int(11)	YES			
posee_deposito_fitosanitarios	enum('si','no','nsnc')	YES			
created_at	timestamp	NO		current_timestamp()	

🔗 Relaciones (salientes):
Columna finca_id referencia a prod_fincas.id
🔁 Relaciones (entrantes): prod_fincas
📄 Tabla: prod_finca_superficie
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(10) unsigned	NO	PRI		auto_increment
finca_id	int(10) unsigned	NO	MUL		
anio	smallint(5) unsigned	NO			
sup_total_ha	decimal(10,2)	YES			
sup_total_cultivada_ha	decimal(10,2)	YES			
sup_total_vid_ha	decimal(10,2)	YES			
sup_vid_destinada_coop_ha	decimal(10,2)	YES			
sup_con_otros_cultivos_ha	decimal(10,2)	YES			
clasificacion_riesgo_salinizacion	varchar(150)	YES			
analisis_suelo_completo	enum('si','no','nsnc')	YES			
created_at	timestamp	NO		current_timestamp()	

🔗 Relaciones (salientes):
Columna finca_id referencia a prod_fincas.id
🔁 Relaciones (entrantes): prod_fincas
📄 Tabla: prod_fincas
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(10) unsigned	NO	PRI		auto_increment
codigo_finca	varchar(20)	NO	MUL		
productor_id_real	varchar(20)	NO	MUL		
nombre_finca	varchar(150)	YES			
variedad	varchar(150)	YES			
created_at	timestamp	NO		current_timestamp()	

🔗 Relaciones (salientes):
Columna productor_id_real referencia a usuarios.id_real
🔁 Relaciones (entrantes): prod_finca_cultivos, usuarios, prod_cuartel, relevamiento_fincas, prod_finca_direccion, prod_finca_maquinaria, prod_finca_agua, prod_finca_gerencia, rel_productor_finca, prod_finca_superficie
📄 Tabla: prod_hijos
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(10) unsigned	NO	PRI		auto_increment
productor_id	int(11)	NO	MUL		
anio	smallint(5) unsigned	NO			
motivo_no_trabajar	varchar(255)	YES			
rango_etario	varchar(50)	YES			
sexo	enum('M','F','Otro')	YES			
cantidad	int(11)	YES			
nivel_estudio	varchar(100)	YES			
nom_hijo_1	varchar(100)	YES			
fecha_nacimiento_1	date	YES			
sexo1	enum('M','F','Otro')	YES			
nivel_estudio1	varchar(100)	YES			
nom_hijo_2	varchar(100)	YES			
fecha_nacimiento_2	date	YES			
sexo2	enum('M','F','Otro')	YES			
nivel_estudio2	varchar(100)	YES			
nom_hijo_3	varchar(100)	YES			
fecha_nacimiento_3	date	YES			
sexo3	enum('M','F','Otro')	YES			
nivel_estudio3	varchar(100)	YES			
created_at	timestamp	NO		current_timestamp()	

🔗 Relaciones (salientes):
Columna productor_id referencia a usuarios.id
🔁 Relaciones (entrantes): usuarios
📄 Tabla: productores_contactos_alternos
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(10) unsigned	NO	PRI		auto_increment
productor_id	int(11)	NO	MUL		
contacto_preferido	varchar(100)	YES			
celular_alternativo	varchar(30)	YES			
telefono_fijo	varchar(30)	YES			
mail_alternativo	varchar(100)	YES			
created_at	timestamp	NO		current_timestamp()	

🔗 Relaciones (salientes):
Columna productor_id referencia a usuarios.id
🔁 Relaciones (entrantes): usuarios
📄 Tabla: productos
Columna	Tipo	Nulo	Clave	Default	Extra
Id	int(11)	NO	PRI		auto_increment
Nombre_producto	varchar(100)	NO			
Detalle_producto	text	YES			
Precio_producto	decimal(10,2)	NO			
moneda	varchar(20)	NO		Pesos	
Unidad_Medida_venta	varchar(100)	NO			
categoria	enum('Fertilizantes Sólidos','Fertilizantes Complejos','Fertilizantes Líquidos','Fungicidas','Insecticidas','Feromona Asperjable','Difusor Feromona','Herbicidas','Fertilizantes Especiales','Fertilizantes Foliares','Levadura SA','Levadura SA Siembra Directa','Levadura SA Bayanus','Levadura SA TRB Genérico','Levadura SA Tinto Verietal','Levadura SA Blanco Varietal','Levadura SA Dulce Natural','Nutriente enologico','Nutriente enológico','Desincrustante','Clarificante','Acidulante','Acido columna','Enzima','EPP (Elementos de protección personal )','Indumentaria','Calzado','Elementos de limpieza','Cañerias','Accesorios','Otros')	NO		Otros	
alicuota	enum('0','2.5','5','10.5','21','27')	NO		0	

🔁 Relaciones (entrantes): operativos_productos
📄 Tabla: publicaciones
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(11)	NO	PRI		auto_increment
titulo	varchar(255)	NO			
subtitulo	varchar(255)	YES			
autor	varchar(100)	NO			
descripcion	text	YES			
categoria_id	int(11)	NO	MUL		
subcategoria_id	int(11)	NO	MUL		
fecha_publicacion	date	NO			
archivo	varchar(255)	YES			
vistas	int(11)	YES		0	
descargas	int(11)	YES		0	
created_at	timestamp	YES		current_timestamp()	

🔗 Relaciones (salientes):
Columna categoria_id referencia a categorias_publicaciones.id
Columna subcategoria_id referencia a subcategorias_publicaciones.id
🔁 Relaciones (entrantes): categorias_publicaciones, subcategorias_publicaciones
📄 Tabla: rel_coop_ingeniero
Columna	Tipo	Nulo	Clave	Default	Extra
cooperativa_id_real	varchar(20)	NO	PRI		
ingeniero_id_real	varchar(20)	NO	PRI		
created_at	timestamp	NO		current_timestamp()	

🔗 Relaciones (salientes):
Columna cooperativa_id_real referencia a usuarios.id_real
Columna ingeniero_id_real referencia a usuarios.id_real
🔁 Relaciones (entrantes): usuarios
📄 Tabla: rel_productor_coop
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(11)	NO	PRI		auto_increment
productor_id_real	varchar(20)	NO	MUL		
cooperativa_id_real	varchar(20)	NO	MUL		

🔗 Relaciones (salientes):
Columna cooperativa_id_real referencia a usuarios.id_real
Columna productor_id_real referencia a usuarios.id_real
🔁 Relaciones (entrantes): usuarios
📄 Tabla: rel_productor_finca
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(10) unsigned	NO	PRI		auto_increment
productor_id	int(11)	NO	MUL		
productor_id_real	varchar(20)	NO	MUL		
finca_id	int(10) unsigned	NO	MUL		
created_at	timestamp	NO		current_timestamp()	

🔗 Relaciones (salientes):
Columna finca_id referencia a prod_fincas.id
Columna productor_id referencia a usuarios.id
🔁 Relaciones (entrantes): prod_fincas, usuarios
📄 Tabla: relevamiento_fincas
Columna	Tipo	Nulo	Clave	Default	Extra
id	bigint(20) unsigned	NO	PRI		auto_increment
productor_id	int(11)	NO	MUL		
finca_id	int(10) unsigned	NO	MUL		
ancho_callejon_norte	int(11)	NO			
ancho_callejon_sur	int(11)	NO			
promedio_callejon	decimal(6,2)	YES			
interfilar	enum('Mayor a 2,5 metros','Mayor a 2,3 metros','Mayor a 2.2 metros','Mayor a 2 metros','Menor a 2 metros')	NO			
cantidad_postes	int(11)	NO			
postes_mal_estado	int(11)	NO			
porcentaje_postes_mal_estado	decimal(5,2)	YES			
estructura_separadores	enum('Todos asegurados y tensados firmemente','Asegurados y tensados, algunos olvidados','Sin atar o tensar')	NO			
agua_lavado	enum('Suficiente y cercana','Suficiente a mas de 1km','Insuficiente pero cercana','Insuficiente a mas de 1km','No tiene')	NO			
preparacion_acequias	enum('Acequias borradas y sin impedimentos','Acequias suavizadas de facil transito','Acequias con dificultades para el transito','Profundas sin borrar')	NO			
preparacion_obstaculos	enum('Ausencia de malesas','Ausencia en la mayoria de las superficies','Malezas menores a 40cm','Suelo enmalezado','Obstaculos o malesas sobre el alambre')	NO			
observaciones	text	YES			
created_at	timestamp	NO		current_timestamp()	
updated_at	timestamp	YES		current_timestamp()	on update current_timestamp()

🔗 Relaciones (salientes):
Columna finca_id referencia a prod_fincas.id
Columna productor_id referencia a usuarios.id
🔁 Relaciones (entrantes): prod_fincas, usuarios
📄 Tabla: subcategorias_publicaciones
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(11)	NO	PRI		auto_increment
nombre	varchar(100)	NO			
categoria_id	int(11)	NO	MUL		

🔗 Relaciones (salientes):
Columna categoria_id referencia a categorias_publicaciones.id
🔁 Relaciones (entrantes): publicaciones, categorias_publicaciones
📄 Tabla: system_audit_log
Columna	Tipo	Nulo	Clave	Default	Extra
id	bigint(20) unsigned	NO	PRI		auto_increment
ts	datetime	NO	MUL		
request_id	char(16)	NO	MUL		
usuario_id	bigint(20)	YES	MUL		
rol	varchar(64)	YES			
ip	varchar(45)	YES			
ua	varchar(512)	YES			
method	varchar(16)	YES			
uri	varchar(1024)	YES			
action_type	enum('request','error','exception','shutdown','ui')	NO	MUL		
action	varchar(512)	YES			
status_code	int(11)	YES	MUL		
duration_ms	int(11)	YES			
meta	longtext	YES			

📄 Tabla: usuarios
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(11)	NO	PRI		auto_increment
usuario	varchar(100)	YES			
contrasena	varchar(255)	NO			
rol	enum('ingeniero','cooperativa','productor','sve','piloto_drone','piloto_tractor')	NO			
permiso_ingreso	enum('Habilitado','Deshabilitado')	NO		Habilitado	
cuit	bigint(11)	NO			
razon_social	varchar(150)	YES			
id_real	varchar(20)	NO	UNI		

🔁 Relaciones (entrantes): drones_solicitud, prod_fincas, prod_hijos, rel_coop_ingeniero, prod_cuartel, drones_calendario_notas, relevamiento_fincas, productores_contactos_alternos, rel_productor_coop, info_productor, usuarios_info, operativos_cooperativas_participacion, rel_productor_finca, prod_colaboradores
📄 Tabla: usuarios_info
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(11)	NO	PRI		auto_increment
usuario_id	int(11)	NO	MUL		
nombre	varchar(100)	YES			
direccion	varchar(255)	YES			
telefono	varchar(20)	YES			
correo	varchar(100)	YES			
fecha_nacimiento	date	YES			
categorizacion	enum('A','B','C')	YES			
tipo_relacion	varchar(50)	YES			
zona_asignada	varchar(100)	NO			

🔗 Relaciones (salientes):
Columna usuario_id referencia a usuarios.id
🔁 Relaciones (entrantes): usuarios
📄 Tabla: usuarios_pwd_backup
Columna	Tipo	Nulo	Clave	Default	Extra
id	int(11)	NO	PRI		
contrasena	text	NO			
backed_at	datetime	NO	PRI		
