document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('formProducto');

    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(form);

            const precio = parseFloat(formData.get('Precio_producto'));
            if (isNaN(precio) || precio < 0) {
                showAlert('error', 'El precio no puede ser negativo.');
                return;
            }

            try {
                const response = await fetch('/controllers/altaProductosController.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    form.reset();
                    showAlert('success', result.message);
                    cargarProductos();
                } else {
                    showAlert('error', result.message);
                }
            } catch (error) {
                console.error('❌ Error en la solicitud AJAX:', error);
                showAlert('error', 'Error inesperado al enviar el formulario.');
            }
        });
    }

    // 💡 Boton eliminar productos
    const btnEliminar = document.getElementById('btnConfirmarEliminar');
    if (btnEliminar) {
        btnEliminar.addEventListener('click', eliminarProductoConfirmado);
    }

    cargarProductos();
});

async function cargarProductos() {
    const tabla = document.getElementById('tablaProductos');
    try {
        const res = await fetch('/controllers/productosTableController.php');
        const html = await res.text();
        tabla.innerHTML = html;
    } catch (err) {
        tabla.innerHTML = '<tr><td colspan="7">Error al cargar productos</td></tr>';
        console.error('Error cargando productos:', err);
    }
}

function abrirModalEditar(id) {
    console.log("👉 Abrir modal para ID:", id);

    fetch(`/controllers/obtenerProductoController.php?id=${id}`)
        .then(async (res) => {
            if (!res.ok) {
                const errorData = await res.json();
                throw new Error(errorData.message || 'Error al obtener producto.');
            }
            return res.json();
        })
        .then(data => {
            console.log("✅ Producto recibido:", data);

            document.getElementById('edit_id').value = data.producto.Id;
            document.getElementById('edit_Nombre_producto').value = data.producto.Nombre_producto;
            document.getElementById('edit_Detalle_producto').value = data.producto.Detalle_producto;
            document.getElementById('edit_Precio_producto').value = data.producto.Precio_producto;
            document.getElementById('edit_Unidad_Medida_venta').value = data.producto.Unidad_Medida_venta;
            document.getElementById('edit_categoria').value = data.producto.categoria;
            document.getElementById('edit_alicuota').value = data.producto.alicuota;

            openModalEditar();
        })
        .catch((err) => {
            console.error('⛔ Error capturado:', err);
            showAlert('error', err.message);
        });
}

// abrir modal para confirmación
let productoIdAEliminar = null;

function confirmarEliminacion(id) {
    console.log("Quiero eliminar el producto ID:", id);
    productoIdAEliminar = id;

    const modal = document.getElementById('modalConfirmacion');
    modal.classList.remove('hidden');
}

function closeModalConfirmacion() {
    document.getElementById('modalConfirmacion').classList.add('hidden');
    productoIdAEliminar = null;
}

async function eliminarProductoConfirmado() {
    if (!productoIdAEliminar) {
        showAlert('error', 'ID no proporcionado para eliminar.');
        return;
    }

    try {
        console.log("👉 Eliminando producto ID:", productoIdAEliminar);

        const response = await fetch(`/controllers/eliminarProductoController.php?id=${productoIdAEliminar}`);

        if (!response.ok) {
            const errorData = await response.json();
            console.log('⛔ Error en la respuesta:', errorData);
            throw new Error(errorData.message || 'Error al eliminar producto.');
        }

        const result = await response.json();
        console.log("✅ Producto eliminado:", result);

        if (result.success) {
            showAlert('success', result.message);
            closeModalConfirmacion();
            cargarProductos();
        } else {
            console.log("❌ Error al eliminar producto:", result);
            showAlert('error', result.message);
        }
    } catch (error) {
        console.error('⛔ Error capturado:', error);
        showAlert('error', error.message || 'Error inesperado.');
    }
}

function openModalEditar() {
    const modal = document.getElementById('modalEditar');
    if (modal) {
        modal.classList.remove('hidden');
    } else {
        console.error('❌ No se encontró el modal con ID modalEditar');
    }
}

function closeModalEditar() {
    document.getElementById('modalEditar').classList.add('hidden');
}


