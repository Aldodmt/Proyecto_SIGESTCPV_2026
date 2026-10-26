<?php
require_once __DIR__ . '/../config/permisos.php';
guard_endpoint(['pedido', 'presupuesto', 'orden_c', 'compra'], 'cualquiera', 'fragmento');   // control de acceso por rol (config/permisos.php)
function paginate($reload, $page, $tpages, $adjacents)
{
    $prevlabel = "&lsaquo; Anterior";
    $nextlabel = "Siguiente &rsaquo;";
    $out = '<ul class="pagination justify-content-center">';

    // Btn anterior
    if ($page == 1) {
        $out .= "<li class='page-item disabled'><span class='page-link'>$prevlabel</span></li>";
    } else {
        $out .= "<li class='page-item'><a class='page-link' href='javascript:void(0);' onclick='load(" . ($page - 1) . ")'>$prevlabel</a></li>";
    }

    // Primera página
    if ($page > ($adjacents + 1)) {
        $out .= "<li class='page-item'><a class='page-link' href='javascript:void(0);' onclick='load(1)'>1</a></li>";
    }

    // Intervalo inicial
    if ($page > ($adjacents + 2)) {
        $out .= "<li class='page-item disabled'><span class='page-link'>...</span></li>";
    }

    // Páginas intermedias
    $pmin = ($page > $adjacents) ? ($page - $adjacents) : 1;
    $pmax = ($page < ($tpages - $adjacents)) ? ($page + $adjacents) : $tpages;
    for ($i = $pmin; $i <= $pmax; $i++) {
        if ($i == $page) {
            $out .= "<li class='page-item active'><span class='page-link'>$i</span></li>";
        } else {
            $out .= "<li class='page-item'><a class='page-link' href='javascript:void(0);' onclick='load($i)'>$i</a></li>";
        }
    }

    // Intervalo final
    if ($page < ($tpages - $adjacents - 1)) {
        $out .= "<li class='page-item disabled'><span class='page-link'>...</span></li>";
    }

    // Última página
    if ($page < ($tpages - $adjacents - 1)) {
        $out .= "<li class='page-item'><a class='page-link' href='javascript:void(0);' onclick='load($tpages)'>$tpages</a></li>";
    }

    // Btn siguiente
    if ($page < $tpages) {
        $out .= "<li class='page-item'><a class='page-link' href='javascript:void(0);' onclick='load(" . ($page + 1) . ")'>$nextlabel</a></li>";
    } else {
        $out .= "<li class='page-item disabled'><span class='page-link'>$nextlabel</span></li>";
    }

    $out .= "</ul>";
    return $out;
}
?>