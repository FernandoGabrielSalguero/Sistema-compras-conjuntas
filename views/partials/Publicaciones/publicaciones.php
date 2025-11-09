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
    <link rel="stylesheet" href="https://framework.impulsagroup.com/assets/css/framework.css">
    <script src="https://framework.impulsagroup.com/assets/javascript/framework.js" defer></script>

    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
        }

        /* HEADER */
        header {
            background-color: #fff;
            margin: 1rem;
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: bold;
            font-size: 1.3rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            z-index: 10;
            position: relative;
        }

        .menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.4rem;
            cursor: pointer;
        }

        /* LAYOUT */
        .layout {
            display: flex;
            gap: 1rem;
            padding: 0 1rem 1rem;
            margin-top: 0;
        }

        /* SIDEBAR */
        .sidebar {
            width: 250px;
            background: #fff;
            border-right: 1px solid #eee;
            overflow-y: auto;
            padding: 1rem 1rem 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 1rem;
            height: fit-content;
            z-index: 1;
        }

        .sidebar.hidden {
            transform: translateX(-100%);
        }

        /* MAIN */
        .main {
            flex: 1;
            overflow-y: auto;
            background: #f9f9f9;
            padding: 2rem 1rem;
            margin: 0;
            /* Asegura que no se centre automáticamente */
            max-width: 100%;
            /* Rompe cualquier centrado o limitación de ancho */
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }



        /* ACCORDION */
        .accordion-item {
            margin-bottom: 1rem;
        }

        .accordion-toggle {
            background: none;
            border: none;
            font-weight: 600;
            font-size: 0.95rem;
            width: 100%;
            text-align: left;
            padding: 0.4rem 0;
            text-transform: uppercase;
            color: #333;
            border-bottom: 1px solid #eee;
            transition: all 0.2s ease;
        }

        .accordion-toggle:hover {
            color: #6c5ce7;
        }

        .accordion-content {
            padding-left: 1rem;
            margin-top: 0.5rem;
        }

        .accordion-content.hidden {
            display: none;
        }

        /* SUBCATEGORÍA LINK */
        .subcat-link {
            background: none;
            border: none;
            color: #6c5ce7;
            font-size: 0.9rem;
            padding: 0.3rem 0;
            text-align: left;
            width: 100%;
            display: block;
            border-radius: 6px;
            transition: background 0.2s, color 0.2s;
        }

        .subcat-link:hover {
            background: #f2f2ff;
            color: #5943d2;
        }

        /* GRID Y CARD */
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

        /* RESPONSIVE (MOBILE) */
        @media (max-width: 768px) {
            .layout {
                flex-direction: column;
                height: auto;
                gap: 0;
            }

            .sidebar {
                position: absolute;
                top: 70px;
                left: 0;
                width: 220px;
                height: calc(100vh - 70px);
                z-index: 15;
                transform: translateX(-100%);
                box-shadow: 2px 0 4px rgba(0, 0, 0, 0.05);
                border-radius: 0;
            }

            .sidebar.visible {
                transform: translateX(0);
            }

            .menu-toggle {
                display: block;
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
    <header>
        <span>SVE</span>
        <button class="menu-toggle" onclick="document.querySelector('.sidebar').classList.toggle('visible')">☰</button>
    </header>

    <div class="layout">
        <aside class="sidebar">
            <h3 class="text-md font-semibold mb-2">Categorías</h3>
            <div class="menu" id="menu-categorias">
                <?php foreach ($categorias as $cat): ?>
                    <div class="accordion-item">
                        <button class="accordion-toggle" data-cat="<?= $cat['id'] ?>">
                            <?= htmlspecialchars($cat['nombre']) ?>
                        </button>
                        <div class="accordion-content hidden" id="subcat-<?= $cat['id'] ?>">
                            <p class="muted">Cargando...</p>
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

    <!-- Modal lectura del framework -->
    <div class="modal hidden" id="modal-lectura">
        <div class="modal-content">
            <h3 id="modal-titulo" class="modal-title">Título de la publicación</h3>
            <p id="modal-subtitulo" class="muted"></p>
            <p id="modal-cat-subcat" class="muted"></p>
            <p id="modal-autor-fecha" class="muted"></p>
            <p id="modal-descripcion" class="my-2"></p>
            <a id="modal-archivo" href="#" target="_blank" class="btn btn-info mb-4">Descargar archivo</a>

            <div class="form-buttons">
                <button class="btn btn-aceptar" onclick="Framework.closeModal('modal-lectura')">Aceptar</button>
                <button class="btn btn-cancelar" onclick="Framework.closeModal('modal-lectura')">Cancelar</button>
            </div>
        </div>
    </div>

    <script>
        const contenedor = document.getElementById('contenedor-publicaciones');
        const modal = document.getElementById('modal-lectura');

        // Definir Framework solo si no existe
        if (typeof Framework === 'undefined') {
            window.Framework = {
                openModal(id) {
                    const modal = document.getElementById(id);
                    if (modal) modal.classList.remove('hidden');
                },
                closeModal(id) {
                    const modal = document.getElementById(id);
                    if (modal) modal.classList.add('hidden');
                }
            };
        }

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

            // 1. Incrementar vistas
            fetch(`../../controllers/sve_publicacionesController.php?action=incrementar_vista&id=${id}`);

            document.getElementById('modal-titulo').textContent = pub.titulo;
            document.getElementById('modal-subtitulo').textContent = pub.subtitulo || '';
            document.getElementById('modal-cat-subcat').textContent = `${pub.categoria} > ${pub.subcategoria}`;
            document.getElementById('modal-autor-fecha').textContent = `${pub.autor} · ${pub.fecha_publicacion}`;
            document.getElementById('modal-descripcion').textContent = pub.descripcion || '';

            const archivoBtn = document.getElementById('modal-archivo');

            if (pub.archivo) {
                archivoBtn.href = `../../uploads/publications/${pub.archivo}`;
                archivoBtn.style.display = 'inline-block';

                // 2. Incrementar descargas al hacer clic
                archivoBtn.onclick = () => {
                    fetch(`../../controllers/sve_publicacionesController.php?action=incrementar_descarga&id=${id}`);
                };

            } else {
                archivoBtn.style.display = 'none';
                archivoBtn.onclick = null;
            }

            Framework.openModal('modal-lectura');
        }

        cargarPublicaciones();
    </script>
</body>

</html>