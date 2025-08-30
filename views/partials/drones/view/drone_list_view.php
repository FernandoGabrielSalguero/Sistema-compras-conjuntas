<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <!-- Íconos de Material Design -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <!-- Framework Success desde CDN -->
    <link rel="stylesheet" href="https://www.fernandosalguero.com/cdn/assets/css/framework.css">
    <script src="https://www.fernandosalguero.com/cdn/assets/javascript/framework.js" defer></script>
</head>

<body>
    <div class="content">
        <div class="card" style="background-color: #5b21b6;">
            <h3>Buscar proyecto de vuelo</h3>

            <form class="form-grid grid-4" id="form-search" enctype="multipart/form-data">
                <!-- Buscamos por piloto -->
                <div class="input-group">
                    <label for="piloto" style="color: white;">Nombre piloto</label>
                    <div class="input-icon input-icon-name">
                        <input type="text" id="piloto" name="piloto" placeholder="Piloto" />
                    </div>
                </div>

                <!-- Buscamos por productor -->
                <div class="input-group">
                    <label for="ses_usuario " style="color: white">Nombre productor</label>
                    <div class="input-icon input-icon-name">
                        <input type="text" id="ses_usuario" name="ses_usuario" placeholder="Productor" />
                    </div>
                </div>

                <!-- Buscamos por estado -->
                <div class="input-group">
                    <label for="estado" style="color: white">Estado</label>
                    <div class="input-icon input-icon-globe">
                        <select id="estado" name="estado">
                            <option value="">Seleccionar</option>
                            <option value="pendiente">Pendiente</option>
                            <option value="en_proceso">En proceso</option>
                            <option value="completado">Completado</option>
                            <option value="cancelado">Cancelado</option>
                        </select>
                    </div>
                </div>

                <!-- Buscamos por fecha -->
                <div class="input-group">
                    <label for="fecha_visita" style="color: white">Fecha del servicio</label>
                    <div class="input-icon input-icon-date">
                        <input id="fecha_visita" name="fecha_visita" />
                    </div>
                </div>
            </form>
        </div>

        <!-- tarjetas con proyectos -->
        <div class="triple-tarjetas card-grid grid-4">
            <div class="product-card">
                <div class="product-header">
                    <h4>ses_usuario</h4>
                    <p>piloto</p>
                </div>
                <div class="product-body">
                    <div class="user-info">
                        <div>
                            <strong>productor_id_real</strong>
                            <div class="role">fecha_visita & hora_visita</div>
                        </div>
                    </div>

                    <!-- Descripción -->
                    <p class="description">
                        observaciones
                    </p>

                    <hr />

                    <div class="product-footer">
                        <div class="metric">
                            <td><span class="badge warning">Pendiente</span></td>
                        </div>
                        <div class="metric">
                            <span>motivo_cancelacion</span>
                        </div>
                        <button class="btn-view">Ver detalle</button>
                    </div>
                </div>
            </div>

        </div>
    </div>
    </div>
    <!-- Alert -->
    <div class="alert-container" id="alertContainer"></div>
</body>

</html>