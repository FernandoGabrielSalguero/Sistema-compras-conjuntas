console.log("🟢 El archivo JS se está ejecutando (inicio).");
document.addEventListener("DOMContentLoaded", () => {
    console.log("✅ DOM completamente cargado.");

    const coopSelect = document.getElementById("cooperativa");
    const form = document.querySelector("form");

    if (!coopSelect) {
        console.error("❌ No se encontró el selector #cooperativa");
        return;
    }
    if (!form) {
        console.error("❌ No se encontró el <form>");
        return;
    }

    cargarCooperativas();
    cargarProductos();

    coopSelect.addEventListener("change", cargarProductores);
    form.addEventListener("submit", enviarFormulario);
});

let productosSeleccionados = {};

// 1. Cargar cooperativas
function cargarCooperativas() {
    console.log("📦 Ejecutando cargarCooperativas()");
    fetch("/controllers/PedidoController.php?action=getCooperativas")
        .then(res => res.json())
        .then(data => {
            const select = document.getElementById("cooperativa");
            select.innerHTML = '<option value="">Seleccionar</option>';
            data.forEach(coop => {
                const opt = document.createElement("option");
                opt.value = coop.id;
                opt.textContent = coop.nombre;
                select.appendChild(opt);
            });
        })
        .catch(err => console.error("❌ Error al cargar cooperativas:", err));
}

// 2. Cargar productores
function cargarProductores() {
    console.log("📦 Ejecutando cargarProductores()");
    const idCoop = document.getElementById("cooperativa").value;
    const select = document.getElementById("productor");
    select.innerHTML = '<option value="">Seleccionar</option>';

    if (!idCoop) return;

    fetch(`/controllers/PedidoController.php?action=getProductores&id=${idCoop}`)
        .then(res => res.json())
        
        .then(data => {
            console.log("✅ Respuesta obtenida para cooperativas");
            data.forEach(prod => {
                const opt = document.createElement("option");
                opt.value = prod.id;
                opt.textContent = prod.nombre;
                select.appendChild(opt);
            });
        })
        .catch(err => console.error("❌ Error al cargar productores:", err));
}

// 3. Cargar productos
function cargarProductos() {
    console.log("📦 Ejecutando cargarProductos()");
    fetch("/controllers/PedidoController.php?action=getProductos")
        .then(res => res.json())
        .then(data => {
            Object.entries(data).forEach(([categoria, productos]) => {
                crearAcordeonCategoria(categoria, productos);
            });
        })
        .catch(err => console.error("❌ Error al cargar productos:", err));
}

function crearAcordeonCategoria(categoria, productos) {
    const container = document.getElementById("acordeones-productos");

    const acordeon = document.createElement("div");
    acordeon.classList.add("accordion");

    const header = document.createElement("div");
    header.classList.add("accordion-header");
    header.textContent = categoria;
    header.onclick = () => acordeon.classList.toggle("active");

    const body = document.createElement("div");
    body.classList.add("accordion-body");

    productos.forEach(prod => {
        const item = document.createElement("div");
        item.classList.add("input-group");

        item.innerHTML = `
            <label>${prod.Nombre_producto} (${prod.Unidad_Medida_venta})</label>
            <input type="number" min="0" value="0" data-id="${prod.id}" 
                   data-nombre="${prod.Nombre_producto}" 
                   data-detalle="${prod.Detalle_producto}"
                   data-precio="${prod.Precio_producto}"
                   data-unidad="${prod.Unidad_Medida_venta}"
                   data-categoria="${prod.categoria}"
                   onchange="actualizarProductoSeleccionado(this)">
        `;
        body.appendChild(item);
    });

    acordeon.appendChild(header);
    acordeon.appendChild(body);
    container.appendChild(acordeon);
}

// 4. Guardar productos seleccionados
function actualizarProductoSeleccionado(input) {
    const id = input.dataset.id;
    const cantidad = parseFloat(input.value);

    if (cantidad > 0) {
        productosSeleccionados[id] = {
            nombre_producto: input.dataset.nombre,
            detalle_producto: input.dataset.detalle,
            precio_producto: parseFloat(input.dataset.precio),
            unidad_medida_venta: input.dataset.unidad,
            categoria: input.dataset.categoria,
            cantidad: cantidad,
            subtotal_por_categoria: cantidad * parseFloat(input.dataset.precio)
        };
    } else {
        delete productosSeleccionados[id];
    }

    renderResumen();
}

// 5. Mostrar resumen dinámico
function renderResumen() {
    let container = document.getElementById("acordeon-resumen");
    container.innerHTML = `<h3>Resumen del Pedido</h3>`;

    let totalSinIVA = 0;

    Object.entries(productosSeleccionados).forEach(([id, p]) => {
        totalSinIVA += p.subtotal_por_categoria;

        const row = document.createElement("div");
        row.classList.add("input-group");

        row.innerHTML = `
            <strong>${p.nombre_producto}</strong> - ${p.cantidad} x $${p.precio_producto.toFixed(2)} = $${p.subtotal_por_categoria.toFixed(2)}
            <button class="btn btn-cancelar" onclick="eliminarProducto('${id}')">❌</button>
        `;
        container.appendChild(row);
    });

    const iva = totalSinIVA * 0.21;
    const total = totalSinIVA + iva;

    container.innerHTML += `
        <hr>
        <p><strong>Subtotal sin IVA:</strong> $${totalSinIVA.toFixed(2)}</p>
        <p><strong>IVA (21%):</strong> $${iva.toFixed(2)}</p>
        <p><strong>Total:</strong> $${total.toFixed(2)}</p>
    `;
}

// 6. Eliminar producto del resumen
function eliminarProducto(id) {
    delete productosSeleccionados[id];
    const input = document.querySelector(`input[data-id="${id}"]`);
    if (input) input.value = 0;
    renderResumen();
}

// 7. Enviar formulario
function enviarFormulario(e) {
    e.preventDefault();

    const formData = new FormData(e.target);
    const pedido = {
        cooperativa: formData.get("cooperativa"),
        productor: formData.get("productor"),
        persona_facturacion: formData.get("factura"),
        condicion_facturacion: formData.get("condicion"),
        afiliacion: formData.get("afiliacion"),
        ha_cooperativa: formData.get("hectareas"),
        observaciones: formData.get("observaciones"),
        total_sin_iva: calcularTotalSinIVA(),
        total_iva: calcularTotalIVA(),
        total_pedido: calcularTotalFinal(),
        factura: ""
    };

    fetch("/controllers/PedidoController.php?action=guardarPedido", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            pedido,
            detalles: Object.values(productosSeleccionados)
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert("✅ Pedido guardado con éxito");
            location.reload();
        } else {
            alert("❌ Error al guardar el pedido");
            console.error(data.error);
        }
    });
}

// Helpers
function calcularTotalSinIVA() {
    return Object.values(productosSeleccionados).reduce((s, p) => s + p.subtotal_por_categoria, 0);
}

function calcularTotalIVA() {
    return calcularTotalSinIVA() * 0.21;
}

function calcularTotalFinal() {
    return calcularTotalSinIVA() + calcularTotalIVA();
}
