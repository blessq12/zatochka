<?php

namespace App\Application\Order\Command;

use App\Application\Order\DTO\GeneratedDocument;
use App\Application\Order\Port\DocumentTemplateRendererInterface;
use App\Application\Order\Port\PdfRendererInterface;
use App\Application\Order\ReadModel\OrderDocumentData;
use App\Application\Order\ReadModel\OrderDocumentReadModelBuilder;
use App\Domain\Order\Enum\DocumentType;
use App\Domain\Order\OrderStatus;
use App\Domain\Order\Repository\DocumentTemplateRepository;
use App\Domain\Order\Repository\OrderRepository;
use App\Shared\Domain\DomainException;

final readonly class GenerateDocumentHandler
{
    public function __construct(
        private OrderRepository $orders,
        private OrderDocumentReadModelBuilder $readModelBuilder,
        private DocumentTemplateRepository $templateRepository,
        private DocumentTemplateRendererInterface $templateRenderer,
        private PdfRendererInterface $pdfRenderer,
    ) {}

    public function handle(
        int $orderId,
        DocumentType $type,
        ?string $managerName = null,
    ): GeneratedDocument {
        $order = $this->orders->findById($orderId);
        if ($order === null) {
            throw new DomainException('Order not found.');
        }

        if ($order->status() === OrderStatus::Cancelled) {
            throw new DomainException('Нельзя сформировать документ для отменённого заказа.');
        }

        if ($type === DocumentType::HandoverAct
            && ! in_array($order->status(), [OrderStatus::Ready, OrderStatus::Issued], true)) {
            throw new DomainException('Акт выдачи доступен только для заказов в статусе «Готов» или «Выдан».');
        }

        $data = $this->readModelBuilder->build($order, $managerName);
        $documentTitle = $type->label();
        $content = $this->renderPdf($type, $data, $documentTitle);

        return new GeneratedDocument(
            content: $content,
            filename: sprintf('%s_%s.pdf', $type->value, $order->id()),
            type: $type,
        );
    }

    private function renderPdf(DocumentType $type, OrderDocumentData $data, string $documentTitle): string
    {
        $template = $this->templateRepository->findByType($type);

        if ($template === null || trim($template->body()) === '') {
            throw new DomainException(sprintf(
                'Шаблон «%s» не настроен. Заполните его в разделе «Документы».',
                $type->label(),
            ));
        }

        $bodyHtml = $this->templateRenderer->render($template->body(), $data, $documentTitle);

        return $this->pdfRenderer->render('documents.layouts.custom-body', [
            'data' => $data,
            'documentTitle' => $documentTitle,
            'bodyHtml' => $bodyHtml,
        ]);
    }
}
