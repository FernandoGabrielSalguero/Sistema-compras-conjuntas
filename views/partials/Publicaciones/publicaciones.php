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
            margin: 0;
            background-color: #f9f9f9;
            font-family: 'Segoe UI', sans-serif;
        }

        header {
            background-color: #fff;
            padding: 1rem;
            text-align: center;
            font-size: 1.5rem;
            font-weight: bold;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .container {
            display: flex;
            flex-wrap: nowrap;
            min-height: calc(100vh - 70px);
            width: 100%;
        }

        .sidebar {
            width: 240px;
            flex-shrink: 0;
        }

        .sidebar h3 {
            margin-top: 0;
            font-size: 1.1rem;
            margin-bottom: 1rem;
        }

        .sidebar ul {
            list-style: none;
            padding-left: 0;
        }

        .sidebar li {
            padding: 0.3rem 0;
            cursor: pointer;
            transition: color 0.2s;
        }

        .sidebar li:hover {
            color: #6c5ce7;
        }

        .sidebar .sub {
            margin-left: 1rem;
            color: #6c5ce7;
            font-size: 0.95rem;
        }

        .content {
            flex: 1;
            width: 100%;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .card {
            background: #fff;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .card h3 {
            margin: 0;
            font-size: 1rem;
        }

        .card .muted {
            color: #999;
            font-size: 0.9rem;
        }

        .card .btn {
            margin-top: auto;
            background: #6c5ce7;
            color: #fff;
            border: none;
            padding: 0.6rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            width: 100%;
            transition: background 0.2s;
        }

        .card .btn:hover {
            background: #5943d2;
        }

        .modal-content {
            max-width: 600px;
            padding: 2rem;
        }

        .modal-content h2 {
            margin-top: 0;
        }

        .modal-content .muted {
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .modal-content a {
            margin-top: 1.5rem;
        }

        .hidden {
            display: none;
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                border-right: none;
                border-bottom: 1px solid #eee;
            }
        }

        @media (max-width: 768px) {
            .container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                border-right: none;
                border-bottom: 1px solid #eee;
            }

            .content {
                width: 100%;
                padding: 1rem;
            }

            .grid {
                grid-template-columns: repeat(auto-fill, minmax(100%, 1fr));
            }

            .card {
                margin-bottom: 1rem;
            }
        }

        .accordion-item {
            margin-bottom: 0.5rem;
        }

        .accordion-toggle {
            background: none;
            border: none;
            font-weight: 500;
            font-size: 1rem;
            width: 100%;
            text-align: left;
            padding: 0.4rem 0;
            cursor: pointer;
            border-bottom: 1px solid #eee;
            transition: color 0.2s;
        }

        .accordion-toggle:hover {
            color: #6c5ce7;
        }

        .accordion-content {
            display: none;
            padding-left: 0.75rem;
            padding-top: 0.3rem;
        }

        .accordion-content.visible {
            display: block;
        }

        .accordion-content button {
            display: block;
            width: 100%;
            background: none;
            border: none;
            color: #6c5ce7;
            text-align: left;
            padding: 0.3rem 0;
            font-size: 0.95rem;
            cursor: pointer;
            transition: color 0.2s;
        }

        .accordion-content button:hover {
            color: #5943d2;
        }

        .subcat-link {
            background: none;
            border: none;
            color: #6c5ce7;
            padding: 0.3rem 0;
            font-size: 0.95rem;
            text-align: left;
            width: 100%;
            cursor: pointer;
            transition: color 0.2s ease;
        }

        .subcat-link:hover {
            color: #5943d2;
        }
    </style>
</head>

<body>
    <header>SVE</header>
    <div class="container">
        <aside class="sidebar">
            <h3>Categorías</h3>
            <div class="accordion" id="menu-categorias">
                <?php foreach ($categorias as $cat): ?>
                    <div class="accordion-item">
                        <button class="accordion-toggle" data-cat="<?= $cat['id'] ?>">
                            <?= htmlspecialchars($cat['nombre']) ?>
                        </button>
                        <div class="accordion-content" id="subcat-<?= $cat['id'] ?>">
                            <p class="muted" style="padding: 0.5rem;">Cargando...</p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </aside>
        <main class="content">
            <div class="grid" id="contenedor-publicaciones"></div>
        </main>
    </div>

    <!-- Modal -->
    <dialog id="modal-lectura">
        <div class="modal-content">
            <button class="btn-close" onclick="modal.close()" style="float:right;">✖</button>
            <h2 id="modal-titulo"></h2>
            <p class="muted" id="modal-subtitulo"></p>
            <p class="muted" id="modal-cat-subcat"></p>
            <p class="muted" id="modal-autor-fecha"></p>
            <p id="modal-descripcion"></p>
            <a id="modal-archivo" href="#" target="_blank" class="btn">Descargar archivo</a>
        </div>
    </dialog>

    <script>
        const contenedor = document.getElementById('contenedor-publicaciones');
        const menuCategorias = document.getElementById('menu-categorias');
        const modal = document.getElementById('modal-lectura');

        document.querySelectorAll('.accordion-toggle').forEach(btn => {
            btn.addEventListener('click', async () => {
                const catId = btn.dataset.cat;
                const content = document.getElementById(`subcat-${catId}`);

                // Alternar clase visible
                content.classList.toggle('visible');

                // Si ya cargó, no vuelve a pedir
                if (content.dataset.loaded === "1") return;

                try {
                    const res = await fetch(`../../controllers/sve_publicacionesController.php?action=get_subcategorias&categoria_id=${catId}`);
                    const data = await res.json();

                    content.innerHTML = '';

                    data.forEach(sub => {
                        const subBtn = document.createElement('button');
                        subBtn.className = 'subcat-link';
                        subBtn.style.marginBottom = '0.5rem';
                        subBtn.textContent = sub.nombre;
                        subBtn.onclick = () => cargarPublicaciones(catId, sub.id);
                        content.appendChild(subBtn);
                    });

                    content.dataset.loaded = "1";
                } catch (e) {
                    content.innerHTML = '<p class="muted">Error al cargar.</p>';
                }
            });
        });

        let publicaciones = [];

        function cargarPublicaciones(categoria_id = '', subcategoria_id = '') {
            const params = new URLSearchParams({
                action: 'get_publicaciones'
            });
            if (categoria_id) params.append('categoria_id', categoria_id);
            if (subcategoria_id) params.append('subcategoria_id', subcategoria_id);

            fetch(`../../controllers/sve_publicacionesController.php?${params.toString()}`)
                .then(res => res.json())
                .then(data => {
                    publicaciones = data;
                    renderPublicaciones();
                });
        }

        function renderPublicaciones() {
            contenedor.innerHTML = '';

            if (!publicaciones.length) {
                contenedor.innerHTML = '<p class="muted">No se encontraron publicaciones.</p>';
                return;
            }

            publicaciones.forEach(pub => {
                const card = document.createElement('div');
                card.classList.add('card');

                card.innerHTML = `
                    <div>
                        <h3>${pub.titulo}</h3>
                        <p class="muted">${pub.subtitulo || ''}</p>
                        <p class="muted">${pub.categoria} > ${pub.subcategoria}</p>
                        <p style="font-size: 0.95rem; color: #444;">${pub.descripcion?.slice(0, 120)}...</p>
                    </div>
                    <button class="btn" onclick="abrirModal(${pub.id})">Leer</button>
                `;

                contenedor.appendChild(card);
            });
        }

        function abrirModal(id) {
            const pub = publicaciones.find(p => p.id == id);
            if (!pub) return;

            document.getElementById('modal-titulo').textContent = pub.titulo;
            document.getElementById('modal-subtitulo').textContent = pub.subtitulo || '';
            document.getElementById('modal-cat-subcat').textContent = `${pub.categoria} > ${pub.subcategoria}`;
            document.getElementById('modal-autor-fecha').textContent = `${pub.autor} · ${pub.fecha_publicacion}`;
            document.getElementById('modal-descripcion').textContent = pub.descripcion || '';
            const archivoBtn = document.getElementById('modal-archivo');

            if (pub.archivo) {
                archivoBtn.href = `../../uploads/publications/${pub.archivo}`;
                archivoBtn.style.display = 'inline-block';
            } else {
                archivoBtn.style.display = 'none';
            }

            modal.showModal();
        }

        // Cargar subcategorías dinámicamente
        document.querySelectorAll('#menu-categorias li[data-categoria]').forEach(cat => {
            cat.addEventListener('click', () => {
                const categoriaId = cat.dataset.categoria;
                cargarPublicaciones(categoriaId);

                fetch(`../../controllers/sve_publicacionesController.php?action=get_subcategorias&categoria_id=${categoriaId}`)
                    .then(r => r.json())
                    .then(data => {
                        const ul = document.getElementById(`subcat-${categoriaId}`);
                        ul.innerHTML = '';
                        data.forEach(sub => {
                            const li = document.createElement('li');
                            li.classList.add('sub');
                            li.textContent = `↳ ${sub.nombre}`;
                            li.onclick = () => cargarPublicaciones(categoriaId, sub.id);
                            ul.appendChild(li);
                        });
                    });
            });
        });

        cargarPublicaciones();
    </script>
</body>

</html>