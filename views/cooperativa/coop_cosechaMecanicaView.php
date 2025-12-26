<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión y configurar parámetros de seguridad
require_once '../../middleware/authMiddleware.php';
checkAccess('cooperativa');



// Datos del usuario en sesión
$nombre = $_SESSION['nombre'] ?? 'Sin nombre';
$correo = $_SESSION['correo'] ?? 'Sin correo';
$cuit = $_SESSION['cuit'] ?? 'Sin CUIT';
$telefono = $_SESSION['telefono'] ?? 'Sin teléfono';
$observaciones = $_SESSION['observaciones'] ?? 'Sin observaciones';

$cierre_info = $_SESSION['cierre_info'] ?? null;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SVE</title>

    <!-- Íconos de Material Design -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <!-- Framework Success desde CDN -->
    <link rel="stylesheet" href="https://framework.impulsagroup.com/assets/css/framework.css">
    <script src="https://framework.impulsagroup.com/assets/javascript/framework.js" defer></script>

    <!-- Descarga de consolidado (no se usa directamente aquí, pero se deja por consistencia) -->
    <script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>

    <!-- PDF: html2canvas + jsPDF (CDN gratuitos) -->
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

    <style>
        .operativo-card { position: relative; }

        .btn-print-pdf {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 38px;
            height: 38px;
            border-radius: 10px;
            border: 1px solid rgba(0,0,0,0.08);
            background: #fff;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-print-pdf:hover {
            background: rgba(91, 33, 182, 0.06);
            border-color: rgba(91, 33, 182, 0.25);
        }

        .btn-print-pdf .material-icons {
            font-size: 20px;
            color: #5b21b6;
        }

        /* Contenedor offscreen para render PDF */
        #pdfRenderContainer {
            position: fixed;
            left: -99999px;
            top: 0;
            width: 800px;
            background: #fff;
            padding: 24px;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
            color: #111;
        }

        #pdfRenderContainer h1, #pdfRenderContainer h2, #pdfRenderContainer h3 {
            margin: 0 0 10px 0;
            padding: 0;
        }

        #pdfRenderContainer .pdf-muted {
            color: #555;
            font-size: 12px;
        }

        #pdfRenderContainer table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 12px;
        }

        #pdfRenderContainer th, #pdfRenderContainer td {
            border: 1px solid #ddd;
            padding: 8px;
            vertical-align: top;
        }

        #pdfRenderContainer th {
            background: #f6f6f6;
            text-align: left;
        }
    </style>
</head>

<body>

    <!-- 🔲 CONTENEDOR PRINCIPAL -->
    <div class="layout">

        <!-- 🧭 SIDEBAR -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <span class="material-icons logo-icon">dashboard</span>
                <span class="logo-text">SVE</span>
            </div>

            <nav class="sidebar-menu">
                <ul>
                    <li onclick="location.href='coop_dashboard.php'">
                        <span class="material-icons" style="color: #5b21b6;">home</span><span class="link-text">Inicio</span>
                    </li>
                    <li onclick="location.href='coop_mercadoDigital.php'">
                        <span class="material-icons"
                            style="color: #5b21b6;">shopping_cart</span><span class="link-text">Mercado Digital</span>
                    </li>
                    <li onclick="location.href='coop_listadoPedidos.php'">
                        <span class="material-icons"
                            style="color: #5b21b6;">receipt_long</span><span class="link-text">Listado Pedidos</span>
                    </li>
                    <li onclick="location.href='coop_consolidado.php'">
                        <span class="material-icons"
                            style="color: #5b21b6;">analytics</span><span class="link-text">Consolidado</span>
                    </li>
                    <li onclick="location.href='coop_pulverizacion.php'">
                        <span class="material-symbols-outlined"
                            style="color:#5b21b6;">drone</span><span class="link-text">Pulverización con Drone</span>
                    </li>
                    <li onclick="location.href='coop_usuarioInformacion.php'">
                        <span class="material-icons" style="color: #5b21b6;">person</span><span
                            class="link-text">Productores</span>
                    </li>
                    <li onclick="location.href='coop_cosechaMecanicaView.php'">
                        <span class="material-icons"
                            style="color: #5b21b6;">agriculture</span><span class="link-text">Cosecha Mecanica</span>
                    </li>
                    <li onclick="location.href='../../../logout.php'">
                        <span class="material-icons" style="color: red;">logout</span><span
                            class="link-text">Salir</span>
                    </li>
                </ul>
            </nav>

            <div class="sidebar-footer">
                <button class="btn-icon" onclick="toggleSidebar()">
                    <span class="material-icons" id="collapseIcon">chevron_left</span>
                </button>
            </div>
        </aside>

        <!-- 🧱 MAIN -->
        <div class="main">

            <!-- 🟪 NAVBAR -->
            <header class="navbar">
                <button class="btn-icon" onclick="toggleSidebar()">
                    <span class="material-icons">menu</span>
                </button>
                <div class="navbar-title">Cosecha Mecanica</div>
            </header>

            <!-- 📦 CONTENIDO -->
            <section class="content">

                <!-- Bienvenida -->
                <div class="card">
                    <h4>Hola <?php echo htmlspecialchars($nombre); ?> 👋</h4>
                    <p>En esta página, vas a poder visualizar los servicios disponibles para cosecha mecanizada e
                        inscribir a tus productores.</p>
                    <br>
                    <!-- Botón de tutorial (reservado para futuro)
                    <button id="btnIniciarTutorial" class="btn btn-aceptar">
                        Tutorial
                    </button> -->
                </div>

                <!-- Listado de operativos de Cosecha Mecánica -->
                <div class="card">
                    <h3>Operativos disponibles</h3>
                    <p>Seleccioná un operativo para participar con tus productores.</p>
                    <div id="operativosList" class="operativos-grid">
                        <!-- JS inyecta aquí las tarjetas -->
                    </div>
                </div>

            </section>
            <!-- /content -->

        </div>
        <!-- /main -->

    </div>
    <!-- /layout -->

    <?php
    // Modal de participación (tabla de productores)
    require_once __DIR__ . '/../partials/cosechaMecanicaModales/coop_participaciónModal_view.php';

    // Modal de contrato (detalle + firma en conformidad)
    require_once __DIR__ . '/../partials/cosechaMecanicaModales/coop_firmaContratoModal_view.php';
    ?>

    <!-- contenedor del toastify -->
    <div id="toast-container"></div>
    <div id="toast-container-boton"></div>

    <!-- Spinner Global -->
    <script src="../../views/partials/spinner-global.js"></script>

<script>
    console.log(
        'id_real en sesión:',
        '<?php echo addslashes($_SESSION["id_real"] ?? "NULL"); ?>'
    );
</script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // La vista principal solo se encarga de cargar y mostrar los operativos
            cargarOperativos();
        });

        function cargarOperativos() {
            const url = '../../controllers/coop_cosechaMecanicaController.php?action=listar_operativos';

            fetch(url, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(function(response) {
                    return response.json();
                })
                .then(function(json) {
                    if (!json || json.success !== true) {
                        showAlert('error', json && json.message ? json.message : 'No se pudieron obtener los operativos.');
                        return;
                    }
                    renderOperativos(json.data || []);
                })
                .catch(function(error) {
                    console.error('Error al obtener operativos:', error);
                    showAlert('error', 'Error de conexión al obtener los operativos.');
                });
        }

        function renderOperativos(operativos) {
            const contenedor = document.getElementById('operativosList');
            if (!contenedor) return;

            contenedor.innerHTML = '';

            if (!Array.isArray(operativos) || operativos.length === 0) {
                contenedor.innerHTML = '<p>No hay operativos disponibles por el momento.</p>';
                return;
            }

            operativos.forEach(function(op) {
                const card = document.createElement('div');
                card.className = 'card operativo-card';

                const estado = op.estado || '';
                const diasRestantes = (op.dias_restantes !== null && op.dias_restantes !== undefined) ?
                    op.dias_restantes :
                    '-';

                const estadoClase = obtenerClaseEstado(estado);

                // Lo convertimos a número y luego a booleano: maneja 0/1, '0'/'1', true/false
                const contratoFirmado = !!Number(op.contrato_firmado);
                console.log('Operativo', op.id, 'contrato_firmado =', op.contrato_firmado, '=>', contratoFirmado);

                const textoContrato = contratoFirmado ? 'Ver contrato' : 'Contrato';
                const claseInscribirOculta = contratoFirmado ? '' : 'hidden';

                card.innerHTML = `
                    <button
                        type="button"
                        class="btn-print-pdf"
                        title="Descargar contrato en PDF"
                        data-id="${op.id}"
                    >
                        <span class="material-icons">print</span>
                    </button>

                    <h4>${escapeHtml(op.nombre || '')}</h4>
                    <p><strong>Apertura:</strong> ${formatearFecha(op.fecha_apertura)}</p>
                    <p><strong>Cierre:</strong> ${formatearFecha(op.fecha_cierre)}</p>
                    <p><strong>Estado:</strong> <span class="badge ${estadoClase}">${escapeHtml(estado)}</span></p>
                    <p><strong>Días para cierre:</strong> ${diasRestantes}</p>
                    <div class="form-buttons">
                        <button
                            type="button"
                            class="btn btn-info btn-contrato"
                            data-id="${op.id}"
                        >
                            ${textoContrato}
                        </button>
                        <button
                            type="button"
                            class="btn btn-aceptar btn-inscribir ${claseInscribirOculta}"
                            data-id="${op.id}"
                        >
                            Anexo 1 - Inscribir productores
                        </button>
                    </div>
                `;

                contenedor.appendChild(card);
            });

            const botonesContrato = contenedor.querySelectorAll('.btn-contrato');
            botonesContrato.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const contratoId = this.getAttribute('data-id');
                    if (!contratoId) return;

                    if (typeof abrirContratoModal === 'function') {
                        abrirContratoModal(contratoId);
                    } else {
                        console.error('Función abrirContratoModal no disponible.');
                        showAlert('error', 'No se pudo abrir el modal de contrato.');
                    }
                });
            });

            const botonesPrint = contenedor.querySelectorAll('.btn-print-pdf');
            botonesPrint.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const contratoId = this.getAttribute('data-id');
                    if (!contratoId) return;

                    descargarPdfContratoYAnexo(contratoId);
                });
            });

            const botonesInscribir = contenedor.querySelectorAll('.btn-inscribir');
            botonesInscribir.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const contratoId = this.getAttribute('data-id');
                    if (!contratoId) return;

                    if (typeof abrirParticipacionModal === 'function') {
                        abrirParticipacionModal(contratoId);
                    } else {
                        console.error('Función abrirParticipacionModal no disponible.');
                        showAlert('error', 'No se pudo abrir el modal de participación.');
                    }
                });
            });
        }

        function obtenerClaseEstado(estado) {
            switch (estado) {
                case 'abierto':
                    return 'success';
                case 'borrador':
                    return 'warning';
                case 'cerrado':
                    return 'danger';
                default:
                    return 'info';
            }
        }

        function formatearFecha(fechaIso) {
            if (!fechaIso) return '-';
            const partes = fechaIso.split('-'); // esperado 'YYYY-MM-DD'
            if (partes.length !== 3) return fechaIso;
            return partes[2] + '/' + partes[1] + '/' + partes[0];
        }

                function escapeHtml(texto) {
            if (texto === null || texto === undefined) return '';
            return String(texto)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function normalizarSeguroFlete(v) {
            const raw = (v ?? '').toString().trim().toLowerCase();
            if (raw === '' || raw === 'sin definir' || raw === 'sin_definir') return 'Sin definir';
            if (raw === 'si' || raw === '1') return 'Sí';
            if (raw === 'no' || raw === '0') return 'No';
            return 'Sin definir';
        }

                function formatearFechaFlexible(fechaIso) {
            if (!fechaIso) return '-';
            if (typeof fechaIso !== 'string') return String(fechaIso);

            // soporta 'YYYY-MM-DD' y también 'YYYY-MM-DD HH:MM:SS'
            const soloFecha = fechaIso.split(' ')[0];
            const partes = soloFecha.split('-');
            if (partes.length !== 3) return fechaIso;
            return partes[2] + '/' + partes[1] + '/' + partes[0];
        }

        function htmlATextoPlano(html) {
            if (!html) return '';
            const tmp = document.createElement('div');

            // agregamos saltos para que el texto no quede todo pegado
            const normalizado = String(html)
                .replace(/<br\s*\/?>/gi, '\n')
                .replace(/<\/p>/gi, '\n')
                .replace(/<\/div>/gi, '\n')
                .replace(/<\/li>/gi, '\n');

            tmp.innerHTML = normalizado;

            const texto = (tmp.textContent || tmp.innerText || '')
                .replace(/\u00A0/g, ' ')       // nbsp -> espacio normal
                .replace(/[ \t]+\n/g, '\n')    // limpia espacios antes de salto
                .replace(/\n{3,}/g, '\n\n')    // evita demasiados saltos
                .trim();

            return texto;
        }

        function esperarCargaImagenes(root) {
            const imgs = Array.from(root.querySelectorAll('img'));
            return Promise.all(
                imgs.map(function(img) {
                    if (img.complete && img.naturalWidth > 0) return Promise.resolve();
                    return new Promise(function(resolve) {
                        img.onload = function() { resolve(); };
                        img.onerror = function() { resolve(); };
                    });
                })
            );
        }

        function construirTablaParticipaciones(participaciones) {
            if (!Array.isArray(participaciones) || participaciones.length === 0) {
                return `<p class="pdf-muted">No hay productores inscriptos en este operativo.</p>`;
            }

            let rowsHtml = '';
            participaciones.forEach(function(p) {
                rowsHtml += `
                    <tr>
                        <td>${escapeHtml(p.productor ?? '')}</td>
                        <td>${escapeHtml(p.finca_id ?? '-') }</td>
                        <td>${escapeHtml(p.superficie ?? 0)}</td>
                        <td>${escapeHtml(p.variedad ?? '')}</td>
                        <td>${escapeHtml(p.prod_estimada ?? 0)}</td>
                        <td>${escapeHtml(formatearFechaFlexible(p.fecha_estimada))}</td>
                        <td>${escapeHtml(p.km_finca ?? 0)}</td>
                        <td>${escapeHtml(p.flete ?? 0)}</td>
                        <td>${escapeHtml(normalizarSeguroFlete(p.seguro_flete))}</td>
                    </tr>
                `;
            });

            return `
                <table>
                    <thead>
                        <tr>
                            <th>Productor</th>
                            <th>Finca ID</th>
                            <th>Superficie</th>
                            <th>Variedad</th>
                            <th>Prod. estimada</th>
                            <th>Fecha estimada</th>
                            <th>KM finca</th>
                            <th>Flete</th>
                            <th>Seguro flete</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${rowsHtml}
                    </tbody>
                </table>
            `;
        }

                function construirHtmlPdf(payload) {
            const op = (payload && payload.operativo) ? payload.operativo : {};
            const participaciones = payload && payload.participaciones ? payload.participaciones : [];
            const firma = payload && payload.firma_contrato ? payload.firma_contrato : null;
            const contratoFirmado = payload && payload.contrato_firmado ? true : false;

            const logoSrc = '../../assets/png/logo_con_color_original.png';

            const titulo = `Contrato Cosecha Mecánica - ${escapeHtml(op.nombre ?? '')}`;
            const fechaApertura = formatearFechaFlexible(op.fecha_apertura);
            const fechaCierre = formatearFechaFlexible(op.fecha_cierre);
            const estado = escapeHtml(op.estado ?? '-');

            const descripcionPlano = htmlATextoPlano(op.descripcion ?? '');
            const descripcionSegura = escapeHtml(descripcionPlano);

            const fechaFirma = firma && firma.fecha_firma ? formatearFechaFlexible(firma.fecha_firma) : '-';

            return `
                <div>
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                        <img src="${logoSrc}" alt="SVE" style="height:42px; width:auto;" />
                        <div style="text-align:right;">
                            <div class="pdf-muted">SVE</div>
                            <div class="pdf-muted">Generado desde el sistema</div>
                        </div>
                    </div>

                    <h1 style="font-size:18px;">${titulo}</h1>

                    <h2 style="font-size:14px; margin-top:14px;">Datos del contrato</h2>
                    <table>
                        <tbody>
                            <tr><th style="width:220px;">Operativo</th><td>${escapeHtml(op.nombre ?? '')}</td></tr>
                            <tr><th>Apertura</th><td>${escapeHtml(fechaApertura)}</td></tr>
                            <tr><th>Cierre</th><td>${escapeHtml(fechaCierre)}</td></tr>
                            <tr><th>Estado</th><td>${estado}</td></tr>
                            <tr><th>Descripción</th><td style="white-space:pre-wrap;">${descripcionSegura || '-'}</td></tr>
                            <tr><th>Contrato firmado</th><td>${contratoFirmado ? 'Sí' : 'No'}</td></tr>
                            <tr><th>Fecha firma</th><td>${escapeHtml(fechaFirma)}</td></tr>
                        </tbody>
                    </table>

                    <h2 style="font-size:14px; margin-top:16px;">Anexo 1 - Productores inscriptos</h2>
                    ${construirTablaParticipaciones(participaciones)}
                </div>
            `;
        }

        async function descargarPdfContratoYAnexo(contratoId) {
            try {
                if (!window.html2canvas || !window.jspdf || !window.jspdf.jsPDF) {
                    showAlert('error', 'No se cargaron las librerías para generar el PDF.');
                    return;
                }

                const url = '../../controllers/coop_cosechaMecanicaController.php?action=obtener_operativo&id=' + encodeURIComponent(contratoId);

                const resp = await fetch(url, { method: 'GET', headers: { 'Accept': 'application/json' } });
                const json = await resp.json();

                if (!json || json.success !== true || !json.data) {
                    showAlert('error', (json && json.message) ? json.message : 'No se pudo obtener el detalle del operativo.');
                    return;
                }

                let container = document.getElementById('pdfRenderContainer');
                if (!container) {
                    container = document.createElement('div');
                    container.id = 'pdfRenderContainer';
                    document.body.appendChild(container);
                }

                container.innerHTML = construirHtmlPdf(json.data);

                await esperarCargaImagenes(container);

                const canvas = await window.html2canvas(container, {
                    scale: 2,
                    useCORS: true,
                    backgroundColor: '#ffffff'
                });


                const imgData = canvas.toDataURL('image/png');
                const { jsPDF } = window.jspdf;

                const pdf = new jsPDF('p', 'mm', 'a4');
                const pageWidth = pdf.internal.pageSize.getWidth();
                const pageHeight = pdf.internal.pageSize.getHeight();

                const imgWidth = pageWidth;
                const imgHeight = (canvas.height * imgWidth) / canvas.width;

                let heightLeft = imgHeight;
                let position = 0;

                pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                heightLeft -= pageHeight;

                while (heightLeft > 0) {
                    position = heightLeft - imgHeight;
                    pdf.addPage();
                    pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                    heightLeft -= pageHeight;
                }

                const nombreOp = (json.data.operativo && json.data.operativo.nombre) ? json.data.operativo.nombre : 'operativo';
                const safeName = String(nombreOp).replace(/[^\w\-]+/g, '_').substring(0, 60);
                pdf.save(`Contrato_CosechaMecanica_${safeName}_ID${contratoId}.pdf`);
            } catch (e) {
                console.error('Error PDF:', e);
                showAlert('error', 'Ocurrió un error al generar el PDF.');
            }
        }
    </script>

</body>

</html>