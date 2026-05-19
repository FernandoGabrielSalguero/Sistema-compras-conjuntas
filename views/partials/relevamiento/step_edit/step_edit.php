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

    .step-edit-head-main {
        min-width: 0;
    }

    .step-edit-head-title-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: .55rem .75rem;
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

    .step-edit-head-flowbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: .45rem;
        min-width: 0;
    }

    .step-edit-head-flowbar:empty {
        display: none;
    }

    .step-edit-head-flowbar .step-edit-current,
    .step-edit-head-flowbar .step-edit-muted {
        max-width: 220px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
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

    .step-edit-create-card {
        display: grid;
        place-items: center;
        min-height: 132px;
        border-style: dashed;
        background: #f8fafc;
        text-align: center;
    }

    .step-edit-create-card .material-symbols-outlined {
        font-size: 32px;
        color: #2563eb;
    }

    .step-edit-list-title {
        display: flex;
        justify-content: space-between;
        gap: .75rem;
        font-weight: 800;
        color: #0f172a;
    }

    .step-edit-productor-title {
        display: block;
        min-width: 0;
        overflow-wrap: anywhere;
        font-size: 1.02rem;
        line-height: 1.25;
    }

    .step-edit-productor-meta {
        margin-top: .35rem;
        font-size: .84rem;
        color: rgba(15, 23, 42, .62);
    }

    .step-edit-card-actions,
    .step-edit-accordion-actions,
    .step-edit-structure-actions {
        display: flex;
        flex-wrap: wrap;
        gap: .5rem;
        align-items: center;
    }

    .step-edit-card-actions {
        justify-content: flex-end;
        margin-top: .65rem;
    }

    .step-edit-structure-actions {
        justify-content: flex-end;
        margin: 0 0 .75rem;
    }

    .step-edit-action-button {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
    }

    .step-edit-danger {
        color: #b91c1c;
    }

    .step-edit-danger:hover {
        color: #991b1b;
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

    .step-edit-search-tools {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        margin-bottom: 1rem;
    }

    .step-edit-search-wrap {
        position: relative;
        flex: 1 1 280px;
        max-width: 460px;
    }

    .step-edit-search-wrap .material-symbols-outlined {
        position: absolute;
        left: .7rem;
        top: 50%;
        transform: translateY(-50%);
        font-size: 20px;
        color: rgba(15, 23, 42, .5);
        pointer-events: none;
    }

    .step-edit-search-input {
        width: 100%;
        min-height: 40px;
        border: 1px solid rgba(100, 116, 139, .4);
        border-radius: 7px;
        padding: .5rem .75rem .5rem 2.25rem;
        font: inherit;
        background: #fff;
    }

    .step-edit-search-status {
        min-height: 20px;
        color: rgba(15, 23, 42, .66);
        font-size: .86rem;
    }

    .step-edit-accordion-stack {
        display: grid;
        gap: .75rem;
    }

    .step-edit-accordion {
        border: 1px solid rgba(148, 163, 184, .42);
        border-radius: 8px;
        background: #fff;
        overflow: hidden;
    }

    .step-edit-accordion summary {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        padding: .85rem 1rem;
        cursor: pointer;
        font-weight: 800;
        color: #0f172a;
        list-style: none;
        background: #f8fafc;
    }

    .step-edit-accordion summary::-webkit-details-marker {
        display: none;
    }

    .step-edit-accordion summary::after {
        content: 'expand_more';
        font-family: 'Material Symbols Outlined';
        font-weight: normal;
        font-style: normal;
        font-size: 22px;
        line-height: 1;
        transition: transform .16s ease;
    }

    .step-edit-accordion[open] > summary::after {
        transform: rotate(180deg);
    }

    .step-edit-accordion-body {
        display: grid;
        gap: .75rem;
        padding: .9rem;
    }

    .step-edit-cuarteles-stack {
        display: grid;
        gap: .65rem;
    }

    .step-edit-cuartel-accordion {
        border-color: rgba(100, 116, 139, .28);
    }

    .step-edit-cuartel-accordion summary {
        padding: .7rem .85rem;
        background: #fff;
        border-bottom: 1px solid rgba(148, 163, 184, .22);
    }

    .step-edit-accordion-title {
        min-width: 0;
        overflow-wrap: anywhere;
    }

    .step-edit-modal-error {
        min-height: 18px;
        margin-top: .5rem;
        color: #b91c1c;
        font-size: .84rem;
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

        .step-edit-head-title-row {
            align-items: flex-start;
            flex-direction: column;
        }

        .step-edit-head-flowbar .step-edit-current,
        .step-edit-head-flowbar .step-edit-muted {
            max-width: min(72vw, 320px);
        }

        .step-edit-search-wrap {
            max-width: none;
            flex-basis: 100%;
        }
    }
</style>

<div id="step-edit-modal" class="step-edit-modal hidden" aria-hidden="true">
    <div class="step-edit-dialog" role="dialog" aria-modal="true" aria-labelledby="step-edit-title">
        <div class="step-edit-head">
            <div class="step-edit-head-main">
                <div class="step-edit-head-title-row">
                    <div id="step-edit-head-flowbar" class="step-edit-head-flowbar"></div>
                    <h2 id="step-edit-title">Operativo de relevamiento</h2>
                </div>
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

<div id="step-edit-confirm-action-modal" class="step-edit-modal modal hidden" aria-hidden="true">
    <div class="modal-content" role="dialog" aria-modal="true" aria-labelledby="step-edit-confirm-action-title">
        <h3 id="step-edit-confirm-action-title">Confirmar accion</h3>
        <div class="modal-body">
            <p id="step-edit-confirm-action-message"></p>
            <div class="step-edit-modal-error" data-step-edit-action-error></div>
        </div>
        <div class="form-buttons">
            <button type="button" class="btn btn-cancelar" data-step-edit-action-cancel>Cancelar</button>
            <button type="button" class="btn btn-aceptar" data-step-edit-action-confirm>Archivar</button>
        </div>
    </div>
</div>

<div id="step-edit-create-productor-modal" class="step-edit-modal modal hidden" aria-hidden="true">
    <div class="modal-content" role="dialog" aria-modal="true" aria-labelledby="step-edit-create-productor-title">
        <h3 id="step-edit-create-productor-title">Nuevo productor</h3>
        <div class="modal-body">
            <div class="step-edit-fields">
                <div class="step-edit-field">
                    <label>Nombre del productor</label>
                    <input type="text" data-step-edit-new-productor-nombre>
                </div>
                <div class="step-edit-field">
                    <label>CUIT</label>
                    <input type="text" data-step-edit-new-productor-cuit inputmode="numeric">
                </div>
            </div>
            <div class="step-edit-modal-error" data-step-edit-create-productor-error></div>
        </div>
        <div class="form-buttons">
            <button type="button" class="btn btn-cancelar" data-step-edit-create-productor-cancel>Cancelar</button>
            <button type="button" class="btn btn-aceptar" data-step-edit-create-productor-confirm>Crear</button>
        </div>
    </div>
</div>

<div id="step-edit-create-finca-modal" class="step-edit-modal modal hidden" aria-hidden="true">
    <div class="modal-content" role="dialog" aria-modal="true" aria-labelledby="step-edit-create-finca-title">
        <h3 id="step-edit-create-finca-title">Añadir finca</h3>
        <div class="modal-body">
            <div class="step-edit-fields">
                <div class="step-edit-field">
                    <label>Codigo de finca</label>
                    <input type="text" data-step-edit-new-finca-codigo>
                </div>
                <div class="step-edit-field">
                    <label>Nombre de finca</label>
                    <input type="text" data-step-edit-new-finca-nombre>
                </div>
            </div>
            <div class="step-edit-modal-error" data-step-edit-create-finca-error></div>
        </div>
        <div class="form-buttons">
            <button type="button" class="btn btn-cancelar" data-step-edit-create-finca-cancel>Cancelar</button>
            <button type="button" class="btn btn-aceptar" data-step-edit-create-finca-confirm>Añadir</button>
        </div>
    </div>
</div>

<div id="step-edit-create-cuartel-modal" class="step-edit-modal modal hidden" aria-hidden="true">
    <div class="modal-content" role="dialog" aria-modal="true" aria-labelledby="step-edit-create-cuartel-title">
        <h3 id="step-edit-create-cuartel-title">Añadir cuartel</h3>
        <div class="modal-body">
            <div class="step-edit-fields">
                <div class="step-edit-field">
                    <label>Variedad</label>
                    <select data-step-edit-new-cuartel-variedad></select>
                </div>
                <div class="step-edit-field">
                    <label>Sistema de conduccion</label>
                    <input type="text" data-step-edit-new-cuartel-sistema>
                </div>
                <div class="step-edit-field">
                    <label>Superficie ha</label>
                    <input type="number" step="any" data-step-edit-new-cuartel-superficie>
                </div>
            </div>
            <div class="step-edit-modal-error" data-step-edit-create-cuartel-error></div>
        </div>
        <div class="form-buttons">
            <button type="button" class="btn btn-cancelar" data-step-edit-create-cuartel-cancel>Cancelar</button>
            <button type="button" class="btn btn-aceptar" data-step-edit-create-cuartel-confirm>Añadir</button>
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
            pendingBackAfterState: false,
            pendingConfirmAction: null,
            pendingCuartelFincaId: null,
            productorSearchTimer: null,
            productorSearchToken: 0,
            productorSearchQuery: '',
            variedades: null
        };

        const modal = () => document.getElementById('step-edit-modal');
        const content = () => document.getElementById('step-edit-content');
        const progress = () => document.getElementById('step-edit-progress');
        const flowbar = () => document.getElementById('step-edit-flowbar');
        const headerFlowbar = () => document.getElementById('step-edit-head-flowbar');
        const subtitle = () => document.getElementById('step-edit-subtitle');
        const closeConfirmModal = () => document.getElementById('step-edit-confirm-close-modal');
        const productorEstadoModal = () => document.getElementById('step-edit-productor-estado-modal');
        const confirmActionModal = () => document.getElementById('step-edit-confirm-action-modal');
        const createProductorModal = () => document.getElementById('step-edit-create-productor-modal');
        const createFincaModal = () => document.getElementById('step-edit-create-finca-modal');
        const createCuartelModal = () => document.getElementById('step-edit-create-cuartel-modal');

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

        function showModal(modalEl) {
            modalEl.classList.remove('hidden');
            modalEl.setAttribute('aria-hidden', 'false');
        }

        function hideModal(modalEl) {
            modalEl.classList.add('hidden');
            modalEl.setAttribute('aria-hidden', 'true');
        }

        function setModalError(modalEl, selector, message = '') {
            const el = modalEl.querySelector(selector);
            if (el) el.textContent = message;
        }

        function showConfirmAction({ title, message, confirmLabel = 'Archivar', onConfirm }) {
            state.pendingConfirmAction = onConfirm;
            const modalEl = confirmActionModal();
            modalEl.querySelector('#step-edit-confirm-action-title').textContent = title;
            modalEl.querySelector('#step-edit-confirm-action-message').textContent = message;
            modalEl.querySelector('[data-step-edit-action-confirm]').textContent = confirmLabel;
            setModalError(modalEl, '[data-step-edit-action-error]', '');
            showModal(modalEl);
        }

        async function refreshCoopAndProducts() {
            if (!state.operativo || !state.coop) return;
            const [avance, productores, coops] = await Promise.all([
                apiGet('avance_cooperativa', { operativo_id: state.operativo.id, coop_id_real: state.coop.id_real }),
                apiGet('productores', { operativo_id: state.operativo.id, coop_id_real: state.coop.id_real }),
                apiGet('cooperativas', { operativo_id: state.operativo.id })
            ]);
            state.coop.avance = avance;
            if (state.cache?.productoresByCoop) {
                state.cache.productoresByCoop[state.coop.id_real] = productores;
            }
            if (state.cache) {
                state.cache.coops = coops;
                const refreshedCoop = coops.find((coop) => String(coop.id_real) === String(state.coop.id_real));
                if (refreshedCoop) state.coop = refreshedCoop;
            }
        }

        async function refreshCurrentForm() {
            if (!state.operativo || !state.productor) return;
            const productorId = String(state.productor.id_real);
            if (state.cache?.formsByProductor) {
                delete state.cache.formsByProductor[productorId];
            }
            state.form = await apiGet('form', { operativo_id: state.operativo.id, productor_id_real: state.productor.id_real });
            if (state.cache?.formsByProductor) {
                state.cache.formsByProductor[productorId] = state.form;
            }
            state.formDirty = false;
            renderForm();
        }

        function setStep(step) {
            state.step = step;
            renderFlowbar();
        }

        function navigationItems(compact = false) {
            const items = [];
            if (state.step !== 'operativos' && state.step !== 'cargando') {
                items.push(`<button type="button" class="btn-icon" data-step-edit-back title="Volver" aria-label="Volver"><span class="material-symbols-outlined">arrow_back</span></button>`);
            }
            if (state.operativo) items.push(`<span class="step-edit-current">${escapeHtml(state.operativo.nombre)}</span>`);
            if (state.coop) items.push(`<span class="step-edit-muted">/ ${escapeHtml(state.coop.nombre)}</span>`);
            if (!compact && state.productor) items.push(`<span class="step-edit-muted">/ ${escapeHtml(state.productor.nombre)}</span>`);
            return items;
        }

        function renderFlowbar() {
            const items = navigationItems(false);
            flowbar().innerHTML = items.join('');
            headerFlowbar().innerHTML = navigationItems(true).join('');
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

            renderOperativoLoader('Cargando cooperativas y avances...', 0, 1);
            const coops = await apiGet('cooperativas', { operativo_id: op.id });
            if (token !== state.loadingToken) return;
            state.cache.coops = coops;
            state.cache.general = sumAdvances(coops);
            renderOperativoLoader('Operativo listo.', 1, 1);
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
            state.productorSearchQuery = '';
            clearTimeout(state.productorSearchTimer);
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
            state.productorSearchQuery = '';
            clearTimeout(state.productorSearchTimer);
            subtitle().textContent = `${state.operativo.nombre} · ${state.coop.nombre}`;
            renderFlowbar();
            renderLoading('Cargando productores...');

            const productores = state.cache?.productoresByCoop?.[state.coop.id_real] || await apiGet('productores', { operativo_id: state.operativo.id, coop_id_real: state.coop.id_real });
            if (state.cache?.productoresByCoop && !state.cache.productoresByCoop[state.coop.id_real]) {
                state.cache.productoresByCoop[state.coop.id_real] = productores;
            }
            renderProductoresList(productores);
            return;
            content().innerHTML = `<div class="step-edit-grid">
                <article class="step-edit-list-card step-edit-create-card" data-step-edit-create-productor>
                    <div>
                        <span class="material-symbols-outlined" aria-hidden="true">person_add</span>
                        <div class="step-edit-productor-title">Nuevo productor</div>
                        <div class="step-edit-productor-meta">Cargar nuevo productor</div>
                    </div>
                </article>
                ${productores.map((prod) => `
                <article class="step-edit-list-card" data-prod-id="${escapeHtml(prod.id_real)}">
                    <div class="step-edit-list-title">
                        <span class="step-edit-productor-title">${escapeHtml(prod.nombre)}</span>
                        ${estadoBadge(prod.estado_relevamiento)}
                    </div>
                    <div class="step-edit-productor-meta">ID: ${escapeHtml(prod.id_real)} · CUIT: ${escapeHtml(prod.cuit || 'Sin CUIT')}</div>
                    <div class="step-edit-progress"><span style="width:${pct(prod.avance?.completitud_pct)}%"></span></div>
                    <div class="step-edit-muted" style="margin-top:.4rem;">Estado: ${escapeHtml(prod.estado_relevamiento_label || estadoLabel(prod.estado_relevamiento))}</div>
                    <div class="step-edit-card-actions">
                        <button type="button" class="btn-icon step-edit-danger" data-step-edit-archive-productor="${escapeHtml(prod.id_real)}" title="Archivar productor" aria-label="Archivar productor">
                            <span class="material-symbols-outlined">archive</span>
                        </button>
                    </div>
                </article>
            `).join('')}</div>`;

            content().querySelectorAll('[data-prod-id]').forEach((card) => {
                card.addEventListener('click', (ev) => {
                    if (ev.target.closest('button')) return;
                    const productor = productores.find((item) => String(item.id_real) === String(card.dataset.prodId));
                    state.productor = productor;
                    loadForm();
                });
            });
        }

        function productorCard(prod) {
            return `
                <article class="step-edit-list-card" data-prod-id="${escapeHtml(prod.id_real)}">
                    <div class="step-edit-list-title">
                        <span class="step-edit-productor-title">${escapeHtml(prod.nombre)}</span>
                        ${estadoBadge(prod.estado_relevamiento)}
                    </div>
                    <div class="step-edit-productor-meta">ID: ${escapeHtml(prod.id_real)} - CUIT: ${escapeHtml(prod.cuit || 'Sin CUIT')}</div>
                    <div class="step-edit-progress"><span style="width:${pct(prod.avance?.completitud_pct)}%"></span></div>
                    <div class="step-edit-muted" style="margin-top:.4rem;">Estado: ${escapeHtml(prod.estado_relevamiento_label || estadoLabel(prod.estado_relevamiento))}</div>
                    <div class="step-edit-card-actions">
                        <button type="button" class="btn-icon step-edit-danger" data-step-edit-archive-productor="${escapeHtml(prod.id_real)}" title="Archivar productor" aria-label="Archivar productor">
                            <span class="material-symbols-outlined">archive</span>
                        </button>
                    </div>
                </article>
            `;
        }

        function renderProductoresList(productores, options = {}) {
            const query = options.query ?? state.productorSearchQuery ?? '';
            const status = options.status ?? '';
            const noResults = query.length >= 3 && productores.length === 0
                ? '<div class="step-edit-empty">No se encontraron productores para la busqueda ingresada.</div>'
                : '';

            content().innerHTML = `
                <div class="step-edit-search-tools">
                    <div class="step-edit-search-wrap">
                        <span class="material-symbols-outlined" aria-hidden="true">search</span>
                        <input type="search" class="step-edit-search-input" data-step-edit-productor-search value="${escapeHtml(query)}" placeholder="Buscar productor por nombre o CUIT..." autocomplete="off">
                    </div>
                    <div class="step-edit-search-status" data-step-edit-productor-search-status>${escapeHtml(status)}</div>
                </div>
                <div class="step-edit-grid">
                    <article class="step-edit-list-card step-edit-create-card" data-step-edit-create-productor>
                        <div>
                            <span class="material-symbols-outlined" aria-hidden="true">person_add</span>
                            <div class="step-edit-productor-title">Nuevo productor</div>
                            <div class="step-edit-productor-meta">Cargar nuevo productor</div>
                        </div>
                    </article>
                    ${productores.map((prod) => productorCard(prod)).join('')}
                </div>
                ${noResults}
            `;

            const searchInput = content().querySelector('[data-step-edit-productor-search]');
            if (searchInput) {
                searchInput.addEventListener('input', handleProductorSearchInput);
                if (options.keepFocus) {
                    searchInput.focus();
                    searchInput.setSelectionRange(searchInput.value.length, searchInput.value.length);
                }
            }

            content().querySelectorAll('[data-prod-id]').forEach((card) => {
                card.addEventListener('click', (ev) => {
                    if (ev.target.closest('button')) return;
                    const productor = productores.find((item) => String(item.id_real) === String(card.dataset.prodId));
                    state.productor = productor;
                    loadForm();
                });
            });
        }

        function setProductorSearchStatus(text) {
            const el = content().querySelector('[data-step-edit-productor-search-status]');
            if (el) el.textContent = text;
        }

        async function buscarProductores(query, token) {
            if (!state.operativo || !state.coop || state.step !== 'productores') return;
            setProductorSearchStatus('Buscando...');
            try {
                const productores = await apiGet('productores', {
                    operativo_id: state.operativo.id,
                    coop_id_real: state.coop.id_real,
                    q: query
                });
                if (token !== state.productorSearchToken || state.step !== 'productores') return;
                renderProductoresList(productores, { query, keepFocus: true });
            } catch (e) {
                if (token !== state.productorSearchToken || state.step !== 'productores') return;
                renderProductoresList([], {
                    query,
                    status: 'No se pudo realizar la busqueda. Intentalo nuevamente.',
                    keepFocus: true
                });
            }
        }

        function handleProductorSearchInput(ev) {
            const query = String(ev.target.value || '').trim();
            state.productorSearchQuery = query;
            clearTimeout(state.productorSearchTimer);
            const token = ++state.productorSearchToken;

            if (query.length === 0) {
                const base = state.cache?.productoresByCoop?.[state.coop?.id_real] || [];
                renderProductoresList(base, { query: '', keepFocus: true });
                return;
            }

            if (query.length < 3) {
                setProductorSearchStatus('Ingresa al menos 3 caracteres para buscar.');
                return;
            }

            state.productorSearchTimer = setTimeout(() => {
                buscarProductores(query, token);
            }, 400);
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

        function fincaTitle(finca) {
            const nombre = String(finca?.nombre_finca ?? '').trim();
            return nombre || 'Finca sin nombre';
        }

        function cuartelTitle(cuartel) {
            const variedadRaw = String(
                cuartel?.variedad_display ??
                cuartel?.nombre_variedad ??
                ''
            ).trim();
            const variedad = variedadRaw || (String(cuartel?.variedad ?? '').trim() ? 'Variedad sin identificar' : '');
            const sistema = String(cuartel?.sistema_conduccion ?? '').trim();
            const parts = [variedad, sistema].filter(Boolean);
            return parts.length ? parts.join(' · ') : 'Cuartel sin identificar';
        }

        function cuartelesForFinca(finca, cuarteles) {
            const fincaId = String(finca?.id ?? '');
            const codigoFinca = String(finca?.codigo_finca ?? '').trim();
            return cuarteles.filter((cuartel) => {
                const cuartelFincaId = String(cuartel?.finca_id ?? '');
                const cuartelCodigoFinca = String(cuartel?.codigo_finca ?? '').trim();
                return (fincaId && cuartelFincaId === fincaId) ||
                    (!cuartelFincaId && codigoFinca && cuartelCodigoFinca === codigoFinca);
            });
        }

        function renderCuartelAccordion(cuartel, cuartelFields) {
            return `
                <details class="step-edit-accordion step-edit-cuartel-accordion">
                    <summary>
                        <span class="step-edit-accordion-title">${escapeHtml(cuartelTitle(cuartel))}</span>
                        <span class="step-edit-accordion-actions">
                            <button type="button" class="btn-icon step-edit-danger" data-step-edit-archive-cuartel="${Number(cuartel.id)}" title="Archivar cuartel" aria-label="Archivar cuartel">
                                <span class="material-symbols-outlined">archive</span>
                            </button>
                        </span>
                    </summary>
                    <div class="step-edit-accordion-body">
                        ${fieldGroup('Datos del cuartel', cuartelFields, 'cuartel', cuartel.id) || '<div class="step-edit-empty">Este cuartel no tiene campos aplicables.</div>'}
                    </div>
                </details>
            `;
        }

        function renderFincaAccordion(finca, fincaFields, cuartelFields, cuarteles) {
            const cuartelesHtml = cuartelesForFinca(finca, cuarteles)
                .map((cuartel) => renderCuartelAccordion(cuartel, cuartelFields))
                .join('');

            return `
                <details class="step-edit-accordion">
                    <summary>
                        <span class="step-edit-accordion-title">${escapeHtml(fincaTitle(finca))}</span>
                        <span class="step-edit-accordion-actions">
                            <button type="button" class="btn-icon step-edit-danger" data-step-edit-archive-finca="${Number(finca.id)}" title="Archivar finca" aria-label="Archivar finca">
                                <span class="material-symbols-outlined">archive</span>
                            </button>
                        </span>
                    </summary>
                    <div class="step-edit-accordion-body">
                        ${fieldGroup('Datos de la finca', fincaFields, 'finca', finca.id)}
                        <div class="step-edit-structure-actions">
                            <button type="button" class="btn btn-cancelar step-edit-action-button" data-step-edit-create-cuartel="${Number(finca.id)}">
                                <span class="material-symbols-outlined">add</span>
                                Añadir cuartel
                            </button>
                        </div>
                        ${cuartelesHtml ? `<div class="step-edit-cuarteles-stack">${cuartelesHtml}</div>` : '<div class="step-edit-empty">Esta finca no tiene cuarteles asociados.</div>'}
                    </div>
                </details>
            `;
        }

        function renderOrphanCuarteles(fincas, cuarteles, cuartelFields) {
            const assigned = new Set();
            fincas.forEach((finca) => {
                cuartelesForFinca(finca, cuarteles).forEach((cuartel) => assigned.add(String(cuartel.id)));
            });
            const orphans = cuarteles.filter((cuartel) => !assigned.has(String(cuartel.id)));
            if (!orphans.length) return '';

            return `
                <section class="step-edit-field-group">
                    <h3>Cuarteles sin finca asociada</h3>
                    <div class="step-edit-cuarteles-stack">
                        ${orphans.map((cuartel) => renderCuartelAccordion(cuartel, cuartelFields)).join('')}
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
                <div class="step-edit-structure-actions">
                    <button type="button" class="btn btn-aceptar step-edit-action-button" data-step-edit-create-finca>
                        <span class="material-symbols-outlined">add_location_alt</span>
                        Añadir finca
                    </button>
                </div>
                ${fincas.length ? `<div class="step-edit-accordion-stack">${fincas.map((finca) => renderFincaAccordion(finca, fincaFields, cuartelFields, cuarteles)).join('')}</div>` : ''}
                ${renderOrphanCuarteles(fincas, cuarteles, cuartelFields)}
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

        function openCrearProductorModal() {
            const modalEl = createProductorModal();
            modalEl.querySelector('[data-step-edit-new-productor-nombre]').value = '';
            modalEl.querySelector('[data-step-edit-new-productor-cuit]').value = '';
            setModalError(modalEl, '[data-step-edit-create-productor-error]', '');
            showModal(modalEl);
            modalEl.querySelector('[data-step-edit-new-productor-nombre]')?.focus();
        }

        async function crearProductorDesdeModal() {
            const modalEl = createProductorModal();
            try {
                const productor = await apiPost({
                    action: 'crear_productor',
                    operativo_id: state.operativo.id,
                    coop_id_real: state.coop.id_real,
                    nombre: modalEl.querySelector('[data-step-edit-new-productor-nombre]').value,
                    cuit: modalEl.querySelector('[data-step-edit-new-productor-cuit]').value
                });
                hideModal(modalEl);
                await refreshCoopAndProducts();
                await loadProductores();
                state.productor = productor;
                await loadForm();
            } catch (e) {
                setModalError(modalEl, '[data-step-edit-create-productor-error]', e.message);
            }
        }

        function openCrearFincaModal() {
            const modalEl = createFincaModal();
            modalEl.querySelector('[data-step-edit-new-finca-codigo]').value = '';
            modalEl.querySelector('[data-step-edit-new-finca-nombre]').value = '';
            setModalError(modalEl, '[data-step-edit-create-finca-error]', '');
            showModal(modalEl);
            modalEl.querySelector('[data-step-edit-new-finca-codigo]')?.focus();
        }

        async function crearFincaDesdeModal() {
            const modalEl = createFincaModal();
            try {
                await flushPendingSaves();
                await apiPost({
                    action: 'crear_finca',
                    operativo_id: state.operativo.id,
                    productor_id_real: state.productor.id_real,
                    coop_id_real: state.coop.id_real,
                    codigo_finca: modalEl.querySelector('[data-step-edit-new-finca-codigo]').value,
                    nombre_finca: modalEl.querySelector('[data-step-edit-new-finca-nombre]').value
                });
                hideModal(modalEl);
                await refreshCurrentForm();
            } catch (e) {
                setModalError(modalEl, '[data-step-edit-create-finca-error]', e.message);
            }
        }

        async function loadVariedadesForSelect(selectEl) {
            if (!state.variedades) {
                state.variedades = await apiGet('variedades');
            }
            selectEl.innerHTML = '<option value="">Seleccionar variedad</option>' + state.variedades.map((item) => `
                <option value="${escapeHtml(item.codigo_variedad)}">${escapeHtml(item.nombre_variedad || item.codigo_variedad)}</option>
            `).join('');
        }

        async function openCrearCuartelModal(fincaId) {
            state.pendingCuartelFincaId = Number(fincaId);
            const modalEl = createCuartelModal();
            const selectEl = modalEl.querySelector('[data-step-edit-new-cuartel-variedad]');
            modalEl.querySelector('[data-step-edit-new-cuartel-sistema]').value = '';
            modalEl.querySelector('[data-step-edit-new-cuartel-superficie]').value = '';
            setModalError(modalEl, '[data-step-edit-create-cuartel-error]', '');
            showModal(modalEl);
            try {
                await loadVariedadesForSelect(selectEl);
            } catch (e) {
                setModalError(modalEl, '[data-step-edit-create-cuartel-error]', e.message);
            }
            selectEl?.focus();
        }

        async function crearCuartelDesdeModal() {
            const modalEl = createCuartelModal();
            try {
                await flushPendingSaves();
                await apiPost({
                    action: 'crear_cuartel',
                    operativo_id: state.operativo.id,
                    productor_id_real: state.productor.id_real,
                    coop_id_real: state.coop.id_real,
                    finca_id: state.pendingCuartelFincaId,
                    variedad: modalEl.querySelector('[data-step-edit-new-cuartel-variedad]').value,
                    sistema_conduccion: modalEl.querySelector('[data-step-edit-new-cuartel-sistema]').value,
                    superficie_ha: modalEl.querySelector('[data-step-edit-new-cuartel-superficie]').value
                });
                hideModal(modalEl);
                state.pendingCuartelFincaId = null;
                await refreshCurrentForm();
            } catch (e) {
                setModalError(modalEl, '[data-step-edit-create-cuartel-error]', e.message);
            }
        }

        function confirmarArchivarProductor(productorIdReal) {
            const productor = (state.cache?.productoresByCoop?.[state.coop?.id_real] || []).find((item) => String(item.id_real) === String(productorIdReal));
            showConfirmAction({
                title: 'Archivar productor',
                message: `El productor ${productor?.nombre || productorIdReal} sera archivado y dejara de mostrarse en el flujo activo.`,
                confirmLabel: 'Archivar',
                onConfirm: async () => {
                    await apiPost({
                        action: 'archivar_productor',
                        operativo_id: state.operativo.id,
                        coop_id_real: state.coop.id_real,
                        productor_id_real: productorIdReal
                    });
                    await refreshCoopAndProducts();
                    await loadProductores();
                }
            });
        }

        function confirmarArchivarFinca(fincaId) {
            showConfirmAction({
                title: 'Archivar finca',
                message: 'La finca sera archivada y sus cuarteles asociados dejaran de mostrarse en el flujo activo.',
                confirmLabel: 'Archivar',
                onConfirm: async () => {
                    await flushPendingSaves();
                    await apiPost({
                        action: 'archivar_finca',
                        operativo_id: state.operativo.id,
                        productor_id_real: state.productor.id_real,
                        coop_id_real: state.coop.id_real,
                        finca_id: fincaId
                    });
                    await refreshCurrentForm();
                }
            });
        }

        function confirmarArchivarCuartel(cuartelId) {
            showConfirmAction({
                title: 'Archivar cuartel',
                message: 'El cuartel sera archivado y dejara de mostrarse como activo.',
                confirmLabel: 'Archivar',
                onConfirm: async () => {
                    await flushPendingSaves();
                    await apiPost({
                        action: 'archivar_cuartel',
                        operativo_id: state.operativo.id,
                        productor_id_real: state.productor.id_real,
                        coop_id_real: state.coop.id_real,
                        cuartel_id: cuartelId
                    });
                    await refreshCurrentForm();
                }
            });
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
            confirmActionModal().classList.add('hidden');
            createProductorModal().classList.add('hidden');
            createFincaModal().classList.add('hidden');
            createCuartelModal().classList.add('hidden');
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
            const createProductor = ev.target.closest?.('[data-step-edit-create-productor]');
            if (createProductor) {
                openCrearProductorModal();
                return;
            }

            const archiveProductor = ev.target.closest?.('[data-step-edit-archive-productor]');
            if (archiveProductor) {
                ev.preventDefault();
                ev.stopPropagation();
                confirmarArchivarProductor(archiveProductor.dataset.stepEditArchiveProductor);
                return;
            }

            const createFinca = ev.target.closest?.('[data-step-edit-create-finca]');
            if (createFinca) {
                openCrearFincaModal();
                return;
            }

            const createCuartel = ev.target.closest?.('[data-step-edit-create-cuartel]');
            if (createCuartel) {
                ev.preventDefault();
                ev.stopPropagation();
                openCrearCuartelModal(createCuartel.dataset.stepEditCreateCuartel);
                return;
            }

            const archiveFinca = ev.target.closest?.('[data-step-edit-archive-finca]');
            if (archiveFinca) {
                ev.preventDefault();
                ev.stopPropagation();
                confirmarArchivarFinca(Number(archiveFinca.dataset.stepEditArchiveFinca));
                return;
            }

            const archiveCuartel = ev.target.closest?.('[data-step-edit-archive-cuartel]');
            if (archiveCuartel) {
                ev.preventDefault();
                ev.stopPropagation();
                confirmarArchivarCuartel(Number(archiveCuartel.dataset.stepEditArchiveCuartel));
                return;
            }

            if (ev.target.closest?.('[data-step-edit-cancel-close]')) {
                closeConfirmModal().classList.add('hidden');
                closeConfirmModal().setAttribute('aria-hidden', 'true');
                return;
            }

            if (ev.target.closest?.('[data-step-edit-confirm-close]')) {
                close();
                return;
            }

            if (ev.target.closest?.('[data-step-edit-action-cancel]')) {
                hideModal(confirmActionModal());
                state.pendingConfirmAction = null;
                return;
            }

            if (ev.target.closest?.('[data-step-edit-action-confirm]')) {
                const action = state.pendingConfirmAction;
                if (!action) return;
                action().then(() => {
                    state.pendingConfirmAction = null;
                    hideModal(confirmActionModal());
                }).catch((e) => {
                    setModalError(confirmActionModal(), '[data-step-edit-action-error]', e.message);
                });
                return;
            }

            if (ev.target.closest?.('[data-step-edit-create-productor-cancel]')) {
                hideModal(createProductorModal());
                return;
            }

            if (ev.target.closest?.('[data-step-edit-create-productor-confirm]')) {
                crearProductorDesdeModal();
                return;
            }

            if (ev.target.closest?.('[data-step-edit-create-finca-cancel]')) {
                hideModal(createFincaModal());
                return;
            }

            if (ev.target.closest?.('[data-step-edit-create-finca-confirm]')) {
                crearFincaDesdeModal();
                return;
            }

            if (ev.target.closest?.('[data-step-edit-create-cuartel-cancel]')) {
                state.pendingCuartelFincaId = null;
                hideModal(createCuartelModal());
                return;
            }

            if (ev.target.closest?.('[data-step-edit-create-cuartel-confirm]')) {
                crearCuartelDesdeModal();
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
