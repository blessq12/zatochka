<?php

namespace App\Application\Order\Command;

use App\Application\Order\DTO\GeneratedDocument;
use App\Application\Order\Port\DocumentTemplateRendererInterface;
use App\Application\Order\Port\PdfRendererInterface;
use App\Application\Order\ReadModel\OrderDocumentReadModelBuilder;
use App\Domain\Order\Enum\DocumentType;
use App\Domain\Order\Repository\OrderRepository;
use App\Infrastructure\Order\Eloquent\OrderModel;
use App\Shared\Domain\DomainException;

final readonly class PreviewDocumentTemplateHandler
{
    public function __construct(
        private OrderRepository $orders,
        private OrderDocumentReadModelBuilder $readModelBuilder,
        private DocumentTemplateRendererInterface $templateRenderer,
        private PdfRendererInterface $pdfRenderer,
    ) {}

    public function handle(
        DocumentType $type,
        string $body,
        ?int $orderId = null,
        ?string $managerName = null,
    ): GeneratedDocument {
        $resolvedOrderId = $orderId ?? $this->resolvePreviewOrderId();
        $order = $this->orders->findById($resolvedOrderId);
        if ($order === null) {
            throw new DomainException('Order not found.');
        }

        $data = $this->readModelBuilder->build($order, $managerName);
        $documentTitle = $type->label();
        $bodyHtml = $this->templateRenderer->render($body, $data, $documentTitle);

        $content = $this->pdfRenderer->render('documents.layouts.custom-body', [
            'data' => $data,
            'documentTitle' => $documentTitle,
            'bodyHtml' => $bodyHtml,
        ]);

        return new GeneratedDocument(
            content: $content,
            filename: sprintf('preview_%s.pdf', $type->value),
            type: $type,
        );
    }

    private function resolvePreviewOrderId(): int
    {
        $order = OrderModel::query()->latest('id')->first();
        if ($order === null) {
            throw new DomainException('Нет заказов для предпросмотра шаблона.');
        }

        return (int) $order->id;
    }
}
