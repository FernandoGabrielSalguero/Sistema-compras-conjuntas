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
            font-family: 'Segoe UI', sans-serif;
        }

        .layout {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        .sidebar {
            width: 240px;
            background-color: #fff;
            border-right: 1px solid #eee;
            overflow-y: auto;
            padding-top: 1rem;
        }

        .main {
            flex: 1;
            overflow-y: auto;
            background: #f9f9f9;
            padding: 2rem;
        }

        .accordion-toggle {
            background: none;
            border: none;
            width: 100%;
            cursor: pointer;
            padding: 0.3rem 0;
            color: #333;
            font-size: 0.95rem;
            text-align: left;
            transition: color 0.2s;
        }

        .accordion-toggle:hover {
            color: #6c5ce7;
        }

        .accordion-content {
            padding-left: 1rem;
            margin-top: 0.3rem;
        }

        .accordion-content.hidden {
            display: none;
        }

        .subcat-link {
            background: none;
            border: none;
            color: #6c5ce7;
            font-size: 0.9rem;
            padding: 0.2rem 0;
            text-align: left;
            width: 100%;
            cursor: pointer;
            transition: color 0.2s;
        }

        .subcat-link:hover {
            color: #5943d2;
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

        @media (max-width: 768px) {
            .layout {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                border-right: none;
                border-bottom: 1px solid #eee;
            }

            .main {
                padding: 1rem;
            }

            .grid {
                grid-template-columns: 1fr;
            }

            .card {
                margin-bottom: 1rem;
            }
        }
    </style>
</head>

<body>
    <div class="layout">
        <aside class="sidebar">
            <h3 class="px-3 py-2 text-lg font-semibold border-b">Categorías</h3>
            <div class="menu px-3" id="menu-categorias">
                <?php foreach ($categorias as $cat): ?>
                    <div class="accordion-item py-2 border-b">
                        <button class="accordion-toggle" data-cat="<?= $cat['id'] ?>">
                            <?= htmlspecialchars($cat['nombre']) ?>
                        </button>
                        <div class="accordion-content hidden" id="subcat-<?= $cat['id'] ?>">
                            <p class="muted px-2">Cargando...</p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </aside>

        <main class="main">
            <h1 class="text-xl font-bold mb-4">Publicaciones</h1>
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
        const modal = document.getElementById('modal-lectura');

        document.querySelectorAll('.accordion-toggle').forEach(btn => {
            btn.addEventListener('click', async () => {
                const catId = btn.dataset.cat;
                const content = document.getElementById(`subcat-${catId}`);
                content.classList.toggle('hidden');
                if (content.dataset.loaded === "1") return;

                try {
                    const res = await fetch(`../../controllers/sve_publicacionesController.php?action=get_subcategorias&categoria_id=${catId}`);
                    const data = await res.json();

                    content.innerHTML = '';
                    data.forEach(sub => {
                        const subBtn = document.createElement('button');
                        subBtn.className = 'subcat-link';
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
            const params = new URLSearchParams({ action: 'get_publicaciones' });
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

        cargarPublicaciones();
    </script>
</body>
</html>
