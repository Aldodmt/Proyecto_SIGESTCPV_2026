<?php
require_once __DIR__ . '/../../config/permisos.php';
guard_endpoint('clientes', 'leer');   // control de acceso por rol (config/permisos.php)
require_once '../../assets/plugins/vendor/autoload.php';

use Spipu\Html2Pdf\Html2Pdf; //Barras invertidas alt + 92

ob_start();
include "printview.php";
$content = ob_get_clean();
$nombre = "reporte_clientes.pdf";

$html2pdf = new Html2Pdf('P', 'A4', 'es');
$html2pdf->pdf->setDisplayMode('fullpage');
$html2pdf->writeHTML($content);
$html2pdf->output($nombre);
?>