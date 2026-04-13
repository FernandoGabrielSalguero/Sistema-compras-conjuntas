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

    <link rel="stylesheet" href="https://framework.impulsagroup.com/assets/css/framework.css">
    <script src="https://framework.impulsagroup.com/assets/javascript/framework.js" defer></script>

    <style>
        :root {
            --bg: #f7f7f4;
            --surface: rgba(255, 255, 255, 0.92);
            --surface-solid: #ffffff;
            --surface-muted: #f2f1eb;
            --border: rgba(26, 26, 26, 0.08);
            --border-strong: rgba(26, 26, 26, 0.14);
            --text: #171717;
            --muted: #6d6d68;
            --muted-soft: #8a8a84;
            --accent: #17324d;
            --accent-soft: #e7eef5;
            --accent-strong: #10263b;
            --shadow: 0 18px 45px rgba(23, 34, 45, 0.08);
            --radius-xl: 28px;
            --radius-lg: 22px;
            --radius-md: 16px;
            --radius-sm: 12px;
            --transition: 180ms ease;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Segoe UI', sans-serif;
            color: var(--text);
            overflow-x: hidden;
            background:
                radial-gradient(circle at top left, rgba(197, 210, 221, 0.28), transparent 28%),
                radial-gradient(circle at top right, rgba(229, 224, 215, 0.35), transparent 22%),
                linear-gradient(180deg, #ffffff 0%, var(--bg) 100%);
        }

        .page-container {
            width: min(1380px, calc(100vw - 2rem));
            margin: 0 auto;
        }

        .brand-logo {
            width: 76px;
            height: 76px;
            object-fit: contain;
            border-radius: 0;
            background: transparent;
            box-shadow: none;
            padding: 0;
        }

        .layout {
            display: block !important;
            width: 100%;
            padding: 1.1rem 0 2rem;
            margin: 0;
        }

        .sidebar {
            display: none;
        }

        .sidebar-header {
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border);
        }

        .sidebar-header h3 {
            margin: 0 0 0.35rem;
            font-size: 1.2rem;
        }

        .sidebar-header p {
            margin: 0;
            color: var(--muted);
            font-size: 0.92rem;
            line-height: 1.5;
        }

        .main {
            display: block;
            width: 100%;
            max-width: none;
            margin: 0;
            padding: 0;
            min-width: 0;
        }

        .categories-card {
            margin-bottom: 1.25rem;
            padding: 1.25rem;
            border: 1px solid rgba(255, 255, 255, 0.72);
            border-radius: var(--radius-xl);
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.96) 0%, rgba(247, 246, 242, 0.94) 100%);
            box-shadow: var(--shadow);
        }

        .categories-card-header {
            margin-bottom: 1rem;
        }

        .categories-card-header h2 {
            margin: 0;
            font-size: 1.2rem;
            letter-spacing: -0.02em;
        }

        .categories-card-header p {
            margin: 0.35rem 0 0;
            color: var(--muted);
            font-size: 0.95rem;
        }

        .header-categories {
            display: flex;
            align-items: stretch;
            gap: 0.65rem;
            min-width: 0;
            flex-wrap: wrap;
        }

        .header-category-item {
            position: relative;
            flex: 0 1 auto;
        }

        .header-category-toggle {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            min-height: 42px;
            padding: 0.7rem 0.95rem;
            border: 1px solid var(--border);
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.92);
            color: var(--text);
            font-size: 0.88rem;
            font-weight: 600;
            cursor: pointer;
            white-space: nowrap;
            transition: border-color var(--transition), color var(--transition), background var(--transition);
        }

        .header-category-toggle:hover,
        .header-category-item.open .header-category-toggle,
        .header-category-item.open .header-category-toggle {
            border-color: rgba(23, 50, 77, 0.18);
            color: var(--accent);
            background: var(--accent-soft);
        }

        .header-category-toggle.active {
            border-color: var(--accent);
            background: var(--accent);
            color: var(--surface-solid);
        }

        .header-submenu {
            position: absolute;
            top: calc(100% + 0.55rem);
            left: 0;
            min-width: 260px;
            max-width: 320px;
            display: none;
            padding: 0.75rem;
            border: 1px solid rgba(255, 255, 255, 0.78);
            border-radius: 18px;
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 24px 40px rgba(23, 34, 45, 0.12);
        }

        .header-category-item.open .header-submenu {
            display: block;
        }

        .hero {
            position: relative;
            overflow: hidden;
            margin-bottom: 1.25rem;
            padding: 1.75rem;
            border: 1px solid rgba(255, 255, 255, 0.72);
            border-radius: var(--radius-xl);
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.96) 0%, rgba(247, 246, 242, 0.94) 100%);
            box-shadow: var(--shadow);
        }

        .hero::after {
            content: "";
            position: absolute;
            right: -40px;
            bottom: -50px;
            width: 180px;
            height: 180px;
            border-radius: 999px;
            background: radial-gradient(circle, rgba(24, 52, 79, 0.1), transparent 70%);
            pointer-events: none;
        }

        .hero-top {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 1rem;
        }

        .hero-brand {
            display: flex;
            flex-direction: column;
            align-items: stretch;
            gap: 0.85rem;
            width: 100%;
            max-width: 820px;
        }

        .hero-heading {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1.1rem;
        }

        .hero-copy {
            text-align: left;
        }

        .hero-brand .brand-logo {
            width: 132px;
            height: 132px;
            flex: 0 0 auto;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.35rem 0.75rem;
            border-radius: 999px;
            background: var(--accent-soft);
            color: var(--accent);
            font-size: 0.8rem;
            font-weight: 600;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .hero h1 {
            margin: 0.9rem 0 0.6rem;
            font-size: clamp(2rem, 4vw, 3rem);
            line-height: 1.05;
            letter-spacing: -0.03em;
        }

        .hero p {
            margin: 0;
            max-width: 720px;
            color: var(--muted);
            line-height: 1.7;
            text-align: left;
        }

        .accordion-item {
            margin-bottom: 0.5rem;
            border: 1px solid transparent;
            border-radius: var(--radius-md);
            transition: border-color var(--transition), background var(--transition);
        }

        .accordion-item.is-open {
            border-color: var(--border);
            background: rgba(247, 246, 242, 0.82);
        }

        .accordion-toggle {
            width: 100%;
            padding: 0.95rem 1rem;
            border: none;
            background: none;
            color: var(--text);
            text-align: left;
            font-size: 0.94rem;
            font-weight: 600;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            cursor: pointer;
            transition: color var(--transition);
        }

        .accordion-toggle:hover {
            color: var(--accent);
        }

        .accordion-content {
            padding: 0 1rem 1rem;
        }

        .accordion-content.hidden {
            display: none;
        }

        .subcat-link {
            display: block;
            width: 100%;
            margin-top: 0.35rem;
            padding: 0.7rem 0.85rem;
            border: 1px solid transparent;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.78);
            color: var(--muted);
            text-align: left;
            font-size: 0.9rem;
            cursor: pointer;
            transition: background var(--transition), color var(--transition), border-color var(--transition), transform var(--transition);
        }

        .dropdown-state {
            margin: 0.2rem 0;
            color: var(--muted);
            font-size: 0.9rem;
        }

        .subcat-link:hover {
            transform: translateX(2px);
            border-color: var(--border);
            background: var(--surface-solid);
            color: var(--accent);
        }

        .subcat-link.active {
            border-color: var(--accent);
            background: var(--accent);
            color: var(--surface-solid);
        }

        .content-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .toolbar-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .content-toolbar h2 {
            margin: 0;
            font-size: 1.2rem;
            letter-spacing: -0.02em;
        }

        .content-toolbar p {
            margin: 0.35rem 0 0;
            color: var(--muted);
            font-size: 0.95rem;
        }

        .active-filter {
            display: inline-flex;
            align-items: center;
            min-height: 42px;
            padding: 0.65rem 0.95rem;
            border: 1px solid var(--border);
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.75);
            color: var(--accent);
            font-size: 0.9rem;
            font-weight: 600;
        }

        .clear-filter-btn {
            min-height: 42px;
            padding: 0.65rem 1rem;
            border: 1px solid var(--border);
            border-radius: 999px;
            background: var(--surface-solid);
            color: var(--text);
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: background var(--transition), border-color var(--transition), color var(--transition);
        }

        .clear-filter-btn:hover {
            background: var(--surface-muted);
            border-color: var(--border-strong);
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(270px, 1fr));
            gap: 1.25rem;
        }

        .card {
            min-height: 320px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 1.35rem;
            border: 1px solid rgba(255, 255, 255, 0.72);
            border-radius: var(--radius-lg);
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.97) 0%, rgba(250, 249, 246, 0.95) 100%);
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
            transition: transform var(--transition), box-shadow var(--transition), border-color var(--transition);
        }

        .card:hover {
            transform: translateY(-4px);
            border-color: rgba(24, 52, 79, 0.12);
            box-shadow: 0 24px 40px rgba(23, 34, 45, 0.08);
        }

        .card-kicker {
            display: inline-flex;
            align-self: flex-start;
            margin-bottom: 1rem;
            padding: 0.35rem 0.7rem;
            border-radius: 999px;
            background: var(--surface-muted);
            color: var(--accent);
            font-size: 0.72rem;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .card h3 {
            margin: 0;
            font-size: 1.15rem;
            line-height: 1.3;
            letter-spacing: -0.02em;
        }

        .card .muted {
            margin: 0.45rem 0 0;
            color: var(--muted);
            font-size: 0.92rem;
            line-height: 1.6;
        }

        .card-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 0.7rem;
        }

        .card-meta span {
            padding: 0.3rem 0.65rem;
            border: 1px solid var(--border);
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.82);
            color: var(--muted-soft);
            font-size: 0.76rem;
        }

        .card-description {
            margin: 1rem 0 0;
            color: #393935;
            font-size: 0.95rem;
            line-height: 1.7;
        }

        .card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.85rem;
            margin-top: 1.2rem;
        }

        .card-date {
            color: var(--muted-soft);
            font-size: 0.82rem;
        }

        .card .btn {
            width: 100%;
            margin-top: auto;
            padding: 0.85rem 1rem;
            border: none;
            border-radius: 14px;
            background: var(--accent);
            color: var(--surface-solid);
            font-weight: 600;
            letter-spacing: 0.01em;
            cursor: pointer;
            transition: background var(--transition), transform var(--transition);
        }

        .card .btn:hover {
            transform: translateY(-1px);
            background: var(--accent-strong);
        }

        .empty-state {
            grid-column: 1 / -1;
            padding: 2rem;
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            background: rgba(255, 255, 255, 0.88);
            box-shadow: var(--shadow);
            text-align: center;
            color: var(--muted);
        }

        .empty-state h3 {
            margin-top: 0;
            color: var(--text);
        }

        .modal {
            padding: 1.5rem;
            background: rgba(15, 23, 32, 0.48);
            backdrop-filter: blur(10px);
        }

        .modal-content {
            width: min(1040px, 100%);
            max-height: calc(100vh - 3rem);
            overflow-y: auto;
            padding: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.72);
            border-radius: 30px;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.98) 0%, rgba(247, 246, 242, 0.97) 100%);
            box-shadow: 0 30px 70px rgba(10, 17, 24, 0.18);
        }

        .modal-header {
            margin-bottom: 1.5rem;
            padding-bottom: 1.25rem;
            border-bottom: 1px solid var(--border);
        }

        .modal-title {
            margin: 0;
            font-size: clamp(1.6rem, 3vw, 2.2rem);
            line-height: 1.1;
            letter-spacing: -0.03em;
        }

        .modal-subtitle {
            margin: 0.75rem 0 0;
            color: var(--muted);
            font-size: 1rem;
            line-height: 1.6;
        }

        .modal-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 0.6rem;
            margin-top: 1rem;
        }

        .modal-badges span {
            padding: 0.45rem 0.8rem;
            border: 1px solid var(--border);
            border-radius: 999px;
            background: var(--surface-muted);
            color: var(--accent);
            font-size: 0.82rem;
            font-weight: 600;
        }

        .modal-body {
            display: grid;
            gap: 1.25rem;
        }

        .modal-section {
            padding: 1.2rem 1.25rem;
            border: 1px solid var(--border);
            border-radius: 22px;
            background: rgba(255, 255, 255, 0.7);
        }

        .modal-section-label {
            margin: 0 0 0.55rem;
            color: var(--muted-soft);
            font-size: 0.78rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .modal-section p {
            margin: 0;
            color: #33342f;
            line-height: 1.8;
        }

        .modal-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
            margin-top: 1.5rem;
        }

        .modal-primary-actions {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .modal-download {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 48px;
            padding: 0.85rem 1.25rem;
            border-radius: 14px;
            background: var(--accent);
            color: var(--surface-solid);
            text-decoration: none;
            font-weight: 600;
            transition: background var(--transition), transform var(--transition);
        }

        .modal-download:hover {
            transform: translateY(-1px);
            background: var(--accent-strong);
        }

        .btn-secondary,
        .btn-ghost {
            min-width: 130px;
            min-height: 48px;
            border-radius: 14px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-secondary {
            border: 1px solid var(--border-strong);
            background: var(--surface-solid);
            color: var(--text);
        }

        .btn-ghost {
            border: none;
            background: transparent;
            color: var(--muted);
        }

        @media (max-width: 900px) {
            .header-categories {
                flex-direction: column;
                align-items: stretch;
            }

            .header-category-item {
                width: 100%;
            }

            .header-category-toggle {
                width: 100%;
                justify-content: space-between;
            }

            .header-submenu {
                position: static;
                min-width: 100%;
                max-width: 100%;
                margin-top: 0.5rem;
                box-shadow: none;
            }
        }

        @media (max-width: 768px) {
            .layout {
                padding: 0.9rem 0 2rem;
            }

            .page-container {
                width: min(100vw - 1.2rem, 1380px);
            }

            .categories-card,
            .hero {
                padding: 1.15rem;
            }

            .hero-top,
            .content-toolbar,
            .modal-actions {
                flex-direction: column;
                align-items: stretch;
            }

            .hero-heading {
                flex-direction: column-reverse;
                text-align: center;
            }

            .hero-copy,
            .hero p {
                text-align: center;
            }

            .hero-brand .brand-logo {
                width: 104px;
                height: 104px;
                margin: 0 auto;
            }

            .toolbar-actions {
                justify-content: stretch;
            }

            .active-filter {
                width: 100%;
                justify-content: center;
            }

            .clear-filter-btn {
                width: 100%;
            }

            .grid {
                grid-template-columns: 1fr;
            }

            .card-footer {
                flex-direction: column;
                align-items: stretch;
            }

            .modal {
                padding: 0.9rem;
            }

            .modal-content {
                padding: 1.25rem;
                border-radius: 24px;
            }

            .modal-primary-actions,
            .modal-download,
            .btn-secondary,
            .btn-ghost {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="page-container">
        <div class="layout">
            <main class="main">
                <section class="hero">
                    <div class="hero-top">
                        <div class="hero-brand">
                            <div class="hero-heading">
                                <div class="hero-copy">
                                    <span class="eyebrow">Centro de documentación</span>
                                    <h1>Publicaciones técnicas</h1>
                                </div>
                                <img class="brand-logo" src="/assets/png/logo_con_color_original.png" alt="Logo SVE">
                            </div>
                            <p>
                                Una biblioteca digital pensada para consultar ensayos, análisis y documentos de manera
                                ordenada, clara e intuitiva desde cualquier dispositivo.
                            </p>
                        </div>
                    </div>
                </section>

                <section class="categories-card">
                    <div class="categories-card-header">
                        <h2>Categorías</h2>
                        <p>Elegí una categoría y después una subcategoría para filtrar el catálogo.</p>
                    </div>

                    <div class="header-categories" id="header-categories">
                        <?php foreach ($categorias as $cat): ?>
                            <?php if (mb_strtolower(trim($cat['nombre']), 'UTF-8') === 'otros ensayos') continue; ?>
                            <div class="header-category-item" data-cat="<?= $cat['id'] ?>">
                                <button class="header-category-toggle" type="button" data-cat="<?= $cat['id'] ?>" data-name="<?= htmlspecialchars($cat['nombre']) ?>">
                                    <span><?= htmlspecialchars($cat['nombre']) ?></span>
                                    <span>▾</span>
                                </button>
                                <div class="header-submenu" id="subcat-<?= $cat['id'] ?>">
                                    <p class="dropdown-state">Cargando...</p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <div class="content-toolbar">
                    <div>
                        <h2>Catálogo disponible</h2>
                        <p>Seleccioná una publicación para ver el detalle completo y acceder al archivo.</p>
                    </div>
                    <div class="toolbar-actions">
                        <div class="active-filter" id="filtro-activo">Mostrando: todas las publicaciones</div>
                        <button type="button" class="clear-filter-btn" id="clear-filter-btn">Eliminar filtro</button>
                    </div>
                </div>

                <div class="grid" id="contenedor-publicaciones"></div>
            </main>
        </div>
    </div>

    <div class="modal hidden" id="modal-lectura">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modal-titulo" class="modal-title">Título de la publicación</h3>
                <p id="modal-subtitulo" class="modal-subtitle"></p>
                <div class="modal-badges">
                    <span id="modal-cat-subcat"></span>
                    <span id="modal-autor-fecha"></span>
                </div>
            </div>

            <div class="modal-body">
                <div class="modal-section">
                    <p class="modal-section-label">Resumen</p>
                    <p id="modal-descripcion"></p>
                </div>
            </div>

            <div class="modal-actions">
                <a id="modal-archivo" href="#" target="_blank" class="modal-download">Descargar archivo</a>
                <div class="modal-primary-actions">
                    <button class="btn-secondary" onclick="Framework.closeModal('modal-lectura')">Volver</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        const contenedor = document.getElementById('contenedor-publicaciones');
        const headerCategories = document.getElementById('header-categories');
        const clearFilterBtn = document.getElementById('clear-filter-btn');
        const categorias = <?= json_encode(array_values(array_filter($categorias, function ($cat) {
            return mb_strtolower(trim($cat['nombre']), 'UTF-8') !== 'otros ensayos';
        })), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
        const subcategoriasCache = {};

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

        let publicaciones = [];
        let filtroActivo = {
            categoriaId: '',
            categoria: '',
            subcategoriaId: '',
            subcategoria: '',
            etiqueta: 'Todas las publicaciones'
        };

        function escapeHtml(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function truncar(texto, limite = 170) {
            if (!texto) return 'Sin descripción disponible.';
            return texto.length > limite ? `${texto.slice(0, limite).trim()}...` : texto;
        }

        function formatDate(value) {
            if (!value) return 'Sin fecha';

            const parsed = new Date(`${value}T00:00:00`);
            if (!Number.isNaN(parsed.getTime())) {
                const day = String(parsed.getDate()).padStart(2, '0');
                const month = String(parsed.getMonth() + 1).padStart(2, '0');
                const year = parsed.getFullYear();
                return `${day}/${month}/${year}`;
            }

            const parts = String(value).split('-');
            if (parts.length === 3) {
                return `${parts[2]}/${parts[1]}/${parts[0]}`;
            }

            return value;
        }

        function actualizarResumen() {
            document.getElementById('filtro-activo').textContent = `Mostrando: ${filtroActivo.etiqueta.toLowerCase()}`;
        }

        function closeAllCategoryMenus() {
            document.querySelectorAll('.header-category-item.open').forEach(item => item.classList.remove('open'));
        }

        function syncActiveButtons() {
            document.querySelectorAll('.header-category-toggle[data-cat]').forEach(btn => {
                btn.classList.toggle('active', String(btn.dataset.cat) === String(filtroActivo.categoriaId));
            });

            document.querySelectorAll('.subcat-link').forEach(link => {
                link.classList.toggle('active', String(link.dataset.subcatId) === String(filtroActivo.subcategoriaId));
            });
        }

        async function obtenerSubcategorias(catId) {
            if (subcategoriasCache[catId]) return subcategoriasCache[catId];

            const res = await fetch(`../../controllers/sve_publicacionesController.php?action=get_subcategorias&categoria_id=${catId}`);
            const data = await res.json();
            subcategoriasCache[catId] = data;
            return data;
        }

        async function cargarSubcategorias(catId, targetId, categoriaNombre) {
            const content = document.getElementById(targetId);
            if (!content) return;

            if (content.dataset.loaded === '1') {
                syncActiveButtons();
                return;
            }

            try {
                const data = await obtenerSubcategorias(catId);
                content.innerHTML = '';

                if (!data.length) {
                    content.innerHTML = '<p class="dropdown-state">No hay subcategorías disponibles.</p>';
                    content.dataset.loaded = '1';
                    return;
                }

                data.forEach(sub => {
                    const subBtn = document.createElement('button');
                    subBtn.className = 'subcat-link';
                    subBtn.dataset.subcatId = String(sub.id);
                    subBtn.textContent = sub.nombre;
                    subBtn.onclick = () => seleccionarSubcategoria(catId, categoriaNombre, sub.id, sub.nombre);
                    content.appendChild(subBtn);
                });

                content.dataset.loaded = '1';
                syncActiveButtons();
            } catch (e) {
                content.innerHTML = '<p class="dropdown-state">Error al cargar.</p>';
            }
        }

        function seleccionarSubcategoria(catId, categoriaNombre, subId, subNombre) {
            filtroActivo = {
                categoriaId: String(catId),
                categoria: categoriaNombre,
                subcategoriaId: String(subId),
                subcategoria: subNombre,
                etiqueta: `${categoriaNombre} / ${subNombre}`
            };

            syncActiveButtons();
            closeAllCategoryMenus();
            if (window.innerWidth <= 900) {
                headerNav.classList.remove('visible');
            }
            cargarPublicaciones(catId, subId);
        }

        function limpiarFiltro() {
            filtroActivo = {
                categoriaId: '',
                categoria: '',
                subcategoriaId: '',
                subcategoria: '',
                etiqueta: 'Todas las publicaciones'
            };

            syncActiveButtons();
            closeAllCategoryMenus();
            cargarPublicaciones();
        }

        function bindCategoryToggles() {
            document.querySelectorAll('.header-category-toggle[data-cat]').forEach(btn => {
                btn.onclick = async () => {
                    const item = btn.closest('.header-category-item');
                    const willOpen = !item.classList.contains('open');
                    const catId = btn.dataset.cat;
                    const categoriaNombre = btn.dataset.name;
                    const targetId = item.querySelector('.header-submenu').id;

                    closeAllCategoryMenus();
                    item.classList.toggle('open', willOpen);
                    if (!willOpen) return;

                    await cargarSubcategorias(catId, targetId, categoriaNombre);
                };
            });
        }

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
                    actualizarResumen();
                    renderPublicaciones();
                });
        }

        function renderPublicaciones() {
            contenedor.innerHTML = '';

            if (!publicaciones.length) {
                contenedor.innerHTML = `
                    <div class="empty-state">
                        <h3>No se encontraron publicaciones</h3>
                        <p>Probá con otra categoría o eliminá el filtro para seguir explorando la biblioteca.</p>
                    </div>
                `;
                return;
            }

            publicaciones.forEach(pub => {
                const card = document.createElement('div');
                card.classList.add('card');

                card.innerHTML = `
                    <div>
                        <span class="card-kicker">${escapeHtml(pub.categoria || 'Publicación')}</span>
                        <h3>${escapeHtml(pub.titulo)}</h3>
                        <p class="muted">${escapeHtml(pub.subtitulo || '')}</p>
                        <div class="card-meta">
                            <span>${escapeHtml(pub.subcategoria || 'General')}</span>
                            <span>${escapeHtml(pub.autor || 'Autor no especificado')}</span>
                        </div>
                        <p class="card-description">${escapeHtml(truncar(pub.descripcion))}</p>
                    </div>
                    <div class="card-footer">
                        <span class="card-date">${escapeHtml(formatDate(pub.fecha_publicacion))}</span>
                        <button class="btn" onclick="abrirModal(${pub.id})">Ver publicación</button>
                    </div>
                `;

                contenedor.appendChild(card);
            });
        }

        function abrirModal(id) {
            const pub = publicaciones.find(p => p.id == id);
            if (!pub) return;

            fetch(`../../controllers/sve_publicacionesController.php?action=incrementar_vista&id=${id}`);

            document.getElementById('modal-titulo').textContent = pub.titulo;
            document.getElementById('modal-subtitulo').textContent = pub.subtitulo || 'Documento técnico disponible para consulta y descarga.';
            document.getElementById('modal-cat-subcat').textContent = `${pub.categoria} > ${pub.subcategoria}`;
            document.getElementById('modal-autor-fecha').textContent = `${pub.autor} · ${formatDate(pub.fecha_publicacion)}`;
            document.getElementById('modal-descripcion').textContent = pub.descripcion || 'Sin descripción disponible.';

            const archivoBtn = document.getElementById('modal-archivo');

            if (pub.archivo) {
                archivoBtn.href = `../../uploads/publications/${pub.archivo}`;
                archivoBtn.style.display = 'inline-flex';
                archivoBtn.onclick = () => {
                    fetch(`../../controllers/sve_publicacionesController.php?action=incrementar_descarga&id=${id}`);
                };
            } else {
                archivoBtn.style.display = 'none';
                archivoBtn.onclick = null;
            }

            Framework.openModal('modal-lectura');
        }

        bindCategoryToggles();
        clearFilterBtn.addEventListener('click', limpiarFiltro);
        document.addEventListener('click', event => {
            if (!event.target.closest('.header-category-item')) {
                closeAllCategoryMenus();
            }
        });
        cargarPublicaciones();
    </script>
</body>

</html>
