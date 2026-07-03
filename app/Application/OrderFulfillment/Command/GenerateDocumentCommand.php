<?php

namespace App\Application\OrderFulfillment\Command;

use App\Domain\OrderFulfillment\Enum\DocumentType;

final readonly class GenerateDocumentCommand
{
    public function __construct(
        public int $orderId,
        public DocumentType $type,
        public ?string $managerName = null,
        public ?int $userId = null,
    ) {}
}
