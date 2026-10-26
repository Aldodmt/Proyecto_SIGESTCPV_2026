<?php
require_once __DIR__ . '/../../config/permisos.php';
guard_endpoint('deposito', 'leer');   // control de acceso por rol (config/permisos.php)
require '../../assets/plugins/vendor/autoload.php';

use Spipu\Html2Pdf\Html2Pdf; //barra invertida alt + 92 \

ob_start();
include 'print_view.php';
$content = ob_get_clean();
$nombrearchivo = "reporte_deposito.pdf";

$html2pdf = new Html2Pdf('P', 'A4', 'es');
$html2pdf->pdf->SetDisplayMode('fullpage');
$html2pdf->writeHTML($content);
$html2pdf->output($nombrearchivo);