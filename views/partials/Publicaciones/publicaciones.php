<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once('../../../config/db.php'); // Ajustá si la conexión está en otro archivo

// Obtener categorías y subcategorías
$categorias = $conn->query("SELECT id, nombre FROM sve_categorias ORDER BY nombre")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Publicaciones</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Framework CDN -->
    <link rel="stylesheet" href="https://www.fernandosalguero.com/cdn/assets/css/framework.css">
    <script src="https://www.fernandosalguero.com/cdn/assets/javascript/framework.js" defer></script>
</head>
<body>
    <div class="layout" style="padding: 2rem; max-width: 1000px; margin: auto;">
        <h1>📚 Publicaciones</h1>

        <!-- FILTROS -->
        <div class="grid-2" style="margin-bottom: 2rem;">
            <div>
                <label>Categoría:</label>
                <select id="filtro-categoria" class="input">
                    <option value="">Todas</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label>Subcategoría:</label>
                <select id="filtro-subcategoria" class="input" disabled>
                    <option value="">Todas</option>
                </select>
            </div>
        </div>

        <!-- PUBLICACIONES -->
        <div id="contenedor-publicaciones" class="card-grid grid-2">
            <!-- Aquí se cargan dinámicamente -->
        </div>
    </div>

    <script>
        const filtroCategoria = document.getElementById('filtro-categoria');
        const filtroSubcategoria = document.getElementById('filtro-subcategoria');
        const contenedor = document.getElementById('contenedor-publicaciones');

        filtroCategoria.addEventListener('change', () => {
            const catId = filtroCategoria.value;
            filtroSubcategoria.disabled = true;
            filtroSubcategoria.innerHTML = '<option value="">Cargando...</option>';

            if (!catId) {
                filtroSubcategoria.innerHTML = '<option value="">Todas</option>';
                filtroSubcategoria.disabled = true;
                cargarPublicaciones();
                return;
            }

            fetch(`../../controllers/sve_publicacionesController.php?action=get_subcategorias&categoria_id=${catId}`)
                .then(r => r.json())
                .then(subs => {
                    filtroSubcategoria.innerHTML = '<option value="">Todas</option>';
                    subs.forEach(sub => {
                        const opt = document.createElement('option');
                        opt.value = sub.id;
                        opt.textContent = sub.nombre;
                        filtroSubcategoria.appendChild(opt);
                    });
                    filtroSubcategoria.disabled = false;
                    cargarPublicaciones();
                });
        });

        filtroSubcategoria.addEventListener('change', cargarPublicaciones);

        function cargarPublicaciones() {
            const cat = filtroCategoria.value;
            const subcat = filtroSubcategoria.value;

            const params = new URLSearchParams();
            params.append('action', 'get_publicaciones');
            if (cat) params.append('categoria_id', cat);
            if (subcat) params.append('subcategoria_id', subcat);

            fetch(`../../controllers/sve_publicacionesController.php?${params.toString()}`)
                .then(r => r.json())
                .then(data => {
                    contenedor.innerHTML = '';

                    if (!data.length) {
                        contenedor.innerHTML = '<p>No se encontraron publicaciones.</p>';
                        return;
                    }

                    data.forEach(pub => {
                        const card = document.createElement('div');
                        card.classList.add('card');

                        card.innerHTML = `
                            <h3>${pub.titulo}</h3>
                            <p><strong>${pub.autor}</strong> · ${pub.fecha_publicacion}</p>
                            <p class="muted">${pub.categoria} > ${pub.subcategoria}</p>
                            <p>${pub.descripcion.slice(0, 150)}...</p>
                            <div style="margin-top: 8px;">
                                ${pub.archivo
                                    ? `<a href="../../uploads/publications/${pub.archivo}" class="btn" target="_blank">Ver archivo</a>`
                                    : '<span class="muted">Sin archivo</span>'}
                            </div>
                        `;
                        contenedor.appendChild(card);
                    });
                });
        }

        // Inicializar
        cargarPublicaciones();
    </script>
</body>
</html>
