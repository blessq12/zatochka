<?php

namespace App\Application\OrderFulfillment\Command;

use App\Domain\OrderFulfillment\Enum\DocumentType;

final class PreviewDocumentTemplateCommand
{
    public function __construct(
        public DocumentType $type,
        public string $body,
        public ?int $orderId,
        public ?string $managerName,
    ) {}
}
