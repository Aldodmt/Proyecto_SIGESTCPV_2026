<?php
require_once __DIR__ . '/../../config/permisos.php';
guard_endpoint('ciudad', 'leer');   // control de acceso por rol (config/permisos.php)
require_once '../../assets/plugins/vendor/autoload.php';

use Spipu\Html2Pdf\Html2Pdf; //alt 92 para barras invertidas

ob_start();
include 'print_view.php';
$content = ob_get_clean();
$nombre = 'reporte_ciudad.pdf';

$html2pdf = new Html2Pdf('P','A4','es');
$html2pdf->pdf->SetDisplayMode('fullpage');
$html2pdf->writeHTML($content);
$html2pdf->output($nombre);


?>