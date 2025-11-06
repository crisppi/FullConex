<?php
declare(strict_types=1);
ini_set('display_errors','1');
error_reporting(E_ALL);

// 1) Limpando QUALQUER buffer/eco anterior:
while (ob_get_level() > 0) { ob_end_clean(); }

// 2) Tenta localizar o TCPDF:
$paths = [
    __DIR__ . '/tcpdf_min/tcpdf.php',                         // pacote “tcpdf_min”
    __DIR__ . '/vendor/tecnickcom/tcpdf/tcpdf.php',           // via composer
    __DIR__ . '/lib/tcpdf/tcpdf.php',                         // pasta custom
];
$loaded = false;
foreach ($paths as $p) {
    if (is_file($p)) {
        require_once $p;
        $loaded = true;
        break;
    }
}
if (!$loaded) {
    exit('TCPDF não encontrado. Verifique se está em tcpdf_min/, vendor/tecnickcom/ ou lib/tcpdf/.');
}

// 3) Gera um PDF mínimo:
while (ob_get_level() > 0) { ob_end_clean(); } // de novo, por segurança
$pdf = new TCPDF();
$pdf->SetCreator('Sanity');
$pdf->SetAuthor('Sanity');
$pdf->SetTitle('TCPDF OK');
$pdf->AddPage();
$pdf->SetFont('helvetica','',12);
$pdf->Write(0, "Se você está vendo este PDF, o TCPDF está OK.");
$pdf->Output('tcpdf_ok.pdf','I');