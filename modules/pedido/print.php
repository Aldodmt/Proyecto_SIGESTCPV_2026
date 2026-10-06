<?php
require_once "../../assets/plugins/vendor/autoload.php";

use Spipu\Html2Pdf\Html2Pdf;

ob_start();
require 'printview.php';
$nombre_archivo = 'pedido_compra.pdf';
$html = ob_get_clean();

$html2pdf = new Html2Pdf('P', 'A4', 'es', true);
$html2pdf->writeHTML($html);
$html2pdf->output($nombre_archivo);



?>