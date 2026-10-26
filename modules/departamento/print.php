<?php
require_once __DIR__ . '/../../config/permisos.php';
guard_endpoint('departamento', 'leer');   // control de acceso por rol (config/permisos.php)
require_once '../../assets/plugins/vendor/autoload.php';

use Spipu\Html2Pdf\Html2Pdf;

ob_start();
include 'print_view.php';

$content = ob_get_clean();
$nombrearchivo = "report_departameto.pdf";

$html2pdf = new Html2Pdf();
$html2pdf->writeHTML($content);
$html2pdf->output($nombrearchivo);

?>