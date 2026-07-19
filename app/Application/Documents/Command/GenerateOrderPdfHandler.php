<?php

namespace App\Application\Documents\Command;

use App\Application\Documents\DTO\PdfResult;
use App\Application\Documents\Port\CompanyDocumentReadPort;
use App\Application\Documents\Port\OrderDocumentReadPort;
use App\Application\Documents\Port\PdfRenderer;
use App\Application\Documents\Service\PlaceholderBag;
use App\Domain\Documents\Repository\DocumentTemplateRepository;
use App\Domain\Documents\VO\PdfTemplateKind;
use App\Domain\Order\VO\OrderStatus;
use App\Shared\Domain\DomainException;

final readonly class GenerateOrderPdfHandler
{
    public function __construct(
        private DocumentTemplateRepository $templates,
        private OrderDocumentReadPort $orders,
        private CompanyDocumentReadPort $company,
        private PlaceholderBag $placeholders,
        private PdfRenderer $pdfRenderer,
    ) {}

    public function handle(GenerateOrderPdfCommand $command): PdfResult
    {
        $order = $this->orders->findById($command->orderId);

        if ($order === null) {
            throw new DomainException('Order not found.');
        }

        $this->assertStatusAllows($command->kind, $order->status);

        $template = $this->templates->getByKind($command->kind);

        if (! $template->isActive()) {
            throw new DomainException('Document template is inactive.');
        }

        $vars = array_merge(
            $this->company->get()->placeholders,
            $order->placeholders,
        );

        $html = $this->placeholders->fill($template->bodyHtml(), $vars);
        $bytes = $this->pdfRenderer->render($html);
        $filename = sprintf(
            '%s-%s.pdf',
            $command->kind->value,
            preg_replace('/[^A-Za-z0-9_-]+/', '-', $order->orderNumber) ?: $order->orderId,
        );

        return new PdfResult($bytes, $filename);
    }

    private function assertStatusAllows(PdfTemplateKind $kind, string $statusValue): void
    {
        $status = OrderStatus::tryFrom($statusValue);

        if ($status === null) {
            throw new DomainException('Unknown order status.');
        }

        $allowed = match ($kind) {
            PdfTemplateKind::ReceptionReceipt => [
                OrderStatus::Created,
            ],
            PdfTemplateKind::IssueAct => [
                OrderStatus::Ready,
                OrderStatus::Issued,
            ],
        };

        if (! in_array($status, $allowed, true)) {
            throw new DomainException(sprintf(
                'Cannot generate %s for order status "%s".',
                $kind->label(),
                $status->label(),
            ));
        }
    }
}
