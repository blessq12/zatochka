<?php

namespace App\Infrastructure\OrderFulfillment\Pdf;

use App\Application\OrderFulfillment\Port\PdfRendererInterface;
use Barryvdh\DomPDF\Facade\Pdf;

final class DomPdfRenderer implements PdfRendererInterface
{
    public function render(string $view, array $viewData): string
    {
        return Pdf::loadView($view, $viewData)
            ->setPaper('a4')
            ->output();
    }
}
