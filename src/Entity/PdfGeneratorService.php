<?php
// src/Service/PdfGeneratorService.php
namespace App\Entity;
use App\Entity\User;

use TCPDF;

class PdfGeneratorService
{
    public function generatePdf($html)
    {
        $pdf = new TCPDF();
        $pdf->AddPage();
        // Add logo to the PDF
        $image_file = 'public/assets/images/logo.png';
        $pdf->Image($image_file, 10, 10, '', '', '', '', 'T', false, 300, '', false, false, 0, false, false, false);

        $pdf->writeHTML($html, true, false, true, false, '');
        return $pdf->Output('document.pdf', 'S');
    }
}
?>