<?php
$stepEditBasePath = $appBasePath ?? '';
?>

<style>
    .step-edit-launch-row {
        display: flex;
        flex-wrap: wrap;
        gap: .75rem;
        align-items: center;
        justify-content: space-between;
    }

    .step-edit-modal {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: grid;
        place-items: center;
        padding: 18px;
        background: rgba(15, 23, 42, .54);
    }

    .step-edit-modal.hidden {
        display: none;
    }

    .step-edit-dialog {
        width: min(1180px, 100%);
        max-height: min(92vh, 920px);
        display: flex;
        flex-direction: column;
        overflow: hidden;
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 24px 70px rgba(15, 23, 42, .32);
    }

    .step-edit-head {
        display: flex;
        gap: 1rem;
        align-items: flex-start;
        justify-content: space-between;
        padding: 18px 20px 14px;
        border-bottom: 1px solid rgba(148, 163, 184, .35);
    }

    .step-edit-head h2 {
        margin: 0;
        font-size: 1.18rem;
    }

    .step-edit-subtitle {
        margin: .25rem 0 0;
        color: rgba(15, 23, 42, .68);
        font-size: .9rem;
    }

    .step-edit-body {
        overflow: auto;
        padding: 18px 20px 22px;
    }

    .step-edit-flowbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: .5rem;
        margin-bottom: 1rem;
    }

    .step-edit-flowbar button {
        display: inline-flex;
        align-items: center;
        gap: .25rem;
    }

    .step-edit-current {
        color: rgba(15, 23, 42, .68);
        font-weight: 700;
        font-size: .88rem;
    }

    .step-edit-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .9rem;
    }

    .step-edit-list-card,
    .step-edit-progress-card,
    .step-edit-field-group {
        border: 1px solid rgba(148, 163, 184, .35);
        border-radius: 8px;
        padding: 14px;
        background: #fff;
    }

    .step-edit-list-card {
        cursor: pointer;
        transition: border-color .15s ease, box-shadow .15s ease;
    }

    .step-edit-list-card:hover {
        border-color: #2563eb;
        box-shadow: 0 8px 22px rgba(37, 99, 235, .12);
    }

    .step-edit-list-title {
        display: flex;
        justify-content: space-between;
        gap: .75rem;
        font-weight: 800;
        color: #0f172a;
    }

    .step-edit-muted {
        color: rgba(15, 23, 42, .65);
        font-size: .86rem;
    }

    .step-edit-progress {
        width: 100%;
        height: 10px;
        overflow: hidden;
        border-radius: 999px;
        background: #e2e8f0;
        margin-top: .55rem;
    }

    .step-edit-progress > span {
        display: block;
        height: 100%;
        width: 0;
        border-radius: inherit;
        background: linear-gradient(90deg, #16a34a, #2563eb);
        transition: width .2s ease;
    }

    .step-edit-progress-stack {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: .75rem;
        margin-bottom: 1rem;
    }

    .step-edit-form-layout {
        display: grid;
        grid-template-columns: 280px minmax(0, 1fr);
        gap: 1rem;
    }

    .step-edit-side {
        align-self: start;
        position: sticky;
        top: 0;
        display: grid;
        gap: .75rem;
    }

    .step-edit-field-group {
        margin-bottom: 1rem;
    }

    .step-edit-field-group h3 {
        margin: 0 0 .8rem;
        font-size: 1rem;
    }

    .step-edit-fields {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .75rem;
    }

    .step-edit-field label {
        display: block;
        font-weight: 700;
        font-size: .85rem;
        margin-bottom: .35rem;
        color: #334155;
    }

    .step-edit-field input,
    .step-edit-field select,
    .step-edit-field textarea {
        width: 100%;
        border: 1px solid rgba(100, 116, 139, .4);
        border-radius: 7px;
        min-height: 40px;
        padding: .5rem .6rem;
        font: inherit;
        background: #fff;
    }

    .step-edit-field textarea {
        min-height: 84px;
        resize: vertical;
    }

    .step-edit-save-state {
        min-height: 18px;
        margin-top: .25rem;
        font-size: .78rem;
        color: rgba(15, 23, 42, .62);
    }

    .step-edit-save-state.ok {
        color: #15803d;
    }

    .step-edit-save-state.error {
        color: #b91c1c;
    }

    .step-edit-empty {
        padding: 18px;
        border: 1px dashed rgba(100, 116, 139, .45);
        border-radius: 8px;
        color: rgba(15, 23, 42, .7);
        background: #f8fafc;
    }

    @media (max-width: 900px) {
        .step-edit-dialog {
            max-height: 96vh;
        }

        .step-edit-progress-stack,
        .step-edit-grid,
        .step-edit-form-layout,
        .step-edit-fields {
            grid-template-columns: 1fr;
        }

        .step-edit-side {
            position: static;
        }
    }

    @media (max-width: 560px) {
        .step-edit-modal {
            padding: 0;
            place-items: stretch;
        }

        .step-edit-dialog {
            width: 100%;
            max-height: 100vh;
            border-radius: 0;
        }

        .step-edit-head {
            padding: 14px;
        }

        .step-edit-body {
            padding-left: 14px;
            padding-right: 14px;
        }
    }
</style>

<div id="step-edit-modal" class="step-edit-modal hidden" aria-hidden="true">
    <div class="step-edit-dialog" role="dialog" aria-modal="true" aria-labelledby="step-edit-title">
        <div class="step-edit-head">
            <div>
                <h2 id="step-edit-title">Operativo de relevamiento</h2>
                <p class="step-edit-subtitle" id="step-edit-subtitle">Selecciona un operativo abierto para empezar.</p>
            </div>
            <button type="button" class="btn-icon" onclick="StepEdit.close()" aria-label="Cerrar">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <div class="step-edit-body">
            <div id="step-edit-flowbar" class="step-edit-flowbar"></div>
            <div id="step-edit-progress" class="step-edit-progress-stack"></div>
            <div id="step-edit-content"></div>
        </div>
    </div>
</div>

<script>
    window.StepEdit = (() => {
        const API = <?= json_encode($stepEditBasePath . '/views/partials/relevamiento/step_edit/step_editController.php', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
        const state = {
            step: 'operativos',
            operativo: null,
            coop: null,
            productor: null,
            form: null,
            saveTimers: new Map()
        };

        const modal = () => document.getElementById('step-edit-modal');
        const content = () => document.getElementById('step-edit-content');
        const progress = () => document.getElementById('step-edit-progress');
        const flowbar = () => document.getElementById('step-edit-flowbar');
        const subtitle = () => document.getElementById('step-edit-subtitle');

        function escapeHtml(value) {
            return String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');
        }

        function pct(value) {
            const n = Number(value || 0);
            return Math.max(0, Math.min(100, n));
        }

        function cssEscape(value) {
            if (window.CSS && typeof window.CSS.escape === 'function') {
                return window.CSS.escape(value);
            }
            return String(value).replace(/["\\]/g, '\\$&');
        }

        function progressBar(avance, label) {
            const completitud = pct(avance?.completitud_pct);
            const actividad = pct(avance?.actividad_pct);
            return `
                <div class="step-edit-progress-card">
                    <div class="step-edit-list-title"><span>${escapeHtml(label)}</span><span>${completitud.toFixed(0)}%</span></div>
                    <div class="step-edit-muted">${Number(avance?.completos || 0)} de ${Number(avance?.esperados || 0)} campos completos</div>
                    <div class="step-edit-progress"><span style="width:${completitud}%"></span></div>
                    <div class="step-edit-muted" style="margin-top:.4rem;">Actividad: ${actividad.toFixed(0)}%</div>
                </div>
            `;
        }

        async function apiGet(action, params = {}) {
            const qs = new URLSearchParams({ action, ...params });
            const resp = await fetch(`${API}?${qs.toString()}`, { credentials: 'same-origin' });
            const data = await resp.json();
            if (!data.ok) throw new Error(data.error || 'Error de relevamiento');
            return data.data;
        }

        async function apiPost(payload) {
            const resp = await fetch(API, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await resp.json();
            if (!data.ok) throw new Error(data.error || 'Error de relevamiento');
            return data.data;
        }

        function setStep(step) {
            state.step = step;
            renderFlowbar();
        }

        function renderFlowbar() {
            const items = [];
            if (state.step !== 'operativos') {
                items.push(`<button type="button" class="btn-icon" data-step-edit-back title="Volver" aria-label="Volver"><span class="material-symbols-outlined">arrow_back</span></button>`);
            }
            if (state.operativo) items.push(`<span class="step-edit-current">${escapeHtml(state.operativo.nombre)}</span>`);
            if (state.coop) items.push(`<span class="step-edit-muted">/ ${escapeHtml(state.coop.nombre)}</span>`);
            if (state.productor) items.push(`<span class="step-edit-muted">/ ${escapeHtml(state.productor.nombre)}</span>`);
            flowbar().innerHTML = items.join('');
        }

        function renderLoading(text) {
            content().innerHTML = `<div class="step-edit-empty">${escapeHtml(text)}</div>`;
        }

        async function renderTopProgress() {
            if (!state.operativo) {
                progress().innerHTML = '';
                return null;
            }
            try {
                const data = await apiGet('avance', { operativo_id: state.operativo.id });
                if (state.coop) {
                    const refreshedCoop = (data.cooperativas || []).find((coop) => String(coop.id_real) === String(state.coop.id_real));
                    if (refreshedCoop) state.coop = refreshedCoop;
                }
                progress().innerHTML = progressBar(data.general, 'Operativo general');
                return data;
            } catch (e) {
                progress().innerHTML = '';
                return null;
            }
        }

        async function loadOperativos() {
            setStep('operativos');
            state.operativo = null;
            state.coop = null;
            state.productor = null;
            state.form = null;
            subtitle().textContent = 'Selecciona un operativo abierto para empezar.';
            progress().innerHTML = '';
            renderFlowbar();
            renderLoading('Cargando operativos abiertos...');

            const operativos = await apiGet('operativos');
            if (!operativos.length) {
                content().innerHTML = '<div class="step-edit-empty">No hay operativos abiertos disponibles.</div>';
                return;
            }

            content().innerHTML = `<div class="step-edit-grid">${operativos.map((op) => `
                <article class="step-edit-list-card" data-op-id="${Number(op.id)}">
                    <div class="step-edit-list-title">
                        <span>${escapeHtml(op.nombre)}</span>
                        <span class="step-edit-muted">${Number(op.campos_count || 0)} campos</span>
                    </div>
                    <div class="step-edit-muted">Inicio: ${escapeHtml(op.fecha_inicio)} · Cierre: ${escapeHtml(op.fecha_fin)}</div>
                </article>
            `).join('')}</div>`;

            content().querySelectorAll('[data-op-id]').forEach((card) => {
                card.addEventListener('click', () => {
                    const op = operativos.find((item) => String(item.id) === String(card.dataset.opId));
                    state.operativo = op;
                    loadCooperativas();
                });
            });
        }

        async function loadCooperativas() {
            if (!state.operativo) return loadOperativos();
            setStep('cooperativas');
            state.coop = null;
            state.productor = null;
            state.form = null;
            subtitle().textContent = state.operativo.nombre;
            renderFlowbar();
            renderLoading('Cargando cooperativas asociadas...');
            progress().innerHTML = '<div class="step-edit-empty">Calculando avance general...</div>';
            renderTopProgress();

            const coops = await apiGet('cooperativas', { operativo_id: state.operativo.id });
            if (!coops.length) {
                content().innerHTML = '<div class="step-edit-empty">No tenes cooperativas asociadas para este operativo.</div>';
                return;
            }

            content().innerHTML = `<div class="step-edit-grid">${coops.map((coop) => `
                <article class="step-edit-list-card" data-coop-id="${escapeHtml(coop.id_real)}">
                    <div class="step-edit-list-title">
                        <span>${escapeHtml(coop.nombre)}</span>
                        <span data-coop-pct="${escapeHtml(coop.id_real)}">--</span>
                    </div>
                    <div class="step-edit-muted">${Number(coop.productores_count || 0)} productores · ${escapeHtml(coop.cuit || 'Sin CUIT')}</div>
                    <div class="step-edit-progress"><span data-coop-bar="${escapeHtml(coop.id_real)}" style="width:0%"></span></div>
                </article>
            `).join('')}</div>`;

            content().querySelectorAll('[data-coop-id]').forEach((card) => {
                card.addEventListener('click', () => {
                    const coop = coops.find((item) => String(item.id_real) === String(card.dataset.coopId));
                    state.coop = coop;
                    loadProductores();
                });
            });
            hydrateCooperativasProgress(coops);
        }

        async function loadProductores() {
            if (!state.operativo) return loadOperativos();
            if (!state.coop) return loadCooperativas();
            setStep('productores');
            state.productor = null;
            state.form = null;
            subtitle().textContent = `${state.operativo.nombre} · ${state.coop.nombre}`;
            renderFlowbar();
            renderLoading('Cargando productores...');

            const productores = await apiGet('productores', { operativo_id: state.operativo.id, coop_id_real: state.coop.id_real });
            if (!productores.length) {
                content().innerHTML = '<div class="step-edit-empty">No hay productores activos en esta cooperativa.</div>';
                return;
            }

            content().innerHTML = `<div class="step-edit-grid">${productores.map((prod) => `
                <article class="step-edit-list-card" data-prod-id="${escapeHtml(prod.id_real)}">
                    <div class="step-edit-list-title">
                        <span>${escapeHtml(prod.nombre)}</span>
                        <span data-prod-pct="${escapeHtml(prod.id_real)}">--</span>
                    </div>
                    <div class="step-edit-muted">${escapeHtml(prod.id_real)} · ${escapeHtml(prod.cuit || 'Sin CUIT')}</div>
                    <div class="step-edit-progress"><span data-prod-bar="${escapeHtml(prod.id_real)}" style="width:0%"></span></div>
                    <div class="step-edit-muted" data-prod-pending="${escapeHtml(prod.id_real)}" style="margin-top:.4rem;">Calculando avance...</div>
                </article>
            `).join('')}</div>`;

            content().querySelectorAll('[data-prod-id]').forEach((card) => {
                card.addEventListener('click', () => {
                    const productor = productores.find((item) => String(item.id_real) === String(card.dataset.prodId));
                    state.productor = productor;
                    loadForm();
                });
            });
            hydrateProductoresProgress(productores);
        }

        async function hydrateCooperativasProgress(coops) {
            for (const coop of coops) {
                if (state.step !== 'cooperativas') return;
                try {
                    const avance = await apiGet('avance_cooperativa', {
                        operativo_id: state.operativo.id,
                        coop_id_real: coop.id_real
                    });
                    coop.avance = avance;
                    const id = cssEscape(String(coop.id_real));
                    const pctEl = content().querySelector(`[data-coop-pct="${id}"]`);
                    const barEl = content().querySelector(`[data-coop-bar="${id}"]`);
                    if (pctEl) pctEl.textContent = `${pct(avance.completitud_pct).toFixed(0)}%`;
                    if (barEl) barEl.style.width = `${pct(avance.completitud_pct)}%`;
                } catch (e) {
                    console.warn('[StepEdit] avance cooperativa', e);
                }
            }
        }

        async function hydrateProductoresProgress(productores) {
            const queue = [...productores];
            const workers = Array.from({ length: Math.min(3, queue.length) }, async () => {
                while (queue.length && state.step === 'productores') {
                    const prod = queue.shift();
                    try {
                        const avance = await apiGet('avance_productor', {
                            operativo_id: state.operativo.id,
                            productor_id_real: prod.id_real
                        });
                        prod.avance = avance;
                        const id = cssEscape(String(prod.id_real));
                        const pctEl = content().querySelector(`[data-prod-pct="${id}"]`);
                        const barEl = content().querySelector(`[data-prod-bar="${id}"]`);
                        const pendingEl = content().querySelector(`[data-prod-pending="${id}"]`);
                        if (pctEl) pctEl.textContent = `${pct(avance.completitud_pct).toFixed(0)}%`;
                        if (barEl) barEl.style.width = `${pct(avance.completitud_pct)}%`;
                        if (pendingEl) pendingEl.textContent = `Pendientes: ${Number(avance.pendientes || 0)}`;
                    } catch (e) {
                        console.warn('[StepEdit] avance productor', e);
                    }
                }
            });
            await Promise.all(workers);
        }

        function fieldsByScope(scope) {
            return (state.form?.campos || []).filter((campo) => campo.alcance === scope);
        }

        function valueFor(scope, entityId, key) {
            if (scope === 'productor') return state.form?.values?.productor?.[key] ?? '';
            return state.form?.values?.[scope]?.[entityId]?.[key] ?? '';
        }

        function fieldInput(campo, scope, entityId) {
            const key = `${scope}:${entityId || 'productor'}:${campo.key}`;
            const value = valueFor(scope, entityId, campo.key);
            const attrs = `data-step-field="1" data-scope="${escapeHtml(scope)}" data-entity-id="${escapeHtml(entityId || '')}" data-tabla="${escapeHtml(campo.tabla)}" data-campo="${escapeHtml(campo.campo)}" data-alcance="${escapeHtml(campo.alcance)}"`;
            let input = '';
            if (campo.input_type === 'textarea') {
                input = `<textarea ${attrs}>${escapeHtml(value)}</textarea>`;
            } else if (campo.input_type === 'select') {
                input = `<select ${attrs}>${(campo.options || []).map((opt) => `<option value="${escapeHtml(opt.value)}" ${String(value ?? '') === String(opt.value) ? 'selected' : ''}>${escapeHtml(opt.label)}</option>`).join('')}</select>`;
            } else {
                const type = campo.input_type === 'number' ? 'number" step="any' : campo.input_type;
                input = `<input type="${type}" value="${escapeHtml(value)}" ${attrs}>`;
            }
            return `
                <div class="step-edit-field">
                    <label>${escapeHtml(campo.etiqueta)}</label>
                    ${input}
                    <div class="step-edit-save-state" data-save-state="${escapeHtml(key)}"></div>
                </div>
            `;
        }

        function fieldGroup(title, fields, scope, entityId = '') {
            if (!fields.length) return '';
            return `
                <section class="step-edit-field-group">
                    <h3>${escapeHtml(title)}</h3>
                    <div class="step-edit-fields">
                        ${fields.map((campo) => fieldInput(campo, scope, entityId)).join('')}
                    </div>
                </section>
            `;
        }

        function isFilledValue(value) {
            return String(value ?? '').trim() !== '';
        }

        function computeFormProgressFromData() {
            if (!state.form) return { esperados: 0, completos: 0, auditados: 0, pendientes: 0, completitud_pct: 0, actividad_pct: 0 };
            const campos = state.form.campos || [];
            const fincas = state.form.fincas || [];
            const cuarteles = state.form.cuarteles || [];
            let esperados = 0;
            let completos = 0;

            campos.forEach((campo) => {
                if (campo.alcance === 'productor') {
                    esperados++;
                    if (isFilledValue(valueFor('productor', '', campo.key))) completos++;
                } else if (campo.alcance === 'finca') {
                    fincas.forEach((finca) => {
                        esperados++;
                        if (isFilledValue(valueFor('finca', finca.id, campo.key))) completos++;
                    });
                } else if (campo.alcance === 'cuartel') {
                    cuarteles.forEach((cuartel) => {
                        esperados++;
                        if (isFilledValue(valueFor('cuartel', cuartel.id, campo.key))) completos++;
                    });
                }
            });

            return {
                esperados,
                completos,
                auditados: 0,
                pendientes: Math.max(0, esperados - completos),
                completitud_pct: esperados > 0 ? (completos / esperados) * 100 : 0,
                actividad_pct: 0
            };
        }

        function computeFormProgressFromInputs() {
            const inputs = Array.from(content().querySelectorAll('[data-step-field]'));
            const esperados = inputs.length;
            const completos = inputs.filter((input) => isFilledValue(input.value)).length;
            return {
                esperados,
                completos,
                auditados: 0,
                pendientes: Math.max(0, esperados - completos),
                completitud_pct: esperados > 0 ? (completos / esperados) * 100 : 0,
                actividad_pct: 0
            };
        }

        function renderForm() {
            const productorFields = fieldsByScope('productor');
            const fincaFields = fieldsByScope('finca');
            const cuartelFields = fieldsByScope('cuartel');
            const fincas = state.form?.fincas || [];
            const cuarteles = state.form?.cuarteles || [];
            state.form.avance = computeFormProgressFromData();

            const formHtml = `
                ${fieldGroup('Datos del productor', productorFields, 'productor')}
                ${fincas.map((finca) => fieldGroup(`Finca ${finca.codigo_finca || finca.id} · ${finca.nombre_finca || ''}`, fincaFields, 'finca', finca.id)).join('')}
                ${cuarteles.map((cuartel) => fieldGroup(`Cuartel ${cuartel.codigo_cuartel || cuartel.id} · Finca ${cuartel.codigo_finca || ''}`, cuartelFields, 'cuartel', cuartel.id)).join('')}
            `;

            content().innerHTML = `
                <div class="step-edit-form-layout">
                    <aside class="step-edit-side">
                        ${progressBar(state.form.avance, 'Productor')}
                        ${state.coop?.avance ? progressBar(state.coop.avance, 'Cooperativa') : ''}
                    </aside>
                    <div>${formHtml || '<div class="step-edit-empty">Este operativo no tiene campos aplicables al productor seleccionado.</div>'}</div>
                </div>
            `;

            content().querySelectorAll('[data-step-field]').forEach((input) => {
                const handler = () => scheduleSave(input);
                input.addEventListener('change', handler);
                input.addEventListener('input', handler);
            });
        }

        async function loadForm() {
            if (!state.operativo) return loadOperativos();
            if (!state.productor) return loadProductores();
            setStep('edicion');
            subtitle().textContent = `${state.operativo.nombre} · ${state.productor.nombre}`;
            renderFlowbar();
            renderLoading('Cargando campos del operativo...');
            state.form = await apiGet('form', { operativo_id: state.operativo.id, productor_id_real: state.productor.id_real });
            renderForm();
        }

        function saveStateEl(input) {
            const key = `${input.dataset.scope}:${input.dataset.entityId || 'productor'}:${input.dataset.tabla}.${input.dataset.campo}`;
            return content().querySelector(`[data-save-state="${cssEscape(key)}"]`);
        }

        function scheduleSave(input) {
            const key = `${input.dataset.scope}:${input.dataset.entityId || 'productor'}:${input.dataset.tabla}.${input.dataset.campo}`;
            const el = saveStateEl(input);
            if (el) {
                el.className = 'step-edit-save-state';
                el.textContent = 'Guardando...';
            }
            clearTimeout(state.saveTimers.get(key));
            state.saveTimers.set(key, setTimeout(() => saveField(input), 900));
        }

        async function saveField(input) {
            const el = saveStateEl(input);
            try {
                await apiPost({
                    action: 'save_field',
                    operativo_id: state.operativo.id,
                    productor_id_real: state.productor.id_real,
                    tabla: input.dataset.tabla,
                    campo: input.dataset.campo,
                    alcance: input.dataset.alcance,
                    entity_id: input.dataset.entityId || 0,
                    value: input.value
                });
                state.form.avance = computeFormProgressFromInputs();
                if (el) {
                    el.className = 'step-edit-save-state ok';
                    el.textContent = 'Guardado';
                }
                const side = content().querySelector('.step-edit-side');
                if (side) {
                    side.innerHTML = `${progressBar(state.form.avance, 'Productor')}${state.coop?.avance ? progressBar(state.coop.avance, 'Cooperativa') : ''}`;
                }
            } catch (e) {
                if (el) {
                    el.className = 'step-edit-save-state error';
                    el.textContent = e.message;
                }
            }
        }

        function open() {
            modal().classList.remove('hidden');
            modal().setAttribute('aria-hidden', 'false');
            loadOperativos().catch((e) => {
                content().innerHTML = `<div class="step-edit-empty">${escapeHtml(e.message)}</div>`;
            });
        }

        function close() {
            modal().classList.add('hidden');
            modal().setAttribute('aria-hidden', 'true');
        }

        document.addEventListener('click', (ev) => {
            const back = ev.target.closest?.('[data-step-edit-back]');
            if (!back) return;
            if (state.step === 'edicion') {
                loadProductores();
            } else if (state.step === 'productores') {
                loadCooperativas();
            } else if (state.step === 'cooperativas') {
                loadOperativos();
            }
        });

        document.addEventListener('keydown', (ev) => {
            if (ev.key === 'Escape' && !modal().classList.contains('hidden')) {
                close();
            }
        });

        return { open, close };
    })();
</script>
