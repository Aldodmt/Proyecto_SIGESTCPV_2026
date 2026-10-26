<?php
/*
  Control de acceso basado en roles (RBAC).

  Modelo:  usuario --(tiene)--> rol --(recibe)--> permiso --(sobre)--> página
           Cada permiso es una combinación de 4 acciones: leer, insertar, editar, borrar.

  Principios aplicados:
   - Denegado por defecto (fail-safe defaults): si no hay un permiso explícito, no se puede.
   - Mediación completa (complete mediation): se comprueba en CADA petición y en el servidor
     (rutas, formularios, procesos, AJAX y reportes), no solo ocultando botones.
   - Mínimo privilegio (least privilege): cada rol recibe solo las páginas y acciones que necesita.

  Uso rápido:
     puede('pedido', 'insertar')      // ¿el usuario actual puede insertar en la página "pedido"?
     guard_endpoint('pedido');        // al inicio de un proses.php / ajax / print.php
*/

const PERM_ACCIONES = ['leer', 'insertar', 'editar', 'borrar'];

// Páginas básicas: las tiene siempre cualquier usuario con un rol activo (entrar al sistema, ver su perfil y cambiar su contraseña).
// No se pueden quitar desde la matriz: un usuario sin ellas ni siquiera podría abrir el sistema.
const PERM_PAGINAS_BASICAS = ['start', 'perfil', 'password'];

/* ---------------------------------------------------------------------------------------------
   Memoria de la petición actual
   --------------------------------------------------------------------------------------------- */
function &permisos_mem(): array
{
    static $m = ['mapa' => [], 'id_rol' => null, 'rol' => ''];
    return $m;
}

/* ---------------------------------------------------------------------------------------------
   Estado del esquema y carga de permisos
   --------------------------------------------------------------------------------------------- */

// ¿Existen las tablas del modelo de permisos? (si no, hay que aplicar database/migraciones/v1_a_v2...)
function permisos_tablas_ok(mysqli $db): bool
{
    $r = $db->query("SELECT COUNT(*) c FROM information_schema.tables
                     WHERE table_schema = DATABASE() AND table_name IN ('rol','modulo','pagina','permiso')");
    return $r && (int) $r->fetch_assoc()['c'] === 4;
}

// Devuelve [clave_de_pagina => ['leer'=>bool,'insertar'=>bool,'editar'=>bool,'borrar'=>bool]] para un rol.
// Solo incluye páginas y módulos activos. Un rol inactivo no tiene nada; un rol protegido tiene todo.
function permisos_cargar(mysqli $db, int $idRol): array
{
    $stmt = $db->prepare(
        "SELECT p.pag_clave, p.pag_acciones, r.rol_protegido,
                COALESCE(pm.perm_leer, 0) l, COALESCE(pm.perm_insertar, 0) i,
                COALESCE(pm.perm_editar, 0) e, COALESCE(pm.perm_borrar, 0) b
           FROM rol r
           JOIN pagina p ON p.pag_estado = 'activo'
           JOIN modulo m ON m.id_modulo = p.id_modulo AND m.mod_estado = 'activo'
      LEFT JOIN permiso pm ON pm.id_rol = r.id_rol AND pm.id_pagina = p.id_pagina
          WHERE r.id_rol = ? AND r.rol_estado = 'activo'"
    );
    $stmt->bind_param('i', $idRol);
    $stmt->execute();
    $mapa = [];
    foreach ($stmt->get_result() as $f) {
        $aplica = explode(',', $f['pag_acciones']);
        $todo = (int) $f['rol_protegido'] === 1 || in_array($f['pag_clave'], PERM_PAGINAS_BASICAS, true);
        $mapa[$f['pag_clave']] = [
            'leer' => in_array('leer', $aplica, true) && ($todo || $f['l']),
            'insertar' => in_array('insertar', $aplica, true) && ($todo || $f['i']),
            'editar' => in_array('editar', $aplica, true) && ($todo || $f['e']),
            'borrar' => in_array('borrar', $aplica, true) && ($todo || $f['b']),
        ];
    }
    $stmt->close();
    return $mapa;
}

// Revalida al usuario de la sesión contra la base y carga sus permisos. Se llama en cada petición:
// si lo bloquearon, lo dieron de baja o le cambiaron el rol, el efecto es inmediato (sin esperar a que cierre sesión).
// Devuelve la fila del usuario, o null si ya no puede estar dentro.
function permisos_iniciar(mysqli $db): ?array
{
    $idUser = (int) ($_SESSION['id_user'] ?? 0);
    if ($idUser <= 0) {
        return null;
    }
    $stmt = $db->prepare("SELECT u.id_user, u.username, u.status, u.id_rol, r.rol_descripcion
                            FROM usuarios u LEFT JOIN rol r ON r.id_rol = u.id_rol WHERE u.id_user = ?");
    $stmt->bind_param('i', $idUser);
    $stmt->execute();
    $u = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$u || $u['status'] !== 'activo') {
        return $u ?: null;
    }

    $m = &permisos_mem();
    $m['id_rol'] = $u['id_rol'] !== null ? (int) $u['id_rol'] : null;
    $m['rol'] = (string) ($u['rol_descripcion'] ?? '');
    $m['mapa'] = $m['id_rol'] ? permisos_cargar($db, $m['id_rol']) : [];

    // Se mantienen sincronizados los datos de sesión que el resto del sistema todavía lee
    $_SESSION['id_rol'] = $m['id_rol'];
    $_SESSION['permisos_acceso'] = $m['rol'];
    return $u;
}

/* ---------------------------------------------------------------------------------------------
   Preguntas de acceso
   --------------------------------------------------------------------------------------------- */
function puede(string $pagina, string $accion = 'leer'): bool
{
    $m = &permisos_mem();
    return !empty($m['mapa'][$pagina][$accion]);
}

function puede_alguna(string $pagina, array $acciones): bool
{
    foreach ($acciones as $a) {
        if (puede($pagina, $a)) {
            return true;
        }
    }
    return false;
}

/* ---------------------------------------------------------------------------------------------
   Acciones a partir de las rutas y de los procesos
   --------------------------------------------------------------------------------------------- */

// Página dueña de una ruta: las pantallas "form_*" cuelgan de la página que indican en 'menu'.
function route_pagina(string $clave, array $route): string
{
    return $route['menu'] ?? $clave;
}

// Acción que exige abrir una ruta: listados/informes = leer; formularios según ?form=add|edit|detalle.
function route_accion(string $clave, array $get): string
{
    if (strpos($clave, 'form_') !== 0) {
        return 'leer';
    }
    $f = $get['form'] ?? '';
    return $f === 'add' ? 'insertar' : ($f === 'detalle' ? 'leer' : 'editar');
}

// Acción que realiza un proceso según su parámetro act=...
function accion_desde_act(string $act): string
{
    $act = strtolower($act);
    if (in_array($act, ['insert', 'add', 'save', 'create'], true)) {
        return 'insertar';
    }
    if (in_array($act, ['delete', 'cancel', 'anular', 'anulado', 'off', 'rechazado', 'remove', 'clear_borrador'], true)) {
        return 'borrar';
    }
    if (in_array($act, ['update', 'modify', 'confirm', 'on', 'aprobar', 'edit'], true)) {
        return 'editar';
    }
    return 'cualquiera';   // acciones auxiliares (modal, select, ajuste_p, add/delete de líneas...): basta insertar o editar
}

/* ---------------------------------------------------------------------------------------------
   Barrera para endpoints (proses.php, process.php, ajax/*.php, print.php)
   --------------------------------------------------------------------------------------------- */

/*
  Debe llamarse al principio del endpoint, DESPUÉS de session_start() si el archivo la usa.
    $accion  : 'leer' | 'insertar' | 'editar' | 'borrar' | 'cualquiera' | null (se deduce de ?act=)
    $pagina  : clave de la página (o lista de claves)
    $formato : cómo responder si se deniega: 'pagina' (HTML), 'json' o 'fragmento' (div para inyectar en una pantalla)
*/
function guard_endpoint(string|array $pagina, ?string $accion = null, string $formato = 'pagina'): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    require_once __DIR__ . '/database.php';
    $db = $GLOBALS['mysqli'];

    if (empty($_SESSION['id_user']) || empty($_SESSION['username'])) {
        permisos_denegar($formato, 'Tu sesión no está iniciada o venció. Vuelve a iniciar sesión.', 401);
    }
    if (!permisos_tablas_ok($db)) {
        permisos_denegar($formato, 'Falta aplicar la migración de permisos de la base de datos.', 503);
    }
    $u = permisos_iniciar($db);
    if (!$u || $u['status'] !== 'activo') {
        permisos_denegar($formato, 'Tu usuario está bloqueado o fue dado de baja.', 403);
    }

    $accion = $accion ?? accion_desde_act((string) ($_GET['act'] ?? $_POST['act'] ?? ''));
    // $pagina puede ser una lista (endpoints compartidos por varias pantallas): alcanza con tener acceso a una de ellas
    $ok = false;
    foreach ((array) $pagina as $p) {
        if ($accion === 'cualquiera' ? puede_alguna($p, ['insertar', 'editar']) : puede($p, $accion)) {
            $ok = true;
            break;
        }
    }
    if (!$ok) {
        permisos_denegar($formato, 'No tienes permiso para realizar esta acción.', 403);
    }
}

function permisos_denegar(string $formato, string $mensaje, int $http = 403): void
{
    http_response_code($http);
    $m = htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8');
    if ($formato === 'json') {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => $mensaje]);
    } elseif ($formato === 'fragmento') {
        echo "<div class='alert alert-danger' role='alert'><i class='bi bi-x-circle'></i> $m</div>";
    } else {
        echo "<!DOCTYPE html><html lang='es'><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width, initial-scale=1'>"
            . "<title>Sin permiso · Sysweb</title></head>"
            . "<body style='font-family:system-ui,sans-serif;background:#f4f6f9;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0'>"
            . "<div style='background:#fff;border:1px solid #dee2e6;border-radius:.5rem;padding:2rem;max-width:420px;text-align:center'>"
            . "<h1 style='font-size:1.4rem;margin:0 0 .75rem'>Acceso denegado</h1><p style='color:#555'>$m</p>"
            . "<a href='javascript:history.back()'>Volver</a> · <a href='/'>Ir al inicio</a></div></body></html>";
    }
    exit();
}

/* ---------------------------------------------------------------------------------------------
   Recorte de la interfaz: quita de la pantalla los botones/enlaces de acciones que el usuario no puede hacer.
   Es una ayuda visual (el bloqueo real lo hacen guard_endpoint y el router); evita mostrar botones que fallarían.
   --------------------------------------------------------------------------------------------- */
function permisos_recortar(string $html, array $routes): string
{
    // carpeta de modules/ -> clave de página (ej.: "compras" -> "compra")
    static $carpetas = null;
    if ($carpetas === null) {
        $carpetas = [];
        foreach ($routes as $clave => $r) {
            if (!isset($r['menu'])) {
                $carpetas[dirname($r['file'])] ??= $clave;
            }
        }
    }

    return preg_replace_callback('#<a\b[^>]*?\bhref\s*=\s*(["\'])(.*?)\1[^>]*>.*?</a\s*>#is', function ($m) use ($routes, $carpetas) {
        $href = html_entity_decode($m[2]);
        $pagina = $accion = null;

        if (preg_match('#[?&]module=(form_[a-z_]+)#i', $href, $mm) && isset($routes[$mm[1]])) {
            $pagina = route_pagina($mm[1], $routes[$mm[1]]);
            parse_str((string) parse_url($href, PHP_URL_QUERY), $q);
            $accion = route_accion($mm[1], $q);
        } elseif (preg_match('#modules/([a-z_]+)/(?:proses|process)\.php\?(.*)#i', $href, $mm)) {
            parse_str($mm[2], $q);
            $pagina = $carpetas[$mm[1]] ?? null;
            $accion = accion_desde_act((string) ($q['act'] ?? ''));
        }

        if ($pagina === null || $accion === null || $accion === 'cualquiera') {
            return $m[0];
        }
        return puede($pagina, $accion) ? $m[0] : '';
    }, $html);
}
