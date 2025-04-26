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
    console.log("Abrir modal para ID:", id);

    fetch(`/controllers/obtenerProductoController.php?id=${id}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('edit_id').value = data.producto.Id;
                document.getElementById('edit_Nombre_producto').value = data.producto.Nombre_producto;
                document.getElementById('edit_Detalle_producto').value = data.producto.Detalle_producto;
                document.getElementById('edit_Precio_producto').value = data.producto.Precio_producto;
                document.getElementById('edit_Unidad_medida_venta').value = data.producto.Unidad_medida_venta;
                document.getElementById('edit_categoria').value = data.producto.categoria;
                document.getElementById('edit_alicuota').value = data.producto.alicuota;

                openModal();
            } else {
                showAlert('error', 'Error al cargar datos del producto.');
            }
        })
        .catch((err) => {
            console.error('⛔ Error:', err);
            showAlert('error', 'Error de red al buscar producto.');
        });
}

// abrir modal confirmación
let productoIdAEliminar = null; // 👈 variable global para guardar ID

function confirmarEliminacion(id) {
    console.log("Quiero eliminar el producto ID:", id);
    productoIdAEliminar = id;

    const modal = document.getElementById('modalConfirmacion');
    modal.classList.remove('hidden');
}

function closeModalConfirmacion() {
    document.getElementById('modalConfirmacion').classList.add('hidden');
    productoIdAEliminar = null; // 👈 limpiamos
}

async function eliminarProductoConfirmado() {
    if (!productoIdAEliminar) {
        showAlert('error', 'ID no proporcionado');
        return;
    }

    try {
        const response = await fetch(`/controllers/eliminarProductoController.php?id=${productoIdAEliminar}`, { method: 'DELETE' });
        const result = await response.json();

        if (result.success) {
            showAlert('success', result.message);
            closeModalConfirmacion();
            cargarProductos();
        } else {
            showAlert('error', result.message);
        }
    } catch (error) {
        console.error('⛔ Error al eliminar producto:', error);
        showAlert('error', 'Error inesperado al eliminar producto.');
    }
}

// Asignar evento al botón de confirmar
document.getElementById('btnConfirmarEliminar').addEventListener('click', eliminarProductoConfirmado);
