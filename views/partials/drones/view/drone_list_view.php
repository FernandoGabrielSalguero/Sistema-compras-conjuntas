<?php
?>

<div class="content">
    <!-- Filtros mínimos -->
    <div class="card" style="background-color:#5b21b6;">
        <h3 style="color:white;">Buscar proyecto de vuelo</h3>
        <form class="form-grid grid-4" id="form-search" autocomplete="off">
            <div class="input-group">
                <label for="piloto" style="color:white;">Nombre piloto</label>
                <div class="input-icon input-icon-name">
                    <input type="text" id="piloto" name="piloto" placeholder="Piloto" />
                </div>
            </div>
            <div class="input-group">
                <label for="ses_usuario" style="color:white;">Nombre productor</label>
                <div class="input-icon input-icon-name">
                    <input type="text" id="ses_usuario" name="ses_usuario" placeholder="Productor" />
                </div>
            </div>
            <div class="input-group">
                <!-- OJO: ID distinto al del drawer -->
                <label for="estado_filtro" style="color:white;">Estado</label>
                <div class="input-icon input-icon-globe">
                    <select id="estado_filtro" name="estado_filtro">
                        <option value="">Todos</option>
                        <option value="ingresada">Ingresada</option>
                        <option value="procesando">Procesando</option>
                        <option value="aprobada_coop">Aprobada coop</option>
                        <option value="cancelada">Cancelada</option>
                        <option value="completada">Completada</option>
                    </select>
                </div>
            </div>
            <div class="input-group">
                <label for="fecha_visita" style="color:white;">Fecha del servicio</label>
                <div class="input-icon input-icon-date">
                    <input type="date" id="fecha_visita" name="fecha_visita" />
                </div>
            </div>
        </form>
    </div>

    <!-- Contenedor tarjetas -->
    <div id="cards" class="triple-tarjetas card-grid grid-4"></div>
</div>

<style>
    #cards:empty::before {
        content: "No hay solicitudes para los filtros seleccionados.";
        display: block;
        background: #fff;
        border-radius: 14px;
        padding: 18px;
        color: #6b7280;
    }

    .sv-drawer.hidden {
        display: none
    }

    .sv-drawer {
        position: fixed;
        inset: 0;
        z-index: 60
    }

    .sv-drawer__overlay {
        position: absolute;
        inset: 0;
        background: #0006;
        opacity: 0;
        pointer-events: all;
    }

    /* Mantener overlay visible mientras el drawer esté abierto */
    .sv-drawer[aria-hidden="false"] .sv-drawer__overlay {
        opacity: 1;
    }

    .sv-drawer__panel {
        position: absolute;
        top: 0;
        right: 0;
        height: 100%;
        width: min(760px, 100%);
        background: #fff;
        box-shadow: -6px 0 24px #00000022;
        display: flex;
        flex-direction: column;
        border-top-left-radius: 16px;
        border-bottom-left-radius: 16px;
    }

    .sv-drawer__header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 20px;
        border-bottom: 1px solid #eee
    }

    .sv-drawer__footer {
        padding: 12px 20px;
        border-top: 1px solid #eee;
        display: flex;
        gap: 12px;
        justify-content: flex-end
    }

    .sv-drawer__close {
        font-size: 24px;
        line-height: 1;
        border: none;
        background: transparent;
        cursor: pointer
    }

    .sv-drawer__body {
        flex: 1;
        overflow: auto;
        -webkit-overflow-scrolling: touch;
        padding: 16px 20px;
        background-color: darkgray;
    }

    @keyframes slideInRight {
        from {
            transform: translateX(100%)
        }

        to {
            transform: translateX(0)
        }
    }

    @keyframes slideOutRight {
        from {
            transform: translateX(0)
        }

        to {
            transform: translateX(100%)
        }
    }

    @keyframes fadeIn {
        from {
            opacity: 0
        }

        to {
            opacity: 1
        }
    }

    @keyframes fadeOut {
        from {
            opacity: 1
        }

        to {
            opacity: 0
        }
    }

    .sv-drawer.opening .sv-drawer__panel {
        animation: slideInRight .28s cubic-bezier(.22, .61, .36, 1) both;
    }

    .sv-drawer.closing .sv-drawer__panel {
        animation: slideOutRight .22s ease both;
    }

    .sv-drawer.opening .sv-drawer__overlay {
        animation: fadeIn .25s ease both;
    }

    .sv-drawer.closing .sv-drawer__overlay {
        animation: fadeOut .20s ease both;
    }

    .product-card .badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 999px;
        font-size: .8rem
    }

    .helper-text {
        font-size: .85rem;
        color: #6b7280
    }

    .mini-input {
        width: 100%
    }

    .table-actions {
        display: flex;
        gap: 8px;
        justify-content: center
    }

    .chip {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 999px;
        background: #eef
    }

    .nowrap {
        white-space: nowrap
    }

    .sr-only {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        white-space: nowrap;
        border: 0
    }

    textarea#desglose_json {
        min-height: 96px
    }

    .badge.warning {
        background: #FEF3C7;
        color: #92400E
    }

    .badge.info {
        background: #DBEAFE;
        color: #1E40AF
    }

    .badge.primary {
        background: #E0E7FF;
        color: #3730A3
    }

    .badge.success {
        background: #DCFCE7;
        color: #166534
    }

    .badge.danger {
        background: #FEE2E2;
        color: #B91C1C
    }

    .data-table td .input-icon {
        display: block;
    }

    .data-table td .input-icon input,
    .data-table td .input-icon select,
    .data-table td .input-icon textarea {
        width: 100%;
    }

    .card .form-separator {
        margin: 8px 0 16px;
    }

    .chips {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .chip-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #eef;
        border-radius: 999px;
        padding: 4px 10px;
    }

    .chip-pill .close {
        cursor: pointer;
        border: 0;
        background: transparent;
        font-weight: bold;
    }

    .producto-item {
        border: 1px solid #eee;
        border-radius: 12px;
        padding: 10px;
        margin-bottom: 8px;
        display: grid;
        grid-template-columns: 1fr auto;
        gap: 6px;
    }

    .producto-item .meta {
        font-size: .9rem;
        color: #6b7280;
    }

    .producto-item .acciones {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .costos-resumen p {
        margin: .25rem 0;
    }

    /* Oculto por defecto el motivo hasta que el estado sea cancelada */
    #grp_motivo_cancelacion {
        display: none;
    }

    .mini-block {
        margin-top: 6px;
    }

    .mini-title {
        font-size: .83rem;
        color: #5b21b6;
        font-weight: 600;
        margin-bottom: 2px;
    }

    .price {
        font-weight: 600;
    }

    .motivo-cancel {
        display: inline-block;
        margin-left: 10px;
        font-size: .82rem;
        color: #9b1c1c;
        /* rojito suave */
        background: #fee2e2;
        /* igual que .badge.danger pero más liviano */
        padding: 2px 8px;
        border-radius: 999px;
    }

    #cards {
        min-height: 80px;
    }
</style>

<script>
    const DRONE_API = '../partials/drones/controller/drone_list_controller.php';

    (function() {
        // Evita doble inicialización si el view se monta dos veces
        if (window.__SVE_DRONE_LIST_INIT__) return;
        window.__SVE_DRONE_LIST_INIT__ = true;

        const $ = (s, ctx = document) => ctx.querySelector(s);
        const $$ = (s, ctx = document) => Array.from(ctx.querySelectorAll(s));

        const els = {
            piloto: $('#piloto'),
            ses_usuario: $('#ses_usuario'),
            estado_filtro: $('#estado_filtro'),
            fecha_visita: $('#fecha_visita'),
            cards: $('#cards')
        };

        // helpers
        function debounce(fn, t = 300) {
            let id;
            return (...a) => {
                clearTimeout(id);
                id = setTimeout(() => fn(...a), t);
            };
        }

        function esc(s) {
            return (s ?? '').toString().replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }
        const fmtNum = (v) => {
            if (v === null || v === undefined || v === '') return '';
            const n = Number(v);
            if (!Number.isFinite(n)) return '';
            return Number.isInteger(n) ? String(n) : String(n).replace('.', ',');
        };
        const parseNum = (v) => (v === '' || v === null || v === undefined) ? null : Number(String(v).replace(',', '.'));

        const catalog = {
            pilotos: [],
            formasPago: [],
            patologias: [],
            productos: [],
            cooperativas: []
        };
        const state = {
            motivos: [],
            rangos: [],
            items: []
        }; // items incluyen receta única por producto

        const getFilters = () => ({
            piloto: els.piloto.value.trim(),
            ses_usuario: els.ses_usuario.value.trim(),
            estado: els.estado_filtro.value, // <- se envía como 'estado' al backend
            fecha_visita: els.fecha_visita.value
        });

        async function loadCatalogs() {
            const qs = (a) => fetch(`${DRONE_API}?action=${a}`, {
                cache: 'no-store'
            }).then(r => r.json());
            const [pi, fp, pa, pr, co] = await Promise.all([
                qs('list_pilotos'),
                qs('list_formas_pago'),
                qs('list_patologias'),
                qs('list_productos'),
                qs('list_cooperativas')
            ]);
            catalog.pilotos = pi.ok ? pi.data : [];
            catalog.formasPago = fp.ok ? fp.data : [];
            catalog.patologias = pa.ok ? pa.data : [];
            catalog.productos = pr.ok ? pr.data : [];
            catalog.cooperativas = co.ok ? co.data : [];
        }

        function fillSelect(sel, data, {
            valueKey = 'id',
            labelKey = 'nombre',
            selected = null,
            placeholder = null
        } = {}) {
            sel.innerHTML = '';
            if (placeholder !== null) {
                const op = document.createElement('option');
                op.value = '';
                op.textContent = placeholder;
                sel.appendChild(op);
            }
            data.forEach(r => {
                const op = document.createElement('option');
                op.value = r[valueKey];
                op.textContent = r[labelKey];
                if (selected !== null && String(selected) === String(r[valueKey])) op.selected = true;
                sel.appendChild(op);
            });
        }

        // listado
        let currentListAbort = null;
        async function load() {
            const params = new URLSearchParams({
                action: 'list_solicitudes',
                ...getFilters()
            });
            try {
                if (currentListAbort) currentListAbort.abort();
                currentListAbort = new AbortController();
                const res = await fetch(`${DRONE_API}?${params.toString()}`, {
                    cache: 'no-store',
                    signal: currentListAbort.signal
                });
                const json = await res.json();
                if (!json.ok) throw new Error(json.error || 'Error');
                renderCards(json.data.items || []);
            } catch (e) {
                if (e.name === 'AbortError') return; // se canceló por nueva consulta
                console.error(e);
                els.cards.innerHTML = '<div class="card">Ocurrió un error cargando las solicitudes.</div>';
            }
        }

        function prettyEstado(e) {
            switch ((e || '').toLowerCase()) {
                case 'ingresada':
                    return 'Ingresada';
                case 'procesando':
                    return 'Procesando';
                case 'aprobada_coop':
                    return 'Aprobada coop';
                case 'cancelada':
                    return 'Cancelada';
                case 'completada':
                    return 'Completada';
                default:
                    return e || '';
            }
        }

        function badgeClass(e) {
            switch ((e || '').toLowerCase()) {
                case 'ingresada':
                    return 'warning';
                case 'procesando':
                    return 'info';
                case 'aprobada_coop':
                    return 'primary';
                case 'completada':
                    return 'success';
                case 'cancelada':
                    return 'danger';
                default:
                    return 'secondary';
            }
        }

        function renderCards(items) {
            els.cards.innerHTML = '';
            items.forEach(it => {
                const card = document.createElement('div');
                card.className = 'product-card';
                card.innerHTML = `
  <div class="product-header">
    <h4>${esc(it.productor_nombre || it.ses_usuario || 'Sin dato')}</h4>
    <p>Pedido número: ${esc(it.id??'')}</p>
  </div>
  <div class="product-body">
    <div class="user-info">
      <div>
        <strong>${esc(it.piloto||'Sin piloto asignado')}</strong>
        <div class="role">
          Fecha visita: ${esc(it.fecha_visita||'')}
          ${it.hora_visita ? `(${esc(it.hora_visita)})` : ''}
        </div>
      </div>
    </div>

    <div class="mini-block">
      <div class="mini-title">Observaciones productor</div>
      <p class="description">${esc(it.observaciones||'—')}</p>
    </div>

    <div class="mini-block">
      <div class="mini-title">Costo servicio</div>
      <p class="price">$${fmtNum(it.costo_total ?? 0)}</p>
    </div>

    <hr />

    <div class="product-footer">
      <div class="metric">
        <span class="badge ${badgeClass(it.estado)}">${prettyEstado(it.estado)}</span>
        ${it.motivo_cancelacion ? `<span class="motivo-cancel">${esc(it.motivo_cancelacion)}</span>` : ''}
      </div>
      <button class="btn-view" data-id="${it.id}">Ver detalle</button>
    </div>
  </div>`;


                els.cards.appendChild(card);
            });

            els.cards.querySelectorAll('.btn-view').forEach(btn => {
                btn.addEventListener('click', async () => {
                    const id = btn.dataset.id;
                    try {
                        await loadCatalogs();
                        const url = `${DRONE_API}?action=get_solicitud_full&id=${encodeURIComponent(id)}`;
                        const res = await fetch(url, {
                            cache: 'no-store'
                        });
                        const json = await res.json();
                        if (!json.ok) throw new Error(json.error || 'Error');
                        fillForm(json.data);
                        openDrawer({
                            id
                        });
                    } catch (err) {
                        console.error('No se pudo obtener la solicitud', err);
                        openDrawer({
                            id
                        });
                    }
                });
            });
        }

        // chips / listas
        const $chipsPat = $('#patologias-chips');
        const $chipsRan = $('#rangos-chips');
        const $listProd = $('#productos-list');


        function renderPatologias() {
            if (!$chipsPat) return;
            $chipsPat.innerHTML = '';
            state.motivos.forEach((m, i) => {
                const pat = catalog.patologias.find(p => String(p.id) === String(m.patologia_id));
                const label = pat ? pat.nombre : (m.patologia_nombre || 'Otra');
                const extra = m.es_otros ? ` (otros: ${m.otros_text || '—'})` : '';
                const div = document.createElement('div');
                div.className = 'chip-pill';
                div.innerHTML = `<span>${esc(label)}${esc(extra)}</span><button class="close" aria-label="Quitar">&times;</button>`;
                div.querySelector('.close').addEventListener('click', () => {
                    state.motivos.splice(i, 1);
                    renderPatologias();
                });
                $chipsPat.appendChild(div);
            });
        }

        function renderRangos() {
            if (!$chipsRan) return;
            $chipsRan.innerHTML = '';
            state.rangos.forEach((r, i) => {
                const div = document.createElement('div');
                div.className = 'chip-pill';
                div.innerHTML = `<span>${esc(r.rango)}</span><button class="close" aria-label="Quitar">&times;</button>`;
                div.querySelector('.close').addEventListener('click', () => {
                    state.rangos.splice(i, 1);
                    renderRangos();
                });
                $chipsRan.appendChild(div);
            });
        }

        function renderProductos() {
            if (!$listProd) return;
            $listProd.innerHTML = '';
            state.items.forEach((it, idx) => {
                const pInfo = catalog.productos.find(p => String(p.id) === String(it.producto_id));
                const nombre = it.nombre_producto || pInfo?.nombre || `Producto #${it.producto_id}`;
                const pat = it.patologia_id ?
                    catalog.patologias.find(p => String(p.id) === String(it.patologia_id)) :
                    null;
                const costo = it.costo_hectarea_snapshot ?? pInfo?.costo_hectarea ?? null;

                const wrapper = document.createElement('div');
                wrapper.className = 'producto-item';
                wrapper.innerHTML = `
      <div>
        <strong>${esc(nombre)}</strong>
        <div class="meta">Fuente: ${esc(it.fuente || 'sve')}${pat ? ` · Patología: ${esc(pat.nombre)}` : ''}</div>
        <div class="meta">Costo/ha: $${fmtNum(costo)}</div>
      </div>
      <div class="acciones">
        <button type="button" class="btn btn-cancelar" data-role="quitar">Quitar</button>
      </div>`;
                wrapper.querySelector('[data-role="quitar"]').addEventListener('click', () => {
                    state.items.splice(idx, 1);
                    renderProductos();
                    renderRecetaCombinada();
                    recalcCostos();
                });
                $listProd.appendChild(wrapper);
            });
        }


        // receta combinada
        function ensureRecetaSlots() {
            state.items.forEach(it => {
                if (!it.receta) {
                    const first = Array.isArray(it.recetas) && it.recetas.length ? it.recetas[0] : null;
                    it.receta = {
                        principio_activo: first?.principio_activo ?? it.principio_activo ?? null,
                        dosis: first?.dosis ?? null,
                        unidad: first?.unidad ?? '',
                        orden_mezcla: first?.orden_mezcla ?? null,
                        notas: first?.notas ?? ''
                    };
                }
            });
        }

        function renderRecetaCombinada() {
            const tb = $('#tabla-receta-combinada tbody');
            if (!tb) return;
            ensureRecetaSlots();
            tb.innerHTML = '';

            state.items.forEach((it) => {
                const pInfo = catalog.productos.find(p => String(p.id) === String(it.producto_id));
                const nombre = it.nombre_producto || pInfo?.nombre || `Producto #${it.producto_id}`;
                const r = it.receta;

                const tr = document.createElement('tr');
                tr.innerHTML = `
        <td>${esc(nombre)}</td>
        <td><div class="input-icon input-icon-edit"><input type="text" value="${esc(r.principio_activo ?? (it.principio_activo || ''))}"></div></td>
        <td><div class="input-icon input-icon-hashtag"><input type="number" step="0.001" value="${fmtNum(r.dosis)}"></div></td>
        <td><div class="input-icon input-icon-edit"><input type="text" value="${esc(r.unidad||'')}"></div></td>
        <td><div class="input-icon input-icon-hashtag"><input type="number" value="${fmtNum(r.orden_mezcla)}"></div></td>
        <td><div class="input-icon input-icon-edit"><input type="text" value="${esc(r.notas||'')}"></div></td>
      `;
                const [pa, dosis, uni, ord, notas] = tr.querySelectorAll('input');
                pa.addEventListener('input', e => it.receta.principio_activo = e.target.value);
                dosis.addEventListener('input', e => it.receta.dosis = parseNum(e.target.value));
                uni.addEventListener('input', e => it.receta.unidad = e.target.value);
                ord.addEventListener('input', e => it.receta.orden_mezcla = parseNum(e.target.value));
                notas.addEventListener('input', e => it.receta.notas = e.target.value);

                tb.appendChild(tr);
            });
        }

        // costos
        function recalcCostos() {
            const base_ha = parseNum($('#base_ha')?.value);
            const costo_base = parseNum($('#costo_base_por_ha')?.value);
            const base_total = (base_ha || 0) * (costo_base || 0);
            $('#base_total') && ($('#base_total').value = fmtNum(base_total));

            let productos_total = 0;
            state.items.forEach(it => {
                const ch = Number(it.costo_hectarea_snapshot || 0);
                productos_total += ch * (base_ha || 0);
                it.total_producto_snapshot = ch * (base_ha || 0);
            });
            $('#productos_total') && ($('#productos_total').value = fmtNum(productos_total));

            const total = base_total + productos_total;
            $('#total') && ($('#total').value = fmtNum(total));

            const resumen = $('#costos-resumen');
            if (resumen) {
                const baseTxt = `Base: ${fmtNum(base_ha||0)} ha × $${fmtNum(costo_base||0)} = $${fmtNum(base_total)}`;
                const prodsTxt = `Productos: $${fmtNum(productos_total)}`;
                const totalTxt = `<strong>Total: $${fmtNum(total)}</strong>`;
                resumen.innerHTML = `<p>${baseTxt}</p><p>${prodsTxt}</p><p>${totalTxt}</p>`;
            }
        }

        // utils set/get
        function setV(id, val) {
            const n = document.getElementById(id);
            if (!n) return;
            if (n.type === 'checkbox') n.checked = Boolean(val);
            else n.value = (val ?? '') === null ? '' : String(val ?? '');
        }

        function getV(id) {
            const n = document.getElementById(id);
            if (!n) return null;
            return n.value === '' ? null : n.value;
        }

        document.getElementById('forma_pago_id')?.addEventListener('change', toggleCoopField);
        // estado cancelada -> mostrar motivo
        function toggleMotivo() {
            const sel = document.querySelector('#form-solicitud #estado');
            const grp = document.querySelector('#grp_motivo_cancelacion');
            const help = document.querySelector('#estadoHelp');
            const motivo = document.querySelector('#motivo_cancelacion');
            if (!sel || !grp || !help || !motivo) return;

            const isCancelada = String(sel.value).toLowerCase() === 'cancelada';
            grp.style.display = isCancelada ? 'block' : 'none'; // <-- antes: ''

            motivo.required = isCancelada;
            help.textContent = isCancelada ?
                'Seleccionaste “Cancelada”. Indicá el motivo en el campo de abajo.' :
                'Seleccioná el estado actual.';

            if (isCancelada) setTimeout(() => motivo.focus(), 0);
        }

        // Forma de pago = Cooperativa => mostrar selector de cooperativas
        function toggleCoopField() {
            const sel = document.getElementById('forma_pago_id');
            const grp = document.getElementById('grp_coop_descuento');
            const coSel = document.getElementById('coop_descuento_nombre');
            if (!sel || !grp) return;
            const fp = catalog.formasPago.find(f => String(f.id) === String(sel.value));
            const isCoop = !!(fp && String(fp.nombre || '').toLowerCase().includes('cooperativa'));
            grp.style.display = isCoop ? '' : 'none';
            if (!isCoop && coSel) coSel.value = '';
        }

        // después de setear forma de pago y coop_descuento_nombre:
const fpSel = $('#forma_pago_id');
const coopSel = $('#coop_descuento_nombre');
toggleCoopField();

(function pintarCoopEtiqueta(){
  const wrap = $('#coop_etiqueta_wrap');
  const out  = $('#coop_etiqueta');
  if (!wrap || !out) return;
  const fp = catalog.formasPago.find(f => String(f.id) === String(fpSel.value));
  const isCoop = !!(fp && String(fp.nombre || '').toLowerCase().includes('cooperativa'));
  if (!isCoop) { wrap.style.display = 'none'; out.value=''; return; }

  const codigo = getV('coop_descuento_nombre'); // id_real (ej: C2)
  let etiqueta = codigo || '';
  // Buscar nombre “humano” por relación del productor (preferente) o catálogo general
  const rel = (d.productor?.cooperativas || []).find(c => String(c.cooperativa_id_real) === String(codigo));
  if (rel?.cooperativa_usuario) etiqueta = `${rel.cooperativa_usuario} (${codigo})`;
  else {
    const cata = (catalog.cooperativas || []).find(c => String(c.id_real||c.id) === String(codigo) || String(c.nombre) === String(codigo));
    if (cata?.usuario || cata?.nombre) etiqueta = `${(cata.usuario || cata.nombre)} (${codigo})`;
  }
  out.value = etiqueta || '';
  wrap.style.display = etiqueta ? '' : 'none';
})();

$('#forma_pago_id')?.addEventListener('change', () => { toggleCoopField(); /* repintar */ fillForm({ solicitud: { ...(__ULTIMO_PEDIDO__?.solicitud||{}), forma_pago_id: getV('forma_pago_id'), coop_descuento_nombre: getV('coop_descuento_nombre') }, productor: __ULTIMO_PEDIDO__?.productor }); });
$('#coop_descuento_nombre')?.addEventListener('change', () => { const d=__ULTIMO_PEDIDO__||{}; fillForm({ solicitud: { ...(d.solicitud||{}), forma_pago_id:getV('forma_pago_id'), coop_descuento_nombre:getV('coop_descuento_nombre') }, productor:d.productor }); });


        // rellenar formulario
        function fillForm(d) {
            $('#drawer-id').textContent = d?.solicitud?.id ? `#${d.solicitud.id}` : '';

            const s = d.solicitud || {};
            setV('productor_id_real', s.productor_id_real);
            setV('ses_usuario_edit', s.ses_usuario ?? d?.productor?.usuario ?? '');
            setV('superficie_ha', fmtNum(s.superficie_ha));
            setV('fecha_visita_edit', s.fecha_visita);
            setV('hora_visita_desde', s.hora_visita_desde);
            setV('hora_visita_hasta', s.hora_visita_hasta);
            setV('estado', s.estado);
            toggleMotivo();
            setV('motivo_cancelacion', s.motivo_cancelacion);
            setV('observaciones', s.observaciones);

            // flags
            ['representante', 'linea_tension', 'zona_restringida', 'corriente_electrica', 'agua_potable', 'libre_obstaculos', 'area_despegue', 'en_finca']
            .forEach(k => setV(k, s[k]));

            // dir + ubica
            ['dir_provincia', 'dir_localidad', 'dir_calle', 'dir_numero', 'ubicacion_lat', 'ubicacion_lng', 'ubicacion_acc']
            .forEach(k => setV(k, s[k]));

            // selects de catálogos
            fillSelect($('#piloto_id'), catalog.pilotos, {
                selected: s.piloto_id,
                placeholder: 'Seleccionar piloto'
            });
            fillSelect($('#forma_pago_id'), catalog.formasPago, {
                selected: s.forma_pago_id,
                placeholder: 'Seleccionar forma de pago'
            });
            /* Cooperativas: mostrar nombre pero guardar id_real */
            fillSelect($('#coop_descuento_nombre'), catalog.cooperativas, {
                valueKey: 'id_real',
                labelKey: 'usuario',
                selected: s.coop_descuento_nombre,
                placeholder: 'Seleccionar cooperativa'
            });
            toggleCoopField();
            fillSelect($('#patologia_new'), catalog.patologias, {
                placeholder: 'Seleccionar patología'
            });
            fillSelect($('#producto_new'), catalog.productos, {
                placeholder: 'Seleccionar producto'
            });

            // opción "Otra" en patologías
            const selPatNew = $('#patologia_new');
            selPatNew.append(new Option('Otra', '__otra__'));
            selPatNew.addEventListener('change', e => {
                const show = e.target.value === '__otra__';
                $('#grp_patologia_otro_text').style.display = show ? '' : 'none';
                // al elegir "Otra" marcamos "Sí" en el toggle de otros para coherencia visual
                $('#patologia_new_otro').value = show ? '1' : '0';
            });

            // costos
            const c = d.costos || {};
            setV('costo_moneda', c.moneda);
            setV('costo_base_por_ha', fmtNum(c.costo_base_por_ha));
            setV('base_ha', fmtNum(c.base_ha));
            setV('base_total', fmtNum(c.base_total));
            setV('productos_total', fmtNum(c.productos_total));
            setV('total', fmtNum(c.total));
            recalcCostos();

            // motivos
            state.motivos = (d.motivos || []).map(m => ({
                patologia_id: m.patologia_id,
                es_otros: Number(m.es_otros) ? 1 : 0,
                otros_text: m.otros_text || '',
                patologia_nombre: m.patologia_nombre
            }));
            renderPatologias();

            // rangos
            state.rangos = (d.rangos || []).map(r => ({
                rango: r.rango
            }));
            renderRangos();

            // items -> receta única por producto
            state.items = (d.items || []).map(it => ({
                patologia_id: it.patologia_id,
                fuente: it.fuente || 'sve',
                producto_id: it.producto_id,
                nombre_producto: it.producto_nombre || it.nombre_producto || null,
                costo_hectarea_snapshot: it.costo_hectarea_snapshot ?? it.producto_costo_hectarea ?? null,
                receta: (() => {
                    const r0 = (it.recetas && it.recetas[0]) ? it.recetas[0] : null;
                    return {
                        principio_activo: r0?.principio_activo ?? it.principio_activo ?? null,
                        dosis: r0?.dosis ?? null,
                        unidad: r0?.unidad ?? '',
                        orden_mezcla: r0?.orden_mezcla ?? null,
                        notas: r0?.notas ?? ''
                    };
                })()
            }));
            renderProductos();
            renderRecetaCombinada();
            recalcCostos();
        }

        // listeners
        document.querySelector('#form-solicitud #estado')?.addEventListener('change', toggleMotivo);

        $('#btn-abrir-ubicacion')?.addEventListener('click', () => {
            const lat = getV('ubicacion_lat');
            const lng = getV('ubicacion_lng');
            if (!lat || !lng) {
                showAlert('error', 'Cargá latitud y longitud primero');
                return;
            }
            window.open(`https://www.google.com/maps?q=${lat},${lng}`, '_blank');
        });

        // Patologías
        $('#patologia_new_otro')?.addEventListener('change', (e) => {
            $('#grp_patologia_otro_text').style.display = e.target.value === '1' ? '' : 'none';
        });
        $('#btn_add_patologia')?.addEventListener('click', () => {
            const val = $('#patologia_new').value;
            if (!val) return;

            if (val === '__otra__') {
                const txt = ($('#patologia_new_text').value || '').trim();
                if (!txt) return showAlert('error', 'Escribí el detalle para "Otra".');
                state.motivos.push({
                    patologia_id: null,
                    es_otros: 1,
                    otros_text: txt
                });
            } else {
                state.motivos.push({
                    patologia_id: Number(val),
                    es_otros: 0,
                    otros_text: ''
                });
            }

            $('#patologia_new').value = '';
            $('#patologia_new_text').value = '';
            $('#grp_patologia_otro_text').style.display = 'none';
            renderPatologias();
        });

        // Rangos
        $('#btn_add_rango')?.addEventListener('click', () => {
            const r = $('#rango_new').value;
            if (!r) return;
            state.rangos.push({
                rango: r
            });
            $('#rango_new').value = '';
            renderRangos();
        });

        // productos
        $('#btn_add_producto')?.addEventListener('click', () => {
            const pid = $('#producto_new').value;
            if (!pid) return showAlert('error', 'Elegí un producto');

            const fuente = 'sve';
            const patologiaIdAuto = state.motivos[0]?.patologia_id ?? null;
            const prod = catalog.productos.find(p => String(p.id) === String(pid));

            state.items.push({
                patologia_id: patologiaIdAuto,
                fuente,
                producto_id: Number(pid),
                nombre_producto: prod?.nombre || null,
                costo_hectarea_snapshot: prod?.costo_hectarea ?? null,
                receta: {
                    principio_activo: null,
                    dosis: null,
                    unidad: '',
                    orden_mezcla: null,
                    notas: ''
                }
            });

            $('#producto_new').value = '';
            renderProductos();
            renderRecetaCombinada();
            recalcCostos();
        });


        // Costos live
        $('#base_ha')?.addEventListener('input', recalcCostos);
        $('#costo_base_por_ha')?.addEventListener('input', recalcCostos);

        // Guardar
        $('#btn-guardar')?.addEventListener('click', async () => {
            const payload = {
                id: Number(($('#drawer-id').textContent || '').replace('#', '')) || null,
                solicitud: {
                    productor_id_real: getV('productor_id_real'),
                    ses_usuario: getV('ses_usuario_edit'),
                    superficie_ha: parseNum(getV('superficie_ha')),
                    fecha_visita: getV('fecha_visita_edit'),
                    hora_visita_desde: getV('hora_visita_desde'),
                    hora_visita_hasta: getV('hora_visita_hasta'),
                    estado: getV('estado'),
                    motivo_cancelacion: getV('motivo_cancelacion'),
                    observaciones: getV('observaciones'),
                    representante: getV('representante'),
                    linea_tension: getV('linea_tension'),
                    zona_restringida: getV('zona_restringida'),
                    corriente_electrica: getV('corriente_electrica'),
                    agua_potable: getV('agua_potable'),
                    libre_obstaculos: getV('libre_obstaculos'),
                    area_despegue: getV('area_despegue'),
                    en_finca: getV('en_finca'),
                    dir_provincia: getV('dir_provincia'),
                    dir_localidad: getV('dir_localidad'),
                    dir_calle: getV('dir_calle'),
                    dir_numero: getV('dir_numero'),
                    ubicacion_lat: parseNum(getV('ubicacion_lat')),
                    ubicacion_lng: parseNum(getV('ubicacion_lng')),
                    ubicacion_acc: parseNum(getV('ubicacion_acc')),
                    ubicacion_ts: null,
                    piloto_id: getV('piloto_id') ? parseInt(getV('piloto_id'), 10) : null,
                    forma_pago_id: getV('forma_pago_id') ? parseInt(getV('forma_pago_id'), 10) : null,
                    // Guardamos id_real de la cooperativa (o null si no aplica)
                    coop_descuento_nombre: getV('coop_descuento_nombre')

                },
  costos: (function(){
    const obj = {
      moneda: getV('costo_moneda'),
      costo_base_por_ha: parseNum(getV('costo_base_por_ha')),
      base_ha: parseNum(getV('base_ha')),
      base_total: parseNum(getV('base_total')),
      productos_total: parseNum(getV('productos_total')),
      total: parseNum(getV('total')),
      desglose_json: null
    };
    const vacio = !obj.moneda && [obj.costo_base_por_ha,obj.base_ha,obj.base_total,obj.productos_total,obj.total].every(v => v === null);
    return vacio ? undefined : obj; // <- no enviar el bloque si está vacío
  })(),
                motivos: state.motivos.map(m => ({
                    patologia_id: m.patologia_id ? Number(m.patologia_id) : null,
                    es_otros: m.es_otros ? 1 : 0,
                    otros_text: m.es_otros ? (m.otros_text || null) : null
                })),
                items: state.items.map(it => ({
                    patologia_id: it.patologia_id ?? null,
                    fuente: it.fuente || 'sve',
                    producto_id: it.producto_id ?? null,
                    nombre_producto: it.nombre_producto || null,
                    costo_hectarea_snapshot: it.costo_hectarea_snapshot ?? null,
                    total_producto_snapshot: it.total_producto_snapshot ?? null,
                    recetas: [{
                        principio_activo: it.receta?.principio_activo || null,
                        dosis: it.receta?.dosis ?? null,
                        unidad: it.receta?.unidad || null,
                        orden_mezcla: it.receta?.orden_mezcla ?? null,
                        notas: it.receta?.notas || null
                    }]
                })),
                rangos: state.rangos.map(r => ({
                    rango: r.rango
                })),
                parametros: {
                    volumen_ha: parseNum(getV('volumen_ha')),
                    velocidad_vuelo: parseNum(getV('velocidad_vuelo')),
                    alto_vuelo: parseNum(getV('alto_vuelo')),
                    ancho_pasada: parseNum(getV('ancho_pasada')),
                    tamano_gota: getV('tamano_gota'),
                    observaciones: getV('param_observaciones')
                }

            };

            if (!payload.id) {
                showAlert('error', 'ID de solicitud no válido');
                return;
            }
            try {
                const res = await fetch(`${DRONE_API}?action=update_solicitud`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(payload),
                    cache: 'no-store'
                });
                const json = await res.json();
                if (!json.ok) throw new Error(json.error || 'Error');
                showAlert('success', '¡Operación completada con éxito!');
                closeDrawer();
                debouncedLoad();
            } catch (err) {
                showAlert('error', `No se pudo guardar: ${err.message}`);
            }
        });

        // Drawer
        const drawer = document.getElementById('drawer');
        const drawerPanel = drawer.querySelector('.sv-drawer__panel');
        const drawerOverlay = drawer.querySelector('.sv-drawer__overlay');
        const drawerClose = document.getElementById('drawer-close');
        const drawerCancel = document.getElementById('drawer-cancel');
        let lastFocus = null;

        async function openDrawer({
            id
        }) {
            lastFocus = document.activeElement;
            $('#drawer-id').textContent = `#${id}`;
            drawer.setAttribute('aria-hidden', 'false');
            drawer.classList.remove('hidden', 'closing');
            drawer.classList.add('opening');
            drawerPanel.setAttribute('tabindex', '-1');
            setTimeout(() => drawerPanel.focus(), 0);
            const onEnd = (e) => {
                if (e.target !== drawerPanel) return;
                drawer.classList.remove('opening');
                drawer.removeEventListener('animationend', onEnd, true);
            };
            drawer.addEventListener('animationend', onEnd, true);
        }

        function closeDrawer() {
            const active = document.activeElement;
            if (active && drawer.contains(active)) {
                if (lastFocus && typeof lastFocus.focus === 'function') lastFocus.focus();
                else {
                    document.body.setAttribute('tabindex', '-1');
                    document.body.focus();
                    document.body.removeAttribute('tabindex');
                }
            }
            drawer.classList.add('closing');
            drawer.setAttribute('aria-hidden', 'true');
            const onEnd = (e) => {
                if (e.target !== drawerPanel) return;
                drawer.classList.remove('closing');
                drawer.classList.add('hidden');
                drawer.removeEventListener('animationend', onEnd, true);
            };
            drawer.addEventListener('animationend', onEnd, true);
        }
        drawerOverlay.addEventListener('click', closeDrawer);
        drawerClose.addEventListener('click', closeDrawer);
        drawerCancel.addEventListener('click', closeDrawer);

        // Filtros en vivo
        const debouncedLoad = debounce(load, 300);
        els.piloto.addEventListener('input', debouncedLoad);
        els.ses_usuario.addEventListener('input', debouncedLoad);
        els.estado_filtro.addEventListener('change', debouncedLoad);
        els.fecha_visita.addEventListener('change', debouncedLoad);

        load(); // arranque
    })();

    // Funciones para borrar
    /* == DEBUG PEDIDO (re-fetch al backend) ==================================== */
    let __ULTIMO_PEDIDO__ = null;

    function debugPedidoPretty(data) {
        try {
            console.groupCollapsed('[Pedido] Detalle completo');
            console.log('Solicitud:', data.solicitud);
            console.log('Costos:', data.costos);
            console.log('Items:', data.items);
            console.log('Motivos:', data.motivos);
            console.log('Rangos:', data.rangos);
            console.log('Productor:', data.productor);
            console.log('Piloto:', data.piloto);
            console.log('Forma de pago:', data.forma_pago);
            console.log('Eventos:', data.eventos);
            console.log('JSON:', JSON.stringify(data, null, 2));
        } finally {
            console.groupEnd();
        }
    }

    function getCurrentSolicitudId() {
        const t = (document.getElementById('drawer-id')?.textContent || '').trim();
        const m = t.match(/#?(\d+)/);
        return m ? parseInt(m[1], 10) : null;
    }

    async function fetchPedidoById(id) {
        const url = `${DRONE_API}?action=get_solicitud_full&id=${encodeURIComponent(id)}`;
        const res = await fetch(url, {
            cache: 'no-store'
        });
        const json = await res.json();
        if (!json.ok) throw new Error(json.error || 'Error');
        return json.data;
    }

    document.getElementById('btn-debug-pedido')?.addEventListener('click', async () => {
        try {
            const id = getCurrentSolicitudId();
            if (!id) {
                console.warn('No hay ID de solicitud en el drawer.');
                return;
            }
            // Re-fetch siempre para tener lo último del backend
            const data = await fetchPedidoById(id);
            __ULTIMO_PEDIDO__ = data; // refresco el cache local también
            debugPedidoPretty(data); // imprime todo legible
        } catch (e) {
            console.error('No se pudo obtener el pedido:', e);
        }
    });
</script>