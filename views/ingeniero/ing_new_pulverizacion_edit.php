<?php /* Vista mínima + productos por patología + costos + snapshot productor */ ?>
<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Nueva solicitud de pulverización</title>
    <style>
        body {
            font-family: system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, sans-serif;
            background: #f8fafc;
            margin: 0;
            padding: 16px
        }

        .card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 16px;
            max-width: 1280px;
            margin: 0 auto 16px
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px
        }

        @media (max-width:900px) {
            .grid {
                grid-template-columns: repeat(2, 1fr)
            }
        }

        @media (max-width:600px) {
            .grid {
                grid-template-columns: 1fr
            }
        }

        label {
            font-weight: 600;
            font-size: .95rem;
            margin-bottom: 4px;
            display: block
        }

        input,
        select,
        button,
        textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 1rem
        }

        .full {
            grid-column: 1/-1
        }

        .btns {
            display: flex;
            gap: 8px;
            justify-content: flex-end;
            margin-top: 10px
        }

        .btn-primary {
            background: #4f46e5;
            color: #fff;
            border: none;
            cursor: pointer
        }

        .typeahead {
            position: relative
        }

        .ta-list {
            position: absolute;
            z-index: 10;
            top: 100%;
            left: 0;
            right: 0;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            margin-top: 4px;
            max-height: 220px;
            overflow: auto
        }

        .ta-item {
            padding: 8px;
            cursor: pointer
        }

        .ta-item:hover {
            background: #f1f5f9
        }

        .hidden {
            display: none
        }

        .error {
            border-color: #ef4444
        }

        .notice {
            padding: 10px;
            border-radius: 8px;
            background: #ecfeff;
            border: 1px solid #a5f3fc;
            margin-bottom: 12px
        }

        .chips-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 6px;
            flex-direction: row;
            /* desktop: horizontal */
        }

        .chip input[type="checkbox"] {
            position: absolute;
            opacity: 0;
            pointer-events: none
        }

        .chip-box {
            display: flex;
            align-items: center;
            gap: .5rem;
            padding: 8px 10px;
            border: 1px solid #e5e7eb;
            border-radius: 999px;
            background: #fff;
            cursor: pointer;
            user-select: none
        }

        .chip input[type="checkbox"]:checked+.chip-box {
            background: #ecfdf5;
            border-color: #10b981;
            box-shadow: 0 0 0 2px rgba(16, 185, 129, .20) inset
        }

        .chip-name {
            font-weight: 600
        }

        .chip-cost {
            font-size: .9rem;
            opacity: .7
        }

        .chips-custom {
            margin-top: 8px
        }

        .tabla-wrapper {
            overflow: auto
        }

        table {
            width: 100%;
            border-collapse: collapse
        }

        thead th {
            background: #f8fafc
        }

        th,
        td {
            border: 1px solid #e5e7eb;
            padding: 8px;
            text-align: left
        }

        .right {
            text-align: right;
            white-space: nowrap
        }

        /* Título pequeño de sección (similar a “APPS”) */
        .sidebar-section-title {
            margin: 12px 16px 6px;
            font-size: .72rem;
            font-weight: 600;
            letter-spacing: .08em;
            text-transform: uppercase;
            opacity: .7;
        }

        /* Lista simple de subitems */
        .submenu-root {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .submenu-root a {
            display: flex;
            align-items: center;
            gap: .5rem;
            padding: .4rem 1.5rem;
            text-decoration: none;
        }

        /* Tarjetas por patología dentro de "Productos sugeridos" */
        .pat-card {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 12px;
            margin-top: 10px;
            background: #fff;
        }

        .pat-card h4 {
            margin: 0 0 8px 0;
            font-size: 1rem;
        }

        /* Chips: vertical en mobile */
        @media (max-width:600px) {
            .chips-grid {
                flex-direction: column;
                /* mobile: vertical */
            }
        }
    </style>
</head>

<body>

    <div class="card">
        <h2 style="text-align:center;">Formulario de inscripción</h2>
        <p class="notice">Completá los campos y confirmá. Se enviará un correo al productor y, si corresponde, a la cooperativa.</p>

        <form id="frm" novalidate>
            <div class="grid">

                <!-- Productor (typeahead) -->
                <div class="full">
                    <label for="prod_txt">Productor</label>
                    <div class="typeahead">
                        <input id="prod_txt" autocomplete="off" placeholder="Buscar productor..." />
                        <input type="hidden" id="prod_idreal" />
                        <input type="hidden" id="prod_nombre_snap" />
                        <div id="ta" class="ta-list hidden" role="listbox" aria-label="Sugerencias"></div>
                    </div>
                </div>

                <!-- Campos Si/No -->
                <?php
                $bin = [
                    'representante' => '¿Hay representante en la finca?',
                    'linea_tension' => '¿Líneas de media/alta tensión (<30m)?',
                    'zona_restringida' => '¿Zonas restringidas cercanas?',
                    'corriente_electrica' => '¿Corriente eléctrica disponible?',
                    'agua_potable' => '¿Agua potable disponible?',
                    'libre_obstaculos' => '¿Cuarteles libres de obstáculos?',
                    'area_despegue' => '¿Área de despegue apropiada?'
                ];
                foreach ($bin as $id => $lbl): ?>
                    <div>
                        <label for="<?= $id ?>"><?= $lbl ?></label>
                        <select id="<?= $id ?>">
                            <option value="">Seleccionar</option>
                            <option value="si">Si</option>
                            <option value="no">No</option>
                        </select>
                    </div>
                <?php endforeach; ?>

                <!-- Hectáreas -->
                <div>
                    <label for="ha">Hectáreas</label>
                    <input id="ha" type="number" min="1" step="1" placeholder="Ej: 10" />
                </div>

                <!-- Forma de pago -->
                <div>
                    <label for="pago">Forma de pago</label>
                    <select id="pago"></select>
                </div>

                <!-- Cooperativa (solo si forma_pago_id = 6) -->
                <div id="coop_wrap" class="hidden">
                    <label for="coop">Cooperativa (si aplica)</label>
                    <select id="coop"></select>
                </div>

                <!-- Quincena -->
                <div>
                    <label for="rango">Quincena de visita</label>
                    <select id="rango"></select>
                </div>

                <!-- Dirección -->
                <div style="display: none;">
                    <label for="prov">Provincia</label>
                    <input id="prov" value="Mendoza" />
                </div>
                <div>
                    <label for="loc">Localidad</label>
                    <input id="loc" />
                </div>
                <div>
                    <label for="calle">Calle</label>
                    <input id="calle" />
                </div>
                <div>
                    <label for="nro">Número</label>
                    <input id="nro" type="number" min="1" step="1" />
                </div>

                <!-- Tarjeta de Patologías (nueva, debajo de dirección y arriba de observaciones) -->
                <div class="full card" id="card-patologias">
                    <h3>Patologías</h3>
                    <div id="pat-chips" class="chips-grid" role="group" aria-label="Seleccionar patologías"></div>
                </div>

                <!-- Observaciones -->
                <div class="full">
                    <label for="obs">Observaciones</label>
                    <textarea id="obs" rows="3" maxlength="233"></textarea>
                </div>

                <!-- Productos por patología (tarjetas por cada patología seleccionada) -->
                <div class="full card" id="card-productos" hidden>
                    <h3>Productos sugeridos por patología</h3>
                    <div id="cards-productos"></div>
                </div>

                <!-- Costos -->
                <div class="full card" id="card-costos">
                    <h3>Resumen de costos</h3>
                    <div class="tabla-wrapper">
                        <table aria-label="Resumen de costos">
                            <thead>
                                <tr>
                                    <th>Ítem</th>
                                    <th>Detalle</th>
                                    <th class="right">Importe</th>
                                </tr>
                            </thead>
                            <tbody id="costos-body"></tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="2">Precio final</th>
                                    <th id="precio-final" class="right">$ 0,00</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

            </div>

            <div class="btns">
                <button type="button" id="enviar" class="btn-primary">Confirmar y guardar</button>
                <button type="reset">Limpiar</button>
            </div>
        </form>

        <div id="msg" class="notice hidden"></div>
    </div>

    <script>
        (() => {
            const CTRL = '../../controllers/ing_new_pulverizacion_edit_controller.php';

            const $ = s => document.querySelector(s);
            const show = (el, v) => el.classList.toggle('hidden', !v);
            const msg = $('#msg');

            async function getJSON(url) {
                const r = await fetch(url, {
                    credentials: 'same-origin'
                });
                const txt = await r.text();
                try {
                    const j = JSON.parse(txt);
                    if (!j.ok) throw new Error(j.error || 'Error');
                    return j.data;
                } catch (e) {
                    console.error('No es JSON. Respuesta cruda:', txt.slice(0, 300));
                    throw new Error('Respuesta no-JSON del servidor');
                }
            }
            async function postJSON(url, body) {
                const r = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(body)
                });
                const j = await r.json();
                if (!j.ok) throw new Error(j.error || 'Error');
                return j.data;
            }

            // ====== Estado productos/costos ======
            const ProductosState = {
                seleccionados: new Set(), // keys `${id}_${patologia_id}`
                catalog: new Map(), // key -> {id, patologia_id, nombre, costo_hectarea, detalle}
                catalogByPat: new Map(), // pat_id -> Array<{id, nombre, costo_hectarea, detalle}>
                customByPat: new Map(), // pat_id -> string (nombre ingresado "Otro")
                costoBaseHa: 0,
                moneda: 'ARS'
            };

            const pago = $('#pago'),
                coop = $('#coop'),
                rango = $('#rango'),
                patChips = $('#pat-chips');

            const coopWrap = $('#coop_wrap');
            const cardProd = $('#card-productos'),
                cardsProductos = $('#cards-productos');
            const costosBody = $('#costos-body'),
                precioFinal = $('#precio-final');

            function fmtMon(n, curr) {
                try {
                    return new Intl.NumberFormat('es-AR', {
                        style: 'currency',
                        currency: curr
                    }).format(Number(n || 0));
                } catch {
                    return '$ ' + Number(n || 0).toFixed(2);
                }
            }
            const currFrom = (m) => (m === 'USD' ? 'USD' : 'ARS');

            // === Chips de Patologías (render + lectura) ===
            function renderPatChips(pats) {
                const frag = document.createDocumentFragment();
                (pats || []).forEach(p => {
                    const id = Number(p.id);
                    const label = document.createElement('label');
                    label.className = 'chip';
                    label.innerHTML = `
  <input type="checkbox" value="${id}">
  <span class="chip-box">
    <span class="chip-name">${String(p.nombre || '')}</span>
  </span>`;
                    frag.appendChild(label);
                });
                const cont = document.getElementById('pat-chips');
                cont.innerHTML = '';
                cont.appendChild(frag);
            }


            function getSelectedPatIds() {
                return Array.from(document.querySelectorAll('#pat-chips input[type="checkbox"]:checked'))
                    .map(cb => Number(cb.value))
                    .filter(v => v > 0);
            }


            async function init() {
                const [pagos, rangos, pats, coops, costo] = await Promise.all([
                    getJSON(`${CTRL}?action=formas_pago`),
                    getJSON(`${CTRL}?action=rangos`),
                    getJSON(`${CTRL}?action=patologias`),
                    getJSON(`${CTRL}?action=cooperativas`),
                    getJSON(`${CTRL}?action=costo_base_ha`)
                ]);
                pago.innerHTML = '<option value="">Seleccionar</option>' + pagos.map(x => `<option value="${x.id}">${x.nombre}</option>`).join('');
                rango.innerHTML = '<option value="">Seleccionar</option>' + rangos.map(x => `<option value="${x.rango}">${x.label}</option>`).join('');
                window.__PatMap = new Map(pats.map(x => [Number(x.id), String(x.nombre)]));
                renderPatChips(pats);
                coop.innerHTML = '<option value="">Seleccionar</option>' + coops.map(x => `<option value="${x.id_real}">${x.usuario}</option>`).join('');
                ProductosState.costoBaseHa = Number(costo.costo || 0);
                ProductosState.moneda = costo.moneda || 'ARS';
                recalcCostos();
            }
            init().catch(console.error);

            // Evita autocompletado del navegador específicamente en inputs "Otro"
            document.addEventListener('focusin', (e) => {
                if (e.target && e.target.classList && e.target.classList.contains('prod-custom')) {
                    e.target.setAttribute('autocomplete', 'off');
                }
            });


            // Pago -> cooperativa
            pago.addEventListener('change', () => show(coopWrap, String(pago.value) === '6'));

            // Typeahead productores (guarda snapshot de nombre)
            const txt = $('#prod_txt'),
                hid = $('#prod_idreal'),
                ta = $('#ta'),
                prodNombreSnap = $('#prod_nombre_snap');

            // === Prefill de productor desde el parent/URL ===
            // Permite completar automáticamente el productor al abrir el modal.
            // Prioriza: evento -> objeto global -> querystring.

            function __prefillProductor(data) {
                if (!data) return;
                const idReal = String(
                    data.id_real ?? data.productor_id_real ?? data.idReal ?? ''
                ).trim();
                const nombre =
                    (data.nombre ?? data.usuario ?? data.name ?? '').toString().trim();

                if (idReal && nombre) {
                    // Autocompleta UI
                    txt.value = nombre;
                    // Campos que usa el backend:
                    hid.value = idReal.slice(0, 20); // drones_solicitud.productor_id_real (FK -> usuarios.id_real)
                    prodNombreSnap.value = nombre.slice(0, 150); // snapshot opcional
                    // Cierra cualquier typeahead abierto
                    show(ta, false);
                }
            }

            // 1) Escucha un evento para prefill (padre puede dispararlo al abrir el modal)
            window.addEventListener('sve:modal_prefill', (ev) => {
                try {
                    __prefillProductor(ev.detail || {});
                } catch (e) {}
            });

            // 2) Objeto global opcional que el padre puede setear antes de cargar el iframe/contenido
            if (window.__SVE_MODAL_PREFILL) {
                try {
                    __prefillProductor(window.__SVE_MODAL_PREFILL);
                } catch (e) {}
            }

            // 3) Querystring ?prod_id_real=...&prod_nombre=...
            try {
                const qs = new URLSearchParams(location.search);
                const qId = qs.get('prod_id_real');
                const qNom = qs.get('prod_nombre') || qs.get('nombre') || qs.get('usuario');
                if (qId && qNom) __prefillProductor({
                    id_real: qId,
                    nombre: qNom
                });
            } catch (e) {
                /* noop */
            }

            // 4) API pública para uso directo desde el padre
            window.dronePulvPrefill = __prefillProductor;

            // 5) Soporte postMessage desde el parent (cuando está embebido en iframe)
            window.addEventListener('message', (ev) => {
                try {
                    const data = ev && ev.data ? ev.data : null;
                    if (!data) return;
                    // Acepta { type:'sve:modal_prefill', payload:{ id_real, nombre } } o el objeto directo
                    if (data.type === 'sve:modal_prefill' && data.payload) {
                        __prefillProductor(data.payload);
                    } else if (data.id_real || data.productor_id_real || data.nombre || data.usuario) {
                        __prefillProductor(data);
                    }
                } catch (e) {}
            });


            let items = [];
            txt.addEventListener('input', async () => {
                const q = txt.value.trim();
                hid.value = '';
                prodNombreSnap.value = '';
                if (q.length < 2) {
                    show(ta, false);
                    ta.innerHTML = '';
                    return;
                }
                try {
                    const data = await getJSON(`${CTRL}?action=buscar_usuarios&q=${encodeURIComponent(q)}${coop.value?`&coop_id=${encodeURIComponent(coop.value)}`:''}`);
                    items = data || [];
                    // Escapar simple (textContent via template)
                    ta.innerHTML = items.map(it => `<div class="ta-item" data-id="${it.id_real}" data-nombre="${encodeURIComponent(it.usuario)}">${it.usuario}</div>`).join('');
                    show(ta, items.length > 0);
                } catch (e) {
                    show(ta, false);
                }
            });
            ta.addEventListener('mousedown', (e) => {
                const li = e.target.closest('.ta-item');
                if (!li) return;
                const id = li.getAttribute('data-id');
                const nombre = decodeURIComponent(li.getAttribute('data-nombre') || '');
                txt.value = nombre;
                hid.value = id;
                prodNombreSnap.value = nombre;
                show(ta, false);
            });
            document.addEventListener('click', (e) => {
                if (!ta.contains(e.target) && e.target !== txt) {
                    show(ta, false);
                }
            });

            // Patologías (chips) -> cargar productos y renderizar tarjetas por patología
            patChips.addEventListener('change', async () => {
                ProductosState.seleccionados.clear();
                ProductosState.catalog.clear();
                ProductosState.catalogByPat.clear();
                ProductosState.customByPat.clear();
                cardsProductos.innerHTML = '';

                const patIds = getSelectedPatIds();
                if (patIds.length === 0) {
                    cardProd.hidden = true;
                    recalcCostos();
                    return;
                }

                try {
                    const all = await Promise.all(
                        patIds.map(pid =>
                            getJSON(`${CTRL}?action=productos_por_patologia&patologia_id=${pid}`)
                            .then(arr => ({
                                pid,
                                arr
                            }))
                        )
                    );

                    all.forEach(({
                        pid,
                        arr
                    }) => {
                        const list = [];
                        (arr || []).forEach(p => {
                            const id = Number(p.id);
                            const key = `${id}_${pid}`;
                            const item = {
                                id,
                                patologia_id: pid,
                                nombre: String(p.nombre || ''),
                                detalle: String(p.detalle || ''),
                                costo_hectarea: Number(p.costo_hectarea || 0)
                            };
                            ProductosState.catalog.set(key, item);
                            list.push(item);
                        });
                        ProductosState.catalogByPat.set(pid, list);
                    });

                    renderProductoCards(patIds);
                    cardProd.hidden = (ProductosState.catalogByPat.size === 0);
                    recalcCostos();
                } catch (e) {
                    cardProd.hidden = true;
                    recalcCostos();
                }
            });

            function renderProductoCards(patIds) {
                const patName = (id) => (window.__PatMap && window.__PatMap.get(Number(id))) || `Patología ${id}`;
                const moneda = currFrom(ProductosState.moneda);

                const frag = document.createDocumentFragment();
                patIds.forEach(pid => {
                    const wrapper = document.createElement('div');
                    wrapper.className = 'pat-card';
                    wrapper.innerHTML = `<h4>${patName(pid)}</h4>`;

                    // chips de productos para esta patología
                    const chipsDiv = document.createElement('div');
                    chipsDiv.className = 'chips-grid';
                    (ProductosState.catalogByPat.get(pid) || [])
                    .sort((a, b) => a.nombre.localeCompare(b.nombre, 'es'))
                        .forEach(p => {
                            const key = `${p.id}_${pid}`;
                            const label = document.createElement('label');
                            label.className = 'chip';
                            label.innerHTML = `
        <input type="checkbox" value="${key}">
        <span class="chip-box">
            <span class="chip-name">${p.nombre}</span>
            <span class="chip-cost">${fmtMon(p.costo_hectarea, moneda)}/ha</span>
        </span>`;
                            chipsDiv.appendChild(label);
                        });

                    chipsDiv.addEventListener('change', (e) => {
                        const cb = e.target;
                        if (cb && cb.type === 'checkbox' && cb.value) {
                            if (cb.checked) ProductosState.seleccionados.add(cb.value);
                            else ProductosState.seleccionados.delete(cb.value);
                            recalcCostos();
                        }
                    });

                    wrapper.appendChild(chipsDiv);

                    // campo "Otro" por patología (sin autocompletar)
                    const customDiv = document.createElement('div');
                    customDiv.className = 'chips-custom';
                    customDiv.innerHTML = `
                        <label>Si el productor aporta el producto para tratar esta patologia, coloque su nombre aquí:</label>
                        <input
                            type="text"
                            class="prod-custom"
                            data-patologia-id="${pid}"
                            name="prod_custom_${pid}"
                            placeholder="Nombre del producto..."
                            autocomplete="off"
                            aria-autocomplete="none"
                            inputmode="text"
                            spellcheck="false"
                        />
                    `;
                    customDiv.addEventListener('input', (e) => {
                        const inp = e.target;
                        if (inp && inp.classList.contains('prod-custom')) {
                            const pId = Number(inp.getAttribute('data-patologia-id'));
                            const val = (inp.value || '').trim();
                            if (val) ProductosState.customByPat.set(pId, val);
                            else ProductosState.customByPat.delete(pId);
                            recalcCostos();
                        }
                    });


                    wrapper.appendChild(customDiv);
                    frag.appendChild(wrapper);
                });

                cardsProductos.innerHTML = '';
                cardsProductos.appendChild(frag);
            }

            function recalcCostos() {
                const ha = Math.max(0, Number($('#ha').value || 0));
                const baseHa = Number(ProductosState.costoBaseHa || 0);
                const moneda = currFrom(ProductosState.moneda);
                const patName = (id) => (window.__PatMap && window.__PatMap.get(Number(id))) || `Patología ${id}`;

                const rows = [];
                const baseTotal = baseHa * ha;
                rows.push(`<tr><th>Valor de las hectáreas</th><td>${ProductosState.moneda}</td><td class="right">${fmtMon(baseHa, moneda)}</td></tr>`);
                rows.push(`<tr><th>Cantidad de hectáreas</th><td></td><td class="right">${ha}</td></tr>`);
                rows.push(`<tr><th>Total base (servicio)</th><td></td><td class="right">${fmtMon(baseTotal, moneda)}</td></tr>`);

                let prodTotal = 0;
                Array.from(ProductosState.seleccionados).forEach(key => {
                    const p = ProductosState.catalog.get(key);
                    if (!p) return;
                    const tot = (p.costo_hectarea || 0) * ha;
                    prodTotal += tot;
                    rows.push(`<tr><th>Producto</th><td>${p.nombre} (SVE · ${patName(p.patologia_id)})</td><td class="right">${fmtMon(tot, moneda)}</td></tr>`);
                });

                // "Otro" por patología (no suma costo)
                ProductosState.customByPat.forEach((val, pId) => {
                    if ((val || '').trim()) {
                        rows.push(`<tr><th>Producto</th><td>${val} (Productor · ${patName(pId)})</td><td class="right">${fmtMon(0, moneda)}</td></tr>`);
                    }
                });

                const total = baseTotal + prodTotal;
                costosBody.innerHTML = rows.join('');
                precioFinal.textContent = fmtMon(total, moneda);
            }


            // eventos que impactan costos
            $('#ha').addEventListener('input', recalcCostos);

            // Validación mínima
            function valSel(id) {
                const v = $(id).value.trim();
                return v === 'si' || v === 'no';
            }

            function req(el) {
                el.classList.add('error');
                setTimeout(() => el.classList.remove('error'), 1500);
            }

            // Submit
            $('#enviar').addEventListener('click', async () => {
                msg.classList.add('hidden');
                msg.textContent = '';
                const ha = $('#ha'),
                    prov = $('#prov'),
                    loc = $('#loc'),
                    calle = $('#calle'),
                    nro = $('#nro');

                // payload base
                const patIds = getSelectedPatIds();
                const payload = {
                    productor_id_real: $('#prod_idreal').value || null,
                    productor_nombre_snapshot: $('#prod_nombre_snap').value || null,
                    representante: $('#representante').value,
                    linea_tension: $('#linea_tension').value,
                    zona_restringida: $('#zona_restringida').value,
                    corriente_electrica: $('#corriente_electrica').value,
                    agua_potable: $('#agua_potable').value,
                    libre_obstaculos: $('#libre_obstaculos').value,
                    area_despegue: $('#area_despegue').value,
                    superficie_ha: Number(ha.value || 0),
                    forma_pago_id: Number($('#pago').value || 0),
                    coop_descuento_id_real: $('#coop').value || null,
                    patologia_ids: patIds,
                    rango: $('#rango').value || '',
                    dir_provincia: prov.value || '',
                    dir_localidad: loc.value || '',
                    dir_calle: calle.value || '',
                    dir_numero: String(nro.value || ''),
                    observaciones: $('#obs').value || '',
                    items: []
                };


                // items de productos (con patología por item)
                Array.from(ProductosState.seleccionados).forEach(key => {
                    const p = ProductosState.catalog.get(key);
                    if (!p) return;
                    payload.items.push({
                        producto_id: Number(p.id),
                        fuente: 'sve',
                        patologia_id: Number(p.patologia_id)
                    });
                });

                // "Otro" por patología (productor, sin costo)
                ProductosState.customByPat.forEach((val, patId) => {
                    const nombre = (val || '').trim();
                    if (!nombre) return;
                    payload.items.push({
                        producto_id: 0,
                        fuente: 'productor',
                        nombre_producto_custom: nombre,
                        patologia_id: Number(patId)
                    });
                });


                // checks
                if (!payload.productor_id_real) {
                    req($('#prod_txt'));
                    return;
                }
                for (const id of ['#representante', '#linea_tension', '#zona_restringida', '#corriente_electrica', '#agua_potable', '#libre_obstaculos', '#area_despegue']) {
                    if (!valSel(id)) {
                        req($(id));
                        return;
                    }
                }
                if (!(payload.superficie_ha > 0)) {
                    req(ha);
                    return;
                }
                if (!payload.forma_pago_id) {
                    req($('#pago'));
                    return;
                }
                if (payload.forma_pago_id === 6 && !payload.coop_descuento_id_real) {
                    req($('#coop'));
                    return;
                }
                if (!payload.patologia_ids || payload.patologia_ids.length === 0) {
                    req($('#pat-chips'));
                    return;
                }
                if (!payload.rango) {
                    req($('#rango'));
                    return;
                }
                if (!payload.dir_provincia || !payload.dir_localidad || !payload.dir_calle || !payload.dir_numero) {
                    req(prov);
                    return;
                }

                try {
                    const res = await postJSON(CTRL, payload);
                    msg.textContent = `Solicitud creada. ID: ${res.id}`;
                    msg.classList.remove('hidden');
                    $('#frm').reset();
                    show(coopWrap, false);
                    // limpiar estado productos
                    ProductosState.seleccionados.clear();
                    ProductosState.catalog.clear();
                    ProductosState.catalogByPat.clear();
                    ProductosState.customByPat.clear();
                    cardsProductos.innerHTML = '';
                    cardProd.hidden = true;
                    recalcCostos();
                } catch (e) {
                    msg.textContent = 'Error: ' + (e.message || e);
                    msg.classList.remove('hidden');
                }
            });
        })();
    </script>
</body>

</html>