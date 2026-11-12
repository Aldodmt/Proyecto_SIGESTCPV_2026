<?php
/*
  Informe general de REFERENCIALES (tablas de datos que usan otros módulos: departamentos, ciudades, productos...).

  En vez de una pantalla por tabla (con el mismo formulario, la misma tabla y el mismo PDF repetidos), hay UNA sola
  pantalla y este arreglo describe cada referencial: de dónde salen los datos, qué columnas se muestran y qué filtros
  admite. Para sumar un referencial nuevo basta agregar una entrada aquí; no hace falta tocar la pantalla.

  Seguridad de los filtros:
   - Los NOMBRES de columnas y tablas salen solo de este arreglo (lista blanca), nunca de lo que escribe el usuario.
   - Los VALORES se envían como parámetros de una sentencia preparada (bind_param), nunca concatenados.

  Los informes de MOVIMIENTOS (pedidos, presupuestos, órdenes, compras, notas, ajustes) siguen siendo uno por
  documento: cada uno tiene estados, fechas, importes y detalles propios que no se pueden unificar sin perder precisión.

  Tipos de filtro:  texto (LIKE %valor%) | numero (= valor) | select (= valor, con opciones de una consulta) | estado (activo/inactivo)
*/

function informe_ref_cfg(): array
{
    static $c = null;
    if ($c !== null) {
        return $c;
    }
    $estados = [['activo', 'Activo'], ['inactivo', 'Inactivo']];
    $c = [
        // ------------------------------------------------------------------ General
        'departamento' => [
            'grupo' => 'General', 'titulo' => 'Departamentos',
            'from' => 'departamento t',
            'select' => 't.id_departamento AS codigo, t.dep_descripcion AS descripcion',
            'orden' => 't.dep_descripcion',
            'cols' => [['Código', 'codigo'], ['Descripción', 'descripcion']],
            'filtros' => [
                ['codigo', 'Código', 'numero', 't.id_departamento'],
                ['descripcion', 'Descripción', 'texto', 't.dep_descripcion'],
            ],
        ],
        'ciudad' => [
            'grupo' => 'General', 'titulo' => 'Ciudades',
            'from' => 'ciudad t JOIN departamento d ON d.id_departamento = t.id_departamento',
            'select' => 't.cod_ciudad AS codigo, t.descrip_ciudad AS descripcion, d.dep_descripcion AS departamento',
            'orden' => 'd.dep_descripcion, t.descrip_ciudad',
            'cols' => [['Código', 'codigo'], ['Ciudad', 'descripcion'], ['Departamento', 'departamento']],
            'filtros' => [
                ['codigo', 'Código', 'numero', 't.cod_ciudad'],
                ['descripcion', 'Ciudad', 'texto', 't.descrip_ciudad'],
                ['departamento', 'Departamento', 'select', 't.id_departamento',
                    "SELECT id_departamento, dep_descripcion FROM departamento ORDER BY dep_descripcion"],
            ],
        ],
        'nacionalidad' => [
            'grupo' => 'General', 'titulo' => 'Nacionalidades',
            'from' => 'nacionalidad t',
            'select' => 't.id_nacionalidad AS codigo, t.nac_descripcion AS descripcion, t.nac_estado AS estado',
            'orden' => 't.nac_descripcion',
            'cols' => [['Código', 'codigo'], ['Descripción', 'descripcion'], ['Estado', 'estado']],
            'filtros' => [
                ['codigo', 'Código', 'numero', 't.id_nacionalidad'],
                ['descripcion', 'Descripción', 'texto', 't.nac_descripcion'],
                ['estado', 'Estado', 'estado', 't.nac_estado', $estados],
            ],
        ],
        'genero' => [
            'grupo' => 'General', 'titulo' => 'Géneros',
            'from' => 'genero t',
            'select' => 't.id_genero AS codigo, t.gen_descripcion AS descripcion, t.gen_estado AS estado',
            'orden' => 't.gen_descripcion',
            'cols' => [['Código', 'codigo'], ['Descripción', 'descripcion'], ['Estado', 'estado']],
            'filtros' => [
                ['codigo', 'Código', 'numero', 't.id_genero'],
                ['descripcion', 'Descripción', 'texto', 't.gen_descripcion'],
                ['estado', 'Estado', 'estado', 't.gen_estado', $estados],
            ],
        ],
        // ------------------------------------------------------------------ Personal
        'cargo' => [
            'grupo' => 'Personal', 'titulo' => 'Cargos',
            'from' => 'cargo t',
            'select' => 't.id_cargo AS codigo, t.car_descripcion AS descripcion, t.car_estado AS estado',
            'orden' => 't.car_descripcion',
            'cols' => [['Código', 'codigo'], ['Descripción', 'descripcion'], ['Estado', 'estado']],
            'filtros' => [
                ['codigo', 'Código', 'numero', 't.id_cargo'],
                ['descripcion', 'Descripción', 'texto', 't.car_descripcion'],
                ['estado', 'Estado', 'estado', 't.car_estado', $estados],
            ],
        ],
        // ------------------------------------------------------------------ Compras
        'tipo_producto' => [
            'grupo' => 'Compras', 'titulo' => 'Tipos de producto',
            'from' => 'tipo_producto t',
            'select' => 't.cod_tipo_prod AS codigo, t.t_p_descrip AS descripcion',
            'orden' => 't.t_p_descrip',
            'cols' => [['Código', 'codigo'], ['Descripción', 'descripcion']],
            'filtros' => [
                ['codigo', 'Código', 'numero', 't.cod_tipo_prod'],
                ['descripcion', 'Descripción', 'texto', 't.t_p_descrip'],
            ],
        ],
        'u_medida' => [
            'grupo' => 'Compras', 'titulo' => 'Unidades de medida',
            'from' => 'u_medida t',
            'select' => 't.id_u_medida AS codigo, t.u_descrip AS descripcion',
            'orden' => 't.u_descrip',
            'cols' => [['Código', 'codigo'], ['Descripción', 'descripcion']],
            'filtros' => [
                ['codigo', 'Código', 'numero', 't.id_u_medida'],
                ['descripcion', 'Descripción', 'texto', 't.u_descrip'],
            ],
        ],
        'producto' => [
            'grupo' => 'Compras', 'titulo' => 'Productos',
            'from' => 'producto t JOIN tipo_producto tp ON tp.cod_tipo_prod = t.cod_tipo_prod JOIN u_medida um ON um.id_u_medida = t.id_u_medida',
            'select' => 't.cod_producto AS codigo, t.p_descrip AS descripcion, tp.t_p_descrip AS tipo, um.u_descrip AS unidad, t.tipo_impuesto AS impuesto',
            'orden' => 't.p_descrip',
            'cols' => [['Código', 'codigo'], ['Producto', 'descripcion'], ['Tipo de producto', 'tipo'], ['Unidad de medida', 'unidad'], ['Impuesto', 'impuesto']],
            'filtros' => [
                ['codigo', 'Código', 'numero', 't.cod_producto'],
                ['descripcion', 'Descripción', 'texto', 't.p_descrip'],
                ['tipo', 'Tipo de producto', 'select', 't.cod_tipo_prod', "SELECT cod_tipo_prod, t_p_descrip FROM tipo_producto ORDER BY t_p_descrip"],
                ['unidad', 'Unidad de medida', 'select', 't.id_u_medida', "SELECT id_u_medida, u_descrip FROM u_medida ORDER BY u_descrip"],
                ['impuesto', 'Impuesto', 'select', 't.tipo_impuesto', "SELECT DISTINCT tipo_impuesto, tipo_impuesto FROM producto ORDER BY tipo_impuesto"],
            ],
        ],
        'proveedor' => [
            'grupo' => 'Compras', 'titulo' => 'Proveedores',
            'from' => 'proveedor t',
            'select' => 't.cod_proveedor AS codigo, t.razon_social AS descripcion, t.ruc AS ruc, t.direccion AS direccion, t.telefono AS telefono',
            'orden' => 't.razon_social',
            'cols' => [['Código', 'codigo'], ['Razón social', 'descripcion'], ['RUC', 'ruc'], ['Dirección', 'direccion'], ['Teléfono', 'telefono']],
            'filtros' => [
                ['codigo', 'Código', 'numero', 't.cod_proveedor'],
                ['descripcion', 'Razón social', 'texto', 't.razon_social'],
                ['ruc', 'RUC', 'texto', 't.ruc'],
            ],
        ],
    ];
    return $c;
}

// Opciones de un filtro 'select': [[valor, texto], ...] (fijas o salidas de una consulta de dos columnas)
function informe_ref_opciones(array $filtro): array
{
    $fuente = $filtro[4] ?? [];
    if (is_array($fuente)) {
        return $fuente;
    }
    return array_map(fn($f) => [$f[0], $f[1]], $GLOBALS['mysqli']->query($fuente)->fetch_all());
}

/*
  Ejecuta el informe de un referencial con los filtros recibidos ($valores: ['codigo' => '5', ...]).
  Devuelve ['filas' => [...], 'aplicados' => [['Etiqueta', 'valor legible'], ...]].
  Un valor vacío se ignora; un número mal escrito se ignora también (se informa al usuario con 'ignorados').
*/
function informe_ref_ejecutar(string $ref, array $valores): array
{
    $cfg = informe_ref_cfg()[$ref];
    $db = $GLOBALS['mysqli'];
    $where = [];
    $tipos = '';
    $params = [];
    $aplicados = [];
    $ignorados = [];

    foreach ($cfg['filtros'] as $f) {
        [$nombre, $etiqueta, $tipo, $columna] = $f;
        $v = trim((string) ($valores[$nombre] ?? ''));
        if ($v === '') {
            continue;
        }
        if ($tipo === 'texto') {
            $where[] = "$columna LIKE ?";
            $tipos .= 's';
            $params[] = '%' . $v . '%';
            $aplicados[] = [$etiqueta, $v];
        } elseif ($tipo === 'numero') {
            if (!ctype_digit($v)) {
                $ignorados[] = $etiqueta;
                continue;
            }
            $where[] = "$columna = ?";
            $tipos .= 'i';
            $params[] = (int) $v;
            $aplicados[] = [$etiqueta, $v];
        } else {   // select | estado: el valor debe ser una de las opciones válidas (lista blanca)
            $texto = null;
            foreach (informe_ref_opciones($f) as [$valor, $etiq]) {
                if ((string) $valor === $v) {
                    $texto = $etiq;
                    break;
                }
            }
            if ($texto === null) {
                $ignorados[] = $etiqueta;
                continue;
            }
            $where[] = "$columna = ?";
            $tipos .= 's';
            $params[] = $v;
            $aplicados[] = [$etiqueta, (string) $texto];
        }
    }

    $sql = "SELECT {$cfg['select']} FROM {$cfg['from']}" . ($where ? ' WHERE ' . implode(' AND ', $where) : '') . " ORDER BY {$cfg['orden']}";
    $s = $db->prepare($sql);
    if ($params) {
        $s->bind_param($tipos, ...$params);
    }
    $s->execute();
    $filas = $s->get_result()->fetch_all(MYSQLI_ASSOC);
    $s->close();
    return ['filas' => $filas, 'aplicados' => $aplicados, 'ignorados' => $ignorados];
}
