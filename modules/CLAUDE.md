# modules/ — reglas para pantallas y procesos

Cada carpeta es una página registrada en `config/routes.php`. Su clave de página no siempre coincide con el
nombre de la carpeta (ej. carpeta `compras` → página `compra`; `permisos_recortar()` resuelve eso a partir de las rutas).

## Archivos de un módulo
| Archivo | Se abre desde | Protección |
|---|---|---|
| `view.php` | `main.php?module=<pagina>` (incluido) | `route_resolve()` en `main.php` (pide `leer`) |
| `form.php` | `main.php?module=form_<pagina>&form=add\|edit\|detalle` | `route_resolve()` (insertar / editar / leer) |
| `proses.php` o `process.php` | URL directa (`?act=...`, normalmente POST) | **`guard_endpoint('<pagina>')` en la primera línea útil** |
| `print.php` + `printview.php` / `print_view.php` | URL directa, genera PDF con Html2Pdf | `guard_endpoint('<pagina>', 'leer')` en ambos |
| `ajax/*.php` | `$.post` desde el formulario | `guard_endpoint(..., 'cualquiera', 'fragmento'\|'json')` |

Las vistas incluidas ya tienen `$mysqli`, `$_SESSION`, `$routes`, `puede()` y los helpers de `config/ui.php`
cuando los carga el módulo. No hagas `session_start()` ni `require database.php` dentro de una vista.

## Patrón a seguir (módulos v2)
- Catálogos: `genero`, `nacionalidad`, `cargo` → una línea cada archivo, todo en `config/catalogo.php`.
- Entidades: `persona`, `funcionario`, `user`, `rol` → `prepare()`, validación en el servidor, `proses_volver()`,
  `ui_auditar()`. `persona` y `funcionario` además vuelven al formulario conservando lo tecleado
  (`$_SESSION['old_<modulo>']`): es el patrón preferido para formularios largos.
- Eliminar solo si no hay registros que lo usen; si no, "dar de baja" (`act=off`) y "reactivar" (`act=on`).

## Módulos heredados (compras y ventas)
`pedido`, `presupuesto`, `orden_c`, `compras`, `cuenta`, `nota_c_d`, `ajuste`, `nota_remision`, `clientes`,
`pedido_v`, `ventas`, `departamento`, `ciudad`, `deposito`, `producto`, `proveedor`, `tipo_producto`, `u_medida`:
- SQL interpolado y `alert()` en JS. Si cambias una consulta, pásala a `prepare()`; no copies este estilo.
- Los movimientos tienen estados en mayúsculas (`BORRADOR`, `PENDIENTE`, `CONFIRMADO`, `MODIFICANDO`, `ANULADO`) y
  el detalle se arma por AJAX (`ajax/agregar_*.php`, `ajax/productos_*.php`, tabla `tmp` por `session_id`).
- `pedido/proses.php` responde JSON (usa `guard_endpoint('pedido', null, 'json')`); otros redirigen.
  Respeta el tipo de respuesta que espera el JavaScript del `form.php` correspondiente.
- `deposito` está fuera del menú a propósito ("no se usa"); `clientes`, `pedido_v`, `ventas` están ocultos
  (`'hidden' => true`) hasta que se trabaje Ventas.
