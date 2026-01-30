<?php declare(strict_types=1); ?>
<!-- Este es el modal que muestra las cooperativas y los productores que firmaron el contrato de cosecha mecánica.-->
<style>
    /* Ajuste de tamaño del modal y comportamiento responsive */
    #modalCoopProd .modal-content {
        width: 70vw;
        height: 70vh;
        max-width: 70vw;
        max-height: 70vh;
        display: flex;
        flex-direction: column;
    }

    #modalCoopProd .modal-body {
        padding: 1rem 1.5rem;
        overflow: auto;
        max-height: calc(70vh - 120px); /* deja espacio para header+footer */
    }

    #modalCoopProd .modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem 1rem 1.5rem;
    }

    /* Tarjeta de filtros */
    #coopProdFiltersCard {
        margin-bottom: 1rem;
        padding: 0.75rem 1rem;
        border-radius: 0.75rem;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.12);
        border: 1px solid #e5e7eb;
        background-color: #ffffff;
    }

    #coopProdFiltersCard h4 {
        margin: 0 0 0.75rem 0;
        font-size: 1rem;
        font-weight: 600;
    }

    #coopProdFiltersCard .filters-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.75rem;
    }

    #coopProdFiltersCard .filter-group label {
        display: block;
        font-size: 0.8rem;
        margin-bottom: 0.2rem;
        color: #4b5563;
    }

    #coopProdFiltersCard .filter-group input,
    #coopProdFiltersCard .filter-group select {
        width: 100%;
        padding: 0.35rem 0.5rem;
        border-radius: 0.5rem;
        border: 1px solid #d1d5db;
        font-size: 0.85rem;
    }

    #coopProdFiltersCard .filter-group input:focus,
    #coopProdFiltersCard .filter-group select:focus {
        outline: none;
        border-color: #5b21b6;
        box-shadow: 0 0 0 1px rgba(91, 33, 182, 0.15);
    }

    @media (max-width: 900px) {
        #coopProdFiltersCard .filters-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 600px) {
        #modalCoopProd .modal-content {
            width: 100vw;
            height: 100vh;
            max-height: 100vh;
            border-radius: 0;
        }

        #coopProdFiltersCard .filters-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div id="modalCoopProd" class="modal hidden" aria-hidden="true" role="dialog" aria-modal="true"
     aria-labelledby="modalCoopProdTitulo">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalCoopProdTitulo">Cooperativas y productores</h3>
            <button type="button"
                    class="modal-close-btn"
                    aria-label="Cerrar"
                    data-close-modal="modalCoopProd">
                <span class="material-icons">close</span>
            </button>
        </div>

        <div class="modal-body">
            <!-- Tarjeta de filtros -->
            <div id="coopProdFiltersCard">
                <h4>Filtros</h4>
                <div class="filters-grid">
                    <div class="filter-group">
                        <label for="filtroCoopProdCoop">Cooperativa</label>
                        <input type="text"
                               id="filtroCoopProdCoop"
                               placeholder="Buscar por cooperativa">
                    </div>
                    <div class="filter-group">
                        <label for="filtroCoopProdProductor">Productor</label>
                        <input type="text"
                               id="filtroCoopProdProductor"
                               placeholder="Buscar por productor">
                    </div>
                    <div class="filter-group">
                        <label for="filtroCoopProdVariedad">Variedad</label>
                        <input type="text"
                               id="filtroCoopProdVariedad"
                               placeholder="Buscar por variedad">
                    </div>
                    <div class="filter-group">
                        <label for="filtroCoopProdFlete">Flete</label>
                        <select id="filtroCoopProdFlete">
                            <option value="">Todos</option>
                            <option value="con">Con flete</option>
                            <option value="sin">Sin flete</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Contenedor que ya usa el JS actual para dibujar la tabla -->
            <div id="modalCoopProdBody">
                Cargando cooperativas y productores...
            </div>
        </div>

        <div class="modal-footer">
            <button type="button"
                    class="btn btn-info"
                    id="btnDescargarCoopProd">
                Descargar
            </button>
            <button type="button"
                    class="btn btn-aceptar"
                    data-close-modal="modalCoopProd">
                Cerrar
            </button>
        </div>
    </div>
</div>

<!-- SheetJS para exportar a Excel (si ya se incluye globalmente no pasa nada por repetir) -->
<script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>

<script>
    (function () {
        function normalizarTexto(texto) {
            return (texto || '')
                .toString()
                .toLowerCase()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '');
        }

        function aplicarFiltrosCoopProd() {
            var modal = document.getElementById('modalCoopProd');
            if (!modal) return;

            var tabla = modal.querySelector('#modalCoopProdBody table');
            if (!tabla) return;

            var filtroCoop = document.getElementById('filtroCoopProdCoop');
            var filtroProd = document.getElementById('filtroCoopProdProductor');
            var filtroVar  = document.getElementById('filtroCoopProdVariedad');
            var filtroFlete = document.getElementById('filtroCoopProdFlete');

            var valorCoop = normalizarTexto(filtroCoop ? filtroCoop.value : '');
            var valorProd = normalizarTexto(filtroProd ? filtroProd.value : '');
            var valorVar  = normalizarTexto(filtroVar ? filtroVar.value : '');
            var valorFlete = filtroFlete ? filtroFlete.value : '';

            var filas = tabla.querySelectorAll('tbody tr');

            filas.forEach(function (fila) {
                var celdas = fila.querySelectorAll('td');
                if (!celdas.length) return;

                var txtCoop = normalizarTexto(
                    (celdas[1] ? celdas[1].textContent : '') + ' ' +
                    (celdas[2] ? celdas[2].textContent : '') + ' ' +
                    (celdas[3] ? celdas[3].textContent : '')
                );
                var txtProd = normalizarTexto(
                    (celdas[4] ? celdas[4].textContent : '') + ' ' +
                    (celdas[5] ? celdas[5].textContent : '') + ' ' +
                    (celdas[6] ? celdas[6].textContent : '')
                );
                var txtVar  = normalizarTexto(celdas[8] ? celdas[8].textContent : '');
                var txtFlete = normalizarTexto(celdas[13] ? celdas[13].textContent : '');

                var visible = true;

                if (valorCoop && txtCoop.indexOf(valorCoop) === -1) {
                    visible = false;
                }
                if (visible && valorProd && txtProd.indexOf(valorProd) === -1) {
                    visible = false;
                }
                if (visible && valorVar && txtVar.indexOf(valorVar) === -1) {
                    visible = false;
                }

                if (visible && valorFlete) {
                    // Considera texto tipo "Sí/No", "Con flete/Sin flete", etc.
                    var esConFlete = /si|sí|con/.test(txtFlete);
                    if (valorFlete === 'con' && !esConFlete) {
                        visible = false;
                    }
                    if (valorFlete === 'sin' && esConFlete) {
                        visible = false;
                    }
                }

                fila.style.display = visible ? '' : 'none';
            });
        }

        function exportarTablaCoopProdExcel() {
            var modal = document.getElementById('modalCoopProd');
            if (!modal) return;

            var tabla = modal.querySelector('#modalCoopProdBody table');
            if (!tabla) {
                alert('No se encontró la tabla para exportar.');
                return;
            }

            if (typeof XLSX === 'undefined') {
                alert('No se encontró la librería XLSX para exportar a Excel.');
                return;
            }

            var wb = XLSX.utils.book_new();
            var ws = XLSX.utils.table_to_sheet(tabla);
            XLSX.utils.book_append_sheet(wb, ws, 'CoopProd');
            XLSX.writeFile(wb, 'cooperativas_productores.xlsx');
        }

        function inicializarModalCoopProd() {
            var filtroCoop = document.getElementById('filtroCoopProdCoop');
            var filtroProd = document.getElementById('filtroCoopProdProductor');
            var filtroVar  = document.getElementById('filtroCoopProdVariedad');
            var filtroFlete = document.getElementById('filtroCoopProdFlete');
            var btnDescargar = document.getElementById('btnDescargarCoopProd');

            if (filtroCoop) {
                filtroCoop.addEventListener('input', aplicarFiltrosCoopProd);
            }
            if (filtroProd) {
                filtroProd.addEventListener('input', aplicarFiltrosCoopProd);
            }
            if (filtroVar) {
                filtroVar.addEventListener('input', aplicarFiltrosCoopProd);
            }
            if (filtroFlete) {
                filtroFlete.addEventListener('change', aplicarFiltrosCoopProd);
            }
            if (btnDescargar) {
                btnDescargar.addEventListener('click', exportarTablaCoopProdExcel);
            }

            // Si la tabla ya está cargada cuando abre la página, aplicamos filtros iniciales (sin cambios)
            aplicarFiltrosCoopProd();
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', inicializarModalCoopProd);
        } else {
            inicializarModalCoopProd();
        }
    })();
</script>
