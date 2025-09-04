<?php

declare(strict_types=1);
ini_set('display_errors', '0');
error_reporting(E_ALL);

require_once '../../middleware/authMiddleware.php';
checkAccess('productor');

session_start();
$nombre  = $_SESSION['nombre']  ?? 'Sin nombre';
$idReal  = $_SESSION['id_real'] ?? null;

$sesion_payload = [
    'id_real' => $idReal,
    'nombre'  => $nombre,
    'usuario' => $_SESSION['usuario'] ?? null,
    'rol'     => $_SESSION['rol'] ?? null,
];
?>
<script id="session-data" type="application/json">
    <?= json_encode($sesion_payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
</script>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SVE - Mis pedidos</title>

    <!-- Material Icons (si ya lo usás en el proyecto) -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />

    <!-- CDN proyecto -->
    <link rel="stylesheet" href="https://www.fernandosalguero.com/cdn/assets/css/framework.css">
    <script defer src="https://www.fernandosalguero.com/cdn/assets/javascript/framework.js"></script>

    <style>
        .main {
            margin-left: 0;
        }

        .header-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.25rem 1rem;
        }

        .listado {
            display: grid;
            gap: .75rem;
        }

        .pedido-card {
            border: 1px solid #eee;
            border-radius: 14px;
            background: #fff;
            padding: .85rem .9rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, .04);
        }

        .pedido-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: .75rem;
        }

        .pedido-title {
            font-weight: 700;
            font-size: 1rem;
            margin: 0;
        }

        .pedido-meta {
            display: grid;
            grid-template-columns: 1fr;
            gap: .35rem;
            margin-top: .5rem;
        }

        .row {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: .75rem;
        }

        .label {
            color: #475569;
            font-size: .92rem;
        }

        .value {
            font-weight: 600;
            font-variant-numeric: tabular-nums;
            text-align: right;
            white-space: nowrap;
        }

        .estado-badge {
            padding: .15rem .5rem;
            border-radius: 999px;
            font-size: .78rem;
            line-height: 1;
        }

        .estado-pendiente {
            background: #e6f4ff;
            color: #0b72b9;
        }

        .estado-en_proceso {
            background: #fff7e6;
            color: #b6720b;
        }

        .estado-completado {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .estado-cancelado {
            background: #ffe8e8;
            color: #b71c1c;
        }

        .card-actions {
            display: flex;
            gap: .5rem;
            margin-top: .75rem;
            justify-content: flex-end;
        }

        .btn-cancelar-soft {
            background: #fff;
            color: #b71c1c;
            border: 1px solid #ffd1d1;
        }

        @media (min-width:680px) {
            .pedido-meta {
                grid-template-columns: 1fr 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="layout">
        <div class="main">
            <header class="navbar">
                <h4>Listado de pedidos de pulverización con dron</h4>
            </header>

            <section class="content">
                <!-- Intro -->
                <div class="card">
                    <p>Señor productor, aquí puede ver sus solicitudes y, si fuera necesario, cancelarlas antes de ser atendidas.</p>
                </div>

                <!-- Header resumen / volver -->
                <div class="card header-card">
                    <div>
                        <h4><?= htmlspecialchars($nombre) ?></h4>
                        <p>¿Queres ir al atras?</p>
                    </div>
                    <a class="btn btn-info" href="prod_dashboard.php">Apreta acá</a>
                </div>

                <!-- Listado -->
                <div class="card">
                    <h4>Mis pedidos</h4>
                    <div id="listado" class="listado" aria-live="polite"></div>
                    <div id="paginacion" class="form-buttons" style="justify-content:center; margin-top: .5rem;"></div>
                </div>
            </section>
        </div>
    </div>

    <!-- Spinner global (si lo tenés en tu proyecto) -->
    <script src="../../views/partials/spinner-global.js"></script>

    <script>
        (() => {
            const API = '../../controllers/prod_listadoPedidosController.php';
            const $ = (s, ctx = document) => ctx.querySelector(s);
            const $$ = (s, ctx = document) => Array.from(ctx.querySelectorAll(s));

            const ses = (() => {
                try {
                    return JSON.parse($('#session-data')?.textContent || '{}')
                } catch {
                    return {}
                }
            })();
            const listado = $('#listado');
            const pag = $('#paginacion');
            const PAGE_SIZE = 8;
            let page = 1;

            const fmtNum = (n) => new Intl.NumberFormat('es-AR', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(Number(n || 0));
            const fmtFecha = (s) => s ? new Date(s).toLocaleDateString('es-AR') : '—';

            async function apiGet(params) {
                const url = `${API}?${new URLSearchParams(params).toString()}`;
                const res = await fetch(url, {
                    credentials: 'same-origin'
                });
                const json = await res.json().catch(() => ({
                    ok: false,
                    error: 'JSON inválido'
                }));
                if (!json.ok) throw new Error(json.error || 'Error de red');
                return json.data;
            }
            async function apiPost(body) {
                const res = await fetch(API, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(body)
                });
                const json = await res.json().catch(() => ({
                    ok: false,
                    error: 'JSON inválido'
                }));
                if (!json.ok) throw new Error(json.error || 'Error de red');
                return json;
            }

            function renderItems(items, total, pageNow) {
                listado.innerHTML = items.map(it => {
                    const estadoClass = `estado-${it.estado}`;
                    const puedeCancelar = (it.estado === 'pendiente' || it.estado === 'en_proceso');
                    return `
        <article class="pedido-card" aria-label="Solicitud #${it.id}">
          <div class="pedido-head">
            <h5 class="pedido-title">Solicitud #${it.id}</h5>
            <span class="estado-badge ${estadoClass}">${it.estado}</span>
          </div>
          <div class="pedido-meta">
            <div class="row"><span class="label">Hectáreas</span><span class="value">${fmtNum(it.superficie_ha)}</span></div>
            <div class="row"><span class="label">Fecha de visita</span><span class="value">${fmtFecha(it.fecha_visita)}</span></div>
            <div class="row"><span class="label">Patologías</span><span class="value" style="white-space:normal; text-align:right;">${it.patologias || '—'}</span></div>
            <div class="row"><span class="label">Costo del servicio</span><span class="value">${fmtNum(it.costo_total)} ${it.moneda||''}</span></div>
          </div>
          <div class="card-actions">
            ${puedeCancelar ? `<button class="btn btn-cancelar btn-cancelar-soft" data-id="${it.id}" aria-label="Cancelar solicitud ${it.id}">Cancelar</button>` : ''}
          </div>
        </article>
      `;
                }).join('') || `<div class="gform-helper">No hay pedidos todavía.</div>`;

                // Pagination (simple)
                const pages = Math.max(1, Math.ceil(total / PAGE_SIZE));
                pag.innerHTML = pages > 1 ? `
      <div class="form-grid grid-3">
        <button class="btn" ${pageNow<=1?'disabled':''} data-nav="prev">Anterior</button>
        <span class="gform-helper" style="align-self:center;">Página ${pageNow} de ${pages}</span>
        <button class="btn" ${pageNow>=pages?'disabled':''} data-nav="next">Siguiente</button>
      </div>` : '';
            }

            async function load() {
                try {
                    window.showSpinner?.();
                    const data = await apiGet({
                        action: 'list',
                        page: String(page),
                        size: String(PAGE_SIZE)
                    });
                    renderItems(data.items, data.total, data.page);
                } catch (e) {
                    window.showToast?.('error', e.message || 'No se pudo cargar el listado.');
                } finally {
                    window.hideSpinner?.();
                }
            }

            // Delegación: cancelar
            listado.addEventListener('click', async (ev) => {
                const btn = ev.target.closest('button.btn-cancelar');
                if (!btn) return;
                const id = btn.dataset.id;
                const ok = confirm(`¿Cancelar la solicitud #${id}?`);
                if (!ok) return;

                try {
                    window.showSpinner?.();
                    await apiPost({
                        action: 'cancel',
                        id: Number(id)
                    });
                    window.showToast?.('success', 'Solicitud cancelada.');
                    load();
                } catch (e) {
                    window.showToast?.('error', e.message || 'No se pudo cancelar.');
                } finally {
                    window.hideSpinner?.();
                }
            });

            // Paginación
            pag.addEventListener('click', (ev) => {
                const b = ev.target.closest('button[data-nav]');
                if (!b) return;
                page += (b.dataset.nav === 'next' ? 1 : -1);
                if (page < 1) page = 1;
                load();
            });

            // Init
            load();
        })();
    </script>
</body>

</html>