console.log('✅ sve_operativo.js cargado correctamente');

async function cargarOperativos() {
    const tabla = document.querySelector('#tablaOperativos');
    tabla.innerHTML = '<tr><td colspan="7">Cargando...</td></tr>';

    try {
        const res = await fetch('/controllers/OperativosController.php');
        const data = await res.json();

        if (!data.success) throw new Error(data.message || 'Error al obtener operativos.');

        tabla.innerHTML = '';

        data.operativos.forEach(op => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${op.id}</td>
                <td>${op.nombre}</td>
                <td>${op.fecha_inicio}</td>
                <td>${op.fecha_cierre}</td>
                <td>${op.estado}</td>
                <td>${op.created_at}</td>
                <td>
                    <button class="btn btn-info" onclick="editarOperativo(${op.id})">Editar</button>
                </td>
            `;
            tabla.appendChild(row);
        });

    } catch (err) {
        console.error('❌ Error cargando operativos:', err);
        tabla.innerHTML = `<tr><td colspan="7" style="color:red;">${err.message}</td></tr>`;
    }
}

// Crear nuevo operativo
document.getElementById('formOperativo').addEventListener('submit', async function (e) {
    e.preventDefault();

    const formData = new FormData(this);

    try {
        const res = await fetch('/controllers/OperativosController.php', {
            method: 'POST',
            body: formData
        });

        const result = await res.json();

        if (result.success) {
            this.reset();
            showAlert('success', result.message);
            cargarOperativos();
        } else {
            showAlert('error', result.message);
        }
    } catch (err) {
        console.error('❌ Error al guardar:', err);
        showAlert('error', 'Error al guardar el operativo.');
    }
});

// Abrir modal con datos
async function editarOperativo(id) {
    try {
        const res = await fetch(`/controllers/OperativosController.php?id=${id}`);
        const result = await res.json();

        if (!result.success) {
            showAlert('error', result.message || 'No se pudo cargar el operativo');
            return;
        }

        const op = result.operativo;

        document.getElementById('edit_id').value = op.id;
        document.getElementById('edit_nombre').value = op.nombre;
        document.getElementById('edit_fecha_inicio').value = op.fecha_inicio;
        document.getElementById('edit_fecha_cierre').value = op.fecha_cierre;
        document.getElementById('edit_estado').value = op.estado;

        openModalEditar();
    } catch (err) {
        console.error('❌ Error al editar:', err);
        showAlert('error', 'Error al cargar el operativo');
    }
}

// Guardar edición
document.getElementById('formEditarOperativo').addEventListener('submit', async function (e) {
    e.preventDefault();

    const formData = new FormData(this);

    try {
        const res = await fetch('/controllers/OperativosController.php', {
            method: 'POST',
            body: formData
        });

        const result = await res.json();

        if (result.success) {
            closeModalEditar();
            showAlert('success', result.message);
            cargarOperativos();
        } else {
            showAlert('error', result.message || 'No se pudo guardar');
        }
    } catch (err) {
        console.error('❌ Error al guardar edición:', err);
        showAlert('error', 'Error al guardar');
    }
});

// Utilidad para mostrar alertas
function showAlert(tipo, mensaje) {
    const contenedor = document.getElementById('alertContainer');
    const alerta = document.createElement('div');
    alerta.className = `alert alert-${tipo}`;
    alerta.textContent = mensaje;
    contenedor.innerHTML = '';
    contenedor.appendChild(alerta);
    setTimeout(() => alerta.remove(), 5000);
}

function openModalEditar() {
    document.getElementById('modalEditar').classList.remove('hidden');
}

function closeModalEditar() {
    document.getElementById('modalEditar').classList.add('hidden');
}

// Inicial
document.addEventListener('DOMContentLoaded', cargarOperativos);
