<?php
// Utilidades de interfaz compartidas por los módulos nuevos (catálogos, personas, funcionarios, roles, permisos...).

function h($s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

// Mensajes estándar que se muestran con ?alert=N (el 9 trae el detalle en ?msg=...)
function ui_alertas(): void
{
    static $mapa = [
        1 => ['success', 'bi-check-circle', 'Registro creado correctamente.'],
        2 => ['success', 'bi-check-circle', 'Registro modificado correctamente.'],
        3 => ['success', 'bi-check-circle', 'Registro eliminado correctamente.'],
        4 => ['danger', 'bi-x-circle', 'No se pudo realizar la operación.'],
        5 => ['warning', 'bi-exclamation-triangle', 'Ya existe un registro con esos datos.'],
        6 => ['warning', 'bi-exclamation-triangle', 'El registro está en uso y no se puede eliminar. Puedes darlo de baja.'],
        7 => ['success', 'bi-check-circle', 'Registro dado de baja.'],
        8 => ['success', 'bi-check-circle', 'Registro reactivado.'],
        9 => ['danger', 'bi-x-circle', ''],
        10 => ['success', 'bi-check-circle', 'Permisos guardados correctamente.'],
        11 => ['warning', 'bi-exclamation-triangle', 'Este registro está protegido y no se puede modificar.'],
        12 => ['success', 'bi-check-circle', ''],
    ];
    $n = (int) ($_GET['alert'] ?? 0);
    if (!isset($mapa[$n])) {
        return;
    }
    [$nivel, $icono, $texto] = $mapa[$n];
    if ($n === 9 || $n === 12) {
        $texto = h($_GET['msg'] ?? ($n === 9 ? 'Datos inválidos.' : 'Operación realizada.'));
    }
    echo "<div class='alert alert-$nivel alert-dismissible fade show' role='alert'><i class='bi $icono'></i> $texto"
        . "<button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Cerrar'></button></div>";
}

function ui_estado_badge(string $estado): string
{
    return $estado === 'activo'
        ? '<span class="badge text-bg-success">Activo</span>'
        : '<span class="badge text-bg-secondary">Inactivo</span>';
}

// Cabecera de pantalla (migas de pan + título + botón opcional). $migas: [['Texto', '?module=x'], ['Actual']]
function ui_cabecera(string $titulo, string $icono, array $migas, ?string $btnTexto = null, ?string $btnHref = null): void
{
    echo '<section class="app-content-header"><nav aria-label="breadcrumb"><ol class="breadcrumb">'
        . '<li class="breadcrumb-item"><a href="?module=start"><i class="bi bi-house-door"></i> Inicio</a></li>';
    foreach ($migas as $m) {
        echo isset($m[1])
            ? '<li class="breadcrumb-item"><a href="' . h($m[1]) . '">' . h($m[0]) . '</a></li>'
            : '<li class="breadcrumb-item active" aria-current="page">' . h($m[0]) . '</li>';
    }
    echo '</ol></nav><h1><i class="bi ' . h($icono) . '"></i> ' . h($titulo);
    if ($btnTexto && $btnHref) {
        echo ' <a class="btn btn-primary btn-sm float-end" href="' . h($btnHref) . '"><i class="bi bi-plus-lg"></i> ' . h($btnTexto) . '</a>';
    }
    echo '</h1></section>';
}

// Redirección de los procesos hacia la pantalla principal del módulo
function proses_volver(string $modulo, int $alert, string $msg = ''): void
{
    $url = '../../main.php?module=' . urlencode($modulo) . '&alert=' . $alert;
    if ($msg !== '') {
        $url .= '&msg=' . urlencode($msg);
    }
    header('Location: ' . $url);
    exit();
}

// Registro de auditoría de cambios de seguridad (usa la misma tabla que el registro de accesos)
function ui_auditar(string $detalle): void
{
    require_once __DIR__ . '/auth.php';
    $db = $GLOBALS['mysqli'];
    auth_log($db, (int) ($_SESSION['id_user'] ?? 0) ?: null, (string) ($_SESSION['username'] ?? ''), 0, 'AUDITORIA', mb_substr($detalle, 0, 120), 0);
}
