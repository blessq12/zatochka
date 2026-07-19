<?php

namespace App\Http\Controllers\Documents;

use App\Application\Documents\Command\GenerateOrderPdfCommand;
use App\Application\Documents\Command\GenerateOrderPdfHandler;
use App\Domain\Documents\VO\PdfTemplateKind;
use App\Http\Controllers\Controller;
use App\Shared\Domain\DomainException;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

final class PrintOrderDocumentController extends Controller
{
    public function __construct(
        private GenerateOrderPdfHandler $generateOrderPdf,
    ) {}

    public function page(string $orderId, string $kind): View|SymfonyResponse
    {
        $pdfKind = PdfTemplateKind::tryFrom($kind);

        if ($pdfKind === null) {
            abort(404);
        }

        return view('documents.order-print', [
            'orderId' => $orderId,
            'kind' => $pdfKind->value,
            'title' => $pdfKind->label(),
            'pdfUrl' => route('documents.orders.print.pdf', [
                'orderId' => $orderId,
                'kind' => $pdfKind->value,
            ]),
        ]);
    }

    public function pdf(string $orderId, string $kind): Response
    {
        $pdfKind = PdfTemplateKind::tryFrom($kind);

        if ($pdfKind === null) {
            abort(404);
        }

        try {
            $result = $this->generateOrderPdf->handle(
                new GenerateOrderPdfCommand($orderId, $pdfKind),
            );
        } catch (DomainException $exception) {
            abort(422, $exception->getMessage());
        }

        return response($result->bytes, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$result->filename.'"',
            'Cache-Control' => 'private, no-store, max-age=0',
        ]);
    }
}
