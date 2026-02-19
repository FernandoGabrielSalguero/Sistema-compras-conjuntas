<div id="modalCalificacion" class="modal hidden" aria-hidden="true">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Calificación</h3>
            <button type="button" class="modal-close-btn" data-close-modal="modalCalificacion" aria-label="Cerrar">
                <span class="material-icons">close</span>
            </button>
        </div>

        <div class="modal-section-title">Detalle de la evaluación</div>
        <div id="modalCalificacionTexto" class="modal-readonly-field text-block">Sin calificación registrada.</div>

        <div class="modal-section-title">Ponderación / Calificación</div>
        <div class="modal-table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Variable</th>
                        <th>Puntos</th>
                        <th>Impacto</th>
                        <th>Ponderado</th>
                    </tr>
                </thead>
                <tbody id="modalCalificacionBody">
                    <tr>
                        <td colspan="4">Sin datos.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="modal-section-title">Puntaje obtenido</div>
        <div id="modalCalificacionResumen" class="modal-readonly-field text-block">
            Sin calificación registrada.
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-cancelar" data-close-modal="modalCalificacion">Cerrar</button>
        </div>
    </div>
</div>

<script>
    (function() {
        'use strict';

        function toNumber(value) {
            if (value === null || value === undefined) return null;
            const raw = String(value).replace(',', '.').trim();
            if (raw === '') return null;
            const num = Number(raw);
            return Number.isFinite(num) ? num : null;
        }

        function clamp(num, min, max) {
            return Math.min(Math.max(num, min), max);
        }

        function puntajeCallejon(metros) {
            if (metros === null) return 0;
            if (metros > 6) return 4;
            if (metros >= 5.7) return 3;
            if (metros >= 5.4) return 2;
            if (metros >= 5.0) return 1;
            return 0;
        }

        function parseInterfilar(valor) {
            if (valor === null || valor === undefined) return null;
            const raw = String(valor).toLowerCase().trim();
            if (!raw) return null;
            const numeric = raw.match(/(\d+[.,]?\d*)/g);
            if (numeric && numeric.length) {
                const last = numeric[numeric.length - 1].replace(',', '.');
                const num = Number(last);
                if (Number.isFinite(num)) return num;
            }
            return null;
        }

        function puntajeInterfilar(metrosOrText) {
            const metros = typeof metrosOrText === 'number' ? metrosOrText : parseInterfilar(metrosOrText);
            if (metros === null) return 0;
            if (metros >= 2.5) return 4;
            if (metros >= 2.3) return 3;
            if (metros >= 2.1) return 2;
            if (metros >= 2.0) return 1;
            return 0;
        }

        function puntajePostes(porcentaje) {
            if (porcentaje === null) return 0;
            if (porcentaje <= 10) return 4;
            if (porcentaje <= 20) return 3;
            if (porcentaje <= 30) return 2;
            if (porcentaje <= 40) return 1;
            return 0;
        }

        function puntajeSeparadores(valor) {
            const raw = String(valor ?? '').toLowerCase();
            if (raw.includes('todos asegurados')) return 4;
            if (raw.includes('algunos olvidados') || raw.includes('asegurados y tensados')) return 2;
            if (raw.includes('sin atar') || raw.includes('sin tensar')) return 0;
            return 0;
        }

        function puntajeAgua(valor) {
            const raw = String(valor ?? '').toLowerCase();
            if (raw.includes('suficiente y cerc')) return 4;
            if (raw.includes('suficiente a mas de 1km') || raw.includes('suficiente a más de 1km')) return 3;
            if (raw.includes('insuficiente pero cercana')) return 2;
            if (raw.includes('insuficiente a mas de 1km') || raw.includes('insuficiente a más de 1km')) return 1;
            if (raw.includes('no tiene')) return 0;
            return 0;
        }

        function puntajeAcequias(valor) {
            const raw = String(valor ?? '').toLowerCase();
            if (raw.includes('borradas')) return 4;
            if (raw.includes('suavizadas')) return 2.5;
            if (raw.includes('dificultades')) return 1;
            if (raw.includes('profundas') || raw.includes('sin borrar')) return 0;
            return 0;
        }

        function puntajeMalezas(valor) {
            const raw = String(valor ?? '').toLowerCase();
            if (raw.includes('ausencia de malesas') || raw.includes('ausencia de malezas')) return 4;
            if (raw.includes('mayoria') || raw.includes('mayoría')) return 3;
            if (raw.includes('menores a 40cm')) return 2;
            if (raw.includes('suelo enmalezado')) return 1;
            if (raw.includes('sobre el alambre')) return 0;
            return 0;
        }

        function formatPonderado(value) {
            return Number.isInteger(value) ? String(value) : value.toFixed(1);
        }

        function getCalificacion(total) {
            if (total >= 91) return { label: 'Óptima', descuento: '30 USD' };
            if (total >= 81) return { label: 'Muy Buena', descuento: '20 USD' };
            if (total >= 70) return { label: 'Buena', descuento: '10 USD' };
            return { label: 'Regular', descuento: '0 USD' };
        }

        function buildTextoCalificacion(data) {
            if (!data) {
                return 'Sin calificación registrada.';
            }
            const fields = [
                { label: 'Ancho callejón norte', value: data.ancho_callejon_norte },
                { label: 'Ancho callejón sur', value: data.ancho_callejon_sur },
                { label: 'Promedio callejón', value: data.promedio_callejon },
                { label: 'Interfilar', value: data.interfilar },
                { label: 'Cantidad postes', value: data.cantidad_postes },
                { label: 'Postes mal estado', value: data.postes_mal_estado },
                { label: '% postes mal estado', value: data.porcentaje_postes_mal_estado },
                { label: 'Estructura separadores', value: data.estructura_separadores },
                { label: 'Agua para el lavado', value: data.agua_lavado },
                { label: 'Preparación acequias', value: data.preparacion_acequias },
                { label: 'Preparación obstáculos', value: data.preparacion_obstaculos },
                { label: 'Observaciones', value: data.observaciones },
                { label: 'Creado', value: data.created_at ?? data.relevamiento_creado },
                { label: 'Actualizado', value: data.updated_at ?? data.relevamiento_actualizado },
            ];
            return fields.map((item) => {
                const raw = (item.value === null || item.value === undefined || String(item.value).trim() === '')
                    ? 'Sin dato'
                    : String(item.value);
                return `${item.label}: ${raw}`;
            }).join('\n');
        }

        function calcularCalificacion(data) {
            if (!data) return null;

            const promedio = toNumber(data.promedio_callejon);
            const norte = toNumber(data.ancho_callejon_norte);
            const sur = toNumber(data.ancho_callejon_sur);
            const promedioCalc = (promedio !== null) ? promedio
                : (norte !== null && sur !== null) ? (norte + sur) / 2
                : null;

            const interfilar = parseInterfilar(data.interfilar);
            const postesPct = toNumber(data.porcentaje_postes_mal_estado);

            const puntos = {
                callejon: puntajeCallejon(promedioCalc),
                interfilar: puntajeInterfilar(interfilar),
                palos: puntajePostes(postesPct),
                alambres: puntajeSeparadores(data.estructura_separadores),
                agua: puntajeAgua(data.agua_lavado),
                acequias: puntajeAcequias(data.preparacion_acequias),
                malezas: puntajeMalezas(data.preparacion_obstaculos),
            };

            const impactos = {
                callejon: 15,
                interfilar: 5,
                palos: 25,
                alambres: 10,
                agua: 30,
                acequias: 10,
                malezas: 5,
            };

            const filas = [
                { label: 'Ancho callejón', puntos: puntos.callejon, impacto: impactos.callejon },
                { label: 'Interfilar', puntos: puntos.interfilar, impacto: impactos.interfilar },
                { label: 'Estructura palos', puntos: puntos.palos, impacto: impactos.palos },
                { label: 'Estructura alambres', puntos: puntos.alambres, impacto: impactos.alambres },
                { label: 'Agua de lavado', puntos: puntos.agua, impacto: impactos.agua },
                { label: 'Prep. suelo acequias', puntos: puntos.acequias, impacto: impactos.acequias },
                { label: 'Prep. suelo malezas', puntos: puntos.malezas, impacto: impactos.malezas },
            ];

            const total = filas.reduce((acc, row) => {
                const ponderado = (row.puntos / 4) * row.impacto;
                row.ponderado = clamp(ponderado, 0, row.impacto);
                return acc + row.ponderado;
            }, 0);

            const flags = [];
            if (promedioCalc !== null && promedioCalc < 5) flags.push('Callejón menor a 5 m: no se puede prestar el servicio.');
            if (interfilar !== null && interfilar < 2) flags.push('Interfilar menor a 2 m: no se puede prestar el servicio.');
            if (String(data.agua_lavado ?? '').toLowerCase().includes('no tiene')) flags.push('Sin agua para lavado: no se puede prestar el servicio.');
            if (String(data.preparacion_acequias ?? '').toLowerCase().includes('profundas') || String(data.preparacion_acequias ?? '').toLowerCase().includes('sin borrar')) {
                flags.push('Acequias sin borrar/interfilar partido: no se puede prestar el servicio.');
            }
            if (postesPct !== null && postesPct > 50) flags.push('Más del 50% de postes en mal estado: se aconseja no cosechar con máquina.');

            return { filas, total, flags };
        }

        window.renderCalificacionModal = function(data) {
            const resumenEl = document.getElementById('modalCalificacionResumen');
            const bodyEl = document.getElementById('modalCalificacionBody');
            const textoEl = document.getElementById('modalCalificacionTexto');

            const texto = buildTextoCalificacion(data);
            if (textoEl) textoEl.textContent = texto;

            if (!data) {
                if (resumenEl) resumenEl.textContent = 'Sin calificación registrada.';
                if (bodyEl) bodyEl.innerHTML = '<tr><td colspan="4">Sin datos.</td></tr>';
                return;
            }

            const calc = calcularCalificacion(data);
            if (!calc) {
                if (resumenEl) resumenEl.textContent = 'Sin calificación registrada.';
                if (bodyEl) bodyEl.innerHTML = '<tr><td colspan="4">Sin datos.</td></tr>';
                return;
            }

            const total = clamp(calc.total, 0, 100);
            const calif = getCalificacion(total);
            const flagsText = calc.flags.length ? `\n\nObservaciones:\n- ${calc.flags.join('\n- ')}` : '';

            if (resumenEl) {
                resumenEl.textContent = `Puntaje total: ${formatPonderado(total)} / 100\n` +
                    `Calificación: ${calif.label}\n` +
                    `Descuento: ${calif.descuento}${flagsText}`;
            }

            if (bodyEl) {
                bodyEl.innerHTML = calc.filas.map((row) => {
                    const ponderado = formatPonderado(row.ponderado);
                    return `
                        <tr>
                            <td>${row.label}</td>
                            <td>${row.puntos}</td>
                            <td>${row.impacto}</td>
                            <td>${ponderado}</td>
                        </tr>
                    `;
                }).join('');
            }
        };
    })();
</script>
