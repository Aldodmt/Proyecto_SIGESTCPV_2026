<?php
/*
  IVA de compras  (tabla iva_compra; en el DER: IVA_compra, relacionada con compra por cod_compra)

  Regla de negocio aplicada (Paraguay, IVA incluido en el precio):
   - Los precios de la factura ya traen el IVA incluido. El IVA de una línea gravada se separa del importe:
         IVA 10% = importe - importe / 1,10        IVA 5% = importe - importe / 1,05
     Las líneas exentas no generan IVA: su importe se informa aparte (iva_exentas).
   - Se registra UNA fila de resumen por documento (compra o nota), con los tres montos y el estado. Así el "libro de
     compras" de un período se obtiene sumando filas, sin recorrer el detalle de productos.
   - La fecha del registro (iva_fecha) es la fecha DEL DOCUMENTO (emisión de la factura o de la nota), no la del día
     que se cargó: el período fiscal lo define la fecha de la factura.
   - Una nota de CRÉDITO reduce el IVA crédito fiscal (se guarda con signo negativo); una nota de DÉBITO lo aumenta
     (signo positivo). Así la suma de las filas vigentes de una compra es su IVA neto.
   - Si el documento se anula, su fila de IVA queda ANULADO (no se borra: queda el rastro).
   - Una nota por "ajuste de cuentas" no tiene productos: su IVA se reparte en la misma proporción que tenía la factura
     original (prorrateo).

  Columnas de iva_compra: id_iva, iva_fecha, iva_5, iva_10, iva_exentas, iva_estado, cod_compra (las del DER) más dos
  para poder distinguir el origen: id_nota (NULL si la fila es de la compra) e iva_origen ('COMPRA' | 'NOTA').
*/

require_once __DIR__ . "/ui.php";   // h() para las pantallas

// Normaliza el tipo de impuesto del producto ('10%', '5%', 'EXENTAS'...) a una de tres clases
function iva_clasificar(?string $tipoImpuesto): string
{
    $t = strtoupper(trim((string) $tipoImpuesto));
    return $t === '10%' || $t === '10' ? '10%' : ($t === '5%' || $t === '5' ? '5%' : 'EXENTA');
}

// Montos de una línea: ['iva_5' => , 'iva_10' => , 'iva_exentas' => ] a partir del importe (precio * cantidad)
function iva_de_linea(float $importe, string $clase): array
{
    return [
        'iva_5' => $clase === '5%' ? round($importe - $importe / 1.05, 2) : 0.0,
        'iva_10' => $clase === '10%' ? round($importe - $importe / 1.10, 2) : 0.0,
        'iva_exentas' => $clase === 'EXENTA' ? round($importe, 2) : 0.0,
    ];
}

// Suma varios resultados de iva_de_linea()
function iva_sumar(array $lineas): array
{
    $t = ['iva_5' => 0.0, 'iva_10' => 0.0, 'iva_exentas' => 0.0];
    foreach ($lineas as $l) {
        foreach ($t as $k => $_) {
            $t[$k] += (float) ($l[$k] ?? 0);
        }
    }
    return array_map(fn($v) => round($v, 2), $t);
}

function iva_insertar(string $fecha, array $t, int $codCompra, ?int $idNota, string $origen): int
{
    $db = $GLOBALS['mysqli'];
    $s = $db->prepare("INSERT INTO iva_compra (iva_fecha, iva_5, iva_10, iva_exentas, iva_estado, cod_compra, id_nota, iva_origen)
                       VALUES (?, ?, ?, ?, 'ACTIVO', ?, ?, ?)");
    $s->bind_param('sdddiis', $fecha, $t['iva_5'], $t['iva_10'], $t['iva_exentas'], $codCompra, $idNota, $origen);
    $s->execute();
    $id = (int) $db->insert_id;
    $s->close();
    return $id;
}

// IVA de la factura de compra (fecha = fecha de emisión de la factura)
function iva_registrar_compra(int $codCompra, string $fechaFactura, array $totales): int
{
    return iva_insertar($fechaFactura, $totales, $codCompra, null, 'COMPRA');
}

// IVA de una nota de crédito/débito (fecha = fecha de emisión de la nota). CRÉDITO resta, DÉBITO suma.
function iva_registrar_nota(int $idNota, int $codCompra, string $tipoNota, string $fechaNota, array $totales): int
{
    $signo = strtoupper($tipoNota) === 'CREDITO' ? -1 : 1;
    foreach ($totales as $k => $v) {
        $totales[$k] = round($signo * (float) $v, 2);
    }
    return iva_insertar($fechaNota, $totales, $codCompra, $idNota, 'NOTA');
}

// Reparte un monto sin detalle de productos (nota por ajuste de cuentas) con la misma composición de la factura original
function iva_prorratear(int $codCompra, float $monto): array
{
    $s = $GLOBALS['mysqli']->prepare(
        "SELECT ic.iva_5, ic.iva_10, ic.iva_exentas, c.total_compra
           FROM compra c LEFT JOIN iva_compra ic ON ic.cod_compra = c.cod_compra AND ic.id_nota IS NULL
          WHERE c.cod_compra = ? LIMIT 1");
    $s->bind_param('i', $codCompra);
    $s->execute();
    $f = $s->get_result()->fetch_assoc();
    $s->close();
    $total = (float) ($f['total_compra'] ?? 0);
    if (!$f || $f['iva_5'] === null || $total <= 0) {
        return iva_de_linea($monto, '10%');   // sin composición conocida se asume la tasa general
    }
    return [
        'iva_5' => round($monto * (float) $f['iva_5'] / $total, 2),
        'iva_10' => round($monto * (float) $f['iva_10'] / $total, 2),
        'iva_exentas' => round($monto * (float) $f['iva_exentas'] / $total, 2),
    ];
}

// Marca como ANULADO la fila de IVA de la compra ($idNota = null) o de una nota
function iva_anular(?int $codCompra, ?int $idNota = null): void
{
    $db = $GLOBALS['mysqli'];
    if ($idNota !== null) {
        $s = $db->prepare("UPDATE iva_compra SET iva_estado = 'ANULADO' WHERE id_nota = ?");
        $s->bind_param('i', $idNota);
    } else {
        $s = $db->prepare("UPDATE iva_compra SET iva_estado = 'ANULADO' WHERE cod_compra = ? AND id_nota IS NULL");
        $s->bind_param('i', $codCompra);
    }
    $s->execute();
    $s->close();
}

// Filas de IVA para mostrar: de una compra (con sus notas) o de una sola nota
function iva_filas(?int $codCompra, ?int $idNota = null): array
{
    $db = $GLOBALS['mysqli'];
    if ($idNota !== null) {
        $s = $db->prepare("SELECT i.*, n.tipo FROM iva_compra i LEFT JOIN nota_credito_debito n ON n.id_nota = i.id_nota WHERE i.id_nota = ? ORDER BY i.id_iva");
        $s->bind_param('i', $idNota);
    } else {
        $s = $db->prepare("SELECT i.*, n.tipo FROM iva_compra i LEFT JOIN nota_credito_debito n ON n.id_nota = i.id_nota WHERE i.cod_compra = ? ORDER BY i.id_iva");
        $s->bind_param('i', $codCompra);
    }
    $s->execute();
    $r = $s->get_result()->fetch_all(MYSQLI_ASSOC);
    $s->close();
    return $r;
}

// Bloque "Liquidación del IVA" para las pantallas de detalle
function iva_render_bloque(array $filas): void
{
    $fmt = fn($n) => number_format((float) $n, 0, ',', '.');
    echo '<div class="mt-4"><h6 class="fw-bold"><i class="bi bi-receipt"></i> Liquidación del IVA</h6>';
    if (!$filas) {
        echo '<div class="alert alert-secondary py-2 mb-0">Este documento no tiene IVA registrado.</div></div>';
        return;
    }
    echo '<div class="table-responsive"><table class="table table-sm table-bordered align-middle" style="max-width:760px">'
        . '<thead class="table-light"><tr><th>Documento</th><th>Fecha</th><th class="text-end">IVA 5%</th><th class="text-end">IVA 10%</th>'
        . '<th class="text-end">Exentas</th><th>Estado</th></tr></thead><tbody>';
    $neto = ['iva_5' => 0, 'iva_10' => 0, 'iva_exentas' => 0];
    foreach ($filas as $f) {
        $doc = $f['id_nota'] === null ? 'Factura de compra' : 'Nota de ' . (strtoupper((string) $f['tipo']) === 'CREDITO' ? 'crédito' : 'débito') . ' N° ' . (int) $f['id_nota'];
        $vigente = strtoupper($f['iva_estado']) === 'ACTIVO';
        echo '<tr' . ($vigente ? '' : ' class="text-muted"') . '><td>' . h($doc) . '</td><td>' . h($f['iva_fecha']) . '</td>'
            . '<td class="text-end">' . $fmt($f['iva_5']) . '</td><td class="text-end">' . $fmt($f['iva_10']) . '</td><td class="text-end">' . $fmt($f['iva_exentas']) . '</td>'
            . '<td>' . h($f['iva_estado']) . '</td></tr>';
        if ($vigente) {
            foreach ($neto as $k => $_) {
                $neto[$k] += (float) $f[$k];
            }
        }
    }
    echo '</tbody>';
    if (count($filas) > 1) {
        echo '<tfoot><tr class="fw-bold"><td colspan="2" class="text-end">IVA neto vigente</td><td class="text-end">' . $fmt($neto['iva_5']) . '</td>'
            . '<td class="text-end">' . $fmt($neto['iva_10']) . '</td><td class="text-end">' . $fmt($neto['iva_exentas']) . '</td><td></td></tr></tfoot>';
    }
    echo '</table></div></div>';
}
