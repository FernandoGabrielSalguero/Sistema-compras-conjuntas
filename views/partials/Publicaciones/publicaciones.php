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
        body {
            background-color: #f9f9f9;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
        }

        header {
            background: #fff;
            padding: 1rem 2rem;
            text-align: center;
            font-size: 1.8rem;
            font-weight: bold;
            box-shadow: 0 1px 5px rgba(0,0,0,0.1);
        }

        .layout-container {
            display: flex;
            flex: 1;
            max-width: 1200px;
            margin: auto;
            padding: 2rem;
        }

        aside {
            width: 200px;
            padding-right: 2rem;
            border-right: 1px solid #eee;
        }

        aside h3 {
            margin-bottom: 1rem;
        }

        aside .categoria, aside .subcategoria {
            cursor: pointer;
            padding: 0.3rem 0;
            transition: all 0.2s;
        }

        aside .categoria:hover,
        aside .subcategoria:hover {
            color: #4b0082;
        }

        .publicaciones {
            flex: 1;
            padding-left: 2rem;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .card {
            border-radius: 8px;
            padding: 1rem;
            background: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .card h3 {
            font-size: 1.1rem;
            margin-bottom: 0.2rem;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0,0,0,0.6);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal.activo {
            display: flex;
        }

        .modal .card {
            max-width: 600px;
            width: 90%;
            padding: 2rem;
            position: relative;
        }

        .modal .close {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 1.2rem;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <header>SVE</header>

    <div class="layout-container">
        <!-- Menú lateral -->
        <aside>
            <h3>Categorías</h3>
            <div id="menu-categorias">
                <?php foreach ($categorias as $cat): ?>
                    <div class="categoria" data-id="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['nombre']) ?></div>
                <?php endforeach; ?>
            </div>
            <div id="menu-subcategorias" style="margin-top: 1rem;"></div>
        </aside>

        <!-- Contenido -->
        <div class="publicaciones" id="contenedor-publicaciones"></div>
    </div>

    <!-- Modal -->
    <div id="modal-publicacion" class="modal">
        <div class="card">
            <div class="close" onclick="cerrarModal()">✖</div>
            <h2 id="modal-titulo"></h2>
            <h4 id="modal-subtitulo" class="muted"></h4>
            <p id="modal-categoria" class="muted"></p>
            <p id="modal-autor-fecha" class="muted"></p>
            <p id="modal-descripcion" style="margin-top: 1rem;"></p>
            <div id="modal-archivo" style="margin-top: 1rem;"></div>
        </div>
    </div>

    <script>
        const publicacionesContenedor = document.getElementById('contenedor-publicaciones');
        const menuCategorias = document.getElementById('menu-categorias');
        const menuSubcategorias = document.getElementById('menu-subcategorias');

        let categoriaSeleccionada = null;
        let subcategoriaSeleccionada = null;

        menuCategorias.addEventListener('click', e => {
            if (e.target.classList.contains('categoria')) {
                categoriaSeleccionada = e.target.dataset.id;
                subcategoriaSeleccionada = null;
                cargarSubcategorias(categoriaSeleccionada);
                cargarPublicaciones();
            }
        });

        menuSubcategorias.addEventListener('click', e => {
            if (e.target.classList.contains('subcategoria')) {
                subcategoriaSeleccionada = e.target.dataset.id;
                cargarPublicaciones();
            }
        });

        function cargarSubcategorias(catId) {
            menuSubcategorias.innerHTML = '';
            fetch(`../../controllers/sve_publicacionesController.php?action=get_subcategorias&categoria_id=${catId}`)
                .then(res => res.json())
                .then(data => {
                    data.forEach(sub => {
                        const div = document.createElement('div');
                        div.classList.add('subcategoria');
                        div.dataset.id = sub.id;
                        div.textContent = '↳ ' + sub.nombre;
                        menuSubcategorias.appendChild(div);
                    });
                });
        }

        function cargarPublicaciones() {
            const params = new URLSearchParams();
            params.append('action', 'get_publicaciones');
            if (categoriaSeleccionada) params.append('categoria_id', categoriaSeleccionada);
            if (subcategoriaSeleccionada) params.append('subcategoria_id', subcategoriaSeleccionada);

            fetch(`../../controllers/sve_publicacionesController.php?${params.toString()}`)
                .then(res => res.json())
                .then(data => {
                    publicacionesContenedor.innerHTML = '';

                    if (!data.length) {
                        publicacionesContenedor.innerHTML = '<p class="muted">No se encontraron publicaciones.</p>';
                        return;
                    }

                    data.forEach(pub => {
                        const card = document.createElement('div');
                        card.className = 'card';
                        card.innerHTML = `
                            <h3>${pub.titulo}</h3>
                            <p class="muted">${pub.subtitulo || ''}</p>
                            <p class="muted">${pub.categoria} > ${pub.subcategoria}</p>
                            <p style="font-size: 0.9rem; color: #444;">${pub.descripcion.slice(0, 100)}...</p>
                            <div style="margin-top: auto;">
                                <button class="btn btn-outline full-width" onclick='abrirModal(${JSON.stringify(pub)})'>Leer</button>
                            </div>
                        `;
                        publicacionesContenedor.appendChild(card);
                    });
                });
        }

        function abrirModal(pub) {
            document.getElementById('modal-titulo').textContent = pub.titulo;
            document.getElementById('modal-subtitulo').textContent = pub.subtitulo || '';
            document.getElementById('modal-categoria').textContent = `${pub.categoria} > ${pub.subcategoria}`;
            document.getElementById('modal-autor-fecha').textContent = `${pub.autor} · ${pub.fecha_publicacion}`;
            document.getElementById('modal-descripcion').textContent = pub.descripcion;

            const archivoDiv = document.getElementById('modal-archivo');
            archivoDiv.innerHTML = pub.archivo
                ? `<a class="btn" href="../../uploads/publications/${pub.archivo}" target="_blank">Descargar archivo</a>`
                : `<span class="muted">Sin archivo</span>`;

            document.getElementById('modal-publicacion').classList.add('activo');
        }

        function cerrarModal() {
            document.getElementById('modal-publicacion').classList.remove('activo');
        }

        // Inicial
        cargarPublicaciones();
    </script>
</body>
</html>
