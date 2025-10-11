<?php // views/partials/drones/view/drone_registro_view.php ?>
<div class="content">
  <div class="card" style="background-color:#5b21b6;">
    <h3 style="color:white;">Módulo: Registro Fito Sanitario</h3>
    <p style="color:white;margin:0;">Listado de RF (solo servicios completados) segmentado por rol.</p>
  </div>

  <div class="card">
    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
      <input id="rf-search" type="search" placeholder="Buscar por productor o localidad…" class="input" style="min-width:260px;">
      <div id="registro-health" style="margin-left:auto;color:#64748b;">Verificando conexión…</div>
    </div>
  </div>

  <div id="rf-grid" class="card" style="padding:12px;">
    <div id="rf-cards" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:12px;"></div>
    <div id="rf-empty" style="display:none;color:#64748b;">Sin resultados.</div>
  </div>
</div>

<!-- Modal estándar -->
<div id="modal" class="modal hidden">
  <div class="modal-content" style="max-width:980px;width:98%;">
    <h3 id="rf-modal-title">Registro Fitosanitario</h3>
    <div id="rf-modal-content"></div>
    <div class="form-buttons" style="margin-top:12px;">
      <button class="btn btn-aceptar" id="btn-export-pdf">Exportar PDF</button>
      <button class="btn btn-cancelar" onclick="closeModal()">Cerrar</button>
    </div>
  </div>
</div>

<!-- Exportar a PDF -->
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<script>
(function () {
  const API = '../partials/drones/controller/drone_registro_controller.php';

  // Healthcheck
  const healthEl = document.getElementById('registro-health');
  fetch(API + '?t=' + Date.now(), { cache: 'no-store' })
    .then(r => r.json())
    .then(j => healthEl && (healthEl.textContent = j.ok ? 'Conectado ✅' : 'Sin conexión ❌'))
    .catch(() => healthEl && (healthEl.textContent = 'Error de conexión ❌'));

  // Listado
  const cardsEl = document.getElementById('rf-cards');
  const emptyEl = document.getElementById('rf-empty');
  const qEl = document.getElementById('rf-search');

  function fmt(d){ return d ? new Date(d).toLocaleDateString() : '—'; }

  function renderCards(rows){
    cardsEl.innerHTML = '';
    if(!rows || !rows.length){ emptyEl.style.display='block'; return; }
    emptyEl.style.display='none';
    rows.forEach(r => {
      const html = `
        <div class="card" style="border:1px solid #e5e7eb;border-radius:14px;padding:12px;">
          <div style="font-weight:600">${r.productor || '—'}</div>
          <div style="font-size:12px;color:#64748b;">Orden N° ${r.id}</div>
          <div style="margin-top:6px;">
            <div><strong>Fecha visita:</strong> ${fmt(r.fecha_visita)}</div>
          </div>
          <div style="margin-top:8px;display:flex;gap:8px;">
            <button class="btn btn-aceptar" data-role="ver" data-id="${r.id}">Ver</button>
          </div>
        </div>`;
      cardsEl.insertAdjacentHTML('beforeend', html);
    });
  }

  function loadList(){
    const qs = new URLSearchParams();
    qs.set('action','list');
    if(qEl.value) qs.set('q', qEl.value.trim());
    fetch(API + '?' + qs.toString(), { cache:'no-store' })
      .then(r=>r.json())
      .then(j=>renderCards(j.data || []))
      .catch(()=>{ cardsEl.innerHTML=''; emptyEl.style.display='block'; });
  }

  qEl.addEventListener('input', () => loadList());

  // Modal helpers (se exponen al scope global para el botón Cerrar)
  window.closeModal = () => document.getElementById('modal').classList.add('hidden');
  function openModal(){ document.getElementById('modal').classList.remove('hidden'); }

  // Manejo clicks botón "Ver" (se corrige bug: antes no encontraba el selector)
  document.getElementById('rf-cards').addEventListener('click', ev => {
    const btn = ev.target.closest('button[data-role="ver"]');
    if(!btn) return;
    const id = btn.dataset.id;
    openRF(id);
  });

  // Carga detalle RF → abre modal
  function openRF(id){
    fetch(API + '?action=detail&id=' + encodeURIComponent(id), { cache:'no-store' })
      .then(r=>r.json())
      .then(j=>{
        if(!j.ok){ showAlert && showAlert('error', j.message || 'No se pudo obtener el detalle'); return; }
        const d = j.data;

        document.getElementById('rf-modal-title').textContent = `Registro Fitosanitario – Orden #${d.solicitud.id}`;

        const rpt = d.reporte || {};
        const prm = d.parametros || {};
        const sol = d.solicitud;

        const filas = (d.items || []).map(it => `
          <tr>
            <td>${rpt.fecha_visita || sol.fecha_visita || ''}</td>
            <td>${rpt.cuadro_cuartel || ''}</td>
            <td>${(prm.volumen_ha ?? rpt.vol_aplicado ?? '') || ''}</td>
            <td>${it.nombre_producto || ''}</td>
            <td>${it.principio_activo || ''}</td>
            <td>${it.tiempo_carencia || ''}</td>
            <td>${it.dosis ?? ''}</td>
            <td>${it.cant_prod_usado ?? ''}</td>
            <td>${it.fecha_vencimiento || ''}</td>
          </tr>
        `).join('');

        const fotos = (d.media || []).filter(m=>m.tipo==='foto').map(m=>`<img src="${m.ruta}" style="max-height:120px;border-radius:8px;">`).join(' ');
        const firmas = (d.media || []).filter(m=>m.tipo!=='foto').map(m=>`<div style="text-align:center"><img src="${m.ruta}" style="max-height:90px"><div style="font-size:12px;color:#64748b">${m.tipo}</div></div>`).join(' ');

        document.getElementById('rf-modal-content').innerHTML = `
          <div id="rf-pdf-root" style="background:#fff;padding:16px;border:1px solid #e5e7eb;border-radius:12px;">
            <div style="display:flex;justify-content:space-between;gap:12px;">
              <div>
                <div><strong>N°:</strong> ${String(rpt.id || sol.id).padStart(3,'0')}</div>
                <div><strong>Fecha:</strong> ${rpt.fecha_visita || sol.fecha_visita || ''}</div>
              </div>
              <div>
                <div><strong>Cliente:</strong> ${rpt.nom_cliente || sol.productor_nombre || sol.productor || ''}</div>
                <div><strong>Operador Drone:</strong> ${rpt.nom_piloto || ''}</div>
              </div>
              <div>
                <div><strong>Hora Ingreso:</strong> ${rpt.hora_ingreso || sol.hora_visita_desde || ''}</div>
                <div><strong>Hora Salida:</strong> ${rpt.hora_egreso || sol.hora_visita_hasta || ''}</div>
              </div>
            </div>

            <hr style="margin:12px 0">

            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:8px;">
              <div><strong>Representante:</strong> ${rpt.nom_encargado || ''}</div>
              <div><strong>Nombre finca:</strong> ${rpt.nombre_finca || ''}</div>
              <div><strong>Cultivo pulverizado:</strong> ${rpt.cultivo_pulverizado || ''}</div>
              <div><strong>Vel. viento (m/s):</strong> ${rpt.vel_viento ?? ''}</div>
              <div><strong>Temperatura (°C):</strong> ${rpt.temperatura ?? ''}</div>
              <div><strong>Humedad Relativa (%):</strong> ${rpt.humedad_relativa ?? ''}</div>
              <div><strong>Sup. pulverizada (ha):</strong> ${rpt.sup_pulverizada ?? sol.superficie_ha ?? ''}</div>
              <div><strong>Volumen aplicado (L/ha):</strong> ${rpt.vol_aplicado ?? prm.volumen_ha ?? ''}</div>
            </div>

            <h4 style="margin-top:12px;">Productos aplicados</h4>
            <table style="width:100%;border-collapse:collapse;font-size:12px">
              <thead>
                <tr>
                  <th style="border-bottom:1px solid #e5e7eb;text-align:left;">Fecha</th>
                  <th style="border-bottom:1px solid #e5e7eb;text-align:left;">Cuadro/Cuartel</th>
                  <th style="border-bottom:1px solid #e5e7eb;text-align:left;">Vol L/ha</th>
                  <th style="border-bottom:1px solid #e5e7eb;text-align:left;">Nombre Comercial</th>
                  <th style="border-bottom:1px solid #e5e7eb;text-align:left;">Principio Activo</th>
                  <th style="border-bottom:1px solid #e5e7eb;text-align:left;">Tiempo Carencia</th>
                  <th style="border-bottom:1px solid #e5e7eb;text-align:left;">Dosis (ml/gr/ha)</th>
                  <th style="border-bottom:1px solid #e5e7eb;text-align:left;">Cant. usada</th>
                  <th style="border-bottom:1px solid #e5e7eb;text-align:left;">Vence</th>
                </tr>
              </thead>
              <tbody>${filas}</tbody>
            </table>

            <div style="margin-top:10px;"><strong>Observaciones:</strong><br>${(rpt.observaciones || sol.observaciones || '')}</div>

            <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:12px;">${fotos}</div>
            <div style="display:flex;gap:24px;justify-content:space-around;margin-top:18px;">${firmas}</div>
          </div>
        `;
        openModal();
        wirePdf();
      })
      .catch(()=> showAlert && showAlert('error','Error cargando el detalle.'));
  }

  // Exportar PDF
  function wirePdf(){
    const btn = document.getElementById('btn-export-pdf');
    btn.onclick = async () => {
      const { jsPDF } = window.jspdf;
      const node = document.getElementById('rf-pdf-root');
      const canvas = await html2canvas(node, {scale:2, useCORS:true});
      const img = canvas.toDataURL('image/png');
      const pdf = new jsPDF('p','pt','a4');
      const pageW = pdf.internal.pageSize.getWidth();
      const pageH = pdf.internal.pageSize.getHeight();
      const ratio = Math.min(pageW / canvas.width, pageH / canvas.height);
      const w = canvas.width * ratio;
      const h = canvas.height * ratio;
      pdf.addImage(img, 'PNG', (pageW-w)/2, 20, w, h);
      pdf.save('Registro_Fitosanitario.pdf');
    };
  }

  // Primera carga
  loadList();
})();
</script>
