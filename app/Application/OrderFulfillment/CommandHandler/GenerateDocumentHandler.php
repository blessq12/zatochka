<?php

namespace App\Application\OrderFulfillment\CommandHandler;

use App\Application\OrderFulfillment\Command\GenerateDocumentCommand;
use App\Application\OrderFulfillment\Dto\GeneratedDocument;
use App\Application\OrderFulfillment\Port\PdfRendererInterface;
use App\Application\OrderFulfillment\ReadModel\OrderDocumentReadModelBuilder;
use App\Application\OrderFulfillment\Support\OrderLoader;
use App\Domain\OrderFulfillment\Enum\DocumentType;
use App\Domain\OrderFulfillment\Enum\OrderStatus;
use App\Domain\OrderFulfillment\Event\DocumentGenerated;
use App\Domain\OrderFulfillment\Exception\OrderPolicyViolation;

final class GenerateDocumentHandler
{
    public function __construct(
        private OrderLoader $orderLoader,
        private OrderDocumentReadModelBuilder $readModelBuilder,
        private PdfRendererInterface $pdfRenderer,
    ) {}

    public function handle(GenerateDocumentCommand $command): GeneratedDocument
    {
        $order = $this->orderLoader->load($command->orderId);

        if ($order->status() === OrderStatus::Cancelled) {
            throw new OrderPolicyViolation('Нельзя сформировать документ для отменённого заказа.');
        }

        if ($command->type === DocumentType::HandoverAct
            && ! in_array($order->status(), [OrderStatus::Ready, OrderStatus::Issued], true)) {
            throw new OrderPolicyViolation('Акт выдачи доступен только для заказов в статусе «Готов» или «Выдан».');
        }

        $data = $this->readModelBuilder->build($order, $command->managerName);

        $content = $this->pdfRenderer->render($command->type->viewName(), [
            'data' => $data,
            'documentTitle' => $command->type->label(),
        ]);

        $filename = sprintf(
            '%s_%s.pdf',
            $command->type->value,
            $order->orderNumber()->value,
        );

        event(new DocumentGenerated(
            orderId: $command->orderId,
            type: $command->type,
            userId: $command->userId,
        ));

        return new GeneratedDocument(
            content: $content,
            filename: $filename,
            type: $command->type,
        );
    }
}
