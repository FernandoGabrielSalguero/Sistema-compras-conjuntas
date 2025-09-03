<?php // views/partials/drones/view/drone_variables_view.php 
?>
<div class="content">
  <!-- Tarjeta violeta (solo encabezado) -->
  <div class="card" style="background-color:#5b21b6;">
    <h3 style="color:white;">Variables del sistema</h3>
    <p style="color:white;margin:0;">Gestioná catálogos reutilizables por todo el sistema.</p>
  </div>

  <!-- Patologías -->
  <div id="card-patologias" class="card tabla-card" aria-labelledby="h-patologias">
    <h4 id="h-patologias">Patologías</h4>
    <!-- <p class="muted" style="margin-top:-6px;color:#64748b;">CRUD de patologías (tabla: <code>dron_patologias</code>).</p> -->

    <form id="form-patologias" class="form-grid grid-3" autocomplete="off" aria-describedby="p-msg">
      <input type="hidden" name="id" id="p-id" value="">
      <div class="input-group">
        <label for="p-nombre">Nombre</label>
        <div class="input-icon input-icon-name">
          <input type="text" id="p-nombre" name="nombre" placeholder="Ej.: Mildiu" required maxlength="100" />
        </div>
      </div>
      <div class="input-group">
        <label for="p-desc">Descripción</label>
        <div class="input-icon input-icon-description">
          <input type="text" id="p-desc" name="descripcion" placeholder="Opcional (máx. 255)" maxlength="255" />
        </div>
      </div>
      <div class="form-grid grid-3">
        <button id="p-submit" type="submit" class="btn btn-aceptar">Guardar</button>
        <button id="p-cancel" type="button" class="btn btn-cancelar" aria-label="Cancelar edición">Cancelar</button>
        <button id="p-limpiar" type="button" class="btn btn-info">Limpiar</button>
      </div>
    </form>

    <div class="form-grid grid-3" style="margin-top:8px;">
      <div class="input-group">
        <label for="p-q">Buscar</label>
        <div class="input-icon input-icon-search">
          <input type="text" id="p-q" placeholder="Filtrar por nombre/descr." />
        </div>
      </div>
      <div class="input-group">
        <label for="p-inactivos">Ver inactivos</label>
        <label class="switch">
          <input type="checkbox" id="p-inactivos" />
          <span class="slider"></span>
        </label>
      </div>
    </div>

    <div id="p-msg" role="status" aria-live="polite" class="muted" style="margin:6px 0;"></div>

    <div class="tabla-wrapper" style="margin-top:8px;">
      <table class="data-table" aria-describedby="h-patologias">
        <thead>
          <tr>
            <th>#</th>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Estado</th>
            <th style="width:220px;">Acciones</th>
          </tr>
        </thead>
        <tbody id="p-tbody"></tbody>
      </table>
    </div>
  </div>

  <!-- Formas de pago -->
  <div id="card-formas-pago" class="card tabla-card" aria-labelledby="h-formaspago">
    <h4 id="h-formaspago">Formas de pago</h4>
    <!-- <p class="muted" style="margin-top:-6px;color:#64748b;">CRUD de formas de pago (tabla: <code>dron_formas_pago</code>).</p> -->

    <form id="form-formaspago" class="form-grid grid-3" autocomplete="off" aria-describedby="fp-msg">
      <input type="hidden" name="id" id="fp-id" value="">
      <div class="input-group">
        <label for="fp-nombre">Nombre</label>
        <div class="input-icon input-icon-name">
          <input type="text" id="fp-nombre" name="nombre" placeholder="Ej.: Transferencia, Efectivo, Tarjeta" required maxlength="100" />
        </div>
      </div>
      <div class="input-group">
        <label for="fp-desc">Descripción</label>
        <div class="input-icon input-icon-description">
          <input type="text" id="fp-desc" name="descripcion" placeholder="Opcional (máx. 255)" maxlength="255" />
        </div>
      </div>
      <div class="form-grid grid-3">
        <button id="fp-submit" type="submit" class="btn btn-aceptar">Guardar</button>
        <button id="fp-cancel" type="button" class="btn btn-cancelar" aria-label="Cancelar edición">Cancelar</button>
        <button id="fp-limpiar" type="button" class="btn btn-info">Limpiar</button>
      </div>
    </form>

    <div class="form-grid grid-3" style="margin-top:8px;">
      <div class="input-group">
        <label for="fp-q">Buscar</label>
        <div class="input-icon input-icon-search">
          <input type="text" id="fp-q" placeholder="Filtrar por nombre/descr." />
        </div>
      </div>
      <div class="input-group">
        <label for="fp-inactivos">Ver inactivos</label>
        <label class="switch">
          <input type="checkbox" id="fp-inactivos" />
          <span class="slider"></span>
        </label>
      </div>
    </div>

    <div id="fp-msg" role="status" aria-live="polite" class="muted" style="margin:6px 0;"></div>

    <div class="tabla-wrapper" style="margin-top:8px;">
      <table class="data-table" aria-describedby="h-formaspago">
        <thead>
          <tr>
            <th>#</th>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Estado</th>
            <th style="width:220px;">Acciones</th>
          </tr>
        </thead>
        <tbody id="fp-tbody"></tbody>
      </table>
    </div>
  </div>


  <!-- Producción -->
  <div id="card-produccion" class="card tabla-card" aria-labelledby="h-produccion">
    <h4 id="h-produccion">Producción</h4>
    <!-- <p class="muted" style="margin-top:-6px;color:#64748b;">CRUD de producción (tabla: <code>dron_produccion</code>).</p> -->

    <form id="form-produccion" class="form-grid grid-3" autocomplete="off" aria-describedby="r-msg">
      <input type="hidden" name="id" id="r-id" value="">
      <div class="input-group">
        <label for="r-nombre">Nombre</label>
        <div class="input-icon input-icon-name">
          <input type="text" id="r-nombre" name="nombre" placeholder="Ej.: Uva, Olivo, etc." required maxlength="100" />
        </div>
      </div>
      <div class="input-group">
        <label for="r-desc">Descripción</label>
        <div class="input-icon input-icon-description">
          <input type="text" id="r-desc" name="descripcion" placeholder="Opcional (máx. 255)" maxlength="255" />
        </div>
      </div>
      <div class="form-grid grid-3">
        <button id="r-submit" type="submit" class="btn btn-aceptar">Guardar</button>
        <button id="r-cancel" type="button" class="btn btn-cancelar" aria-label="Cancelar edición">Cancelar</button>
        <button id="r-limpiar" type="button" class="btn btn-info">Limpiar</button>
      </div>
    </form>

    <div class="form-grid grid-3" style="margin-top:8px;">
      <div class="input-group">
        <label for="r-q">Buscar</label>
        <div class="input-icon input-icon-search">
          <input type="text" id="r-q" placeholder="Filtrar por nombre/descr." />
        </div>
      </div>
      <div class="input-group">
        <label for="r-inactivos">Ver inactivos</label>
        <label class="switch">
          <input type="checkbox" id="r-inactivos" />
          <span class="slider"></span>
        </label>
      </div>
    </div>

    <div id="r-msg" role="status" aria-live="polite" class="muted" style="margin:6px 0;"></div>

    <div class="tabla-wrapper" style="margin-top:8px;">
      <table class="data-table" aria-describedby="h-produccion">
        <thead>
          <tr>
            <th>#</th>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Estado</th>
            <th style="width:220px;">Acciones</th>
          </tr>
        </thead>
        <tbody id="r-tbody"></tbody>
      </table>
    </div>
  </div>


  <!-- Costo por hectárea -->
  <div id="card-costo" class="card tabla-card" aria-labelledby="h-costo">
    <h4 id="h-costo">Costo por hectárea</h4>
    <!-- <p class="muted" style="margin-top:-6px;color:#64748b;">Variable única (tabla: <code>dron_costo_hectarea</code>).</p> -->

    <form id="form-costo" class="form-grid grid-3" autocomplete="off" aria-describedby="c-msg">
      <div class="input-group">
        <label for="c-valor">Costo (por ha)</label>
        <div class="input-icon input-icon-money">
          <input type="number" id="c-valor" name="costo" placeholder="Ej.: 1200.00" step="0.01" min="0" required />
        </div>
      </div>
      <div class="input-group">
        <label for="c-moneda">Moneda</label>
        <div class="input-icon input-icon-money">
          <input type="text" id="c-moneda" name="moneda" placeholder="Pesos" maxlength="20" value="Pesos" />
        </div>
      </div>
      <div class="form-grid grid-3">
        <button id="c-submit" type="submit" class="btn btn-aceptar">Guardar</button>
        <button id="c-cancel" type="button" class="btn btn-cancelar">Cancelar</button>
        <button id="c-recargar" type="button" class="btn btn-info">Recargar</button>
      </div>
    </form>

    <div id="c-msg" role="status" aria-live="polite" class="muted" style="margin:6px 0;"></div>
  </div>

</div>

<style>
  /* Ajustes mínimos, no rompe el CDN */
  .tabla-card {
    margin-top: 14px;
  }

  .data-table td .badge {
    display: inline-block;
  }

  .acciones-wrap {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
  }
</style>

<script>
  (function() {
    // Ruta ABSOLUTA
    const DVAR_API = '/views/partials/drones/controller/drone_variables_controller.php';

    // Utils
    const $ = (sel, ctx = document) => ctx.querySelector(sel);
    const el = (tag, props = {}) => Object.assign(document.createElement(tag), props);
    const debounce = (fn, ms = 280) => {
      let t;
      return (...a) => {
        clearTimeout(t);
        t = setTimeout(() => fn(...a), ms);
      }
    };
    const show = (type, msg) => {
      try {
        window.showAlert ? window.showAlert(type, msg) : alert(msg);
      } catch (_) {
        alert(msg);
      }
    };

    // Render fila
    function renderRow(item, tbody, entity) {
      const tr = el('tr');
      tr.append(
        el('td', {
          textContent: item.id
        }),
        el('td', {
          textContent: item.nombre
        }),
        el('td', {
          textContent: item.descripcion || ''
        })
      );

      const tdEstado = el('td');
      const estadoOk = item.activo === 'si';
      const badge = el('span', {
        className: 'badge ' + (estadoOk ? 'success' : 'warning'),
        textContent: estadoOk ? 'Activo' : 'Inactivo'
      });
      tdEstado.append(badge);
      tr.append(tdEstado);

      const tdAcc = el('td');
      const wrap = el('div', {
        className: 'acciones-wrap'
      });

      const btnEdit = el('button', {
        className: 'btn btn-info',
        type: 'button',
        textContent: 'Editar',
        'aria-label': 'Editar'
      });
      const btnToggle = el('button', {
        className: 'btn ' + (estadoOk ? 'btn-cancelar' : 'btn-aceptar'),
        type: 'button',
        textContent: estadoOk ? 'Eliminar' : 'Reactivar',
        'aria-label': estadoOk ? 'Eliminar' : 'Reactivar'
      });

      btnEdit.addEventListener('click', () => {
        if (entity === 'patologias') {
          $('#p-id').value = item.id;
          $('#p-nombre').value = item.nombre;
          $('#p-desc').value = item.descripcion || '';
          $('#p-nombre').focus();
        } else if (entity === 'produccion') {
          $('#r-id').value = item.id;
          $('#r-nombre').value = item.nombre;
          $('#r-desc').value = item.descripcion || '';
          $('#r-nombre').focus();
        } else if (entity === 'formas_pago') {
          $('#fp-id').value = item.id;
          $('#fp-nombre').value = item.nombre;
          $('#fp-desc').value = item.descripcion || '';
          $('#fp-nombre').focus();
        }
      });

      btnToggle.addEventListener('click', async () => {
        if (estadoOk && !confirm('¿Eliminar este registro? Quedará inactivo.')) return;
        const res = await fetch(DVAR_API + '?action=delete&entity=' + entity, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            id: item.id
          })
        }).then(r => r.json()).catch(() => ({
          ok: false,
          error: 'Error de red'
        }));

        if (res.ok) {
          show('success', estadoOk ? 'Registro inactivado' : 'Registro reactivado');
          await (entity === 'patologias' ? loadPatologias() : (entity === 'produccion' ? loadProduccion() : loadFormasPago()));
        } else {
          show('error', res.error || 'No se pudo actualizar');
        }
      });


      wrap.append(btnEdit, btnToggle);
      tdAcc.append(wrap);
      tr.append(tdAcc);
      tbody.append(tr);
    }

    async function list(entity, q, inactivos) {
      const url = new URL(DVAR_API, location.origin);
      url.searchParams.set('action', 'list');
      url.searchParams.set('entity', entity);
      url.searchParams.set('q', q || '');
      url.searchParams.set('inactivos', inactivos ? '1' : '0');
      url.searchParams.set('t', Date.now());
      return fetch(url, {
        cache: 'no-store'
      }).then(r => r.json());
    }

    async function save(entity, payload) {
      const url = DVAR_API + '?action=' + (payload.id ? 'update' : 'create') + '&entity=' + entity;
      return fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
      }).then(r => r.json());
    }

    // Patologías
    const loadPatologias = async () => {
      const q = $('#p-q').value.trim();
      const ina = $('#p-inactivos').checked;
      const tbody = $('#p-tbody');
      tbody.innerHTML = '';
      const data = await list('patologias', q, ina);
      if (!data.ok) {
        $('#p-msg').textContent = 'Error: ' + (data.error || 'No se pudo cargar');
        show('error', 'No se pudo cargar Patologías');
        return;
      }
      (data.data || []).forEach(it => renderRow(it, tbody, 'patologias'));
      $('#p-msg').textContent = (data.data || []).length ? '' : 'Sin resultados.';
      if (!(data.data || []).length) show('info', 'Sin resultados en Patologías');
    };

    $('#form-patologias').addEventListener('submit', async (e) => {
      e.preventDefault();
      const id = parseInt($('#p-id').value || '0', 10) || null;
      const nombre = $('#p-nombre').value.trim();
      const descripcion = $('#p-desc').value.trim();
      const res = await save('patologias', {
        id,
        nombre,
        descripcion
      });
      if (res.ok) {
        show('success', id ? 'Patología actualizada' : 'Patología creada');
        $('#p-id').value = '';
        $('#p-nombre').value = '';
        $('#p-desc').value = '';
        await loadPatologias();
      } else {
        show('error', res.error || 'No se pudo guardar');
      }
    });
    $('#p-cancel').addEventListener('click', () => {
      $('#p-id').value = '';
      $('#p-nombre').value = '';
      $('#p-desc').value = '';
      $('#p-msg').textContent = '';
      show('info', 'Edición cancelada');
    });
    $('#p-limpiar').addEventListener('click', () => {
      $('#p-q').value = '';
      $('#p-inactivos').checked = false;
      show('info', 'Filtros limpiados');
      loadPatologias();
    });
    $('#p-q').addEventListener('input', debounce(loadPatologias, 300));
    $('#p-inactivos').addEventListener('change', loadPatologias);

    // Producción
    const loadProduccion = async () => {
      const q = $('#r-q').value.trim();
      const ina = $('#r-inactivos').checked;
      const tbody = $('#r-tbody');
      tbody.innerHTML = '';
      const data = await list('produccion', q, ina);
      if (!data.ok) {
        $('#r-msg').textContent = 'Error: ' + (data.error || 'No se pudo cargar');
        show('error', 'No se pudo cargar Producción');
        return;
      }
      (data.data || []).forEach(it => renderRow(it, tbody, 'produccion'));
      $('#r-msg').textContent = (data.data || []).length ? '' : 'Sin resultados.';
      if (!(data.data || []).length) show('info', 'Sin resultados en Producción');
    };

    $('#form-produccion').addEventListener('submit', async (e) => {
      e.preventDefault();
      const id = parseInt($('#r-id').value || '0', 10) || null;
      const nombre = $('#r-nombre').value.trim();
      const descripcion = $('#r-desc').value.trim();
      const res = await save('produccion', {
        id,
        nombre,
        descripcion
      });
      if (res.ok) {
        show('success', id ? 'Producción actualizada' : 'Producción creada');
        $('#r-id').value = '';
        $('#r-nombre').value = '';
        $('#r-desc').value = '';
        await loadProduccion();
      } else {
        show('error', res.error || 'No se pudo guardar');
      }
    });
    $('#r-cancel').addEventListener('click', () => {
      $('#r-id').value = '';
      $('#r-nombre').value = '';
      $('#r-desc').value = '';
      $('#r-msg').textContent = '';
      show('info', 'Edición cancelada');
    });
    $('#r-limpiar').addEventListener('click', () => {
      $('#r-q').value = '';
      $('#r-inactivos').checked = false;
      show('info', 'Filtros limpiados');
      loadProduccion();
    });
    $('#r-q').addEventListener('input', debounce(loadProduccion, 300));
    $('#r-inactivos').addEventListener('change', loadProduccion);

    // ---------- Costo por hectárea (singleton) ----------
    function parseDecimal(v) {
      if (v == null) return NaN;
      // admitir coma como separador decimal
      const s = String(v).replace(/\./g, '').replace(',', '.'); // "1.234,56" -> "1234,56"
      return parseFloat(s);
    }

    async function loadCosto() {
      const res = await fetch(DVAR_API + '?action=get&entity=costo_hectarea&t=' + Date.now(), {
          cache: 'no-store'
        })
        .then(r => r.json()).catch(() => ({
          ok: false,
          error: 'Error de red'
        }));
      if (!res.ok) {
        show('error', res.error || 'No se pudo cargar Costo por hectárea');
        return;
      }
      const row = res.data || {};
      $('#c-valor').value = (row.costo != null) ? Number(row.costo).toFixed(2) : '';
      $('#c-moneda').value = row.moneda || 'Pesos';
    }

    document.getElementById('form-costo').addEventListener('submit', async (e) => {
      e.preventDefault();
      const costo = parseDecimal($('#c-valor').value);
      const moneda = $('#c-moneda').value.trim() || 'Pesos';

      if (!isFinite(costo) || costo < 0) {
        show('error', 'Ingresá un costo válido (>= 0)');
        return;
      }

      const res = await fetch(DVAR_API + '?action=update&entity=costo_hectarea', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          costo: Number(costo.toFixed(2)),
          moneda
        })
      }).then(r => r.json()).catch(() => ({
        ok: false,
        error: 'Error de red'
      }));

      if (res.ok) {
        show('success', 'Costo actualizado');
        await loadCosto();
      } else {
        show('error', res.error || 'No se pudo actualizar el costo');
      }
    });

    document.getElementById('c-cancel').addEventListener('click', async () => {
      await loadCosto();
      show('info', 'Cambios descartados');
    });
    document.getElementById('c-recargar').addEventListener('click', loadCosto);

    // -------- Formas de pago
    const loadFormasPago = async () => {
      const q = $('#fp-q').value.trim();
      const ina = $('#fp-inactivos').checked;
      const tbody = $('#fp-tbody');
      tbody.innerHTML = '';
      const data = await list('formas_pago', q, ina);
      if (!data.ok) {
        $('#fp-msg').textContent = 'Error: ' + (data.error || 'No se pudo cargar');
        show('error', 'No se pudo cargar Formas de pago');
        return;
      }
      (data.data || []).forEach(it => renderRow(it, tbody, 'formas_pago'));
      $('#fp-msg').textContent = (data.data || []).length ? '' : 'Sin resultados.';
      if (!(data.data || []).length) show('info', 'Sin resultados en Formas de pago');
    };

    document.getElementById('form-formaspago').addEventListener('submit', async (e) => {
      e.preventDefault();
      const id = parseInt($('#fp-id').value || '0', 10) || null;
      const nombre = $('#fp-nombre').value.trim();
      const descripcion = $('#fp-desc').value.trim();
      const res = await save('formas_pago', {
        id,
        nombre,
        descripcion
      });
      if (res.ok) {
        show('success', id ? 'Forma de pago actualizada' : 'Forma de pago creada');
        $('#fp-id').value = '';
        $('#fp-nombre').value = '';
        $('#fp-desc').value = '';
        await loadFormasPago();
      } else {
        show('error', res.error || 'No se pudo guardar');
      }
    });

    $('#fp-cancel').addEventListener('click', () => {
      $('#fp-id').value = '';
      $('#fp-nombre').value = '';
      $('#fp-desc').value = '';
      $('#fp-msg').textContent = '';
      show('info', 'Edición cancelada');
    });
    $('#fp-limpiar').addEventListener('click', () => {
      $('#fp-q').value = '';
      $('#fp-inactivos').checked = false;
      show('info', 'Filtros limpiados');
      loadFormasPago();
    });
    $('#fp-q').addEventListener('input', debounce(loadFormasPago, 300));
    $('#fp-inactivos').addEventListener('change', loadFormasPago);


    (async function init() {
      try {
        await fetch(DVAR_API + '?action=health&t=' + Date.now(), {
          cache: 'no-store'
        }).then(r => r.json());
      } catch (_) {}
      await loadPatologias();
      await loadProduccion();
      await loadFormasPago();
      await loadCosto();
    })();
  })();
</script>