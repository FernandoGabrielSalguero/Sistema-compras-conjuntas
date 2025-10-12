<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$isSVE = isset($_SESSION['rol']) && strtolower((string)$_SESSION['rol']) === 'sve';
?>

<div id="modal-fito-json" class="modal hidden" role="dialog" aria-modal="true" aria-labelledby="modal-fito-json-title" aria-describedby="modal-fito-json-desc">

    <div class="modal-content" style="max-width: 1024px;">
        <h3 id="modal-fito-json-title">Registro Fitosanitario</h3>
        <p id="modal-fito-json-desc" class="sr-only">Vista consolidada de la solicitud y sus tablas relacionadas.</p>

        <!-- Tabs (solo SVE) -->
        <?php if ($isSVE): ?>
            <div class="tabs" style="display:flex; gap:8px; margin-bottom:12px;">
                <button type="button" class="btn btn-aceptar" id="tab-formato" aria-selected="true">Formato</button>
                <button type="button" class="btn btn-aceptar" id="tab-json" aria-selected="false">JSON</button>
                <div style="flex:1"></div>
                <button type="button" class="btn btn-info" id="btn-imprimir">Imprimir</button>
            </div>
        <?php endif; ?>

        <!-- CONTENIDO FORMATEADO -->
        <div id="fito-formato" style="background:#fff; border-radius:14px; padding:16px;">
            <!-- Encabezado -->
            <div class="header" style="display:flex; align-items:center; gap:16px; border-bottom:1px solid #e5e7eb; padding-bottom:12px; margin-bottom:12px;">
                <img id="fito-logo" src="/assets/png/logo_con_color_original.png" alt="Logo" style="height:56px; width:auto;">
                <div style="flex:1;">
                    <div style="font-weight:600;">Registro Aplicación Drone:</div>
                    <div>Ruta50Km1036,SanMartín</div>
                    <div>BodegaToro–Mdz.Arg</div>
                    <div>Teléfonodecontacto:261-2070518</div>
                </div>
                <div style="text-align:right;">
                    <div><strong>N°: <span id="fito-num"></span></strong></div>
                    <div>Fecha: <span id="fito-fecha"></span></div>
                </div>
            </div>

            <!-- Datos principales -->
            <div class="grid-2" style="display:grid; grid-template-columns: 1fr 1fr; gap:12px;">
                <div class="card" style="padding:12px; border:1px solid #e5e7eb; border-radius:12px;">
                    <div><strong>Cliente:</strong> <span id="fito-cliente"></span></div>
                    <div><strong>Representante:</strong> <span id="fito-representante"></span></div>
                    <div><strong>Nombre finca:</strong> <span id="fito-finca"></span></div>
                </div>
                <div class="card" style="padding:12px; border:1px solid #e5e7eb; border-radius:12px;">
                    <div><strong>Cultivo pulverizado:</strong> <span id="fito-cultivo"></span></div>
                    <div><strong>Superficie pulverizada (ha):</strong> <span id="fito-superficie"></span></div>
                    <div><strong>Operador Drone:</strong> <span id="fito-operador"></span></div>
                </div>
            </div>

            <!-- Condiciones (si existen en reporte) -->
            <div id="fito-condiciones" class="card" style="margin-top:12px; padding:12px; border:1px solid #e5e7eb; border-radius:12px; display:none;">
                <div style="font-weight:600; margin-bottom:8px;">Condiciones meteorológicas al momento del vuelo</div>
                <div class="grid-4" style="display:grid; grid-template-columns: repeat(4,1fr); gap:8px;">
                    <div>Hora Ingreso: <span id="fito-hora-in"></span></div>
                    <div>Hora Salida: <span id="fito-hora-out"></span></div>
                    <div>Temperatura (°C): <span id="fito-temp"></span></div>
                    <div>Humedad Relativa (%): <span id="fito-hr"></span></div>
                    <div>Vel. Viento (m/s): <span id="fito-vv"></span></div>
                    <div>Volumen aplicado (l/ha): <span id="fito-vol"></span></div>
                </div>
            </div>

            <!-- Tabla de productos -->
            <div class="card" style="margin-top:12px; padding:12px; border:1px solid #e5e7eb; border-radius:12px;">
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
                    <div style="font-weight:600;">Productos utilizados</div>
                </div>
                <div style="overflow:auto;">
                    <table id="fito-tabla-productos" style="width:100%; border-collapse:collapse;">
                        <thead>
                            <tr>
                                <th style="border-bottom:1px solid #e5e7eb; text-align:left; padding:8px;">Nombre Comercial</th>
                                <th style="border-bottom:1px solid #e5e7eb; text-align:left; padding:8px;">Principio Activo</th>
                                <th style="border-bottom:1px solid #e5e7eb; text-align:left; padding:8px;">Dosis (ml/gr/ha)</th>
                                <th style="border-bottom:1px solid #e5e7eb; text-align:left; padding:8px;">Cant. Producto Usado</th>
                                <th style="border-bottom:1px solid #e5e7eb; text-align:left; padding:8px;">Fecha de Vencimiento</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <!-- Galería -->
            <div class="card" style="margin-top:12px; padding:12px; border:1px solid #e5e7eb; border-radius:12px;">
                <div style="font-weight:600; margin-bottom:8px;">Registro fotográfico y firmas</div>
                <div id="fito-galeria" class="grid" style="display:grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap:12px;"></div>
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:16px; margin-top:12px;">
                    <div style="text-align:center;">
                        <img id="fito-firma-prestador" alt="Firma Prestador" style="max-height:120px; max-width:100%; object-fit:contain; border:1px dashed #e5e7eb; padding:6px;">
                        <div>Firma Prestador de Servicio</div>
                    </div>
                    <div style="text-align:center;">
                        <img id="fito-firma-cliente" alt="Firma Cliente" style="max-height:120px; max-width:100%; object-fit:contain; border:1px dashed #e5e7eb; padding:6px;">
                        <div>Firma Representante del cliente</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pestaña JSON -->
        <div id="fito-json" class="hidden">
            <div class="form-grid" style="gap:12px; margin-bottom:12px;">
                <button type="button" class="btn btn-info" id="btn-fito-copiar">Copiar JSON</button>
                <button type="button" class="btn btn-info" id="btn-fito-descargar">Descargar JSON</button>
            </div>
            <pre id="fito-json-pre" style="background:#0b1020; color:#d6e4ff; border-radius:12px; padding:14px; max-height:60vh; overflow:auto; font-size:12px; line-height:1.45; white-space:pre; tab-size:2;"></pre>
        </div>

        <div class="form-buttons">
            <button type="button" class="btn btn-aceptar" id="btn-fito-aceptar">Aceptar</button>
            <button type="button" class="btn btn-info" id="btn-fito-pdf">Descargar PDF</button>
            <button type="button" class="btn btn-cancelar" id="btn-fito-cancelar">Cancelar</button>
        </div>

    </div>
</div>

<style>
    /* visibilidad utilitaria */
    #modal-fito-json .hidden {
        display: none !important
    }

    #modal-fito-json table td,
    #modal-fito-json table th {
        font-size: .95rem
    }

    /* Mostrar tabs solo a SVE */
    #modal-fito-json.role-no-sve .tabs {
        display: none !important;
    }

    /* Centramos el contenedor y damos padding para respirar en viewport pequeños */
    #modal-fito-json {
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px;
    }

    /* Caja del modal: tamaño máximo y scroll vertical */
    #modal-fito-json .modal-content {
        max-width: 1024px;
        /* respeta tu ancho actual */
        max-height: 82vh;
        /* ACHICADO: no ocupa toda la altura */
        overflow: auto;
        /* scroll interno */
        scrollbar-gutter: stable both-edges;
    }

    /* Tabs siempre visibles al hacer scroll (mejora UX) */
    #modal-fito-json .tabs {
        position: sticky;
        top: 0;
        background: #fff;
        padding-top: 4px;
        z-index: 2;
    }

    /* Scrollbar amigable (opcional) */
    #modal-fito-json .modal-content::-webkit-scrollbar {
        width: 8px;
    }

    #modal-fito-json .modal-content::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 8px;
    }

    /* Un poco menos de margen vertical entre cards internas */
    #modal-fito-json .card {
        margin-top: 10px;
    }

    /* En móviles permitimos un poco más de alto útil */
    @media (max-width: 768px) {
        #modal-fito-json .modal-content {
            max-height: 88vh;
        }
    }

    /* Impresión se mantiene como antes (si ya lo añadiste en la vista principal, no hace falta duplicar) */
    @media print {
        body * {
            visibility: hidden;
        }

        #modal-fito-json,
        #modal-fito-json * {
            visibility: visible;
        }

        #modal-fito-json .form-buttons,
        #modal-fito-json .tabs {
            display: none !important;
        }

        #modal-fito-json .modal-content {
            box-shadow: none !important;
            border: none !important;
        }
    }

    /* Tamaño más grande para fotos (mejor impresión) */
    #fito-galeria img {
        height: 220px !important;
        border-radius: 12px;
        border: 1px solid #e5e7eb;
        object-fit: cover;
        width: 100%;
    }
</style>

<!-- Export a PDF -->
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>


<script>
    (function() {
        if (window.__SVE_FITO_JSON_INIT__) return;
        window.__SVE_FITO_JSON_INIT__ = true;

const DRONE_API_CANDIDATES = [
  '/controllers/drone_pilot_dashboardController.php?action=fito_json'
];
        const modal = document.getElementById('modal-fito-json');

        // Tabs
        const tabFormato = document.getElementById('tab-formato');
        const tabJSON = document.getElementById('tab-json');
        const paneFormato = document.getElementById('fito-formato');
        const paneJSON = document.getElementById('fito-json');

        // JSON elements
        const pre = document.getElementById('fito-json-pre');
        const btnCp = document.getElementById('btn-fito-copiar');
        const btnDl = document.getElementById('btn-fito-descargar');

        // Formato elements
        const numEl = document.getElementById('fito-num');
        const fechaEl = document.getElementById('fito-fecha');
        const clienteEl = document.getElementById('fito-cliente');
        const reprEl = document.getElementById('fito-representante');
        const fincaEl = document.getElementById('fito-finca');
        const cultivoEl = document.getElementById('fito-cultivo');
        const supEl = document.getElementById('fito-superficie');
        const operEl = document.getElementById('fito-operador');

        const condBox = document.getElementById('fito-condiciones');
        const horaInEl = document.getElementById('fito-hora-in');
        const horaOutEl = document.getElementById('fito-hora-out');
        const tempEl = document.getElementById('fito-temp');
        const hrEl = document.getElementById('fito-hr');
        const vvEl = document.getElementById('fito-vv');
        const volEl = document.getElementById('fito-vol');

        const tbodyProd = document.querySelector('#fito-tabla-productos tbody');
        const galEl = document.getElementById('fito-galeria');
        const firmaPrestadorEl = document.getElementById('fito-firma-prestador');
        const firmaClienteEl = document.getElementById('fito-firma-cliente');

        const btnOk = document.getElementById('btn-fito-aceptar');
        const btnCa = document.getElementById('btn-fito-cancelar');
        const btnPrint = document.getElementById('btn-imprimir');
        const btnPDF = document.getElementById('btn-fito-pdf');


        // helpers
        const pad3 = n => String(Number(n || 0)).padStart(3, '0');

        function fmtFechaYMDToDMY2(yMd) {
            if (!yMd) return '';
            const [y, m, d] = yMd.split('-');
            return `${d}/${m}/${String(y).slice(2)}`;
        }

        function esc(s) {
            return (s ?? '').toString()
                .replace(/&/g, '&amp;').replace(/</g, '&lt;')
                .replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }

        function buildProductos(items, recetas) {
            // indexar recetas por solicitud_item_id
            const map = {};
            (recetas || []).forEach(r => {
                const sid = r.solicitud_item_id;
                if (!map[sid]) map[sid] = [];
                map[sid].push(r);
            });

            const rows = [];
            (items || []).forEach(it => {
                const rs = map[it.id] || [{}];
                rs.forEach(r => {
                    rows.push({
                        nombre: it.nombre_producto || '',
                        principio: r.principio_activo || '',
                        dosis: r.dosis != null ? String(r.dosis) : '',
                        cant: r.cant_prod_usado != null ? String(r.cant_prod_usado) : '',
                        venc: r.fecha_vencimiento || '',
                    });
                });
            });
            return rows;
        }

        function populateFormato(data) {
            const s = data.solicitud || {};
            const rep = (data.reporte && data.reporte[0]) ? data.reporte[0] : {};
            const media = data.reporte_media || [];

            numEl.textContent = pad3(s.id);
            fechaEl.textContent = fmtFechaYMDToDMY2(s.fecha_visita);
            // Cliente: nombre desde usuarios.usuario expuesto como productor_usuario
            clienteEl.textContent = s.productor_usuario || s.ses_usuario || s.productor_id_real || '';
            reprEl.textContent = rep.nom_encargado || '';
            fincaEl.textContent = rep.nombre_finca || '';
            cultivoEl.textContent = rep.cultivo_pulverizado || '';
            supEl.textContent = rep.sup_pulverizada != null ? String(rep.sup_pulverizada) : '';
            operEl.textContent = rep.nom_piloto || '';

            // condiciones (mostrar solo si tenemos algún dato)
            const hasCond = (rep.hora_ingreso || rep.hora_egreso || rep.temperatura || rep.humedad_relativa || rep.vel_viento || rep.vol_aplicado);
            if (hasCond) {
                condBox.style.display = 'block';
            }
            horaInEl.textContent = rep.hora_ingreso || '';
            horaOutEl.textContent = rep.hora_egreso || '';
            tempEl.textContent = rep.temperatura != null ? String(rep.temperatura) : '';
            hrEl.textContent = rep.humedad_relativa != null ? String(rep.humedad_relativa) : '';
            vvEl.textContent = rep.vel_viento != null ? String(rep.vel_viento) : '';
            volEl.textContent = rep.vol_aplicado != null ? String(rep.vol_aplicado) : '';

            // productos
            tbodyProd.innerHTML = '';
            buildProductos(data.items, data.items_recetas).forEach(r => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                <td style="border-bottom:1px solid #f1f5f9; padding:6px;">${esc(r.nombre)}</td>
                <td style="border-bottom:1px solid #f1f5f9; padding:6px;">${esc(r.principio)}</td>
                <td style="border-bottom:1px solid #f1f5f9; padding:6px;">${esc(r.dosis)}</td>
                <td style="border-bottom:1px solid #f1f5f9; padding:6px;">${esc(r.cant)}</td>
                <td style="border-bottom:1px solid #f1f5f9; padding:6px;">${esc(r.venc)}</td>
            `;
                tbodyProd.appendChild(tr);
            });

            // galería + firmas
            galEl.innerHTML = '';
            let firmaPrestador = '';
            let firmaCliente = '';
            media.forEach(m => {
                const url = `/${m.ruta}`.replace(/\/{2,}/g, '/'); // asegurar slash inicial
                if (m.tipo === 'foto') {
                    const fig = document.createElement('figure');
                    fig.style.margin = '0';
                    fig.innerHTML = `<img src="${esc(url)}" alt="Foto">`;
                    galEl.appendChild(fig);
                } else if (m.tipo === 'firma_piloto') {
                    firmaPrestador = url;
                } else if (m.tipo === 'firma_cliente') {
                    firmaCliente = url;
                }
            });
            if (firmaPrestador) {
                firmaPrestadorEl.src = firmaPrestador;
            }
            if (firmaCliente) {
                firmaClienteEl.src = firmaCliente;
            }
        }

        function openModal() {
            modal.classList.remove('hidden');
            if (tabFormato) tabFormato.focus();
        }

        function closeModal() {
            modal.classList.add('hidden');
            pre.textContent = '';
            document.querySelector('#fito-tabla-productos tbody').innerHTML = '';
            document.getElementById('fito-galeria').innerHTML = '';
            document.getElementById('fito-firma-prestador').removeAttribute('src');
            document.getElementById('fito-firma-cliente').removeAttribute('src');
        }

        async function fetchDeepJSON(id) {
    let lastErr;
    for (const base of DRONE_API_CANDIDATES) {
        // El proxy ya fija el action, sólo pasamos el id
        const url = `${base}&id=${encodeURIComponent(id)}`;
        try {
            const res = await fetch(url, { cache: 'no-store', credentials: 'same-origin' });
            if (!res.ok) { lastErr = new Error(`HTTP ${res.status} en ${url}`); continue; }

            let json;
            try { json = await res.json(); } 
            catch { lastErr = new Error(`Respuesta no-JSON en ${url}`); continue; }

            if (json && json.ok) return json.data;

            // El proxy siempre devuelve {ok:false,error?:string}
            throw new Error(json?.error || 'Solicitud no encontrada');
        } catch (e) { lastErr = e; }
    }
    throw lastErr || new Error('No se pudo resolver el endpoint de Registro Fitosanitario');
}

        async function open(id) {
            try {
                pre.textContent = 'Cargando...';
                openModal();
                const data = await fetchDeepJSON(id);
                // Pestaña JSON
                pre.textContent = JSON.stringify(data, null, 2);
                // Pestaña Formato
                populateFormato(data);
            } catch (e) {
                console.error(e);
                pre.textContent = 'No se pudo cargar el JSON del registro.';
                notifyError(e?.message || 'No se pudo cargar el Registro Fitosanitario.');
            }
        }

        // Utilidad para mostrar errores consistentes (si showAlert existe lo usa)
        function notifyError(msg) {
            if (typeof window.showAlert === 'function') {
                window.showAlert('error', msg);
            } else {
                console.error(msg);
                alert(msg);
            }
        }

        function activate(tab) {
            if (tab === 'formato') {
                paneFormato.classList.remove('hidden');
                paneJSON.classList.add('hidden');
                if (tabFormato && tabJSON) {
                    tabFormato.classList.remove('btn-secondary');
                    tabJSON.classList.add('btn-secondary');
                }
            } else {
                paneFormato.classList.add('hidden');
                paneJSON.classList.remove('hidden');
                if (tabFormato && tabJSON) {
                    tabFormato.classList.add('btn-secondary');
                    tabJSON.classList.remove('btn-secondary');
                }
            }
        }
        if (tabFormato) tabFormato.addEventListener('click', () => activate('formato'));
        if (tabJSON) tabJSON.addEventListener('click', () => activate('json'));

        // Copiar / Descargar JSON
        if (btnCp) btnCp.addEventListener('click', async () => {
            try {
                await navigator.clipboard.writeText(pre.textContent || '');
                if (typeof window.showAlert === 'function') window.showAlert('success', 'JSON copiado al portapapeles.');
            } catch (e) {
                if (typeof window.showAlert === 'function') window.showAlert('error', 'No se pudo copiar.');
            }
        });
        if (btnDl) btnDl.addEventListener('click', () => {
            try {
                const blob = new Blob([pre.textContent || ''], {
                    type: 'application/json;charset=utf-8'
                });
                const a = document.createElement('a');
                a.href = URL.createObjectURL(blob);
                const now = new Date();
                const pad = n => String(n).padStart(2, '0');
                a.download = `registro_fitosanitario_${now.getFullYear()}-${pad(now.getMonth()+1)}-${pad(now.getDate())}_${pad(now.getHours())}${pad(now.getMinutes())}${pad(now.getSeconds())}.json`;
                document.body.appendChild(a);
                a.click();
                a.remove();
            } catch (e) {
                if (typeof window.showAlert === 'function') window.showAlert('error', 'No se pudo descargar.');
            }
        });

        // Descargar PDF (multipágina) del contenido formateado
        async function exportPDF() {
            try {
                // Forzar pestaña Formato para capturar
                const wasJSON = !paneJSON.classList.contains('hidden');
                activate('formato');

                // Área a exportar
                const target = document.getElementById('fito-formato');

                // html2canvas de alta calidad
                const canvas = await html2canvas(target, {
                    scale: 2, // más DPI
                    useCORS: true,
                    backgroundColor: '#ffffff',
                    windowWidth: document.documentElement.scrollWidth
                });

                const imgData = canvas.toDataURL('image/jpeg', 0.92);
                const pdf = new window.jspdf.jsPDF('p', 'mm', 'a4');

                const pageW = 210; // A4 width
                const pageH = 297; // A4 height
                const imgW = pageW; // ocupamos ancho completo
                const imgH = canvas.height * imgW / canvas.width;

                let y = 0;
                let remaining = imgH;

                // Partimos la imagen si excede una página
                while (remaining > 0) {
                    const srcY = (imgH - remaining) * (canvas.width / imgW);
                    const pageCanvas = document.createElement('canvas');
                    const pageCtx = pageCanvas.getContext('2d');

                    const pagePxH = pageH * (canvas.width / imgW);
                    pageCanvas.width = canvas.width;
                    pageCanvas.height = Math.min(pagePxH, remaining * (canvas.width / imgW));

                    pageCtx.drawImage(
                        canvas,
                        0, srcY,
                        canvas.width, pageCanvas.height,
                        0, 0,
                        canvas.width, pageCanvas.height
                    );

                    const pageImg = pageCanvas.toDataURL('image/jpeg', 0.92);
                    if (y > 0) pdf.addPage();
                    pdf.addImage(pageImg, 'JPEG', 0, 0, imgW, pageCanvas.height * (imgW / canvas.width));
                    remaining -= pageH;
                    y += pageH;
                }

                const num = (document.getElementById('fito-num')?.textContent || 'registro');
                pdf.save(`registro_fitosanitario_${num}.pdf`);

                // Volver al estado previo
                if (wasJSON) activate('json');
            } catch (err) {
                console.error(err);
                if (typeof window.showAlert === 'function') window.showAlert('error', 'No se pudo generar el PDF.');
            }
        }

        if (btnPDF) btnPDF.addEventListener('click', exportPDF);


        // Imprimir (si existe botón)
        if (btnPrint) {
            btnPrint.addEventListener('click', () => {
                const wasJSONVisible = !paneJSON.classList.contains('hidden');
                activate('formato');
                window.print();
                if (wasJSONVisible) activate('json');
            });
        }

        // Cerrar
        btnOk.addEventListener('click', closeModal);
        btnCa.addEventListener('click', closeModal);
        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeModal();
        });

        // Asegurar que, si no hay tabs visibles, el formato quede activo por defecto
        if (!tabFormato && paneFormato && paneJSON) {
            paneFormato.classList.remove('hidden');
            paneJSON.classList.add('hidden');
        }

        // API global
        window.FitoJSONModal = {
            open,
            close: closeModal
        };
    })();
</script>