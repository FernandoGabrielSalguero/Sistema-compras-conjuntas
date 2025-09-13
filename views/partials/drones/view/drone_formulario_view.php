<?php

?>


<div class="content">
    <div class="card" style="background-color:#5b21b6;">
        <h3 style="color:white;">Solicitud de pulverización con dron</h3>
        <p style="color:white;margin:0;">Complete el siguiente formulario para registrar su solicitud.</p>
    </div>


    <!-- Formulario -->
    <form id="form-dron" class="gform-grid cols-2" novalidate>
        <!-- representante -->
        <div class="gform-question" role="group" aria-labelledby="q_representante_label" id="q_representante">
            <div id="q_representante_label" class="gform-legend">
                ¿A LA HORA DE TOMAR EL SERVICIO PODREMOS CONTAR CON UN REPRESENTANTE DE LA PROPIEDAD EN LA FINCA? <span class="gform-required">*</span>
            </div>
            <div class="gform-helper">
                Debe recibir al piloto, indicar cuarteles, asistir y firmar registro fitosanitario.
            </div>
            <div class="gform-options">
                <label class="gform-option"><input type="radio" id="representante_si" name="representante" value="si"><span>SI</span></label>
                <label class="gform-option"><input type="radio" id="representante_no" name="representante" value="no"><span>NO</span></label>
            </div>
            <div class="gform-error">Esta pregunta es obligatoria.</div>
        </div>

        <!-- línea tensión -->
        <div class="gform-question" role="group" aria-labelledby="q_linea_tension_label" id="q_linea_tension">
            <div id="q_linea_tension_label" class="gform-legend">
                ¿LOS CUARTELES TIENEN LÍNEA DE MEDIA/ALTA TENSIÓN A &lt; 30 m? <span class="gform-required">*</span>
            </div>
            <div class="gform-options">
                <label class="gform-option"><input type="radio" id="linea_tension_si" name="linea_tension" value="si"><span>SI</span></label>
                <label class="gform-option"><input type="radio" id="linea_tension_no" name="linea_tension" value="no"><span>NO</span></label>
            </div>
            <div class="gform-error">Esta pregunta es obligatoria.</div>
        </div>

        <!-- zona restringida -->
        <div class="gform-question" role="group" aria-labelledby="q_zona_restringida_label" id="q_zona_restringida">
            <div id="q_zona_restringida_label" class="gform-legend">
                ¿SE ENCUENTRA A &lt; 3 KM DE AEROPUERTO O ZONA RESTRINGIDA? <span class="gform-required">*</span>
            </div>
            <div class="gform-options">
                <label class="gform-option"><input type="radio" id="zona_restringida_si" name="zona_restringida" value="si"><span>SI</span></label>
                <label class="gform-option"><input type="radio" id="zona_restringida_no" name="zona_restringida" value="no"><span>NO</span></label>
            </div>
            <div class="gform-error">Esta pregunta es obligatoria.</div>
        </div>

        <!-- corriente eléctrica -->
        <div class="gform-question" role="group" aria-labelledby="q_corriente_electrica_label" id="q_corriente_electrica">
            <div id="q_corriente_electrica_label" class="gform-legend">
                ¿CUENTA CON CORRIENTE ELÉCTRICA? <span class="gform-required">*</span>
            </div>
            <div class="gform-helper">Requerido para carga de baterías (toma de 35A).</div>
            <div class="gform-options">
                <label class="gform-option"><input type="radio" id="corriente_electrica_si" name="corriente_electrica" value="si"><span>SI</span></label>
                <label class="gform-option"><input type="radio" id="corriente_electrica_no" name="corriente_electrica" value="no"><span>NO</span></label>
            </div>
            <div class="gform-error">Esta pregunta es obligatoria.</div>
        </div>

        <!-- agua potable -->
        <div class="gform-question" role="group" aria-labelledby="q_agua_potable_label" id="q_agua_potable">
            <div id="q_agua_potable_label" class="gform-legend">
                ¿HAY DISPONIBILIDAD DE AGUA POTABLE? <span class="gform-required">*</span>
            </div>
            <div class="gform-helper">Para preparación de caldos y limpieza del dron.</div>
            <div class="gform-options">
                <label class="gform-option"><input type="radio" id="agua_potable_si" name="agua_potable" value="si"><span>SI</span></label>
                <label class="gform-option"><input type="radio" id="agua_potable_no" name="agua_potable" value="no"><span>NO</span></label>
            </div>
            <div class="gform-error">Esta pregunta es obligatoria.</div>
        </div>

        <!-- obstáculos -->
        <div class="gform-question" role="group" aria-labelledby="q_obstaculos_label" id="q_obstaculos">
            <div id="q_obstaculos_label" class="gform-legend">
                ¿LOS CUARTELES ESTÁN LIBRES DE OBSTÁCULOS? <span class="gform-required">*</span>
            </div>
            <div class="gform-helper">Árboles internos, cables/alambres que superen la altura del cultivo, etc.</div>
            <div class="gform-options">
                <label class="gform-option"><input type="radio" id="libre_obstaculos_si" name="libre_obstaculos" value="si"><span>SI</span></label>
                <label class="gform-option"><input type="radio" id="libre_obstaculos_no" name="libre_obstaculos" value="no"><span>NO</span></label>
            </div>
            <div class="gform-error">Esta pregunta es obligatoria.</div>
        </div>

        <!-- área despegue -->
        <div class="gform-question" role="group" aria-labelledby="q_area_despegue_label" id="q_area_despegue">
            <div id="q_area_despegue_label" class="gform-legend">
                ¿CUENTAN CON ÁREA DE DESPEGUE APROPIADA (5×5 m)? <span class="gform-required">*</span>
            </div>
            <div class="gform-helper">Callejón despejado y libre de obstáculos.</div>
            <div class="gform-options">
                <label class="gform-option"><input type="radio" id="area_despegue_si" name="area_despegue" value="si"><span>SI</span></label>
                <label class="gform-option"><input type="radio" id="area_despegue_no" name="area_despegue" value="no"><span>NO</span></label>
            </div>
            <div class="gform-error">Esta pregunta es obligatoria.</div>
        </div>

        <!-- hectáreas -->
        <div class="gform-question" data-required="true" id="q_superficie">
            <label class="gform-label" for="superficie_ha">SUPERFICIE (ha) <span class="gform-required">*</span></label>
            <div class="gform-helper">Ingrese sólo el número de hectáreas a pulverizar.</div>
            <input class="gform-input" id="superficie_ha" name="superficie_ha" type="number" inputmode="decimal" min="0.01" step="0.01" placeholder="Ej.: 3.5" />
            <div class="gform-error">Debe ser un número &gt; 0.</div>
        </div>

        <!-- método de pago -->
        <div class="gform-question" data-required="true" id="q_metodo_pago">
            <label class="gform-label" for="metodo_pago">MÉTODO DE PAGO <span class="gform-required">*</span></label>
            <div class="gform-helper">Elegí una opción disponible.</div>
            <select class="gform-input" id="metodo_pago" name="metodo_pago" aria-required="true">
                <option value="">Cargando métodos…</option>
            </select>
            <div id="metodo_pago_desc" class="gform-helper" style="margin-top:.35rem;"></div>

            <div id="wrap_coop_cuota" class="gform-field" style="margin-top:.6rem; display:none;">
                <label class="gform-label" for="coop_descuento_id_real">Seleccioná la cooperativa</label>
                <select class="gform-input" id="coop_descuento_id_real" name="coop_descuento_id_real">
                    <option value="">Cargando cooperativas…</option>
                </select>
                <div class="gform-helper">Sólo cooperativas habilitadas.</div>
                <div class="gform-error" id="coop_descuento_error" style="display:none;">Debés seleccionar una cooperativa.</div>
            </div>
        </div>

        <!-- motivo/patologías -->
        <div class="gform-question" role="group" aria-labelledby="q_motivo_label" id="q_motivo">
            <div id="q_motivo_label" class="gform-legend">INDICAR EL MOTIVO <span class="gform-required">*</span></div>
            <div class="gform-options" id="motivo_dynamic">
                <div class="gform-helper">Cargando patologías…</div>
            </div>
            <div class="gform-error">Seleccioná al menos una opción.</div>
        </div>

        <!-- rango fecha -->
        <div class="gform-question" role="group" aria-labelledby="q_rango_label" id="q_rango">
            <div id="q_rango_label" class="gform-legend">MOMENTO DESEADO <span class="gform-required">*</span></div>
            <div class="gform-options">
                <label class="gform-option"><input type="radio" name="rango_fecha" value="octubre_q1"><span>1ª quincena Octubre</span></label>
                <label class="gform-option"><input type="radio" name="rango_fecha" value="octubre_q2"><span>2ª quincena Octubre</span></label>
                <label class="gform-option"><input type="radio" name="rango_fecha" value="noviembre_q1"><span>1ª quincena Noviembre</span></label>
                <label class="gform-option"><input type="radio" name="rango_fecha" value="noviembre_q2"><span>2ª quincena Noviembre</span></label>
                <label class="gform-option"><input type="radio" name="rango_fecha" value="diciembre_q1"><span>1ª quincena Diciembre</span></label>
                <label class="gform-option"><input type="radio" name="rango_fecha" value="diciembre_q2"><span>2ª quincena Diciembre</span></label>
                <label class="gform-option"><input type="radio" name="rango_fecha" value="enero_q1"><span>1ª quincena Enero</span></label>
                <label class="gform-option"><input type="radio" name="rango_fecha" value="enero_q2"><span>2ª quincena Enero</span></label>
                <label class="gform-option"><input type="radio" name="rango_fecha" value="febrero_q1"><span>1ª quincena Febrero</span></label>
                <label class="gform-option"><input type="radio" name="rango_fecha" value="febrero_q2"><span>2ª quincena Febrero</span></label>
            </div>
            <div class="gform-error">Seleccioná una opción.</div>
        </div>

        <!-- productos dinámicos -->
        <div class="gform-question" role="group" aria-labelledby="q_productos_label" id="q_productos">
            <div id="q_productos_label" class="gform-legend">
                Si necesitás productos fitosanitarios indicá los necesarios. <span class="gform-required">*</span>
            </div>
            <div class="gform-options gopts-with-complement" id="productos_dynamic">
                <div class="gform-helper">Seleccioná patologías arriba para ver productos.</div>
            </div>
            <div class="gform-error">Seleccioná al menos una opción.</div>
        </div>

        <!-- dirección -->
        <div class="gform-question span-2" role="group" aria-labelledby="q_direccion_label" id="q_direccion">
            <div id="q_direccion_label" class="gform-legend">DIRECCIÓN DE LA FINCA</div>
            <div class="gform-helper">Si no capturás coordenadas, completá estos datos.</div>
            <div class="gform-grid cols-4">
                <div class="gform-field">
                    <label class="gform-label" for="dir_provincia">Provincia</label>
                    <input class="gform-input" id="dir_provincia" name="dir_provincia" type="text" placeholder="Provincia">
                </div>
                <div class="gform-field">
                    <label class="gform-label" for="dir_localidad">Localidad</label>
                    <input class="gform-input" id="dir_localidad" name="dir_localidad" type="text" placeholder="Localidad">
                </div>
                <div class="gform-field">
                    <label class="gform-label" for="dir_calle">Calle</label>
                    <input class="gform-input" id="dir_calle" name="dir_calle" type="text" placeholder="Calle">
                </div>
                <div class="gform-field">
                    <label class="gform-label" for="dir_numero">Numeración</label>
                    <input class="gform-input" id="dir_numero" name="dir_numero" type="text" inputmode="numeric" placeholder="Nº">
                </div>
            </div>
        </div>

        <!-- resumen costos -->
        <div id="resumen-costos-inline" class="card" style="margin-top:1rem;">
            <h4 style="margin:0 0 .5rem 0;">Resumen de costos</h4>
            <div class="gform-grid cols-2">
                <div>
                    <div class="gform-helper">Servicio base</div>
                    <div id="rc_base" style="font-weight:700;">—</div>
                </div>
                <div>
                    <div class="gform-helper">Productos SVE</div>
                    <div id="rc_prod" style="font-weight:700;">—</div>
                </div>
                <div class="span-2" style="border-top:1px solid #eee; padding-top:.5rem; margin-top:.35rem;">
                    <div class="gform-helper">Total estimado</div>
                    <div id="rc_total" style="font-weight:800; font-size:1.1rem;">—</div>
                </div>
            </div>
        </div>

        <!-- acciones -->
        <div class="gform-actions span-1">
            <button type="submit" id="btn_solicitar" class="gform-btn gform-primary">Solicitar el servicio</button>
        </div>
    </form>

    <!-- Toasts -->
    <div id="toast-container"></div>
    <div id="toast-container-boton"></div>
</div>

<!-- Modal de confirmación -->
<div id="modalConfirmacion" class="modal" role="dialog" aria-modal="true" aria-hidden="true" aria-labelledby="modalTitle">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle" class="modal-title">¿Confirmar pedido?</h3>
            <button type="button" class="modal-close" aria-label="Cerrar" onclick="cerrarModal()"><span class="material-icons">close</span></button>
        </div>
        <div class="modal-body">
            <p class="muted">Estás por enviar el pedido. Revisá el detalle:</p>
            <div id="resumenModal"></div>
        </div>
        <div class="modal-actions">
            <button type="button" class="btn btn-cancelar" onclick="cerrarModal()">Cancelar</button>
            <button type="button" id="btnConfirmarModal" class="btn btn-aceptar" onclick="confirmarEnvio()">Sí, enviar</button>
        </div>
    </div>
</div>

<!-- CDN -->
<link rel="stylesheet" href="https://www.fernandosalguero.com/cdn/assets/css/framework.css">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
<script src="https://www.fernandosalguero.com/cdn/assets/javascript/framework.js" defer></script>

<!-- Estilos específicos (inline para esta vista) -->
<style>
  .main{margin-left:0;}
  .header-card{display:flex;align-items:center;justify-content:space-between;padding:2rem 1.5rem;}
  .modal{position:fixed;inset:0;display:none;align-items:center;justify-content:center;background:rgba(0,0,0,.55);padding:1rem;z-index:10000;}
  .modal.is-open{display:flex;}
  .modal-content{width:min(720px,100%);background:#fff;border-radius:16px;box-shadow:0 24px 60px rgba(0,0,0,.25);overflow:hidden;}
  .modal-header{display:flex;align-items:center;justify-content:space-between;padding:1rem 1.25rem;border-bottom:1px solid #eee;}
  .modal-title{margin:0;font-size:1.25rem;font-weight:700;}
  .modal-close{border:0;background:transparent;cursor:pointer;font-size:0;line-height:1;display:flex;align-items:center;justify-content:center;}
  .modal-close .material-icons{font-size:22px;color:#666;}
  .modal-body{padding:1rem 1.25rem;max-height:65vh;overflow:auto;overflow-x:hidden;}
  .modal-actions{padding:1rem 1.25rem;display:flex;gap:.75rem;justify-content:flex-end;border-top:1px solid #eee;}
  .modal-summary dl{display:grid;grid-template-columns:1fr;gap:.5rem 1rem;margin:1rem 0 0;}
  .modal-summary dt{font-weight:600;color:#333;}
  .modal-summary dd{margin:0;color:#111;}
  @media(min-width:640px){.modal-summary dl{grid-template-columns:1.2fr 1fr;}}
  .modal-summary .note{white-space:pre-wrap;border:1px solid #eee;border-radius:10px;padding:.75rem;background:#fafafa;}
  .costos-block{margin-top:1rem;}
  .costos-title{font-size:1.05rem;font-weight:700;margin:0 0 .5rem 0;}
  .costos-list{list-style:none;margin:0;padding:0;border:1px solid #eee;border-radius:12px;overflow:hidden;background:#fff;}
  .costos-item{display:flex;align-items:baseline;justify-content:space-between;gap:.75rem;padding:.75rem .9rem;line-height:1.2;}
  .costos-item+.costos-item{border-top:1px solid #f2f2f2;}
  .costos-label{font-weight:500;color:#333;flex:1 1 auto;min-width:0;}
  .costos-help{display:block;font-size:.85rem;color:#666;margin-top:.15rem;word-break:break-word;}
  .costos-amount{flex:0 0 auto;font-variant-numeric:tabular-nums;text-align:right;white-space:nowrap;}
  .costos-total{font-weight:800;}
  .costos-total .costos-amount{font-size:1.05rem;}
  .gform-question .gform-error{display:none;color:#dc2626;font-size:.9rem;margin-top:.5rem}
  .gform-question.has-error .gform-error{display:block}
  .gform-question.has-error .gform-legend,.gform-question.has-error .gform-label{color:#dc2626}
  .gform-question.has-error .gform-options,.gform-question.has-error .gform-input,.gform-question.has-error textarea{outline:2px solid #dc2626;outline-offset:2px}
</style>

<script>
(function(){
  'use strict';
  const API_URL = '../partials/drones/controller/drone_formulario_controller.php';
  const $ = (s, c=document)=>c.querySelector(s);
  const $$ = (s, c=document)=>Array.from(c.querySelectorAll(s));
  const form = $('#form-dron');

  function setConfirmBtnLoading(loading=true){
    const btn = $('#btnConfirmarModal');
    if(!btn) return;
    btn.disabled = loading;
    btn.textContent = loading ? 'Enviando…' : 'Sí, enviar';
  }

  const apiGet = async (params) => {
    const url = `${API_URL}?${new URLSearchParams(params).toString()}`;
    const res = await fetch(url, {method:'GET', credentials:'same-origin', cache:'no-store'});
    const json = await res.json().catch(()=> ({}));
    if(!json?.ok) throw new Error(json?.error || 'Error de red');
    return json.data || json;
  };

  // costo base cache
  let COSTO_BASE = { costo:0, moneda:'Pesos' };
  async function cargarCostoBase(){
    try{
      const d = await apiGet({action:'costo'});
      COSTO_BASE.costo = Number(d.costo||0);
      COSTO_BASE.moneda = d.moneda || 'Pesos';
    }catch{}
  }

  async function cargarCooperativas(){
    const sel = $('#coop_descuento_id_real');
    if(!sel) return;
    try{
      const d = await apiGet({action:'cooperativas'});
      const items = d.items||[];
      sel.innerHTML = `<option value="">Seleccioná…</option>` + items.map(c=>`<option value="${c.id_real}">${c.usuario}</option>`).join('');
      sel.dataset.loaded='1';
    }catch{
      sel.innerHTML = `<option value="">No disponible</option>`;
    }
  }

  const fmtARS = (n)=> new Intl.NumberFormat('es-AR',{minimumFractionDigits:2,maximumFractionDigits:2}).format(Number(n||0));
  function calcularCostos(payload){
    const sup = Math.max(0, Number(payload.superficie_ha||0));
    const base = sup * Number(COSTO_BASE.costo||0);
    let costoProductos = 0;
    (payload.productos||[]).forEach(p=>{
      if(p.fuente==='sve' && p.producto_id){
        const sel = document.querySelector(`#sel_prod_${p.patologia_id}`);
        const c = sel?.selectedOptions?.[0]?.dataset?.costo;
        costoProductos += sup * Number(c||0);
      }
    });
    return {moneda: COSTO_BASE.moneda||'Pesos', base, productos:costoProductos, total: base + costoProductos};
  }
  function buildPayloadMin(){
    const prods=[];
    document.querySelectorAll('.gform-optbox[data-patologia-id]').forEach(box=>{
      const pid = parseInt(box.dataset.patologiaId,10);
      const chk = box.querySelector('input[type="checkbox"][name="productos[]"]');
      if(!chk || !chk.checked) return;
      const fuente = form.querySelector(`input[type="radio"][name="src-${pid}"]:checked`)?.value;
      if(fuente==='yo'){
        const marca = box.querySelector(`#marca_${pid}`)?.value?.trim() || null;
        prods.push({patologia_id:pid, fuente:'yo', marca});
      }else{
        const sel = box.querySelector(`#sel_prod_${pid}`);
        const producto_id = sel && sel.value ? parseInt(sel.value,10) : null;
        const producto_nombre = sel ? sel.options[sel.selectedIndex]?.textContent : null;
        prods.push({patologia_id:pid, fuente:'sve', producto_id, producto_nombre});
      }
    });
    return {superficie_ha: $('#superficie_ha')?.value?.trim() || null, productos: prods};
  }
  function actualizarResumenInline(){
    const base = $('#rc_base'), prod=$('#rc_prod'), tot=$('#rc_total');
    if(!base||!prod||!tot) return;
    const costos = calcularCostos(buildPayloadMin());
    base.textContent = `${fmtARS(costos.base)} ${costos.moneda||''}`;
    prod.textContent = `${fmtARS(costos.productos)} ${costos.moneda||''}`;
    tot.textContent  = `${fmtARS(costos.total)} ${costos.moneda||''}`;
  }

  async function cargarFormasPago(){
    const sel = $('#metodo_pago');
    const desc = $('#metodo_pago_desc');
    const coopWrap = $('#wrap_coop_cuota');
    const coopSel = $('#coop_descuento_id_real');
    if(!sel) return;
    try{
      const d = await apiGet({action:'formas_pago'});
      const items = d.items||[];
      sel.innerHTML = `<option value="">Seleccioná…</option>` + items.map(it=>`<option value="${it.id}" data-descripcion="${(it.descripcion||'').replace(/"/g,'&quot;')}">${it.nombre}</option>`).join('');
      const onChange = async () => {
        const op = sel.selectedOptions[0];
        const id = parseInt(sel.value||'0',10);
        const t = op ? (op.dataset.descripcion || '') : '';
        if(desc) desc.textContent = t || '';
        if(coopWrap){
          coopWrap.style.display = (id===6 ? 'block' : 'none');
          if(id===6 && coopSel && !coopSel.dataset.loaded) await cargarCooperativas();
        }
        actualizarResumenInline();
      };
      sel.addEventListener('change', onChange);
      onChange();
    }catch(e){
      sel.innerHTML = `<option value="">No disponible</option>`;
      if(desc) desc.textContent = '';
      if(coopWrap) coopWrap.style.display='none';
    }
  }

  async function cargarPatologias(){
    const d = await apiGet({action:'patologias'});
    const items = d.items||[];
    const cont = $('#motivo_dynamic');
    if(!cont) return;
    const otrosHTML = `
      <label class="gform-option gform-option-otros">
        <input type="checkbox" id="motivo_otros_chk" name="motivo[]" value="otros" aria-controls="motivo_otros">
        <span>Otros:</span>
        <input type="text" id="motivo_otros" name="motivo_otros" class="gform-input gform-input-inline oculto" placeholder="Especificar" disabled>
      </label>`;
    if(!items.length){
      cont.innerHTML = `<div class="gform-helper">No hay patologías activas.</div>${otrosHTML}`;
      return;
    }
    cont.innerHTML = items.map(p=>`
      <label class="gform-option">
        <input type="checkbox" name="motivo[]" value="${p.id}" data-patologia-nombre="${p.nombre}">
        <span>${p.nombre}</span>
      </label>`).join('') + otrosHTML;

    const chkOtros = $('#motivo_otros_chk');
    const inputOtros = $('#motivo_otros');
    if(chkOtros && inputOtros){
      const sync=()=>{ inputOtros.disabled=!chkOtros.checked; inputOtros.classList.toggle('oculto',!chkOtros.checked); if(!chkOtros.checked) inputOtros.value=''; };
      chkOtros.addEventListener('change', sync); sync();
    }
    cont.addEventListener('change', ()=> reconstruirProductos());
  }

  async function reconstruirProductos(){
    const wrap = $('#productos_dynamic');
    if(!wrap) return;
    const patChecks = Array.from(document.querySelectorAll('#motivo_dynamic input[type="checkbox"][name="motivo[]"]')).filter(i=>i.value!=='otros' && i.checked);
    if(!patChecks.length){
      wrap.innerHTML = `<div class="gform-helper">Seleccioná patologías arriba para ver productos.</div>`;
      return;
    }
    wrap.innerHTML = patChecks.map(chk=>{
      const pid = chk.value;
      const pnom = chk.dataset.patologiaNombre || chk.nextElementSibling?.textContent || `Patología #${pid}`;
      return `
      <div class="gform-optbox" data-patologia-id="${pid}" data-patologia-nombre="${pnom}">
        <label class="gform-option">
          <input type="checkbox" name="productos[]" value="${pid}" data-complement="#cmp-pat-${pid}">
          <span>Productos para ${pnom}</span>
        </label>
        <div id="cmp-pat-${pid}" class="gform-complement" hidden>
          <div class="gform-miniopts">
            <span>¿Tenés el producto?</span>
            <label><input type="radio" name="src-${pid}" value="sve" checked> No</label>
            <label><input type="radio" name="src-${pid}" value="yo"> Sí</label>
          </div>
          <div class="gform-brand" id="brand_${pid}" hidden>
            <input type="text" class="gform-input gform-input-inline" id="marca_${pid}" placeholder="Marca del producto">
            <div class="gform-helper">Indicá marca y/o concentración.</div>
          </div>
          <div class="gform-brand" id="sve_${pid}">
            <select class="gform-input gform-input-inline" id="sel_prod_${pid}">
              <option value="">Seleccioná un producto SVE…</option>
            </select>
            <div class="gform-helper">Productos de stock asociados a ${pnom}.</div>
          </div>
        </div>
      </div>`;
    }).join('');

    // activar complementos
    $$('input[type="checkbox"][data-complement]', wrap).forEach(cb=>{
      const cmp = document.querySelector(cb.dataset.complement);
      const sync = ()=>{ if(cmp) cmp.hidden = !cb.checked; actualizarResumenInline(); };
      cb.addEventListener('change', sync); sync();
    });

    // radios por patología, y carga perezosa de productos
    $$('.gform-optbox', wrap).forEach(box=>{
      const pid = box.dataset.patologiaId;
      const rbNo = box.querySelector(`input[type="radio"][name="src-${pid}"][value="sve"]`);
      const rbSi = box.querySelector(`input[type="radio"][name="src-${pid}"][value="yo"]`);
      const brand = box.querySelector(`#brand_${pid}`);
      const sveWrap = box.querySelector(`#sve_${pid}`);
      const sel = box.querySelector(`#sel_prod_${pid}`);
      const sync = ()=>{
        const yo = rbSi?.checked;
        if(brand) brand.hidden = !yo;
        if(sveWrap) sveWrap.hidden = !!yo;
        actualizarResumenInline();
        if(!yo && sel && !sel.dataset.loaded){
          apiGet({action:'productos', patologia_id: pid})
            .then(d=>{
              const items = d.items||[];
              sel.innerHTML = `<option value="">Seleccioná un producto SVE…</option>` + items.map(it=>`<option value="${it.id}" data-costo="${Number(it.costo_hectarea||0)}">${it.nombre}</option>`).join('');
              sel.dataset.loaded='1';
            })
            .catch(()=>{ sel.innerHTML = `<option value="">No se pudieron cargar productos</option>`; });
        }
      };
      rbNo?.addEventListener('change', sync);
      rbSi?.addEventListener('change', sync);
      sel?.addEventListener('change', actualizarResumenInline);
      sync();
    });

    actualizarResumenInline();
  }

  cargarPatologias().catch(()=>{
    const cont = $('#motivo_dynamic');
    if(cont) cont.innerHTML = `<div class="gform-helper">No se pudieron cargar las patologías.</div>`;
  });
  cargarFormasPago();
  cargarCostoBase().then(actualizarResumenInline);
  $('#superficie_ha')?.addEventListener('input', actualizarResumenInline);

  // sesión
  const sessionData = (()=>{ try{ return JSON.parse($('#session-data')?.textContent || '{}'); }catch{ return {}; } })();

  // modal
  const modal = $('#modalConfirmacion');
  const resumenModal = $('#resumenModal');
  let __ultimoPayload = null;
  const abrirModal=()=>{ if(!modal) return; modal.classList.add('is-open'); modal.setAttribute('aria-hidden','false'); document.body.style.overflow='hidden'; setTimeout(()=>$('#btnConfirmarModal')?.focus(),0); }
  const cerrarModal=()=>{ if(!modal) return; modal.classList.remove('is-open'); modal.setAttribute('aria-hidden','true'); document.body.style.overflow=''; $('#btn_solicitar')?.focus(); }
  window.cerrarModal = cerrarModal;

  modal?.addEventListener('click', (e)=>{ if(e.target===modal) cerrarModal(); });
  document.addEventListener('keydown', (e)=>{ if(e.key==='Escape' && modal?.classList.contains('is-open')) cerrarModal(); });

  window.confirmarEnvio = async ()=>{
    if(!__ultimoPayload) return cerrarModal();
    try{
      setConfirmBtnLoading(true);
      const res = await fetch(API_URL, {method:'POST', headers:{'Content-Type':'application/json'}, credentials:'same-origin', body: JSON.stringify(__ultimoPayload)});
      const raw = await res.text(); let data=null; try{ data = JSON.parse(raw);}catch{}
      if(!res.ok || !data?.ok){ const msg = data?.error || `Error ${res.status} al registrar la solicitud.`; window.showToast?.('error', msg); return; }
      window.showToast?.('success', `Solicitud registrada (#${data.id}).`);
      cerrarModal(); form.reset();
      // reset dinámicos
      $$('input[type="checkbox"][data-complement]').forEach(cb=>{ const cmp = document.querySelector(cb.dataset.complement); if(cmp) cmp.hidden = true; });
      const otrosChk = $('#motivo_otros_chk'); const otrosTxt = $('#motivo_otros');
      if(otrosChk && otrosTxt){ otrosChk.checked=false; otrosTxt.value=''; otrosTxt.disabled=true; otrosTxt.classList.add('oculto'); }
      reconstruirProductos();
      setTimeout(()=>{ window.location.href='prod_dashboard.php'; }, 1200);
    }catch{
      window.showToast?.('error', 'No se pudo enviar la solicitud. Verificá tu conexión.');
    }finally{
      setConfirmBtnLoading(false);
    }
  };

  // geolocalización
  const enFincaSi = $('#en_finca_si'), enFincaNo = $('#en_finca_no');
  const status = $('#ubicacion_status');
  const lat = $('#ubicacion_lat'), lng=$('#ubicacion_lng'), acc=$('#ubicacion_acc'), ts=$('#ubicacion_ts');
  function clearGeo(){ [lat,lng,acc,ts].forEach(i=> i && (i.value='')); if(status) status.textContent='No se capturarán coordenadas.'; }
  function captureGeo(){
    if(!navigator.geolocation){ if(status) status.textContent='Geolocalización no soportada.'; return; }
    if(status) status.textContent='Obteniendo coordenadas…';
    navigator.geolocation.getCurrentPosition(
      (pos)=>{ const {latitude, longitude, accuracy} = pos.coords; if(lat) lat.value=latitude; if(lng) lng.value=longitude; if(acc) acc.value=accuracy; if(ts) ts.value=new Date(pos.timestamp).toISOString(); if(status) status.textContent=`Coordenadas capturadas (±${Math.round(accuracy)} m).`; },
      (err)=>{ if(status) status.textContent=`No se pudo obtener ubicación: ${err.message}`; clearGeo(); },
      { enableHighAccuracy:true, timeout:10000, maximumAge:0 }
    );
  }
  if(enFincaSi && enFincaNo){ enFincaSi.addEventListener('change', ()=> enFincaSi.checked ? captureGeo() : clearGeo()); enFincaNo.addEventListener('change', clearGeo); }

  // helpers
  const getRadioValue = (name)=>{ const el = form.querySelector(`input[type="radio"][name="${name}"]:checked`); return el? el.value : null; };
  const getCheckboxValues = (name)=> $$( `input[type="checkbox"][name="${name}"]:checked`, form).map(i=>i.value);
  const toSiNo = (v)=> v==='si' ? 'Sí' : v==='no' ? 'No' : '—';
  const escapeHTML = (s)=> (s??'').toString().replace(/[&<>"'`=\/]/g, c=> ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;','/':'&#x2F;','`':'&#x60;','=':'&#x3D;'}[c]) );

  const labelRango = {
    enero_q1:'1ª quincena de Enero', enero_q2:'2ª quincena de Enero',
    febrero_q1:'1ª quincena de Febrero', febrero_q2:'2ª quincena de Febrero',
    octubre_q1:'1ª quincena de Octubre', octubre_q2:'2ª quincena de Octubre',
    noviembre_q1:'1ª quincena de Noviembre', noviembre_q2:'2ª quincena de Noviembre',
    diciembre_q1:'1ª quincena de Diciembre', diciembre_q2:'2ª quincena de Diciembre'
  };

  function getDireccionFromForm(){
    const provincia=$('#dir_provincia')?.value?.trim()||null;
    const localidad=$('#dir_localidad')?.value?.trim()||null;
    const calle=$('#dir_calle')?.value?.trim()||null;
    const numero=$('#dir_numero')?.value?.trim()||null;
    return {provincia,localidad,calle,numero};
  }
  function formatDireccion(dir){
    if(!dir) return '—';
    const parts=[];
    if(dir.calle){ let c = escapeHTML(dir.calle); if(dir.numero) c += ' ' + escapeHTML(dir.numero); parts.push(c); }
    if(dir.localidad) parts.push(escapeHTML(dir.localidad));
    if(dir.provincia) parts.push(escapeHTML(dir.provincia));
    return parts.length ? parts.join(', ') : '—';
  }

  function renderResumenHTML(payload){
    const rangoSel = payload.rango_fecha ? (labelRango[payload.rango_fecha] || payload.rango_fecha) : '—';
    const prodsItems = (payload.productos||[]).map(p=>{
      const fuente = p.fuente==='yo' ? 'Proveedor propio' : 'SVE';
      const detalle = p.fuente==='yo' ? (p.marca ? ` — Marca: ${escapeHTML(p.marca)}` : '') : (p.producto_nombre ? ` — Producto: ${escapeHTML(p.producto_nombre)}` : '');
      const pat = p.patologia_nombre || `Patología #${p.patologia_id}`;
      return `<li>${escapeHTML(pat)} <small>(${fuente}${detalle})</small></li>`;
    }).join('');
    const formaPagoSel = $('#metodo_pago');
    const formaPagoTxt = (()=>{ if(!payload.forma_pago_id || !formaPagoSel) return '—'; const opt = formaPagoSel.querySelector(`option[value="${payload.forma_pago_id}"]`); return opt ? opt.textContent : '—'; })();

    const costos = calcularCostos(payload);
    const fmt = (n)=> new Intl.NumberFormat('es-AR',{minimumFractionDigits:2,maximumFractionDigits:2}).format(Number(n||0));

    const costosHTML = `
      <div class="costos-block" role="group" aria-label="Costo estimado">
        <h4 class="costos-title">Costo estimado</h4>
        <ul class="costos-list" role="list">
          <li class="costos-item">
            <div class="costos-label">Servicio base <span class="costos-help">${fmt(payload.superficie_ha)} ha × ${fmt(COSTO_BASE.costo)}/${escapeHTML(costos.moneda)}</span></div>
            <div class="costos-amount">${fmt(costos.base)}</div>
          </li>
          <li class="costos-item">
            <div class="costos-label">Productos SVE <span class="costos-help">${fmt(payload.superficie_ha)} ha</span></div>
            <div class="costos-amount">${fmt(costos.productos)}</div>
          </li>
          <li class="costos-item costos-total" aria-label="Total estimado">
            <div class="costos-label">Total estimado</div>
            <div class="costos-amount">${fmt(costos.total)}</div>
          </li>
        </ul>
      </div>`;

    return `
      <div class="modal-summary">
        <dl>
          <dt>Representante en finca</dt><dd>${toSiNo(payload.representante)}</dd>
          <dt>Líneas de media/alta tensión (&lt;30m)</dt><dd>${toSiNo(payload.linea_tension)}</dd>
          <dt>Zona de vuelo restringida (&lt;3km)</dt><dd>${toSiNo(payload.zona_restringida)}</dd>
          <dt>Corriente eléctrica disponible</dt><dd>${toSiNo(payload.corriente_electrica)}</dd>
          <dt>Agua potable disponible</dt><dd>${toSiNo(payload.agua_potable)}</dd>
          <dt>Cuarteles libres de obstáculos</dt><dd>${toSiNo(payload.libre_obstaculos)}</dd>
          <dt>Área de despegue apropiada</dt><dd>${toSiNo(payload.area_despegue)}</dd>
          <dt>Superficie (ha)</dt><dd>${escapeHTML(payload.superficie_ha ?? '—')}</dd>
          <dt>Dirección</dt><dd>${formatDireccion(payload.direccion)}</dd>
          <dt>Método de pago</dt><dd>${escapeHTML(formaPagoTxt)}</dd>
          <dt>Momento deseado</dt><dd>${escapeHTML(rangoSel)}</dd>
          <dt>Observaciones</dt><dd><div class="note">${escapeHTML(payload.observaciones ?? '—')}</div></dd>
          ${costosHTML}
        </dl>
      </div>`;
  }

  function flag(container, ok){
    if(!container) return ok;
    container.classList.toggle('has-error', !ok);
    const grp = container.querySelector('[role="group"], .gform-options, .gform-input, textarea');
    if(grp) grp.setAttribute('aria-invalid', String(!ok));
    return ok;
  }
  function atLeastOneChecked(sel, ctx=document){ return !!ctx.querySelector(`${sel}:checked`); }
  function getValuesChecked(sel, ctx=document){ return Array.from(ctx.querySelectorAll(`${sel}:checked`)).map(i=>i.value); }

  function validateGForm(){
    let ok=true, firstBad=null;
    const must=(container, cond)=>{ const good=!!cond; ok = ok && good; if(!good && !firstBad) firstBad = container; return flag(container, good); };

    // método de pago (+ cooperativa si id=6)
    const mpSel = $('#metodo_pago');
    flag($('#q_metodo_pago'), !!mpSel && !!mpSel.value);
    if(!mpSel || !mpSel.value){ window.showToast?.('error','Seleccioná un método de pago.'); return false; }
    if(parseInt(mpSel.value,10)===6){
      const wrap=$('#wrap_coop_cuota'), sel=$('#coop_descuento_id_real'), err=$('#coop_descuento_error');
      const coopOK = !!(sel && sel.value);
      if(wrap) wrap.classList.toggle('has-error', !coopOK);
      if(err) err.style.display = coopOK ? 'none' : 'block';
      if(!coopOK) return false;
    }

    must($('#q_representante'), atLeastOneChecked('input[type="radio"][name="representante"]', form));
    must($('#q_linea_tension'), atLeastOneChecked('input[type="radio"][name="linea_tension"]', form));
    must($('#q_zona_restringida'), atLeastOneChecked('input[type="radio"][name="zona_restringida"]', form));
    must($('#q_corriente_electrica'), atLeastOneChecked('input[type="radio"][name="corriente_electrica"]', form));
    must($('#q_agua_potable'), atLeastOneChecked('input[type="radio"][name="agua_potable"]', form));
    must($('#q_obstaculos'), atLeastOneChecked('input[type="radio"][name="libre_obstaculos"]', form));
    must($('#q_area_despegue'), atLeastOneChecked('input[type="radio"][name="area_despegue"]', form));
    must($('#q_ubicacion'), atLeastOneChecked('input[type="radio"][name="en_finca"]', form));

    const supEl = $('#superficie_ha'); let supOk=false;
    if(supEl){ const val = parseFloat(supEl.value); supOk = !isNaN(val) && val > 0; const err = $('#q_superficie .gform-error'); if(err) err.textContent='Debe ser un número mayor a 0.'; }
    must($('#q_superficie'), supOk);

    const motivos = getValuesChecked('input[type="checkbox"][name="motivo[]"]', form);
    let motivoOk = motivos.length > 0;
    if(motivoOk && $('#motivo_otros_chk')?.checked){ motivoOk = !!$('#motivo_otros')?.value.trim(); }
    must($('#q_motivo'), motivoOk);

    must($('#q_rango'), atLeastOneChecked('input[type="radio"][name="rango_fecha"]', form));

    // productos dinámicos
    const productosMarcados = getValuesChecked('input[type="checkbox"][name="productos[]"]', form);
    let prodOk = productosMarcados.length > 0;
    if(prodOk){
      for(const pid of productosMarcados){
        const box = document.querySelector(`.gform-optbox[data-patologia-id="${pid}"]`);
        const fuente = form.querySelector(`input[type="radio"][name="src-${pid}"]:checked`)?.value;
        if(!fuente){ prodOk=false; break; }
        if(fuente==='yo'){ const marca = box.querySelector(`#marca_${pid}`)?.value?.trim(); if(!marca){ prodOk=false; break; } }
        else { const sel = box.querySelector(`#sel_prod_${pid}`); if(!sel || !sel.value){ prodOk=false; break; } }
      }
    }
    must($('#q_productos'), prodOk);

    // dirección requerida si no está en finca
    const dir = getDireccionFromForm();
    let dirOk = true;
    if(getRadioValue('en_finca')==='no'){ dirOk = !!(dir.provincia && dir.localidad && dir.calle && dir.numero); }
    if(!flag($('#q_direccion'), dirOk)){ ok=false; if(!firstBad) firstBad=$('#q_direccion'); }

    if(!ok && firstBad) firstBad.scrollIntoView({behavior:'smooth', block:'center'});
    return ok;
  }

  // submit -> modal
  form.addEventListener('submit', (e)=>{
    e.preventDefault();
    if(!validateGForm()){ window.showToast?.('error','Revisá los campos marcados en rojo.'); return; }
    const motivos = getCheckboxValues('motivo[]');
    const payload = {
      representante: getRadioValue('representante'),
      linea_tension: getRadioValue('linea_tension'),
      zona_restringida: getRadioValue('zona_restringida'),
      corriente_electrica: getRadioValue('corriente_electrica'),
      agua_potable: getRadioValue('agua_potable'),
      libre_obstaculos: getRadioValue('libre_obstaculos'),
      area_despegue: getRadioValue('area_despegue'),
      superficie_ha: $('#superficie_ha')?.value?.trim() || null,
      forma_pago_id: (()=>{
        const v=$('#metodo_pago')?.value;
        return v ? parseInt(v,10) : null;
      })(),
      coop_descuento_nombre: (()=>{
        const mp=$('#metodo_pago')?.value;
        const sel=$('#coop_descuento_id_real');
        return (parseInt(mp||'0',10)===6 && sel) ? (sel.value || null) : null;
      })(),
      motivo: {
        opciones: motivos,
        otros: $('#motivo_otros_chk')?.checked ? ($('#motivo_otros')?.value?.trim() || null) : null,
      },
      rango_fecha: getRadioValue('rango_fecha'),
      productos: (()=>{
        const out=[];
        document.querySelectorAll('.gform-optbox[data-patologia-id]').forEach(box=>{
          const pid = parseInt(box.dataset.patologiaId,10);
          const pnom = box.dataset.patologiaNombre || '';
          const chk = box.querySelector('input[type="checkbox"][name="productos[]"]');
          if(!chk || !chk.checked) return;
          const fuente = getRadioValue(`src-${pid}`);
          if(fuente==='yo'){
            const marca = box.querySelector(`#marca_${pid}`)?.value?.trim() || null;
            out.push({patologia_id:pid, patologia_nombre:pnom, fuente:'yo', marca});
          }else{
            const sel = box.querySelector(`#sel_prod_${pid}`);
            const producto_id = sel && sel.value ? parseInt(sel.value,10) : null;
            const producto_nombre = sel ? sel.options[sel.selectedIndex]?.textContent : null;
            out.push({patologia_id:pid, patologia_nombre:pnom, fuente:'sve', producto_id, producto_nombre});
          }
        });
        return out;
      })(),
      direccion: getDireccionFromForm(),
      ubicacion: {
        en_finca: getRadioValue('en_finca'),
        lat: $('#ubicacion_lat')?.value || null,
        lng: $('#ubicacion_lng')?.value || null,
        acc: $('#ubicacion_acc')?.value || null,
        timestamp: $('#ubicacion_ts')?.value || null,
      },
      observaciones: $('#observaciones')?.value?.trim() || null,
      sesion: sessionData
    };
    __ultimoPayload = payload;
    if(resumenModal) resumenModal.innerHTML = renderResumenHTML(payload);
    abrirModal();
  });

})();
</script>
