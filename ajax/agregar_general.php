<table class="table table-striped table-hover align-middle" id="tablaProductosManual">
    <thead class="table-primary">
        <tr>
            <th>Código</th>
            <th>Producto</th>
            <th>Tipo Impuesto</th>
            <th>Cantidad</th>
            <th>Precio Unitario</th>
            <th>Subtotal</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody id="cuerpoProductosManual">
        <!-- Se agregan dinámicamente desde JS -->
    </tbody>
</table>

<script>
    // Array de productos global (ya existe en tu form.js)
    if (!window.productos) window.productos = [];

    function agregarProductoPresupuesto(cod, nombre, impuesto) {
        // Evitar duplicados
        if (productos.some(p => p.codigo_producto == cod)) {
            alert("Este producto ya está agregado.");
            return;
        }

        // Agregar al array global
        productos.push({
            codigo_producto: cod,
            cantidad: 1,
            precio_unitario: 0,
            impuesto: impuesto,
            descripcion: nombre
        });

        // Crear fila en la tabla visual
        const tbody = document.getElementById("cuerpoProductosManual");
        const fila = document.createElement("tr");
        fila.id = `fila_${cod}`;
        fila.innerHTML = `
            <td>${cod}</td>
            <td>${nombre}</td>
            <td>${impuesto}</td>
            <td><input type="number" class="form-control cantidad" min="1" value="1"></td>
            <td><input type="number" class="form-control precio_unit" min="0" value="0"></td>
            <td class="subtotal">0</td>
            <td>
                <button class="btn btn-danger btn-sm" onclick="eliminarProductoPresupuesto('${cod}')">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(fila);

        // Actualizar subtotal y array al cambiar cantidad o precio
        fila.querySelectorAll('.cantidad, .precio_unit').forEach(input => {
            input.addEventListener('input', () => {
                const cantidad = parseFloat(fila.querySelector('.cantidad').value) || 0;
                const precio = parseFloat(fila.querySelector('.precio_unit').value) || 0;
                fila.querySelector('.subtotal').textContent = (cantidad * precio).toFixed(2);

                // Actualizar en el array productos
                const prod = productos.find(p => p.codigo_producto == cod);
                if (prod) {
                    prod.cantidad = cantidad;
                    prod.precio_unitario = precio;
                }

                // Actualizar input oculto para envío
                document.getElementById('productos_json').value = JSON.stringify(productos);
            });
        });

        // Actualizar input oculto al agregar
        document.getElementById('productos_json').value = JSON.stringify(productos);
    }

    function eliminarProductoPresupuesto(cod) {
        productos = productos.filter(p => p.codigo_producto != cod);
        const fila = document.getElementById(`fila_${cod}`);
        if (fila) fila.remove();
        document.getElementById('productos_json').value = JSON.stringify(productos);
    }
</script>