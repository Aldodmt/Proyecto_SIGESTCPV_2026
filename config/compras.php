<?php
/*
  Flujo de compras:  Pedido -> Presupuesto -> Orden de compra -> Compra   (+ notas y ajustes de stock)

  Este archivo concentra TRES cosas que antes estaban repartidas (y duplicadas) en cada módulo:

  1) La MÁQUINA DE ESTADOS de cada documento (COMPRAS_FLUJO): qué acciones existen, desde qué estados se pueden
     hacer y a qué estado llevan. Es la única fuente de verdad: el servidor la usa para validar y el formulario la
     usa para habilitar/deshabilitar los botones. Así la pantalla y el backend no pueden contradecirse.

  2) El CAMBIO DE ESTADO ATÓMICO (flujo_transicion): "compare-and-set". En vez de leer el estado, decidir en PHP y
     luego actualizar (dos pasos, con una ventana de carrera), se hace UN solo UPDATE ... WHERE estado = <el que leí>.
     Si dos clics/pestañas llegan a la vez, solo uno modifica la fila y el otro recibe "0 filas afectadas".
     Esto funciona incluso en tablas MyISAM (sin transacciones), porque cada UPDATE es atómico por sí solo.

  3) Las CONSULTAS DE CABECERA de los listados (flujo_view): una fila por documento, sin mezclar el detalle de
     productos. El detalle se consulta solo cuando se abre el registro.

  Reglas del flujo (se validan en el servidor, no solo en los botones):
   - Un documento solo nace de su origen: presupuesto <- pedido CONFIRMADO, orden <- presupuesto APROBADO,
     compra <- orden APROBADA. No existe "cargar productos sueltos" para saltarse un paso.
   - Un origen no puede tener dos documentos vigentes a la vez (vigente = no ANULADO).
   - No se puede anular un documento si ya existe uno vigente que depende de él (integridad del flujo).
   - Los datos del detalle (productos, cantidades, precios, proveedor) salen de la base, nunca del navegador.
*/
require_once __DIR__ . '/ui.php';

const COMPRAS_FLUJO = [
    'pedido' => [
        'tabla' => 'pedido', 'pk' => 'id_pedido', 'etiqueta' => 'pedido', 'pagina' => 'pedido',
        'transiciones' => [
            // acción      => desde (lista)                 hasta         verbo        mensaje de éxito
            'save'    => ['desde' => ['BORRADOR', 'MODIFICANDO'], 'hasta' => 'PENDIENTE',   'verbo' => 'guardar',   'ok' => 'Pedido guardado exitosamente'],
            'confirm' => ['desde' => ['PENDIENTE'],               'hasta' => 'CONFIRMADO',  'verbo' => 'confirmar', 'ok' => 'Pedido confirmado'],
            'modify'  => ['desde' => ['PENDIENTE'],               'hasta' => 'MODIFICANDO', 'verbo' => 'modificar', 'ok' => 'Pedido en modificación'],
            'cancel'  => ['desde' => ['CONFIRMADO'],              'hasta' => 'ANULADO',     'verbo' => 'anular',    'ok' => 'Pedido anulado', 'anula' => true, 'revisa_hijos' => true],
            // Transiciones DEL SISTEMA (sin botón): las dispara la confirmación o la anulación del documento hijo
            'consumir' => ['desde' => ['CONFIRMADO'],     'hasta' => 'PRESUPUESTADO', 'verbo' => 'finalizar', 'ok' => 'Pedido presupuestado', 'sistema' => true],
            'liberar'  => ['desde' => ['PRESUPUESTADO'],  'hasta' => 'CONFIRMADO',    'verbo' => 'liberar',   'ok' => 'Pedido liberado',     'sistema' => true],
        ],
        'editable' => ['BORRADOR', 'MODIFICANDO'],      // estados en los que se puede tocar el detalle de productos
        'listo' => 'CONFIRMADO',                        // estado que necesita para originar el siguiente documento
        'hijos' => [['presupuesto', 'id_pedido', 'presupuesto']],
    ],
    'presupuesto' => [
        'tabla' => 'presupuesto', 'pk' => 'id_presupuesto', 'etiqueta' => 'presupuesto', 'pagina' => 'presupuesto',
        'transiciones' => [
            'insert'  => ['desde' => ['BORRADOR'],  'hasta' => 'PENDIENTE', 'verbo' => 'guardar',  'ok' => 'Presupuesto guardado correctamente', 'virtual' => true],
            'confirm' => ['desde' => ['PENDIENTE'], 'hasta' => 'APROBADO',  'verbo' => 'aprobar',  'ok' => 'Presupuesto aprobado correctamente'],
            'cancel'  => ['desde' => ['APROBADO'],  'hasta' => 'ANULADO',   'verbo' => 'anular',   'ok' => 'Presupuesto anulado correctamente', 'anula' => true, 'revisa_hijos' => true],
            'consumir' => ['desde' => ['APROBADO'], 'hasta' => 'ORDENADO', 'verbo' => 'finalizar', 'ok' => 'Presupuesto ordenado', 'sistema' => true],
            'liberar'  => ['desde' => ['ORDENADO'], 'hasta' => 'APROBADO', 'verbo' => 'liberar',   'ok' => 'Presupuesto liberado', 'sistema' => true],
        ],
        // Documento del que nace este (su estado se mueve al confirmar / anular este documento)
        'padre' => ['pedido', 'id_pedido'],
        'editable' => [],
        'listo' => 'APROBADO',
        'hijos' => [['orden_compra', 'id_presupuesto', 'orden de compra']],
    ],
    'orden_c' => [
        'f' => true,   // género femenino (para los artículos de los mensajes)
        'tabla' => 'orden_compra', 'pk' => 'id_orden_comp', 'etiqueta' => 'orden de compra', 'pagina' => 'orden_c',
        'transiciones' => [
            'insert'  => ['desde' => ['BORRADOR'],  'hasta' => 'PENDIENTE', 'verbo' => 'guardar', 'ok' => 'Orden de compra guardada correctamente', 'virtual' => true],
            'confirm' => ['desde' => ['PENDIENTE'], 'hasta' => 'APROBADO',  'verbo' => 'aprobar', 'ok' => 'Orden de compra aprobada correctamente'],
            'cancel'  => ['desde' => ['APROBADO'],  'hasta' => 'ANULADO',   'verbo' => 'anular',  'ok' => 'Orden de compra anulada correctamente', 'anula' => true, 'revisa_hijos' => true],
            'consumir' => ['desde' => ['APROBADO'], 'hasta' => 'COMPRADA', 'verbo' => 'finalizar', 'ok' => 'Orden de compra comprada', 'sistema' => true],
            'liberar'  => ['desde' => ['COMPRADA'], 'hasta' => 'APROBADO', 'verbo' => 'liberar',   'ok' => 'Orden de compra liberada', 'sistema' => true],
        ],
        // Documento del que nace este (su estado se mueve al confirmar / anular este documento)
        'padre' => ['presupuesto', 'id_presupuesto'],
        'editable' => [],
        'listo' => 'APROBADO',
        'hijos' => [['compra', 'id_orden_comp', 'compra']],
    ],
    'compra' => [
        'f' => true,   // género femenino (para los artículos de los mensajes)
        'tabla' => 'compra', 'pk' => 'cod_compra', 'etiqueta' => 'compra', 'pagina' => 'compra',
        'transiciones' => [
            'insert'  => ['desde' => ['BORRADOR'],  'hasta' => 'PENDIENTE', 'verbo' => 'guardar',   'ok' => 'Compra registrada correctamente', 'virtual' => true],
            'confirm' => ['desde' => ['PENDIENTE'], 'hasta' => 'ACTIVO',    'verbo' => 'confirmar', 'ok' => 'Compra confirmada y stock actualizado correctamente.'],
            'cancel'  => ['desde' => ['ACTIVO'],    'hasta' => 'ANULADO',   'verbo' => 'anular',    'ok' => 'Compra anulada correctamente', 'anula' => true, 'revisa_hijos' => true],
        ],
        // Documento del que nace este (su estado se mueve al confirmar / anular este documento)
        'padre' => ['orden_c', 'id_orden_comp'],
        'editable' => [],
        'listo' => 'ACTIVO',
        'hijos' => [['nota_credito_debito', 'cod_compra', 'nota de crédito/débito'], ['notar_compra', 'cod_compra', 'nota de remisión']],
    ],
    'nota_c_d' => [
        'f' => true,   // género femenino (para los artículos de los mensajes)
        'tabla' => 'nota_credito_debito', 'pk' => 'id_nota', 'etiqueta' => 'nota de crédito/débito', 'pagina' => 'nota_c_d',
        'transiciones' => [
            'cancel' => ['desde' => ['ACTIVO'], 'hasta' => 'ANULADO', 'verbo' => 'anular', 'ok' => 'Nota anulada correctamente', 'anula' => true],
        ],
        'editable' => [], 'listo' => 'ACTIVO', 'hijos' => [],
    ],
    'nota_remision' => [
        'f' => true,   // género femenino (para los artículos de los mensajes)
        'tabla' => 'notar_compra', 'pk' => 'id_notaR', 'etiqueta' => 'nota de remisión', 'pagina' => 'nota_remision',
        'transiciones' => [
            'cancel' => ['desde' => ['ACTIVO'], 'hasta' => 'ANULADO', 'verbo' => 'anular', 'ok' => 'Nota anulada correctamente', 'anula' => true],
        ],
        'editable' => [], 'listo' => 'ACTIVO', 'hijos' => [],
    ],
    'ajuste' => [
        'tabla' => 'ajuste_com', 'pk' => 'id_ajuste', 'etiqueta' => 'ajuste de stock', 'pagina' => 'ajuste',
        'transiciones' => [
            'cancel' => ['desde' => ['ACTIVO'], 'hasta' => 'ANULADO', 'verbo' => 'anular', 'ok' => 'Ajuste anulado correctamente', 'anula' => true],
        ],
        'editable' => [], 'listo' => 'ACTIVO', 'hijos' => [],
    ],
];

/* ---------------------------------------------------------------------------------------------
   Acceso a la configuración y al estado
   --------------------------------------------------------------------------------------------- */
function flujo_cfg(string $doc): array
{
    if (!isset(COMPRAS_FLUJO[$doc])) {
        throw new InvalidArgumentException("Documento de flujo desconocido: $doc");
    }
    return COMPRAS_FLUJO[$doc];
}

// Artículo con el género del documento: flujo_art($c, 'un') -> "un"/"una"; 'el' -> "el"/"la"; 'listo' -> "APROBADO"/"APROBADA"
function flujo_art(array $c, string $tipo): string
{
    $f = !empty($c['f']);
    return match ($tipo) {
        'un' => $f ? 'una' : 'un',
        'el' => $f ? 'la' : 'el',
        'listo' => $f ? preg_replace('/O$/', 'A', $c['listo']) : $c['listo'],
        default => '',
    };
}

// Estado actual del documento (en mayúsculas) o null si no existe. Consulta preparada: $id nunca se concatena.
function flujo_estado(string $doc, int $id): ?string
{
    $c = flujo_cfg($doc);
    $s = $GLOBALS['mysqli']->prepare("SELECT estado FROM `{$c['tabla']}` WHERE `{$c['pk']}` = ? LIMIT 1");
    $s->bind_param('i', $id);
    $s->execute();
    $f = $s->get_result()->fetch_row();
    $s->close();
    return $f ? strtoupper(trim((string) $f[0])) : null;
}

/* ---------------------------------------------------------------------------------------------
   Máquina de estados -> mapa para los botones del formulario
   --------------------------------------------------------------------------------------------- */

// [ESTADO => [acciones permitidas]]. El formulario lo recibe como JSON y habilita/deshabilita sus botones con esto.
function flujo_acciones_por_estado(string $doc): array
{
    $mapa = [];
    foreach (flujo_cfg($doc)['transiciones'] as $accion => $t) {
        if (!empty($t['sistema'])) {
            continue;   // consumir / liberar las dispara el sistema, no el usuario
        }
        foreach ($t['desde'] as $desde) {
            $mapa[$desde][] = $accion;
        }
    }
    return $mapa;
}

// Script compartido por los formularios: define FLUJO y manejarBotones(estado). Un estado desconocido no habilita nada.
function flujo_script_botones(string $doc): string
{
    $mapa = json_encode(flujo_acciones_por_estado($doc), JSON_UNESCAPED_UNICODE);
    return <<<HTML
<script>
    // Generado desde COMPRAS_FLUJO (config/compras.php): es la misma tabla que valida el servidor.
    var FLUJO = $mapa;
    function manejarBotones(estado) {
        var a = FLUJO[String(estado || '').toUpperCase()] || [];
        var tiene = function () { for (var i = 0; i < arguments.length; i++) if (a.indexOf(arguments[i]) !== -1) return true; return false; };
        $('#btnGuardar').prop('disabled', !tiene('save', 'insert'));
        $('#btnConfirmar').prop('disabled', !tiene('confirm'));
        $('#btnModificar').prop('disabled', !tiene('modify'));
        $('#btnAnular').prop('disabled', !tiene('cancel'));
    }
</script>
HTML;
}

/* ---------------------------------------------------------------------------------------------
   Validaciones de flujo (origen, duplicados, dependientes)
   --------------------------------------------------------------------------------------------- */

// Documentos "hijos" vigentes (no anulados) de un documento: ['presupuesto 12', ...]. Sirve para impedir anulaciones
// que dejarían un documento huérfano y para impedir dos hijos vigentes sobre el mismo origen.
function flujo_hijos_vigentes(string $doc, int $id): array
{
    $db = $GLOBALS['mysqli'];
    $out = [];
    foreach (flujo_cfg($doc)['hijos'] as [$tabla, $fk, $nombre]) {
        $pkHijo = ['presupuesto' => 'id_presupuesto', 'orden_compra' => 'id_orden_comp', 'compra' => 'cod_compra',
                   'nota_credito_debito' => 'id_nota', 'notar_compra' => 'id_notaR'][$tabla];
        $s = $db->prepare("SELECT `$pkHijo` FROM `$tabla` WHERE `$fk` = ? AND UPPER(estado) <> 'ANULADO'");
        $s->bind_param('i', $id);
        $s->execute();
        foreach ($s->get_result() as $f) {
            $out[] = "$nombre N° {$f[$pkHijo]}";
        }
        $s->close();
    }
    return $out;
}

// ¿Puede este documento originar uno nuevo? Devuelve el mensaje de error, o null si está todo bien.
// Ej.: flujo_validar_origen('pedido', 7) antes de crear un presupuesto sobre el pedido 7.
function flujo_validar_origen(string $doc, int $id): ?string
{
    $c = flujo_cfg($doc);
    if ($id <= 0) {
        return "Debe seleccionar " . flujo_art($c, 'un') . " {$c['etiqueta']}.";
    }
    $estado = flujo_estado($doc, $id);
    if ($estado === null) {
        return ucfirst(flujo_art($c, 'el')) . " {$c['etiqueta']} N° $id no existe.";
    }
    if ($estado !== $c['listo']) {
        return ucfirst(flujo_art($c, 'el')) . " {$c['etiqueta']} N° $id está en estado $estado; solo se puede continuar el flujo desde " . flujo_art($c, 'un') . " {$c['etiqueta']} " . flujo_art($c, 'listo') . ".";
    }
    $hijos = flujo_hijos_vigentes($doc, $id);
    if ($hijos) {
        return ucfirst(flujo_art($c, 'el')) . " {$c['etiqueta']} N° $id ya tiene " . $hijos[0] . ' vigente. Anúlelo antes de generar otro.';
    }
    return null;
}

// ¿El detalle de productos de este documento puede modificarse en su estado actual? (error o null)
function flujo_validar_editable(string $doc, int $id): ?string
{
    $c = flujo_cfg($doc);
    $estado = flujo_estado($doc, $id);
    if ($estado === null) {
        return ucfirst(flujo_art($c, 'el')) . " {$c['etiqueta']} N° $id no existe.";
    }
    if (!in_array($estado, $c['editable'], true)) {
        return "No se pueden modificar los productos: " . flujo_art($c, 'el') . " {$c['etiqueta']} está en estado $estado.";
    }
    return null;
}

/* ---------------------------------------------------------------------------------------------
   Cambio de estado atómico (compare-and-set)
   --------------------------------------------------------------------------------------------- */

/*
  Aplica una acción del flujo. $efecto es opcional: un callable que se ejecuta DESPUÉS de ganar el cambio de estado
  (por ejemplo, mover el stock). Si el efecto falla, se restituye el estado anterior (compensación manual, porque
  las tablas de compras son MyISAM y no tienen ROLLBACK).
  Devuelve ['ok' => bool, 'mensaje' => string, 'estado' => string|null, 'previo' => string|null].
*/
function flujo_transicion(string $doc, int $id, string $accion, ?callable $efecto = null): array
{
    $c = flujo_cfg($doc);
    $t = $c['transiciones'][$accion] ?? null;
    if ($t === null || !empty($t['virtual'])) {
        return ['ok' => false, 'mensaje' => 'Acción no reconocida', 'estado' => null, 'previo' => null];
    }
    $db = $GLOBALS['mysqli'];
    $previo = flujo_estado($doc, $id);
    if ($previo === null) {
        return ['ok' => false, 'mensaje' => ucfirst($c['etiqueta']) . (empty($c['f']) ? ' no encontrado' : ' no encontrada'), 'estado' => null, 'previo' => null];
    }

    // 1) ¿La acción es válida desde el estado actual?
    if (!in_array($previo, $t['desde'], true)) {
        return ['ok' => false, 'estado' => $previo, 'previo' => $previo,
            'mensaje' => "No se puede {$t['verbo']} " . flujo_art($c, 'un') . " {$c['etiqueta']} en estado $previo (solo desde: " . implode(', ', $t['desde']) . ').'];
    }

    // 2) ¿Hay documentos vigentes que dependen de este? (solo al anular)
    if (!empty($t['revisa_hijos'])) {
        $hijos = flujo_hijos_vigentes($doc, $id);
        if ($hijos) {
            return ['ok' => false, 'estado' => $previo, 'previo' => $previo,
                'mensaje' => "No se puede {$t['verbo']} " . flujo_art($c, 'el') . " {$c['etiqueta']} porque tiene " . implode(', ', $hijos) . ' vigente(s). Anúlelo(s) primero.'];
        }
    }

    // 3) Compare-and-set: solo cambia si SIGUE en el estado que leí
    $hasta = $t['hasta'];
    if (!empty($t['anula'])) {
        $idUser = (int) ($_SESSION['id_user'] ?? 0);
        $s = $db->prepare("UPDATE `{$c['tabla']}` SET estado = ?, anulado_por = ?, anulado_fecha = CURDATE(), anulado_hora = CURTIME()
                            WHERE `{$c['pk']}` = ? AND UPPER(estado) = ?");
        $s->bind_param('siis', $hasta, $idUser, $id, $previo);
    } else {
        $s = $db->prepare("UPDATE `{$c['tabla']}` SET estado = ? WHERE `{$c['pk']}` = ? AND UPPER(estado) = ?");
        $s->bind_param('sis', $hasta, $id, $previo);
    }
    $s->execute();
    $ganado = $s->affected_rows === 1;
    $s->close();
    if (!$ganado) {
        $ahora = flujo_estado($doc, $id);
        return ['ok' => false, 'estado' => $ahora, 'previo' => $previo,
            'mensaje' => ucfirst(flujo_art($c, 'el')) . " {$c['etiqueta']} cambió de estado mientras se procesaba (ahora: " . ($ahora ?? 'inexistente') . '). Actualice la pantalla.'];
    }

    // 4) Efectos secundarios (stock, etc.) con compensación si fallan
    if ($efecto !== null) {
        try {
            $efecto($previo);
        } catch (Throwable $e) {
            $limpia = !empty($t['anula']) ? ', anulado_por = NULL, anulado_fecha = NULL, anulado_hora = NULL' : '';
            $r = $db->prepare("UPDATE `{$c['tabla']}` SET estado = ?$limpia WHERE `{$c['pk']}` = ? AND UPPER(estado) = ?");
            $r->bind_param('sis', $previo, $id, $hasta);
            $r->execute();
            $r->close();
            return ['ok' => false, 'estado' => $previo, 'previo' => $previo,
                'mensaje' => "No se pudo {$t['verbo']} " . flujo_art($c, 'el') . " {$c['etiqueta']}: " . $e->getMessage()];
        }
    }
    return ['ok' => true, 'mensaje' => $t['ok'], 'estado' => $hasta, 'previo' => $previo];
}

// Respuesta JSON estándar de las acciones de estado (el JavaScript de los formularios lee success/message/estado).
function flujo_responder(array $r, array $extra = []): void
{
    echo json_encode(array_merge(['success' => $r['ok'], 'message' => $r['mensaje'], 'estado' => $r['estado']], $extra), JSON_UNESCAPED_UNICODE);
    exit;
}

function flujo_error_json(string $mensaje): void
{
    echo json_encode(['success' => false, 'message' => $mensaje], JSON_UNESCAPED_UNICODE);
    exit;
}

/* ---------------------------------------------------------------------------------------------
   Códigos de documento
   --------------------------------------------------------------------------------------------- */

/*
  Siguiente código de un documento SIN depender de lo que mande el navegador. Se toma un lock con nombre
  (GET_LOCK) para que dos altas simultáneas no calculen el mismo MAX()+1; el lock vive mientras dure la conexión,
  es decir, hasta que termine la petición, y cubre también la inserción de la cabecera y el detalle.
*/
function flujo_siguiente_codigo(string $tabla, string $pk): int
{
    $db = $GLOBALS['mysqli'];
    $nombre = 'sysweb_seq_' . $tabla;
    $s = $db->prepare('SELECT GET_LOCK(?, 5)');
    $s->bind_param('s', $nombre);
    $s->execute();
    $ok = (int) $s->get_result()->fetch_row()[0];
    $s->close();
    if ($ok !== 1) {
        throw new RuntimeException('No se pudo reservar el código del documento. Intente nuevamente.');
    }
    return (int) $db->query("SELECT COALESCE(MAX(`$pk`), 0) + 1 FROM `$tabla`")->fetch_row()[0];
}

/* ---------------------------------------------------------------------------------------------
   Detalle de los documentos de origen (siempre desde la base de datos)
   --------------------------------------------------------------------------------------------- */

// Productos de un pedido: [cod_producto, p_descrip, u_descrip, cantidad, tipo_impuesto]
function flujo_detalle_pedido(int $id): array
{
    $s = $GLOBALS['mysqli']->prepare(
        "SELECT d.cod_producto, p.p_descrip, u.u_descrip, d.cantidad, p.tipo_impuesto
           FROM det_pedido d JOIN producto p ON p.cod_producto = d.cod_producto JOIN u_medida u ON u.id_u_medida = p.id_u_medida
          WHERE d.id_pedido = ? ORDER BY d.cod_producto");
    $s->bind_param('i', $id);
    $s->execute();
    $r = $s->get_result()->fetch_all(MYSQLI_ASSOC);
    $s->close();
    return $r;
}

// Productos de un presupuesto: [cod_producto, p_descrip, cantidad, precio_unit, tipo_impuesto]
function flujo_detalle_presupuesto(int $id): array
{
    $s = $GLOBALS['mysqli']->prepare(
        "SELECT d.cod_producto, p.p_descrip, d.cantidad, d.precio_unit, p.tipo_impuesto
           FROM det_presu d JOIN producto p ON p.cod_producto = d.cod_producto WHERE d.id_presupuesto = ? ORDER BY d.cod_producto");
    $s->bind_param('i', $id);
    $s->execute();
    $r = $s->get_result()->fetch_all(MYSQLI_ASSOC);
    $s->close();
    return $r;
}

// Productos de una orden de compra: [cod_producto, p_descrip, cantidad, precio_unit, tipo_impuesto]
function flujo_detalle_orden(int $id): array
{
    $s = $GLOBALS['mysqli']->prepare(
        "SELECT d.cod_producto, p.p_descrip, d.cantidad, d.precio_unit, p.tipo_impuesto
           FROM detalle_orden_comp d JOIN producto p ON p.cod_producto = d.cod_producto WHERE d.id_orden_comp = ? ORDER BY d.cod_producto");
    $s->bind_param('i', $id);
    $s->execute();
    $r = $s->get_result()->fetch_all(MYSQLI_ASSOC);
    $s->close();
    return $r;
}

/* ---------------------------------------------------------------------------------------------
   Listados de cabecera
   --------------------------------------------------------------------------------------------- */

/*
  Configuración de cada listado. 'sql' devuelve UNA fila por documento (nunca por producto).
  Los totales se calculan con una subconsulta correlacionada sobre el detalle: así la cabecera conserva su
  cardinalidad (1 fila = 1 documento) aunque tenga 1 o 100 productos.
  Columnas: [titulo, clave, tipo]; tipos: id | texto | fecha | hora | dinero | estado
*/
function flujo_listado_cfg(string $doc): array
{
    static $cfg = null;
    $cfg ??= [
        'pedido' => [
            'titulo' => 'Datos de Pedidos', 'migas' => 'Pedidos', 'icono' => 'bi-folder', 'form' => 'form_pedido', 'param' => 'id_pedido',
            'print' => 'modules/pedido/print.php?act=imprimir&id_pedido=',
            // Los BORRADOR son hojas de trabajo de cada usuario (se crean al abrir "Agregar"), no documentos todavía.
            'sql' => "SELECT p.id_pedido AS id, p.fecha, p.hora, u.name_user AS usuario, p.estado
                        FROM pedido p JOIN usuarios u ON u.id_user = p.id_user
                       WHERE UPPER(p.estado) <> 'BORRADOR' ORDER BY p.id_pedido",
            'cols' => [['ID', 'id', 'id'], ['Fecha', 'fecha', 'fecha'], ['Hora', 'hora', 'hora'], ['Usuario', 'usuario', 'texto'], ['Estado', 'estado', 'estado']],
        ],
        'presupuesto' => [
            'titulo' => 'Datos de Presupuestos', 'migas' => 'Presupuestos', 'icono' => 'bi-folder', 'form' => 'form_presupuesto', 'param' => 'id_presupuesto',
            'sql' => "SELECT pr.id_presupuesto AS id, pr.id_pedido, v.razon_social AS proveedor, pr.fecha_presu AS fecha, pr.fecha_vencimiento AS vence, pr.estado,
                             (SELECT COALESCE(SUM(d.cantidad * d.precio_unit), 0) FROM det_presu d WHERE d.id_presupuesto = pr.id_presupuesto) AS total
                        FROM presupuesto pr JOIN proveedor v ON v.cod_proveedor = pr.cod_proveedor ORDER BY pr.id_presupuesto",
            'cols' => [['ID', 'id', 'id'], ['Pedido', 'id_pedido', 'id'], ['Proveedor', 'proveedor', 'texto'], ['Emisión', 'fecha', 'fecha'],
                       ['Vencimiento', 'vence', 'fecha'], ['Total', 'total', 'dinero'], ['Estado', 'estado', 'estado']],
        ],
        'orden_c' => [
            'titulo' => 'Datos de Órdenes de Compra', 'migas' => 'Órdenes de compra', 'icono' => 'bi-folder', 'form' => 'form_orden_c', 'param' => 'id_orden_comp',
            'print' => 'modules/orden_c/print.php?act=imprimir&id_orden=',
            'sql' => "SELECT o.id_orden_comp AS id, o.id_presupuesto, v.razon_social AS proveedor, u.name_user AS usuario, o.fecha, o.hora, o.estado,
                             (SELECT COALESCE(SUM(d.cantidad * d.precio_unit), 0) FROM detalle_orden_comp d WHERE d.id_orden_comp = o.id_orden_comp) AS total
                        FROM orden_compra o JOIN proveedor v ON v.cod_proveedor = o.cod_proveedor JOIN usuarios u ON u.id_user = o.id_user
                       ORDER BY o.id_orden_comp",
            'cols' => [['ID', 'id', 'id'], ['Presupuesto', 'id_presupuesto', 'id'], ['Proveedor', 'proveedor', 'texto'], ['Usuario', 'usuario', 'texto'],
                       ['Fecha', 'fecha', 'fecha'], ['Hora', 'hora', 'hora'], ['Total', 'total', 'dinero'], ['Estado', 'estado', 'estado']],
        ],
        'compra' => [
            'titulo' => 'Datos de Compras', 'migas' => 'Compras', 'icono' => 'bi-folder', 'form' => 'form_compra', 'param' => 'cod_compra',
            'sql' => "SELECT c.cod_compra AS id, c.id_orden_comp, c.fac_numero AS factura, c.fecha, v.razon_social AS proveedor,
                             c.com_condicion AS condicion, c.total_compra AS total, c.estado
                        FROM compra c JOIN proveedor v ON v.cod_proveedor = c.cod_proveedor ORDER BY c.cod_compra",
            'cols' => [['ID', 'id', 'id'], ['Orden', 'id_orden_comp', 'id'], ['Nro. factura', 'factura', 'texto'], ['Fecha', 'fecha', 'fecha'],
                       ['Proveedor', 'proveedor', 'texto'], ['Condición', 'condicion', 'texto'], ['Total', 'total', 'dinero'], ['Estado', 'estado', 'estado']],
        ],
        'nota_c_d' => [
            'titulo' => 'Datos de Notas de Crédito/Débito', 'migas' => 'Notas de crédito/débito', 'icono' => 'bi-folder', 'form' => 'form_nota_c_d', 'param' => 'id_nota',
            'sql' => "SELECT n.id_nota AS id, n.cod_compra, n.fac_numero AS factura, v.razon_social AS proveedor, n.fecha_emision AS fecha,
                             n.tipo, n.monto_total AS total, n.estado
                        FROM nota_credito_debito n JOIN compra c ON c.cod_compra = n.cod_compra JOIN proveedor v ON v.cod_proveedor = c.cod_proveedor
                       ORDER BY n.id_nota",
            'cols' => [['ID', 'id', 'id'], ['Compra', 'cod_compra', 'id'], ['Nro. factura', 'factura', 'texto'], ['Proveedor', 'proveedor', 'texto'],
                       ['Fecha', 'fecha', 'fecha'], ['Tipo', 'tipo', 'texto'], ['Monto', 'total', 'dinero'], ['Estado', 'estado', 'estado']],
        ],
        'nota_remision' => [
            'titulo' => 'Datos de Notas de Remisión', 'migas' => 'Notas de remisión', 'icono' => 'bi-folder', 'form' => 'form_remision', 'param' => 'id_notaR',
            'sql' => "SELECT n.id_notaR AS id, n.cod_compra, n.nro_nota, v.razon_social AS proveedor, n.fecha, n.fecha_ini_traslado AS inicio, n.estado
                        FROM notar_compra n JOIN proveedor v ON v.cod_proveedor = n.cod_proveedor ORDER BY n.id_notaR",
            'cols' => [['ID', 'id', 'id'], ['Compra', 'cod_compra', 'id'], ['Nro. nota', 'nro_nota', 'texto'], ['Proveedor', 'proveedor', 'texto'],
                       ['Fecha', 'fecha', 'fecha'], ['Inicio traslado', 'inicio', 'fecha'], ['Estado', 'estado', 'estado']],
        ],
        'ajuste' => [
            'titulo' => 'Datos de Ajustes', 'migas' => 'Ajustes', 'icono' => 'bi-folder', 'form' => 'form_ajuste', 'param' => 'id_ajuste',
            'sql' => "SELECT a.id_ajuste AS id, u.name_user AS usuario, a.fecha_ajuste AS fecha, a.motivo, a.estado
                        FROM ajuste_com a JOIN usuarios u ON u.id_user = a.id_user ORDER BY a.id_ajuste",
            'cols' => [['ID', 'id', 'id'], ['Usuario', 'usuario', 'texto'], ['Fecha', 'fecha', 'fecha'], ['Motivo', 'motivo', 'texto'], ['Estado', 'estado', 'estado']],
            // Acción propia de esta pantalla (el ajuste no tiene botones de estado en un formulario de detalle)
            'extra' => ['titulo' => 'Anular ajuste', 'clase' => 'btn-danger', 'icono' => 'bi-x-lg', 'href' => 'modules/ajuste/proses.php?act=anular&id_ajuste=',
                        'confirmar' => '¿Anular el ajuste {id}? Se devolverá el stock descontado.', 'solo' => ['ACTIVO']],
        ],
    ];
    return $cfg[$doc];
}

function flujo_estado_badge(string $estado): string
{
    $e = strtoupper(trim($estado));
    $clase = ['BORRADOR' => 'secondary', 'PENDIENTE' => 'warning', 'MODIFICANDO' => 'info', 'CONFIRMADO' => 'success',
              'APROBADO' => 'success', 'ACTIVO' => 'success', 'ANULADO' => 'danger',
              'PRESUPUESTADO' => 'primary', 'ORDENADO' => 'primary', 'COMPRADA' => 'primary'][$e] ?? 'secondary';
    return '<span class="badge text-bg-' . $clase . '">' . h($e) . '</span>';
}

function flujo_dinero($n): string
{
    return number_format((float) $n, 0, ',', '.');
}

// Pantalla de listado: SOLO cabecera. El detalle de productos se ve al abrir el registro (botón "Detalles").
function flujo_view(string $doc): void
{
    $v = flujo_listado_cfg($doc);
    $db = $GLOBALS['mysqli'];
    ui_cabecera($v['titulo'], $v['icono'], [[$v['migas']]], 'Agregar', "?module={$v['form']}&form=add");
    echo '<section class="app-content"><div class="row"><div class="col-md-12">';
    ui_alertas();
    if ($doc === 'pedido') {
        flujo_aviso_borrador();
    }

    $filas = $db->query($v['sql'])->fetch_all(MYSQLI_ASSOC);
    echo '<div class="card"><div class="card-body"><div class="table-responsive">'
        . '<table id="dataTables1" class="table table-bordered table-striped table-hover"><thead><tr>';
    foreach ($v['cols'] as [$titulo]) {
        echo '<th class="text-center">' . h($titulo) . '</th>';
    }
    echo '<th class="text-center">Acciones</th></tr></thead><tbody>';

    foreach ($filas as $f) {
        $id = (int) $f['id'];
        echo '<tr>';
        foreach ($v['cols'] as [, $clave, $tipo]) {
            $val = $f[$clave] ?? '';
            if ($tipo === 'dinero') {
                echo '<td class="text-end" data-order="' . h((string) $val) . '">' . flujo_dinero($val) . '</td>';
            } elseif ($tipo === 'estado') {
                echo '<td class="text-center">' . flujo_estado_badge((string) $val) . '</td>';
            } else {
                echo '<td class="text-center">' . h($val === null ? '' : (string) $val) . '</td>';
            }
        }
        // El detalle se abre directo: es una consulta, no una acción que requiera confirmación.
        echo '<td class="text-center"><div class="btn-group" role="group">'
            . '<a class="btn btn-success btn-sm" title="Ver detalle" href="?module=' . h($v['form']) . '&form=detalle&' . h($v['param']) . '=' . $id . '"><i class="bi bi-file-text"></i></a>';
        if (!empty($v['print'])) {
            echo '<a class="btn btn-warning btn-sm" title="Imprimir" target="_blank" href="' . h($v['print'] . $id) . '"><i class="bi bi-printer"></i></a>';
        }
        if (!empty($v['extra']) && in_array(strtoupper((string) $f['estado']), $v['extra']['solo'], true)) {
            $x = $v['extra'];
            echo '<a class="btn ' . h($x['clase']) . ' btn-sm" title="' . h($x['titulo']) . '" href="' . h($x['href'] . $id) . '"'
                . ' onclick="return confirm(' . h(json_encode(str_replace('{id}', (string) $id, $x['confirmar']), JSON_UNESCAPED_UNICODE)) . ');"><i class="bi ' . h($x['icono']) . '"></i></a>';
        }
        echo '</div></td></tr>';
    }
    echo '</tbody></table></div></div></div></div></div></section>'
        . '<script>$(function(){ $("#dataTables1").DataTable({ language: { url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" }, pageLength: 10, order: [[0, "asc"]] }); });</script>';
}

/* ---------------------------------------------------------------------------------------------
   Selectores de origen (ventanas "Agregar pedido/presupuesto/orden"): también de cabecera
   --------------------------------------------------------------------------------------------- */

/*
  Documentos disponibles para continuar el flujo: están en el estado "listo" y todavía no tienen hijo vigente.
  Una fila por documento, con búsqueda y paginación sobre documentos (no sobre productos).
  $tipo: 'pedido' | 'presupuesto' | 'orden_c'
*/
function flujo_origenes_disponibles(string $tipo, string $buscar, int $pagina, int $porPagina = 5): array
{
    $db = $GLOBALS['mysqli'];
    $q = [
        'pedido' => [
            'from' => "pedido o JOIN usuarios u ON u.id_user = o.id_user",
            'sel' => "o.id_pedido AS id, o.fecha, o.hora, u.name_user AS usuario, o.estado,
                      (SELECT COUNT(*) FROM det_pedido d WHERE d.id_pedido = o.id_pedido) AS items",
            'pk' => 'o.id_pedido', 'est' => 'o.estado', 'listo' => 'CONFIRMADO',
            'hijo' => "NOT EXISTS (SELECT 1 FROM presupuesto h WHERE h.id_pedido = o.id_pedido AND UPPER(h.estado) <> 'ANULADO')",
            'txt' => ['o.id_pedido', 'u.name_user', 'o.fecha'],
        ],
        'presupuesto' => [
            'from' => "presupuesto o JOIN proveedor v ON v.cod_proveedor = o.cod_proveedor",
            'sel' => "o.id_presupuesto AS id, o.id_pedido, v.razon_social AS proveedor, o.fecha_presu AS fecha, o.fecha_vencimiento AS vence, o.estado,
                      (SELECT COALESCE(SUM(d.cantidad * d.precio_unit), 0) FROM det_presu d WHERE d.id_presupuesto = o.id_presupuesto) AS total",
            'pk' => 'o.id_presupuesto', 'est' => 'o.estado', 'listo' => 'APROBADO',
            'hijo' => "NOT EXISTS (SELECT 1 FROM orden_compra h WHERE h.id_presupuesto = o.id_presupuesto AND UPPER(h.estado) <> 'ANULADO')",
            'txt' => ['o.id_presupuesto', 'v.razon_social', 'o.id_pedido'],
        ],
        'orden_c' => [
            'from' => "orden_compra o JOIN proveedor v ON v.cod_proveedor = o.cod_proveedor",
            'sel' => "o.id_orden_comp AS id, o.id_presupuesto, v.razon_social AS proveedor, o.fecha, o.estado,
                      (SELECT COALESCE(SUM(d.cantidad * d.precio_unit), 0) FROM detalle_orden_comp d WHERE d.id_orden_comp = o.id_orden_comp) AS total",
            'pk' => 'o.id_orden_comp', 'est' => 'o.estado', 'listo' => 'APROBADO',
            'hijo' => "NOT EXISTS (SELECT 1 FROM compra h WHERE h.id_orden_comp = o.id_orden_comp AND UPPER(h.estado) <> 'ANULADO')",
            'txt' => ['o.id_orden_comp', 'v.razon_social', 'o.id_presupuesto'],
        ],
    ][$tipo];

    $where = "UPPER({$q['est']}) = '{$q['listo']}' AND {$q['hijo']}";
    $tipos = '';
    $vals = [];
    if ($buscar !== '') {
        $like = [];
        foreach ($q['txt'] as $col) {
            $like[] = "$col LIKE ?";
            $tipos .= 's';
            $vals[] = '%' . $buscar . '%';
        }
        $where .= ' AND (' . implode(' OR ', $like) . ')';
    }

    $s = $db->prepare("SELECT COUNT(*) FROM {$q['from']} WHERE $where");
    if ($vals) {
        $s->bind_param($tipos, ...$vals);
    }
    $s->execute();
    $total = (int) $s->get_result()->fetch_row()[0];
    $s->close();

    $pagina = max(1, $pagina);
    $offset = ($pagina - 1) * $porPagina;
    $s = $db->prepare("SELECT {$q['sel']} FROM {$q['from']} WHERE $where ORDER BY {$q['pk']} DESC LIMIT $porPagina OFFSET $offset");
    if ($vals) {
        $s->bind_param($tipos, ...$vals);
    }
    $s->execute();
    $filas = $s->get_result()->fetch_all(MYSQLI_ASSOC);
    $s->close();
    return ['total' => $total, 'paginas' => (int) ceil($total / $porPagina), 'filas' => $filas];
}

// Dibuja la tabla de la ventana "Agregar pedido / presupuesto / orden". $funcionJs: función del formulario que recibe el código.
function flujo_render_selector(string $tipo, string $funcionJs): void
{
    $buscar = trim((string) ($_REQUEST['x'] ?? ''));
    $pagina = max(1, (int) ($_REQUEST['page'] ?? 1));
    $r = flujo_origenes_disponibles($tipo, $buscar, $pagina);
    $nombre = ['pedido' => 'pedidos confirmados', 'presupuesto' => 'presupuestos aprobados', 'orden_c' => 'órdenes de compra aprobadas'][$tipo];
    if (!$r['filas']) {
        echo '<div class="alert alert-info mb-0">No hay ' . $nombre . ' disponibles (sin documento vigente que dependa de ellos).</div>';
        return;
    }
    $cols = [
        'pedido' => [['Código', 'id'], ['Usuario', 'usuario'], ['Fecha', 'fecha'], ['Hora', 'hora'], ['Productos', 'items'], ['Estado', 'estado']],
        'presupuesto' => [['Código', 'id'], ['Pedido', 'id_pedido'], ['Proveedor', 'proveedor'], ['Emisión', 'fecha'], ['Vencimiento', 'vence'], ['Total', 'total'], ['Estado', 'estado']],
        'orden_c' => [['Código', 'id'], ['Presupuesto', 'id_presupuesto'], ['Proveedor', 'proveedor'], ['Fecha', 'fecha'], ['Total', 'total'], ['Estado', 'estado']],
    ][$tipo];

    echo '<div class="table-responsive"><table class="table table-striped table-hover align-middle"><thead class="table-primary"><tr>';
    foreach ($cols as [$titulo]) {
        echo '<th>' . h($titulo) . '</th>';
    }
    echo '<th>Selección</th></tr></thead><tbody>';
    foreach ($r['filas'] as $f) {
        echo '<tr>';
        foreach ($cols as [, $clave]) {
            echo '<td>' . ($clave === 'total' ? flujo_dinero($f[$clave]) : ($clave === 'estado' ? flujo_estado_badge((string) $f[$clave]) : h($f[$clave] ?? ''))) . '</td>';
        }
        echo '<td><button type="button" class="btn btn-success btn-sm" onclick="' . h($funcionJs) . '(' . (int) $f['id'] . ')"><i class="bi bi-plus-lg"></i></button></td></tr>';
    }
    echo '</tbody></table>';
    require_once __DIR__ . '/../ajax/paginacion.php';
    echo '<nav aria-label="Page navigation">' . paginate('./index.php', $pagina, $r['paginas'], 4) . '</nav></div>';
}

/* ---------------------------------------------------------------------------------------------
   Encadenamiento: al confirmar un documento, el documento del que nace queda "finalizado"
   --------------------------------------------------------------------------------------------- */

// Documento padre de uno dado: ['doc' => 'pedido', 'id' => 7] o null (los registros históricos creados sin origen no tienen).
function flujo_padre(string $doc, int $id): ?array
{
    $c = flujo_cfg($doc);
    if (empty($c['padre'])) {
        return null;
    }
    [$docPadre, $columna] = $c['padre'];
    $s = $GLOBALS['mysqli']->prepare("SELECT `$columna` FROM `{$c['tabla']}` WHERE `{$c['pk']}` = ? LIMIT 1");
    $s->bind_param('i', $id);
    $s->execute();
    $fila = $s->get_result()->fetch_row();
    $s->close();
    $idPadre = (int) ($fila[0] ?? 0);
    return $idPadre > 0 ? ['doc' => $docPadre, 'id' => $idPadre] : null;
}

/*
  Mueve el padre de $doc/$id:  'consumir' = queda finalizado (ya siguió el flujo, no admite más acciones);
  'liberar' = vuelve a quedar disponible (se anuló el hijo). Se usa como efecto de flujo_transicion(): si el padre no está
  en un estado que lo permita se lanza una excepción y el cambio del hijo se deshace (compensación).
  'liberar' es tolerante: si el padre ya está disponible no hace nada (registros históricos anteriores a esta regla).
*/
function flujo_propagar(string $doc, int $id, string $accion): void
{
    $padre = flujo_padre($doc, $id);
    if ($padre === null) {
        return;
    }
    if ($accion === 'liberar' && flujo_estado($padre['doc'], $padre['id']) === flujo_cfg($padre['doc'])['listo']) {
        return;
    }
    $r = flujo_transicion($padre['doc'], $padre['id'], $accion);
    if (!$r['ok']) {
        throw new RuntimeException($r['mensaje']);
    }
}

/* ---------------------------------------------------------------------------------------------
   Pedido en BORRADOR (persistido en la base)
   --------------------------------------------------------------------------------------------- */

/*
  Devuelve el borrador del usuario o lo crea. Un usuario tiene como máximo UN borrador: abrir "Agregar" varias veces,
  en varias pestañas o después de un corte retoma el mismo registro. El bloqueo con nombre (GET_LOCK) por usuario
  evita que dos peticiones simultáneas busquen, no encuentren nada y creen dos borradores (la ventana "buscar y luego
  insertar" es una condición de carrera, igual que "leer y luego actualizar").
*/
function flujo_borrador_pedido(int $idUser): int
{
    $db = $GLOBALS['mysqli'];
    $nombre = 'sysweb_borrador_pedido_' . $idUser;
    $l = $db->prepare('SELECT GET_LOCK(?, 5)');
    $l->bind_param('s', $nombre);
    $l->execute();
    $ok = (int) $l->get_result()->fetch_row()[0];
    $l->close();
    if ($ok !== 1) {
        throw new RuntimeException('No se pudo abrir el borrador del pedido. Intente nuevamente.');
    }
    try {
        $s = $db->prepare("SELECT id_pedido FROM pedido WHERE id_user = ? AND UPPER(estado) = 'BORRADOR' ORDER BY id_pedido DESC LIMIT 1");
        $s->bind_param('i', $idUser);
        $s->execute();
        $fila = $s->get_result()->fetch_row();
        $s->close();
        if ($fila) {
            return (int) $fila[0];
        }
        $i = $db->prepare("INSERT INTO pedido (fecha, hora, estado, id_user) VALUES (CURDATE(), CURTIME(), 'BORRADOR', ?)");
        $i->bind_param('i', $idUser);
        $i->execute();
        $id = (int) $db->insert_id;
        $i->close();
        return $id;
    } finally {
        $r = $db->prepare('SELECT RELEASE_LOCK(?)');
        $r->bind_param('s', $nombre);
        $r->execute();
        $r->close();
    }
}

// Aviso en el listado de pedidos: el borrador no es un documento, pero el usuario tiene que poder encontrarlo.
function flujo_aviso_borrador(): void
{
    $idUser = (int) ($_SESSION['id_user'] ?? 0);
    $s = $GLOBALS['mysqli']->prepare(
        "SELECT p.id_pedido, COUNT(d.cod_producto) AS items FROM pedido p
           LEFT JOIN det_pedido d ON d.id_pedido = p.id_pedido
          WHERE p.id_user = ? AND UPPER(p.estado) = 'BORRADOR' GROUP BY p.id_pedido ORDER BY p.id_pedido DESC LIMIT 1");
    $s->bind_param('i', $idUser);
    $s->execute();
    $b = $s->get_result()->fetch_assoc();
    $s->close();
    if (!$b) {
        return;
    }
    echo '<div class="alert alert-warning d-flex justify-content-between align-items-center" role="alert"><span><i class="bi bi-pencil-square"></i> '
        . 'Tienes un pedido en <strong>borrador</strong> (N° ' . (int) $b['id_pedido'] . ', ' . (int) $b['items'] . ' producto(s)) sin guardar.</span>'
        . '<a class="btn btn-sm btn-warning" href="?module=form_pedido&form=add">Continuar</a></div>';
}