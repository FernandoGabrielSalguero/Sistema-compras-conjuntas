<?php
// VISTA LIMPIA: lista + drawer sin lógica de edición/guardar
?>

<!-- Framework SVE -->
<link rel="stylesheet" href="https://www.fernandosalguero.com/cdn/assets/css/framework.css" />
<script src="https://www.fernandosalguero.com/cdn/assets/javascript/framework.js" defer></script>

<!-- Material Icons -->
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

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
        <label for="estado" style="color:white;">Estado</label>
        <div class="input-icon input-icon-globe">
          <select id="estado" name="estado">
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

  <!-- Drawer vacío (solo UI) -->
  <div id="drawer" class="sv-drawer hidden" aria-hidden="true">
    <div class="sv-drawer__overlay" data-close></div>
    <aside class="sv-drawer__panel" role="dialog" aria-modal="true" aria-labelledby="drawer-title">
      <div class="sv-drawer__header">
        <h3 id="drawer-title">Solicitud <span id="drawer-id"></span></h3>
        <button class="sv-drawer__close" id="drawer-close" aria-label="Cerrar">×</button>
      </div>
      <div class="sv-drawer__body">
        <div class="card" style="box-shadow:none;">
          <div class="form-separator"><span class="material-icons mi">info</span>Formulario en construcción</div>
          <p style="color:#6b7280;">
            Esta vista fue limpiada. El formulario y los métodos de actualización están deshabilitados.
          </p>
          <div class="gform-helper">
            Podés usar esta estructura para volver a montar campos, validaciones y guardados más adelante.
          </div>
        </div>
      </div>
      <div class="sv-drawer__footer">
        <button class="btn btn-cancelar" id="drawer-cancel" type="button">Cerrar</button>
      </div>
    </aside>
  </div>
</div>

<style>
  #cards:empty::before {
    content: "No hay solicitudes para los filtros seleccionados.";
    display: block; background:#fff; border-radius:14px; padding:18px; color:#6b7280;
  }
  .sv-drawer.hidden{display:none}
  .sv-drawer{position:fixed; inset:0; z-index:60}
  .sv-drawer__overlay{position:absolute; inset:0; background:#0006; opacity:0}
  .sv-drawer__panel{position:absolute; top:0; right:0; height:100%; width:min(760px,100%); background:#fff;
    box-shadow:-6px 0 24px #00000022; display:flex; flex-direction:column; border-top-left-radius:16px; border-bottom-left-radius:16px;}
  .sv-drawer__header{display:flex; justify-content:space-between; align-items:center; padding:16px 20px; border-bottom:1px solid #eee}
  .sv-drawer__footer{padding:12px 20px; border-top:1px solid #eee; display:flex; gap:12px; justify-content:flex-end}
  .sv-drawer__close{font-size:24px; line-height:1; border:none; background:transparent; cursor:pointer}
  .sv-drawer__body{flex:1; overflow:auto; -webkit-overflow-scrolling:touch; padding:16px 20px;}
  @keyframes slideInRight{from{transform:translateX(100%)} to{transform:translateX(0)}}
  @keyframes slideOutRight{from{transform:translateX(0)} to{transform:translateX(100%)}}
  @keyframes fadeIn{from{opacity:0} to{opacity:1}}
  @keyframes fadeOut{from{opacity:1} to{opacity:0}}
  .sv-drawer.opening .sv-drawer__panel{animation:slideInRight .28s cubic-bezier(.22,.61,.36,1) both;}
  .sv-drawer.closing .sv-drawer__panel{animation:slideOutRight .22s ease both;}
  .sv-drawer.opening .sv-drawer__overlay{animation:fadeIn .25s ease both;}
  .sv-drawer.closing .sv-drawer__overlay{animation:fadeOut .20s ease both;}
  .product-card .badge{display:inline-block; padding:2px 8px; border-radius:999px; font-size:.8rem}
  .badge.warning{background:#FEF3C7; color:#92400E}
  .badge.info{background:#DBEAFE; color:#1E40AF}
  .badge.primary{background:#E0E7FF; color:#3730A3}
  .badge.success{background:#DCFCE7; color:#166534}
  .badge.danger{background:#FEE2E2; color:#B91C1C}
</style>

<script>
const DRONE_API = '../partials/drones/controller/drone_list_controller.php';

(function(){
  const $  = (s, ctx=document)=>ctx.querySelector(s);
  const $$ = (s, ctx=document)=>Array.from(ctx.querySelectorAll(s));
  const els = {
    piloto: $('#piloto'),
    ses_usuario: $('#ses_usuario'),
    estado: $('#estado'),
    fecha_visita: $('#fecha_visita'),
    cards: $('#cards')
  };

  function debounce(fn, t=300){ let id; return (...a)=>{clearTimeout(id); id=setTimeout(()=>fn(...a),t);} }

  function prettyEstado(e){
    switch((e||'').toLowerCase()){
      case 'ingresada': return 'Ingresada';
      case 'procesando': return 'Procesando';
      case 'aprobada_coop': return 'Aprobada coop';
      case 'cancelada': return 'Cancelada';
      case 'completada': return 'Completada';
      default: return e||'';
    }
  }
  function badgeClass(e){
    switch((e||'').toLowerCase()){
      case 'ingresada': return 'warning';
      case 'procesando': return 'info';
      case 'aprobada_coop': return 'primary';
      case 'completada': return 'success';
      case 'cancelada': return 'danger';
      default: return 'secondary';
    }
  }
  function esc(s){ return (s??'').toString().replace(/&/g,'&amp;').replace(/</g,'&lt;')
    .replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;'); }

  function getFilters(){
    return {
      piloto: els.piloto.value.trim(),
      ses_usuario: els.ses_usuario.value.trim(),
      estado: els.estado.value,
      fecha_visita: els.fecha_visita.value
    };
  }

  async function load(){
    const params = new URLSearchParams({ action:'list_solicitudes', ...getFilters() });
    try{
      const res = await fetch(`${DRONE_API}?${params.toString()}`, { cache:'no-store' });
      const json = await res.json();
      if(!json.ok) throw new Error(json.error||'Error');
      renderCards(json.data.items||[]);
    }catch(e){
      console.error(e);
      els.cards.innerHTML = '<div class="card">Ocurrió un error cargando las solicitudes.</div>';
    }
  }

  function renderCards(items){
    els.cards.innerHTML = '';
    items.forEach(it=>{
      const card = document.createElement('div');
      card.className = 'product-card';
     card.innerHTML = `
  <div class="product-header">
    <h4>${esc(it.ses_usuario || '—')}</h4>
    <p>Pedido número: ${esc(it.id ?? '')}</p>
  </div>
  <div class="product-body">
    <div class="user-info">
      <div>
        <strong>${esc(it.piloto || 'Sin piloto asignado')}</strong>
        <div class="role">Fecha visita: ${esc(it.fecha_visita || '')} ${it.hora_visita ? `(${esc(it.hora_visita)})`:''}</div>
      </div>
    </div>

    <p class="description" style="margin-top:6px;">
      <span style="display:block; font-weight:600; color:#5b21b6;">Forma de pago:</span>
      ${(() => {
          const nombre = it.forma_pago_nombre || '—';
          if (String(it.forma_pago_id) === '6') {
            const estado = it.aprob_cooperativa ? ` <em style="color:#6b7280;">(${esc(it.aprob_cooperativa)})</em>` : '';
            return `${esc(nombre)}${estado}`;
          }
          return esc(nombre);
      })()}
    </p>

    <p class="description" style="margin-top:6px;">
      <span style="display:block; font-weight:600; color:#5b21b6;">Cooperativa (pertenencia):</span>
      ${(() => {
          const nom = it.coop_pertenece_nombre || '—';
          const idr = it.coop_pertenece_id_real ? ` | ${esc(it.coop_pertenece_id_real)}` : '';
          return `${esc(nom)}${idr}`;
      })()}
    </p>

    <hr />
    <div class="product-footer">
      <div class="metric">
        <span class="badge ${badgeClass(it.estado)}">${prettyEstado(it.estado)}</span>
      </div>
      <button class="btn-view" data-id="${it.id}">Ver detalle</button>
    </div>
  </div>
`;
      els.cards.appendChild(card);
    });

    els.cards.querySelectorAll('.btn-view').forEach(btn=>{
      btn.addEventListener('click', ()=> openDrawer({ id: btn.dataset.id }));
    });
  }

  // Drawer (solo UI, sin guardar)
  const drawer       = document.getElementById('drawer');
  const drawerPanel  = drawer.querySelector('.sv-drawer__panel');
  const drawerOverlay= drawer.querySelector('.sv-drawer__overlay');
  const drawerClose  = document.getElementById('drawer-close');
  const drawerCancel = document.getElementById('drawer-cancel');
  const drawerId     = document.getElementById('drawer-id');
  let lastFocus = null;

  async function openDrawer({id}){
    lastFocus = document.activeElement;
    drawerId.textContent = `#${id}`;
    drawer.setAttribute('aria-hidden','false');
    drawer.classList.remove('hidden','closing');
    drawer.classList.add('opening');
    drawerPanel.setAttribute('tabindex','-1');
    setTimeout(()=>drawerPanel.focus(),0);
    const onEnd = (e)=>{ if(e.target!==drawerPanel) return; drawer.classList.remove('opening'); drawer.removeEventListener('animationend', onEnd, true); };
    drawer.addEventListener('animationend', onEnd, true);
  }
  function closeDrawer(){
    const active = document.activeElement;
    if(active && drawer.contains(active)){
      if(lastFocus && typeof lastFocus.focus==='function'){ lastFocus.focus(); }
      else { document.body.setAttribute('tabindex','-1'); document.body.focus(); document.body.removeAttribute('tabindex'); }
    }
    drawer.classList.add('closing');
    drawer.setAttribute('aria-hidden','true');
    const onEnd = (e)=>{ if(e.target!==drawerPanel) return; drawer.classList.remove('closing'); drawer.classList.add('hidden'); drawer.removeEventListener('animationend', onEnd, true); };
    drawer.addEventListener('animationend', onEnd, true);
  }
  drawerOverlay.addEventListener('click', closeDrawer);
  drawerClose.addEventListener('click', closeDrawer);
  drawerCancel.addEventListener('click', closeDrawer);

  // Filtro en vivo
  const debouncedLoad = debounce(load, 300);
  els.piloto.addEventListener('input', debouncedLoad);
  els.ses_usuario.addEventListener('input', debouncedLoad);
  els.estado.addEventListener('change', debouncedLoad);
  els.fecha_visita.addEventListener('change', debouncedLoad);

  load();
})();
</script>
