<?php // views/partials/drones/view/drone_stock_view.php ?>
<div class="content">
  <div class="card" style="background-color:#5b21b6;">
    <h3 style="color:white;">Módulo: Stock de Insumos (Drones)</h3>
    <p style="color:white;margin:0;">Alta, edición y control de stock. Asigná hasta 6 patologías por producto.</p>
  </div>

  <!-- Formulario -->
  <div class="card" id="stock-form-card" aria-labelledby="form-title">
    <h4 id="form-title">Producto</h4>
    <form id="producto-form" autocomplete="off" novalidate>
      <input type="hidden" id="producto_id" name="producto_id" value="">

      <div class="form-grid grid-3">
        <div class="input-group">
          <label for="nombre">Nombre</label>
          <div class="input-icon input-icon-name">
            <input type="text" id="nombre" name="nombre" placeholder="Ej: Cobre 50"
                   required aria-required="true" />
          </div>
        </div>

        <div class="input-group">
          <label for="principio_activo">Principio Activo</label>
          <div class="input-icon input-icon-name">
            <input type="text" id="principio_activo" name="principio_activo" placeholder="Ej: Oxicloruro de cobre" />
          </div>
        </div>

        <div class="input-group">
          <label for="cantidad_deposito">Cantidad en depósito</label>
          <div class="input-icon input-icon-number">
            <input type="number" id="cantidad_deposito" name="cantidad_deposito" min="0" step="1"
                   placeholder="0" required aria-required="true" />
          </div>
        </div>

        <div class="input-group" style="grid-column: 1 / -1;">
          <label for="detalle">Detalle</label>
          <div class="input-icon input-icon-name">
            <input type="text" id="detalle" name="detalle" placeholder="Descripción breve del producto" />
          </div>
        </div>

        <!-- Patologías (hasta 6 selects) -->
        <div class="input-group">
          <label for="pat_1">Patología 1</label>
          <div class="input-icon input-icon-name">
            <select id="pat_1" name="patologias[]" aria-label="Patología 1"></select>
          </div>
        </div>
        <div class="input-group">
          <label for="pat_2">Patología 2</label>
          <div class="input-icon input-icon-name">
            <select id="pat_2" name="patologias[]" aria-label="Patología 2"></select>
          </div>
        </div>
        <div class="input-group">
          <label for="pat_3">Patología 3</label>
          <div class="input-icon input-icon-name">
            <select id="pat_3" name="patologias[]" aria-label="Patología 3"></select>
          </div>
        </div>
        <div class="input-group">
          <label for="pat_4">Patología 4</label>
          <div class="input-icon input-icon-name">
            <select id="pat_4" name="patologias[]" aria-label="Patología 4"></select>
          </div>
        </div>
        <div class="input-group">
          <label for="pat_5">Patología 5</label>
          <div class="input-icon input-icon-name">
            <select id="pat_5" name="patologias[]" aria-label="Patología 5"></select>
          </div>
        </div>
        <div class="input-group">
          <label for="pat_6">Patología 6</label>
          <div class="input-icon input-icon-name">
            <select id="pat_6" name="patologias[]" aria-label="Patología 6"></select>
          </div>
        </div>
      </div>

      <div class="form-grid grid-3" style="margin-top:12px;">
        <button type="submit" class="btn btn-aceptar" id="btn-guardar">Guardar</button>
        <button type="button" class="btn btn-info" id="btn-limpiar">Limpiar</button>
        <button type="button" class="btn btn-cancelar" id="btn-cancelar-edicion" style="display:none;">Cancelar edición</button>
      </div>
      <div id="live-status" aria-live="polite" class="sr-only"></div>
    </form>
  </div>

  <!-- Tabla -->
  <div class="card tabla-card" id="stock-root">
    <h2>Listado</h2>
    <div class="tabla-wrapper">
      <table class="data-table" id="tabla-productos">
        <thead>
          <tr>
            <th>#</th>
            <th>Nombre</th>
            <th>Principio activo</th>
            <th>Stock</th>
            <th>Patologías</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody id="tbody-productos">
          <tr><td colspan="6">Cargando…</td></tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Modal eliminar -->
  <div id="modal" class="modal hidden" role="dialog" aria-modal="true" aria-labelledby="modal-title">
    <div class="modal-content">
      <h3 id="modal-title">Eliminar producto</h3>
      <p>¿Confirmás eliminar el producto seleccionado? Esta acción no se puede deshacer.</p>
      <div class="form-buttons">
        <button class="btn btn-aceptar" id="confirm-delete">Aceptar</button>
        <button class="btn btn-cancelar" onclick="closeModal()">Cancelar</button>
      </div>
    </div>
  </div>
</div>

<style>
  /* Inline mínimo y no invasivo */
.badge { display:inline-block; padding:2px 8px; border-radius:9999px; font-size:.75rem; line-height:1.2; background:#dbeafe; color:#1e3a8a; border:1px solid #bfdbfe; font-weight:600; }
.badge + .badge { margin-left:6px; }
  .sr-only { position:absolute; width:1px; height:1px; padding:0; margin:-1px; overflow:hidden; clip:rect(0,0,0,0); white-space:nowrap; border:0; }
</style>

<script>
(function () {
  // Evitar colisiones en global
  const API_URL = '../partials/drones/controller/drone_stock_controller.php';
  let allPatologias = [];
  let deleteId = null;

  const $ = (sel) => document.querySelector(sel);
  const $$ = (sel) => Array.from(document.querySelectorAll(sel));

  function setLive(msg) { const live = $('#live-status'); if (live) live.textContent = msg; }

  async function api(action, payload = null, method = 'POST') {
    const opts = payload ? {
      method,
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action, ...payload })
    } : { method: 'GET' };

    const url = payload ? API_URL : API_URL + '?action=' + encodeURIComponent(action) + '&t=' + Date.now();
    const res = await fetch(url, opts);
    const json = await res.json();
    if (!json.ok) throw new Error(json.error || 'Operación fallida');
    return json.data;
  }

  function optionify(select, items) {
    select.innerHTML = '<option value="">(sin asignar)</option>' +
      items.map(p => `<option value="${p.id}">${p.nombre}</option>`).join('');
  }

  async function cargarPatologias() {
    const data = await api('patologias', null, 'GET');
    allPatologias = data.items || [];
    ['#pat_1','#pat_2','#pat_3','#pat_4','#pat_5','#pat_6'].forEach(id => {
      optionify($(id), allPatologias);
    });
  }

  function renderProductos(items) {
    const tbody = $('#tbody-productos');
    if (!items.length) {
      tbody.innerHTML = '<tr><td colspan="6">Sin productos cargados.</td></tr>';
      return;
    }
    tbody.innerHTML = items.map((it, idx) => {
      const badges = (it.patologias_nombres || []).map(n => `<span class="badge">${n}</span>`).join(' ');
      const patIds = (it.patologias_ids || []).join(',');
      return `
        <tr data-id="${it.id}" data-pat-ids="${patIds}">
          <td>${idx + 1}</td>
          <td>${it.nombre}</td>
          <td>${it.principio_activo || '-'}</td>
          <td>${it.cantidad_deposito}</td>
          <td>${badges || '-'}</td>
          <td>
            <button class="btn btn-info btn-sm" data-action="edit">Editar</button>
            <button class="btn btn-cancelar btn-sm" data-action="del">Eliminar</button>
          </td>
        </tr>
      `;
    }).join('');
  }

  async function cargarProductos() {
    const data = await api('list', null, 'GET');
    renderProductos(data.items || []);
  }

  function leerPatologiasDelForm() {
    const vals = $$('#producto-form select[name="patologias[]"]').map(s => s.value).filter(v => v);
    // Limitar a 6, evitar duplicados
    return Array.from(new Set(vals)).slice(0, 6).map(v => parseInt(v, 10));
  }

  function limpiarForm() {
    $('#producto_id').value = '';
    $('#nombre').value = '';
    $('#principio_activo').value = '';
    $('#cantidad_deposito').value = '';
    $('#detalle').value = '';
    $$('#producto-form select[name="patologias[]"]').forEach(s => { s.value = ''; });
    $('#btn-cancelar-edicion').style.display = 'none';
    setLive('Formulario limpio');
  }

  function poblarFormDesdeFila(tr) {
    $('#producto_id').value = tr.getAttribute('data-id');
    $('#nombre').value = tr.children[1].textContent;
    $('#principio_activo').value = tr.children[2].textContent === '-' ? '' : tr.children[2].textContent;
    $('#cantidad_deposito').value = tr.children[3].textContent;

    const ids = (tr.getAttribute('data-pat-ids') || '').split(',').filter(Boolean);
    const selects = $$('#producto-form select[name="patologias[]"]');
    selects.forEach((s, i) => { s.value = ids[i] || ''; });

    $('#btn-cancelar-edicion').style.display = 'inline-block';
    setLive('Editando producto ' + $('#nombre').value);
  }

  // Eventos
  $('#producto-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const payload = {
      id: $('#producto_id').value ? parseInt($('#producto_id').value, 10) : null,
      nombre: $('#nombre').value.trim(),
      principio_activo: $('#principio_activo').value.trim(),
      cantidad_deposito: parseInt($('#cantidad_deposito').value, 10) || 0,
      detalle: $('#detalle').value.trim(),
      patologias: leerPatologiasDelForm()
    };

    if (!payload.nombre) { showAlert('error', 'El nombre es obligatorio.'); return; }
    if (payload.cantidad_deposito < 0) { showAlert('error', 'La cantidad no puede ser negativa.'); return; }
    if (payload.patologias.length > 6) { showAlert('error', 'Máximo 6 patologías.'); return; }

    try {
      if (payload.id) {
        await api('update', payload, 'POST');
        showAlert('success', 'Producto actualizado.');
      } else {
        await api('create', payload, 'POST');
        showAlert('success', 'Producto creado.');
      }
      limpiarForm();
      await cargarProductos();
    } catch (err) {
      showAlert('error', err.message || 'Error al guardar.');
    }
  });

  $('#btn-limpiar').addEventListener('click', limpiarForm);
  $('#btn-cancelar-edicion').addEventListener('click', limpiarForm);

  $('#tbody-productos').addEventListener('click', (e) => {
    const btn = e.target.closest('button[data-action]');
    if (!btn) return;
    const tr = e.target.closest('tr');
    const id = parseInt(tr.getAttribute('data-id'), 10);

    if (btn.dataset.action === 'edit') {
      poblarFormDesdeFila(tr);
    } else if (btn.dataset.action === 'del') {
      deleteId = id;
      openModal();
    }
  });

  $('#confirm-delete').addEventListener('click', async () => {
    if (!deleteId) return;
    try {
      await api('delete', { id: deleteId }, 'POST');
      showAlert('success', 'Producto eliminado.');
      closeModal();
      deleteId = null;
      await cargarProductos();
    } catch (err) {
      showAlert('error', err.message || 'Error al eliminar.');
    }
  });

  // Init
  (async function init() {
    try {
      await cargarPatologias();
      await cargarProductos();
      setLive('Datos cargados');
    } catch (e) {
      showAlert('error', 'No se pudo inicializar el módulo.');
    }
  })();
})();
</script>
