<?php

namespace App\Infrastructure\Documents\Pdf;

use App\Application\Documents\Port\PdfRenderer;
use Dompdf\Dompdf;
use Dompdf\Options;

final class DompdfPdfRenderer implements PdfRenderer
{
    public function render(string $html): string
    {
        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output() ?? '';
    }
}
