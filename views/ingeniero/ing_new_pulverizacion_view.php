<?php /* Vista mínima para nueva solicitud de pulverización (ingeniero) */ ?>
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
            max-width: 980px;
            margin: 0 auto 16px
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
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
    </style>
</head>

<body>

    <div class="card">
        <h2>Nueva solicitud de pulverización (Ingeniería)</h2>
        <p class="notice">Completá los campos y confirmá. Se enviará un correo al productor y, si corresponde, a la cooperativa.</p>

        <form id="frm" novalidate>
            <div class="grid">

                <!-- Productor (typeahead) -->
                <div class="full">
                    <label for="prod_txt">Productor</label>
                    <div class="typeahead">
                        <input id="prod_txt" autocomplete="off" placeholder="Buscar productor..." />
                        <input type="hidden" id="prod_idreal" />
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

                <!-- Patología (una) -->
                <div>
                    <label for="pat">Patología</label>
                    <select id="pat"></select>
                </div>

                <!-- Dirección -->
                <div>
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

                <!-- Observaciones -->
                <div class="full">
                    <label for="obs">Observaciones</label>
                    <textarea id="obs" rows="3" maxlength="233"></textarea>
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
            const CTRL = '../../controllers/ing_new_pulverizacion_controller.php';

            const $ = s => document.querySelector(s);
            const show = (el, v) => el.classList.toggle('hidden', !v);
            const msg = $('#msg');

            async function getJSON(url){
  const r = await fetch(url,{credentials:'same-origin'});
  const txt = await r.text();
  try{
    const j = JSON.parse(txt);
    if(!j.ok) throw new Error(j.error||'Error');
    return j.data;
  }catch(e){
    console.error('No es JSON. Respuesta cruda:', txt.slice(0,300));
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

            // Cargas iniciales
            const pago = $('#pago'),
                coop = $('#coop'),
                rango = $('#rango'),
                pat = $('#pat');
            const coopWrap = $('#coop_wrap');
            async function init() {
                // combos
                const [pagos, rangos, pats, coops] = await Promise.all([
                    getJSON(`${CTRL}?action=formas_pago`),
                    getJSON(`${CTRL}?action=rangos`),
                    getJSON(`${CTRL}?action=patologias`),
                    getJSON(`${CTRL}?action=cooperativas`)
                ]);
                pago.innerHTML = '<option value="">Seleccionar</option>' + pagos.map(x => `<option value="${x.id}">${x.nombre}</option>`).join('');
                rango.innerHTML = '<option value="">Seleccionar</option>' + rangos.map(x => `<option value="${x.rango}">${x.label}</option>`).join('');
                pat.innerHTML = '<option value="">Seleccionar</option>' + pats.map(x => `<option value="${x.id}">${x.nombre}</option>`).join('');
                coop.innerHTML = '<option value="">Seleccionar</option>' + coops.map(x => `<option value="${x.id_real}">${x.usuario}</option>`).join('');
            }
            init().catch(console.error);

            // Pago -> mostrar cooperativa si id=6
            pago.addEventListener('change', () => {
                show(coopWrap, String(pago.value) === '6');
            });

            // Typeahead productores
            const txt = $('#prod_txt'),
                hid = $('#prod_idreal'),
                ta = $('#ta');
            let items = [];
            txt.addEventListener('input', async () => {
                const q = txt.value.trim();
                hid.value = '';
                if (q.length < 2) {
                    show(ta, false);
                    ta.innerHTML = '';
                    return;
                }
                try {
                    const data = await getJSON(`${CTRL}?action=buscar_usuarios&q=${encodeURIComponent(q)}${coop.value?`&coop_id=${encodeURIComponent(coop.value)}`:''}`);
                    items = data || [];
                    ta.innerHTML = items.map(it => `<div class="ta-item" data-id="${it.id_real}">${it.usuario}</div>`).join('');
                    show(ta, items.length > 0);
                } catch (e) {
                    show(ta, false);
                }
            });
            ta.addEventListener('mousedown', (e) => {
                const li = e.target.closest('.ta-item');
                if (!li) return;
                const id = li.getAttribute('data-id');
                const lab = li.textContent;
                txt.value = lab;
                hid.value = id;
                show(ta, false);
            });
            document.addEventListener('click', (e) => {
                if (!ta.contains(e.target) && e.target !== txt) {
                    show(ta, false);
                }
            });

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
                const payload = {
                    productor_id_real: $('#prod_idreal').value || null,
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
                    patologia_id: Number($('#pat').value || 0),
                    rango: $('#rango').value || '',
                    dir_provincia: prov.value || '',
                    dir_localidad: loc.value || '',
                    dir_calle: calle.value || '',
                    dir_numero: String(nro.value || ''),
                    observaciones: $('#obs').value || ''
                };

                // checks mínimos
                if (!payload.productor_id_real) {
                    req(txt);
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
                if (!payload.patologia_id) {
                    req($('#pat'));
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
                } catch (e) {
                    msg.textContent = 'Error: ' + (e.message || e);
                    msg.classList.remove('hidden');
                }
            });
        })();
    </script>
</body>

</html>