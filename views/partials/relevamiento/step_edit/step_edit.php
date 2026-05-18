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

    .step-edit-status-badge {
        display: inline-flex;
        align-items: center;
        width: fit-content;
        min-height: 26px;
        border-radius: 999px;
        padding: .22rem .6rem;
        font-size: .78rem;
        font-weight: 800;
        border: 1px solid rgba(100, 116, 139, .28);
        background: #f8fafc;
        color: #334155;
    }

    .step-edit-status-badge.is-completado {
        border-color: rgba(22, 163, 74, .32);
        background: #dcfce7;
        color: #166534;
    }

    .step-edit-status-badge.is-en-progreso {
        border-color: rgba(37, 99, 235, .28);
        background: #dbeafe;
        color: #1d4ed8;
    }

    .step-edit-status-actions {
        display: flex;
        flex-wrap: wrap;
        gap: .5rem;
        margin-top: .75rem;
    }

    .step-edit-modal .modal-content {
        max-width: 460px;
    }

    .step-edit-empty {
        padding: 18px;
        border: 1px dashed rgba(100, 116, 139, .45);
        border-radius: 8px;
        color: rgba(15, 23, 42, .7);
        background: #f8fafc;
    }

    .step-edit-loader {
        display: grid;
        gap: 1rem;
        padding: 22px;
        border: 1px solid rgba(37, 99, 235, .22);
        border-radius: 10px;
        background: #f8fafc;
    }

    .step-edit-loader-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        color: #0f172a;
        font-weight: 800;
    }

    .step-edit-spinner {
        width: 28px;
        height: 28px;
        border: 3px solid #dbeafe;
        border-top-color: #2563eb;
        border-radius: 999px;
        animation: stepEditSpin .8s linear infinite;
        flex: 0 0 auto;
    }

    .step-edit-loader-bar {
        height: 12px;
        overflow: hidden;
        border-radius: 999px;
        background: #dbeafe;
    }

    .step-edit-loader-bar > span {
        display: block;
        height: 100%;
        width: 0;
        border-radius: inherit;
        background: linear-gradient(90deg, #2563eb, #16a34a);
        transition: width .18s ease;
    }

    @keyframes stepEditSpin {
        to {
            transform: rotate(360deg);
        }
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
            <button type="button" class="btn-icon" onclick="StepEdit.requestClose()" aria-label="Cerrar">
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

<div id="step-edit-confirm-close-modal" class="step-edit-modal modal hidden" aria-hidden="true">
    <div class="modal-content" role="dialog" aria-modal="true" aria-labelledby="step-edit-confirm-close-title">
        <h3 id="step-edit-confirm-close-title">Cerrar carga</h3>
        <div class="modal-body">
            <p>Si cerras este modal, podrias perder el contexto de carga o tener que volver a cargar datos del operativo.</p>
        </div>
        <div class="form-buttons">
            <button type="button" class="btn btn-cancelar" data-step-edit-cancel-close>Cancelar</button>
            <button type="button" class="btn btn-aceptar" data-step-edit-confirm-close>Cerrar</button>
        </div>
    </div>
</div>

<div id="step-edit-productor-estado-modal" class="step-edit-modal modal hidden" aria-hidden="true">
    <div class="modal-content" role="dialog" aria-modal="true" aria-labelledby="step-edit-productor-estado-title">
        <h3 id="step-edit-productor-estado-title">Estado del productor</h3>
        <div class="modal-body">
            <p>Selecciona el estado en el que debe quedar este productor antes de volver.</p>
        </div>
        <div class="form-buttons">
            <button type="button" class="btn btn-cancelar" data-step-edit-state="en_progreso">En progreso</button>
            <button type="button" class="btn btn-aceptar" data-step-edit-state="completado">Completado</button>
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
            cache: null,
            loadingToken: 0,
            saveTimers: new Map(),
            formDirty: false,
            pendingBackAfterState: false
        };

        const modal = () => document.getElementById('step-edit-modal');
        const content = () => document.getElementById('step-edit-content');
        const progress = () => document.getElementById('step-edit-progress');
        const flowbar = () => document.getElementById('step-edit-flowbar');
        const subtitle = () => document.getElementById('step-edit-subtitle');
        const closeConfirmModal = () => document.getElementById('step-edit-confirm-close-modal');
        const productorEstadoModal = () => document.getElementById('step-edit-productor-estado-modal');

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
            const unidad = avance?.medicion === 'productores' ? 'productores completados' : 'campos completos';
            const progreso = Number(avance?.en_progreso ?? avance?.pendientes ?? 0);
            return `
                <div class="step-edit-progress-card">
                    <div class="step-edit-list-title"><span>${escapeHtml(label)}</span><span>${completitud.toFixed(0)}%</span></div>
                    <div class="step-edit-muted">${Number(avance?.completos || 0)} de ${Number(avance?.esperados || 0)} ${unidad}</div>
                    <div class="step-edit-progress"><span style="width:${completitud}%"></span></div>
                    <div class="step-edit-muted" style="margin-top:.4rem;">En progreso: ${progreso} · Actividad: ${actividad.toFixed(0)}%</div>
                </div>
            `;
        }

        function estadoLabel(estado) {
            return estado === 'completado' ? 'Completado' : 'En progreso';
        }

        function estadoBadge(estado) {
            const normalized = estado === 'completado' ? 'completado' : 'en_progreso';
            const className = normalized === 'completado' ? 'is-completado' : 'is-en-progreso';
            return `<span class="step-edit-status-badge ${className}">${estadoLabel(normalized)}</span>`;
        }

        function productorStatusPanel() {
            const estadoActual = state.form?.estado_relevamiento?.estado || state.productor?.estado_relevamiento || 'en_progreso';
            return `
                <div class="step-edit-progress-card">
                    <div class="step-edit-list-title"><span>Productor</span>${estadoBadge(estadoActual)}</div>
                    <div class="step-edit-muted" style="margin-top:.4rem;">Estado actual: ${estadoLabel(estadoActual)}</div>
                    <div class="step-edit-status-actions">
                        <button type="button" class="btn btn-cancelar" data-step-edit-save-state-button="en_progreso">En progreso</button>
                        <button type="button" class="btn btn-aceptar" data-step-edit-save-state-button="completado">Completado</button>
                    </div>
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
            if (state.step !== 'operativos' && state.step !== 'cargando') {
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

        function renderOperativoLoader(text, done = 0, total = 1) {
            const percent = total > 0 ? Math.round((done / total) * 100) : 0;
            progress().innerHTML = '';
            content().innerHTML = `
                <div class="step-edit-loader">
                    <div class="step-edit-loader-head">
                        <span>${escapeHtml(text)}</span>
                        <span>${pct(percent).toFixed(0)}%</span>
                    </div>
                    <div class="step-edit-loader-bar"><span style="width:${pct(percent)}%"></span></div>
                    <div class="step-edit-muted">Preparando cooperativas, productores y avances para navegar sin recargar.</div>
                    <div class="step-edit-spinner" aria-label="Cargando"></div>
                </div>
            `;
        }

        function emptyAdvance() {
            return { esperados: 0, completos: 0, auditados: 0, pendientes: 0, en_progreso: 0, completitud_pct: 0, actividad_pct: 0, medicion: 'productores' };
        }

        function sumAdvances(items) {
            const total = emptyAdvance();
            items.forEach((item) => {
                const avance = item?.avance || item || {};
                total.esperados += Number(avance.esperados || 0);
                total.completos += Number(avance.completos || 0);
                total.auditados += Number(avance.auditados || 0);
            });
            total.pendientes = Math.max(0, total.esperados - total.completos);
            total.en_progreso = total.pendientes;
            total.completitud_pct = total.esperados > 0 ? (total.completos / total.esperados) * 100 : 0;
            total.actividad_pct = total.esperados > 0 ? (total.auditados / total.esperados) * 100 : 0;
            total.medicion = 'productores';
            return total;
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
                progress().innerHTML = '';
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
            state.formDirty = false;
            state.cache = null;
            state.loadingToken++;
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
                    preloadOperativo(op).catch((e) => {
                        content().innerHTML = `<div class="step-edit-empty">${escapeHtml(e.message)}</div>`;
                    });
                });
            });
        }

        async function preloadOperativo(op) {
            if (!op) return;
            const token = ++state.loadingToken;
            state.operativo = op;
            state.coop = null;
            state.productor = null;
            state.form = null;
            state.formDirty = false;
            state.cache = { operativoId: Number(op.id), coops: [], productoresByCoop: {}, formsByProductor: {}, general: emptyAdvance() };
            setStep('cargando');
            subtitle().textContent = op.nombre;
            renderFlowbar();

            let done = 0;
            let total = 1;
            const update = (text) => {
                if (token !== state.loadingToken) return;
                renderOperativoLoader(text, done, total);
            };

            update('Cargando cooperativas del operativo...');
            const coops = await apiGet('cooperativas', { operativo_id: op.id });
            if (token !== state.loadingToken) return;
            state.cache.coops = coops;
            done++;
            total = Math.max(1, 1 + (coops.length * 2));
            update('Calculando avances de cooperativas...');

            const runLimited = async (items, limit, worker) => {
                const queue = [...items];
                const workers = Array.from({ length: Math.min(limit, queue.length) }, async () => {
                    while (queue.length && token === state.loadingToken) {
                        await worker(queue.shift());
                    }
                });
                await Promise.all(workers);
            };

            await runLimited(coops, 3, async (coop) => {
                let productores = [];
                try {
                    const result = await Promise.all([
                        apiGet('avance_cooperativa', { operativo_id: op.id, coop_id_real: coop.id_real }),
                        apiGet('productores', { operativo_id: op.id, coop_id_real: coop.id_real })
                    ]);
                    coop.avance = result[0];
                    productores = result[1];
                } catch (e) {
                    console.warn('[StepEdit] precarga cooperativa', e);
                    coop.avance = emptyAdvance();
                }
                state.cache.productoresByCoop[coop.id_real] = productores;
                done += 2;
                total += productores.length;
                update(`Cargando productores de ${coop.nombre}...`);
            });

            const allProductores = [];
            Object.values(state.cache.productoresByCoop).forEach((list) => {
                list.forEach((prod) => allProductores.push(prod));
            });

            update('Calculando avances de productores...');
            await runLimited(allProductores, 4, async (prod) => {
                try {
                    prod.avance = await apiGet('avance_productor', {
                        operativo_id: op.id,
                        productor_id_real: prod.id_real
                    });
                } catch (e) {
                    console.warn('[StepEdit] precarga productor', e);
                    prod.avance = emptyAdvance();
                }
                done++;
                update(`Calculando avance de ${prod.nombre || prod.id_real}...`);
            });

            if (token !== state.loadingToken) return;
            state.cache.general = sumAdvances(coops);
            done = total;
            update('Operativo listo.');
            setTimeout(() => {
                if (token === state.loadingToken) loadCooperativas();
            }, 180);
        }

        async function loadCooperativas() {
            if (!state.operativo) return loadOperativos();
            setStep('cooperativas');
            state.coop = null;
            state.productor = null;
            state.form = null;
            state.formDirty = false;
            subtitle().textContent = state.operativo.nombre;
            renderFlowbar();
            renderLoading('Cargando cooperativas asociadas...');

            const coops = state.cache?.coops || await apiGet('cooperativas', { operativo_id: state.operativo.id });
            if (!coops.length) {
                content().innerHTML = '<div class="step-edit-empty">No tenes cooperativas asociadas para este operativo.</div>';
                return;
            }
            progress().innerHTML = '';

            content().innerHTML = `<div class="step-edit-grid">${coops.map((coop) => `
                <article class="step-edit-list-card" data-coop-id="${escapeHtml(coop.id_real)}">
                    <div class="step-edit-list-title">
                        <span>${escapeHtml(coop.nombre)}</span>
                        <span>${pct(coop.avance?.completitud_pct).toFixed(0)}%</span>
                    </div>
                    <div class="step-edit-muted">${Number(coop.productores_count || 0)} productores · ${Number(coop.avance?.completos || 0)} completados · ${Number(coop.avance?.en_progreso ?? coop.avance?.pendientes ?? 0)} en progreso</div>
                    <div class="step-edit-muted">${escapeHtml(coop.cuit || 'Sin CUIT')}</div>
                    <div class="step-edit-progress"><span style="width:${pct(coop.avance?.completitud_pct)}%"></span></div>
                </article>
            `).join('')}</div>`;

            content().querySelectorAll('[data-coop-id]').forEach((card) => {
                card.addEventListener('click', () => {
                    const coop = coops.find((item) => String(item.id_real) === String(card.dataset.coopId));
                    state.coop = coop;
                    loadProductores();
                });
            });
        }

        async function loadProductores() {
            if (!state.operativo) return loadOperativos();
            if (!state.coop) return loadCooperativas();
            setStep('productores');
            state.productor = null;
            state.form = null;
            state.formDirty = false;
            subtitle().textContent = `${state.operativo.nombre} · ${state.coop.nombre}`;
            renderFlowbar();
            renderLoading('Cargando productores...');

            const productores = state.cache?.productoresByCoop?.[state.coop.id_real] || await apiGet('productores', { operativo_id: state.operativo.id, coop_id_real: state.coop.id_real });
            if (!productores.length) {
                content().innerHTML = '<div class="step-edit-empty">No hay productores activos en esta cooperativa.</div>';
                return;
            }

            content().innerHTML = `<div class="step-edit-grid">${productores.map((prod) => `
                <article class="step-edit-list-card" data-prod-id="${escapeHtml(prod.id_real)}">
                    <div class="step-edit-list-title">
                        <span>${escapeHtml(prod.nombre)}</span>
                        ${estadoBadge(prod.estado_relevamiento)}
                    </div>
                    <div class="step-edit-muted">${escapeHtml(prod.id_real)} · ${escapeHtml(prod.cuit || 'Sin CUIT')}</div>
                    <div class="step-edit-progress"><span style="width:${pct(prod.avance?.completitud_pct)}%"></span></div>
                    <div class="step-edit-muted" style="margin-top:.4rem;">Estado: ${escapeHtml(prod.estado_relevamiento_label || estadoLabel(prod.estado_relevamiento))}</div>
                </article>
            `).join('')}</div>`;

            content().querySelectorAll('[data-prod-id]').forEach((card) => {
                card.addEventListener('click', () => {
                    const productor = productores.find((item) => String(item.id_real) === String(card.dataset.prodId));
                    state.productor = productor;
                    loadForm();
                });
            });
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
                        if (pendingEl) pendingEl.textContent = `En progreso: ${Number(avance.en_progreso || avance.pendientes || 0)}`;
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
                        ${productorStatusPanel()}
                        ${progressBar(state.form.avance, 'Campos del formulario')}
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
            const productorId = String(state.productor.id_real);
            if (state.cache?.formsByProductor?.[productorId]) {
                state.form = state.cache.formsByProductor[productorId];
            } else {
                state.form = await apiGet('form', { operativo_id: state.operativo.id, productor_id_real: state.productor.id_real });
                if (state.cache?.formsByProductor) {
                    state.cache.formsByProductor[productorId] = state.form;
                }
            }
            state.formDirty = false;
            renderForm();
        }

        function saveStateEl(input) {
            const key = `${input.dataset.scope}:${input.dataset.entityId || 'productor'}:${input.dataset.tabla}.${input.dataset.campo}`;
            return content().querySelector(`[data-save-state="${cssEscape(key)}"]`);
        }

        function updateFormCachedValue(input) {
            if (!state.form) return;
            const key = `${input.dataset.tabla}.${input.dataset.campo}`;
            const scope = input.dataset.scope;
            const entityId = input.dataset.entityId || '';

            if (scope === 'productor') {
                state.form.values.productor = state.form.values.productor || {};
                state.form.values.productor[key] = input.value;
                return;
            }

            state.form.values[scope] = state.form.values[scope] || {};
            state.form.values[scope][entityId] = state.form.values[scope][entityId] || {};
            state.form.values[scope][entityId][key] = input.value;
        }

        function scheduleSave(input) {
            const key = `${input.dataset.scope}:${input.dataset.entityId || 'productor'}:${input.dataset.tabla}.${input.dataset.campo}`;
            const el = saveStateEl(input);
            if (el) {
                el.className = 'step-edit-save-state';
                el.textContent = 'Guardando...';
            }
            state.formDirty = true;
            clearTimeout(state.saveTimers.get(key));
            state.saveTimers.set(key, setTimeout(() => saveField(input), 900));
        }

        async function flushPendingSaves() {
            const inputs = Array.from(content().querySelectorAll('[data-step-field]'));
            state.saveTimers.forEach((timer) => clearTimeout(timer));
            state.saveTimers.clear();
            await Promise.all(inputs.map((input) => saveField(input)));
        }

        async function saveProductorEstado(estado, options = {}) {
            if (!state.operativo || !state.productor) return null;
            const data = await apiPost({
                action: 'save_productor_estado',
                operativo_id: state.operativo.id,
                productor_id_real: state.productor.id_real,
                estado
            });

            state.productor.estado_relevamiento = data.estado;
            state.productor.estado_relevamiento_label = data.label;
            state.productor.avance = data.avance;
            if (state.form) {
                state.form.estado_relevamiento = { estado: data.estado, label: data.label };
            }

            if (state.cache?.productoresByCoop?.[state.coop?.id_real]) {
                const cached = state.cache.productoresByCoop[state.coop.id_real].find((prod) => String(prod.id_real) === String(state.productor.id_real));
                if (cached) {
                    cached.estado_relevamiento = data.estado;
                    cached.estado_relevamiento_label = data.label;
                    cached.avance = data.avance;
                }
            }

            if (state.coop) {
                state.coop.avance = await apiGet('avance_cooperativa', {
                    operativo_id: state.operativo.id,
                    coop_id_real: state.coop.id_real
                });
            }

            if (options.stayOnForm) {
                renderForm();
            }

            return data;
        }

        function showProductorEstadoModal() {
            const modalEl = productorEstadoModal();
            modalEl.classList.remove('hidden');
            modalEl.setAttribute('aria-hidden', 'false');
        }

        function hideProductorEstadoModal() {
            const modalEl = productorEstadoModal();
            modalEl.classList.add('hidden');
            modalEl.setAttribute('aria-hidden', 'true');
        }

        async function saveEstadoAndBack(estado) {
            try {
                await flushPendingSaves();
                await saveProductorEstado(estado);
                hideProductorEstadoModal();
                state.formDirty = false;
                await loadProductores();
            } catch (e) {
                const body = productorEstadoModal().querySelector('.modal-body');
                if (body) body.innerHTML = `<p>${escapeHtml(e.message)}</p>`;
            }
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
                updateFormCachedValue(input);
                state.form.avance = computeFormProgressFromInputs();
                if (el) {
                    el.className = 'step-edit-save-state ok';
                    el.textContent = 'Guardado';
                }
                const side = content().querySelector('.step-edit-side');
                if (side) {
                    side.innerHTML = `${productorStatusPanel()}${progressBar(state.form.avance, 'Campos del formulario')}${state.coop?.avance ? progressBar(state.coop.avance, 'Cooperativa') : ''}`;
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
            state.loadingToken++;
            closeConfirmModal().classList.add('hidden');
            productorEstadoModal().classList.add('hidden');
            modal().classList.add('hidden');
            modal().setAttribute('aria-hidden', 'true');
        }

        function requestClose() {
            const confirmModal = closeConfirmModal();
            confirmModal.classList.remove('hidden');
            confirmModal.setAttribute('aria-hidden', 'false');
        }

        document.addEventListener('click', (ev) => {
            const back = ev.target.closest?.('[data-step-edit-back]');
            if (!back) return;
            if (state.step === 'edicion') {
                if (state.formDirty) {
                    showProductorEstadoModal();
                    return;
                }
                loadProductores();
            } else if (state.step === 'productores') {
                loadCooperativas();
            } else if (state.step === 'cooperativas') {
                loadOperativos();
            }
        });

        document.addEventListener('keydown', (ev) => {
            if (ev.key === 'Escape' && !modal().classList.contains('hidden')) {
                requestClose();
            }
        });

        document.addEventListener('click', (ev) => {
            if (ev.target.closest?.('[data-step-edit-cancel-close]')) {
                closeConfirmModal().classList.add('hidden');
                closeConfirmModal().setAttribute('aria-hidden', 'true');
                return;
            }

            if (ev.target.closest?.('[data-step-edit-confirm-close]')) {
                close();
                return;
            }

            const stateButton = ev.target.closest?.('[data-step-edit-state]');
            if (stateButton) {
                saveEstadoAndBack(stateButton.dataset.stepEditState);
                return;
            }

            const saveStateButton = ev.target.closest?.('[data-step-edit-save-state-button]');
            if (saveStateButton) {
                saveProductorEstado(saveStateButton.dataset.stepEditSaveStateButton, { stayOnForm: true }).catch((e) => {
                    content().querySelector('.step-edit-side')?.insertAdjacentHTML('afterbegin', `<div class="step-edit-save-state error">${escapeHtml(e.message)}</div>`);
                });
            }
        });

        return { open, close, requestClose };
    })();
</script>
