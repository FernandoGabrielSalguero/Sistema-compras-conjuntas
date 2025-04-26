window.addEventListener('error', function(event) {
    console.error('🌋 Error atrapado globalmente:', event.message);
});

<script>
    console.log("JS cargando desde: ", document.currentScript.src);
</script>


async function fetchConSpinner(url, options = {}, mensaje = '') {
    const spinner = document.getElementById('spinner-global');
    const spinnerText = document.getElementById('spinner-text');

    let timeoutId = null;

    if (mensaje) {
        spinnerText.textContent = mensaje;
    } else {
        spinnerText.textContent = '';
    }

    // Esperar 300ms antes de mostrar el spinner
    timeoutId = setTimeout(() => {
        spinner.style.display = 'flex';
    }, 300);

    try {
        const response = await fetch(url, options);
        return response;
    } catch (error) {
        throw error;
    } finally {
        clearTimeout(timeoutId); // cancelar el timeout si ya estaba esperando
        spinner.style.display = 'none'; // ocultar spinner siempre
    }
}



// Carga inicial de cooperativas y productos
document.getElementById('formOperativo').addEventListener('submit', async function(e) {
    e.preventDefault();

    const form = this;
    const formData = new FormData();

    formData.append('nombre', document.getElementById('nombre').value);
    formData.append('fecha_inicio', document.getElementById('fecha').value);
    formData.append('fecha_cierre', document.getElementsByName('fecha')[1].value);

    const cooperativas = Array.from(document.querySelectorAll('#listaCooperativas input[type=checkbox]:checked')).map(cb => cb.value);
    const productores = Array.from(document.querySelectorAll('#listaProductores input[type=checkbox]:checked')).map(cb => cb.value);
    const productos = Array.from(document.querySelectorAll('#listaProductos input[type=checkbox]:checked')).map(cb => cb.value);

    cooperativas.forEach(id => formData.append('cooperativas[]', id));
    productores.forEach(id => formData.append('productores[]', id));
    productos.forEach(id => formData.append('productos[]', id));

    try {
        const response = await fetch('/controllers/altaOperativoController.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            form.reset();
            document.getElementById('listaProductores').innerHTML = '';
            showAlert('success', result.message);
            cargarOperativos(); // 👈 Refresca tabla
        } else {
            showAlert('error', result.message);
        }

    } catch (err) {
        console.error('❌ Error AJAX:', err);
        showAlert('error', 'Error inesperado al guardar el operativo.');
    }
});

// cargar la tabla de operativos
async function cargarOperativos() {
    console.log('👉 Ejecutando cargarOperativos()...');
    const tabla = document.querySelector('tbody#tablaOperativos');
    if (!tabla) return;

    try {
        const res = await fetch('/controllers/operativosTableController.php'); // SIN spinner por ahora
        if (!res.ok) { 
            throw new Error(`HTTP error ${res.status} - ${res.statusText}`);
        }
        const html = await res.text();
        tabla.innerHTML = html;
    } catch (err) {
        console.error('❌ Error cargando tabla de operativos:', err);
        tabla.innerHTML = `<tr><td colspan="9" style="color:red;">Error al cargar los operativos:<br>${err.message}</td></tr>`;
    }
}


// funciones para arbir y cerrar el modal
function openModalEditar() {
    document.getElementById('modalEditar').classList.remove('hidden');
}

function closeModalEditar() {
    document.getElementById('modalEditar').classList.add('hidden');
}

// funcion para cargar los datos al hacer click en editar
async function editarOperativo(id) {
    try {
        const res = await fetch(`/controllers/obtenerOperativoController.php?id=${id}`);
        const data = await res.json();

        if (!data.success) return showAlert('error', 'No se pudo cargar el operativo.');

        document.getElementById('edit_id').value = data.operativo.id;
        document.getElementById('edit_nombre').value = data.operativo.nombre;
        document.getElementById('edit_fecha_inicio').value = data.operativo.fecha_inicio;
        document.getElementById('edit_fecha_cierre').value = data.operativo.fecha_cierre;

        // Cargar listas con valores seleccionados
        await cargarSelectConSeleccionados('edit_cooperativas', 'cooperativas', data.cooperativas);
        await cargarSelectConSeleccionados('edit_productores', 'productores', data.productores);
        await cargarSelectConSeleccionados('edit_productos', 'productos', data.productos);

        openModalEditar();
    } catch (err) {
        console.error('❌ Error al cargar operativo:', err);
        showAlert('error', 'Error inesperado al cargar el operativo');
    }
}

async function cargarSelectConSeleccionados(id, tipo, seleccionados = []) {
    const res = await fetch(`/controllers/operativosAuxDataController.php?accion=${tipo}`);
    const data = await res.json();
    const select = document.getElementById(id);
    select.innerHTML = '';

    if (tipo === 'productos') {
        const grupos = {};
        data.forEach(p => {
            if (!grupos[p.Categoria]) grupos[p.Categoria] = [];
            grupos[p.Categoria].push(p);
        });

        Object.entries(grupos).forEach(([cat, items]) => {
            const group = document.createElement('optgroup');
            group.label = cat;

            items.forEach(p => {
                const opt = document.createElement('option');
                opt.value = p.id;
                opt.textContent = p.Nombre_producto;
                if (seleccionados.includes(p.id)) opt.selected = true;
                group.appendChild(opt);
            });

            select.appendChild(group);
        });
    } else {
        data.forEach(e => {
            const opt = document.createElement('option');
            opt.value = e.id;
            opt.textContent = `#${e.id} - ${e.nombre}`;
            if (seleccionados.includes(e.id)) opt.selected = true;
            select.appendChild(opt);
        });
    }
}

async function cargarProductoresFiltrados(idsCooperativas) {
    const productoresSelect = document.getElementById('productores');
    productoresSelect.innerHTML = '';

    if (!idsCooperativas.length) return;

    try {
        const res = await fetch(`/controllers/operativosAuxDataController.php?accion=productores&ids=${idsCooperativas.join(',')}`);
        const data = await res.json();

        data.forEach(p => {
            const opt = document.createElement('option');
            opt.value = p.id;
            opt.textContent = `#${p.id} - ${p.nombre}`;
            productoresSelect.appendChild(opt);
        });
    } catch (err) {
        console.error('❌ Error al cargar productores filtrados:', err);
        showAlert('error', 'Error al cargar productores asociados.');
    }
}

function capitalize(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
}

// cargamos los check boxs de cooperativas y productos al cargar la pagina

async function cargarCheckboxList(tipo, url) {
    const contenedor = document.getElementById(`lista${capitalize(tipo)}`);
    contenedor.innerHTML = '';

    try {
        const res = await fetch(url);
        const data = await res.json();

        console.log(`✅ ${tipo} cargados desde: ${url}`, data);

        // Agregar botón seleccionar todos (general)
        const btnSelTodos = document.createElement('button');
        btnSelTodos.type = 'button';
        btnSelTodos.textContent = 'Seleccionar todos';
        btnSelTodos.classList.add('btn-mini');
        btnSelTodos.addEventListener('click', (e) => {
            e.preventDefault();
            contenedor.querySelectorAll('input[type=checkbox]').forEach(cb => cb.checked = true);
        });
        contenedor.appendChild(btnSelTodos);

        // Renderizar los checkboxes
        if (tipo === 'productos') {
            const grupos = {};
            data.forEach(p => {
                if (!grupos[p.Categoria]) grupos[p.Categoria] = [];
                grupos[p.Categoria].push(p);
            });

            Object.entries(grupos).forEach(([categoria, items]) => {
                const titulo = document.createElement('strong');
                titulo.textContent = categoria;
                contenedor.appendChild(titulo);

                items.forEach(p => {
                    const label = document.createElement('label');
                    label.innerHTML = `<input type="checkbox" name="${tipo}[]" value="${p.id}"> ${p.Nombre_producto}`;
                    contenedor.appendChild(label);
                });
            });

        } else {
            data.forEach(e => {
                const label = document.createElement('label');
                label.innerHTML = `<input type="checkbox" name="${tipo}[]" value="${e.id}"> #${e.id} - ${e.nombre}`;
                contenedor.appendChild(label);
            });
        }

    } catch (err) {
        console.error(`Error cargando ${tipo}:`, err);
    }
}


// Funcion para selectores con buscador incorporado: 
document.addEventListener('DOMContentLoaded', async () => {
    await cargarOperativos();

    // Cargar cooperativas, productos y productores
    await cargarCheckboxList('cooperativas', '/controllers/operativosAuxDataController.php?accion=cooperativas');
    await cargarCheckboxList('productos', '/controllers/operativosAuxDataController.php?accion=productos');

    // Activar filtros una vez cargados
    activarBuscadores();

    document.getElementById('listaCooperativas').addEventListener('change', async () => {
        const seleccionadas = Array.from(document.querySelectorAll('#listaCooperativas input[type=checkbox]:checked')).map(cb => cb.value);
        await cargarCheckboxList('productores', `/controllers/operativosAuxDataController.php?accion=productores&ids=${seleccionadas.join(',')}`);
        activarBuscadores(); // volver a activar después de actualizar
    });
});


function activarBuscadores() {
    document.querySelectorAll('.smart-selector-search').forEach(input => {
        input.addEventListener('input', () => {
            const filtro = input.value.toLowerCase();
            const lista = input.parentElement.querySelector('.smart-selector-list');

            lista.querySelectorAll('label').forEach(label => {
                const texto = label.textContent.toLowerCase();
                label.style.display = texto.includes(filtro) ? '' : 'none';
            });
        });
    });
}

function verDetalle(tipo, operativoId) {
    const titulo = {
        cooperativas: 'Cooperativas del operativo',
        productores: 'Productores del operativo',
        productos: 'Productos del operativo'
    };

    // Mostrar modal
    document.getElementById('modalDetalleTitulo').textContent = titulo[tipo] || 'Detalles';
    document.getElementById('modalDetalle').classList.remove('hidden');
    document.getElementById('modalDetalleContenido').innerHTML = 'Cargando...';

    fetch(`/controllers/detalleOperativoController.php?tipo=${tipo}&id=${operativoId}`)
        .then(res => res.json())
        .then(data => { // 🔴 Esto está en tu función verDetalle (código actual):
            if (!data.success) {
                document.getElementById('modalDetalleContenido').innerHTML = `<p>${data.message}</p>`;
                return;
            }

            if (!data.items.length) { // ← ACA el nombre debe coincidir con tu JSON PHP
                document.getElementById('modalDetalleContenido').innerHTML = '<p>No se encontraron registros.</p>';
                return;
            }

            const ul = document.createElement('ul');

            data.items.forEach(item => { // ← items está OK si en PHP devolvés 'items'
                const li = document.createElement('li');
                if (tipo === 'productos') {
                    li.textContent = `#${item.id} - ${item.Nombre_producto} (${item.Categoria})`;
                } else {
                    li.textContent = `#${item.id} - ${item.nombre}`;
                }
                ul.appendChild(li);
            });

            document.getElementById('modalDetalleContenido').innerHTML = '';
            document.getElementById('modalDetalleContenido').appendChild(ul);
        })
        // .catch(err => {
        //     console.error('Error al obtener detalles:', err);
        //     document.getElementById('modalDetalleContenido').innerHTML = 'Error al cargar datos.';
        // });
        .catch(async err => {
            console.error('Error al obtener detalles:', err);

            let mensajeError = 'Error al cargar datos.';

            try {
                const errorJson = await err.response.json();
                if (errorJson && errorJson.message) {
                    mensajeError += `<br><code>${errorJson.message}</code>`;
                }
            } catch (e) {
                mensajeError += `<br><code>${err.message || 'Error desconocido'}</code>`;
            }

            document.getElementById('modalDetalleContenido').innerHTML = mensajeError;
        });
}

function cerrarModalDetalle() {
    document.getElementById('modalDetalle').classList.add('hidden');
}

// Guardar cambios del formulario de edición
document.getElementById('formEditarOperativo').addEventListener('submit', async function(e) {
    e.preventDefault();

    const id = document.getElementById('edit_id').value;
    const nombre = document.getElementById('edit_nombre').value;
    const fecha_inicio = document.getElementById('edit_fecha_inicio').value;
    const fecha_cierre = document.getElementById('edit_fecha_cierre').value;

    const formData = new FormData();
    formData.append('id', id);
    formData.append('nombre', nombre);
    formData.append('fecha_inicio', fecha_inicio);
    formData.append('fecha_cierre', fecha_cierre);

    try {
        const response = await fetch('/controllers/editarOperativoController.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            closeModalEditar();
            showAlert('success', result.message);
            cargarOperativos(); // refrescar tabla
        } else {
            showAlert('error', result.message || 'No se pudo guardar.');
        }

    } catch (err) {
        console.error('❌ Error al guardar edición:', err);
        showAlert('error', 'Error inesperado al guardar el operativo.');
    }
});

cargarOperativos();