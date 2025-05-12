console.log("js funcionando bien");

function previewCSV(tipo) {
    const inputFile = document.getElementById('csv' + capitalize(tipo));
    const previewDiv = document.getElementById('preview' + capitalize(tipo));
    
    if (!inputFile.files.length) {
        alert("Por favor seleccioná un archivo CSV.");
        return;
    }

    const file = inputFile.files[0];
    const reader = new FileReader();

    reader.onload = function (e) {
        const contenido = e.target.result;
        const filas = contenido.split('\n').map(fila => fila.split(','));
        renderPreview(filas, previewDiv);
    };

    reader.readAsText(file);
}

function renderPreview(filas, container) {
    if (!filas.length) {
        container.innerHTML = "<p>No se pudo leer el archivo.</p>";
        return;
    }

    let html = '<table class="table"><thead><tr>';
    filas[0].forEach(col => {
        html += '<th>' + escapeHtml(col) + '</th>';
    });
    html += '</tr></thead><tbody>';

    for (let i = 1; i < filas.length; i++) {
        if (filas[i].length === 1 && filas[i][0].trim() === '') continue;
        html += '<tr>';
        filas[i].forEach(col => {
            html += '<td>' + escapeHtml(col) + '</td>';
        });
        html += '</tr>';
    }

    html += '</tbody></table>';
    container.innerHTML = html;
}

function confirmarCarga(tipo) {
    const inputFile = document.getElementById('csv' + capitalize(tipo));
    if (!inputFile.files.length) {
        alert("Seleccioná un archivo para cargar.");
        return;
    }

    const formData = new FormData();
    formData.append('archivo', inputFile.files[0]);
    formData.append('tipo', tipo);

    fetch('../../controllers/cargaMasivaController.php', {
        method: 'POST',
        body: formData
    })
    .then(resp => resp.json())
    .then(data => {
        alert(data.mensaje || "Carga completada.");
    })
    .catch(err => {
        console.error(err);
        alert("Ocurrió un error al subir el archivo.");
    });
}

function capitalize(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function (m) { return map[m]; });
}
