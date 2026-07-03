<?php

namespace App\Application\OrderFulfillment\CommandHandler;

use App\Application\OrderFulfillment\Command\PreviewDocumentTemplateCommand;
use App\Application\OrderFulfillment\Dto\GeneratedDocument;
use App\Application\OrderFulfillment\Port\DocumentTemplateRendererInterface;
use App\Application\OrderFulfillment\Port\PdfRendererInterface;
use App\Application\OrderFulfillment\ReadModel\OrderDocumentReadModelBuilder;
use App\Application\OrderFulfillment\Support\OrderLoader;
use App\Infrastructure\OrderFulfillment\Persistence\Eloquent\OrderModel;

final class PreviewDocumentTemplateHandler
{
    public function __construct(
        private OrderLoader $orderLoader,
        private OrderDocumentReadModelBuilder $readModelBuilder,
        private DocumentTemplateRendererInterface $templateRenderer,
        private PdfRendererInterface $pdfRenderer,
    ) {}

    public function handle(PreviewDocumentTemplateCommand $command): GeneratedDocument
    {
        $order = $this->orderLoader->load($command->orderId ?? $this->resolvePreviewOrderId());

        $data = $this->readModelBuilder->build($order, $command->managerName);
        $documentTitle = $command->type->label();

        $bodyHtml = $this->templateRenderer->render($command->body, $data, $documentTitle);

        $content = $this->pdfRenderer->render('documents.layouts.custom-body', [
            'data' => $data,
            'documentTitle' => $documentTitle,
            'bodyHtml' => $bodyHtml,
        ]);

        return new GeneratedDocument(
            content: $content,
            filename: sprintf('preview_%s.pdf', $command->type->value),
            type: $command->type,
        );
    }

    private function resolvePreviewOrderId(): int
    {
        $order = OrderModel::query()
            ->latest('id')
            ->first();

        if ($order === null) {
            throw new \RuntimeException('Нет заказов для предпросмотра шаблона.');
        }

        return $order->id;
    }
}
