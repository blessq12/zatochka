<?php

namespace App\Infrastructure\Order\Pdf;

use App\Application\Order\Port\PdfRendererInterface;
use Dompdf\Dompdf;
use Dompdf\Options;

final class DomPdfRenderer implements PdfRendererInterface
{
    public function render(string $view, array $viewData): string
    {
        $html = view($view, $viewData)->render();

        $options = new Options;
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4');
        $dompdf->render();

        return $dompdf->output();
    }
}
