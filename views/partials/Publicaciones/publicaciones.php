<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../../config.php';

try {
    $stmt = $pdo->query("SELECT id, nombre FROM categorias_publicaciones ORDER BY nombre");
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al obtener categorías: " . $e->getMessage());
}
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
<body style="background-color: #f9f9f9;">
    <div class="layout" style="padding: 3rem 1.5rem; max-width: 1100px; margin: auto;">
        <h1 style="font-size: 2rem; font-weight: 600; margin-bottom: 2rem; display: flex; align-items: center; gap: 0.5rem;">
            📚 Publicaciones
        </h1>

        <!-- FILTROS -->
        <div class="grid-3" style="margin-bottom: 2rem; gap: 1rem;">
            <div>
                <label class="muted">Categoría:</label>
                <select id="filtro-categoria" class="input">
                    <option value="">Todas</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="muted">Subcategoría:</label>
                <select id="filtro-subcategoria" class="input" disabled>
                    <option value="">Todas</option>
                </select>
            </div>
            <div>
                <label class="muted">Buscar:</label>
                <input type="text" id="filtro-busqueda" class="input" placeholder="Buscar publicación...">
            </div>
        </div>

        <!-- CONTENEDOR DE PUBLICACIONES -->
        <div id="contenedor-publicaciones" class="grid-2" style="gap: 1.5rem;">
            <!-- Carga dinámica JS -->
        </div>
    </div>

    <script>
        const filtroCategoria = document.getElementById('filtro-categoria');
        const filtroSubcategoria = document.getElementById('filtro-subcategoria');
        const filtroBusqueda = document.getElementById('filtro-busqueda');
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
        filtroBusqueda.addEventListener('input', () => {
            clearTimeout(filtroBusqueda._timeout);
            filtroBusqueda._timeout = setTimeout(cargarPublicaciones, 300);
        });

        function cargarPublicaciones() {
            const cat = filtroCategoria.value;
            const subcat = filtroSubcategoria.value;
            const search = filtroBusqueda.value.trim();

            const params = new URLSearchParams();
            params.append('action', 'get_publicaciones');
            if (cat) params.append('categoria_id', cat);
            if (subcat) params.append('subcategoria_id', subcat);
            if (search) params.append('search', search);

            fetch(`../../controllers/sve_publicacionesController.php?${params.toString()}`)
                .then(r => r.json())
                .then(data => {
                    contenedor.innerHTML = '';

                    if (!data.length) {
                        contenedor.innerHTML = '<p class="muted">No se encontraron publicaciones.</p>';
                        return;
                    }

                    data.forEach(pub => {
                        const card = document.createElement('div');
                        card.classList.add('card');

                        card.innerHTML = `
                            <div style="display: flex; flex-direction: column; height: 100%;">
                                <div style="flex-grow: 1;">
                                    <h3 style="margin-bottom: 0.5rem;">${pub.titulo}</h3>
                                    <p style="margin: 0 0 0.5rem 0; font-weight: 500;">${pub.autor} · <span class="muted">${pub.fecha_publicacion}</span></p>
                                    <p class="muted" style="margin: 0 0 0.5rem 0;">${pub.categoria} &gt; ${pub.subcategoria}</p>
                                    <p style="font-size: 0.95rem; color: #444;">${pub.descripcion?.slice(0, 150) || ''}...</p>
                                </div>
                                <div style="margin-top: 1rem;">
                                    ${pub.archivo
                                        ? `<a href="../../uploads/publications/${pub.archivo}" class="btn btn-outline full-width" target="_blank">Ver archivo</a>`
                                        : '<span class="muted">Sin archivo</span>'}
                                </div>
                            </div>
                        `;
                        contenedor.appendChild(card);
                    });
                });
        }

        cargarPublicaciones();
    </script>
</body>
</html>
