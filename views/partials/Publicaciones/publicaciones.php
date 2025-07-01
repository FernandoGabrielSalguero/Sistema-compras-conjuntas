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
        }
        header {
            background: #fff;
            padding: 1rem;
            text-align: center;
            font-weight: bold;
            font-size: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .layout {
            display: flex;
            max-width: 1300px;
            margin: auto;
            padding: 2rem;
            gap: 2rem;
        }
        aside {
            width: 250px;
            background: #fff;
            border-radius: 12px;
            padding: 1rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .sidebar h3 {
            margin-bottom: 1rem;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
        }
        .sidebar li {
            margin-bottom: 8px;
            cursor: pointer;
            color: #555;
        }
        .sidebar li:hover {
            color: var(--primary);
        }
        main {
            flex-grow: 1;
        }
        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 1.5rem;
        }
        .card {
            background: #fff;
            padding: 1rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .card h4 {
            margin-bottom: 0.25rem;
        }
        .card p {
            margin: 0.25rem 0;
        }
        .modal .body {
            white-space: pre-wrap;
        }
    </style>
</head>
<body>
    <header>SVE</header>
    <div class="layout">
        <aside class="sidebar">
            <h3>Categorías</h3>
            <ul id="lista-categorias"></ul>
        </aside>
        <main>
            <div id="contenedor-publicaciones" class="card-grid"></div>
        </main>
    </div>

    <div class="modal" id="modal-publicacion">
        <div class="modal-overlay" onclick="cerrarModal()"></div>
        <div class="modal-content">
            <div class="header">
                <h3 id="modal-titulo"></h3>
                <button class="close" onclick="cerrarModal()">&times;</button>
            </div>
            <div class="body" id="modal-contenido"></div>
            <div class="footer" id="modal-footer"></div>
        </div>
    </div>

    <script>
        let publicaciones = [];
        let categorias = <?= json_encode($categorias) ?>;

        const listaCategorias = document.getElementById('lista-categorias');
        const contenedor = document.getElementById('contenedor-publicaciones');

        function cerrarModal() {
            document.getElementById('modal-publicacion').classList.remove('show');
        }

        function abrirModal(pub) {
            document.getElementById('modal-titulo').textContent = pub.titulo;
            document.getElementById('modal-contenido').innerHTML = `
                <p><strong>${pub.autor}</strong> · ${pub.fecha_publicacion}</p>
                <p><em>${pub.subtitulo || ''}</em></p>
                <p><strong>${pub.categoria} > ${pub.subcategoria}</strong></p>
                <p>${pub.descripcion}</p>
            `;
            document.getElementById('modal-footer').innerHTML = pub.archivo 
                ? `<a href="../../uploads/publications/${pub.archivo}" class="btn" target="_blank">Descargar archivo</a>`
                : '<span class="muted">Sin archivo</span>';
            document.getElementById('modal-publicacion').classList.add('show');
        }

        function renderPublicaciones(lista) {
            contenedor.innerHTML = '';
            if (!lista.length) {
                contenedor.innerHTML = '<p class="muted">No se encontraron publicaciones.</p>';
                return;
            }
            lista.forEach(pub => {
                const card = document.createElement('div');
                card.className = 'card';
                card.innerHTML = `
                    <div>
                        <h4>${pub.titulo}</h4>
                        <p class="muted">${pub.subtitulo || ''}</p>
                        <p class="muted">${pub.categoria} > ${pub.subcategoria}</p>
                        <p>${pub.descripcion.slice(0, 100)}...</p>
                    </div>
                    <button class="btn btn-outline" onclick='abrirModal(${JSON.stringify(pub)})'>Leer</button>
                `;
                contenedor.appendChild(card);
            });
        }

        function cargarCategorias() {
            listaCategorias.innerHTML = '';
            categorias.forEach(cat => {
                const li = document.createElement('li');
                li.textContent = cat.nombre;
                li.onclick = () => {
                    fetch(`../../controllers/sve_publicacionesController.php?action=get_subcategorias&categoria_id=${cat.id}`)
                        .then(r => r.json())
                        .then(subs => {
                            listaCategorias.innerHTML = '';
                            const back = document.createElement('li');
                            back.textContent = '← Volver';
                            back.style.fontWeight = 'bold';
                            back.onclick = cargarCategorias;
                            listaCategorias.appendChild(back);
                            subs.forEach(sub => {
                                const subLi = document.createElement('li');
                                subLi.textContent = sub.nombre;
                                subLi.style.paddingLeft = '1rem';
                                subLi.style.color = 'purple';
                                subLi.onclick = () => filtrar(cat.id, sub.id);
                                listaCategorias.appendChild(subLi);
                            });
                        });
                    filtrar(cat.id);
                }
                listaCategorias.appendChild(li);
            });
        }

        function filtrar(categoriaId, subcategoriaId = '') {
            let filtradas = publicaciones.filter(p => p.categoria_id == categoriaId);
            if (subcategoriaId) {
                filtradas = filtradas.filter(p => p.subcategoria_id == subcategoriaId);
            }
            renderPublicaciones(filtradas);
        }

        function cargarPublicaciones() {
            fetch('../../controllers/sve_publicacionesController.php?action=get_publicaciones')
                .then(r => r.json())
                .then(data => {
                    publicaciones = data;
                    renderPublicaciones(publicaciones);
                    cargarCategorias();
                });
        }

        cargarPublicaciones();
    </script>
</body>
</html>
