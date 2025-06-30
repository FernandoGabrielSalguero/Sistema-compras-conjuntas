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

    <style>
        .filtros-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            justify-content: flex-end;
            margin-bottom: 2rem;
            align-items: center;
        }
        .filtros-bar label {
            font-size: 0.9rem;
            margin-right: 0.5rem;
            color: #666;
        }
        .card h3 {
            margin-bottom: 0.5rem;
        }
        .card p {
            margin: 0 0 0.5rem;
        }
        @media (max-width: 768px) {
            .filtros-bar {
                justify-content: center;
            }
        }
    </style>
</head>
<body style="background-color: #f9f9f9;">
    <div class="layout" style="padding: 2rem; max-width: 1100px; margin: auto;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
            <h1 style="font-size: 2rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem;">
                📚 Publicaciones
            </h1>
            <div class="filtros-bar">
                <div>
                    <label for="filtro-categoria">Categoría:</label>
                    <select id="filtro-categoria" class="input">
                        <option value="">Todas</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="filtro-subcategoria">Subcategoría:</label>
                    <select id="filtro-subcategoria" class="input" disabled>
                        <option value="">Todas</option>
                    </select>
                </div>
            </div>
        </div>

        <div id="contenedor-publicaciones" class="grid-2" style="gap: 1.5rem;">
            <!-- Publicaciones cargadas por JS -->
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
                        contenedor.innerHTML = '<p class="muted">No se encontraron publicaciones.</p>';
                        return;
                    }

                    data.forEach(pub => {
                        const card = document.createElement('div');
                        card.classList.add('card');

                        card.innerHTML = `
                            <div style="display: flex; flex-direction: column; height: 100%;">
                                <div style="flex-grow: 1;">
                                    <h3>${pub.titulo}</h3>
                                    <p><strong>${pub.autor}</strong> · <span class="muted">${pub.fecha_publicacion}</span></p>
                                    <p class="muted">${pub.categoria} > ${pub.subcategoria}</p>
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
