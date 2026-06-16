<?php

namespace App\Domain\OrderFulfillment\Event;

use App\Domain\OrderFulfillment\Enum\DocumentType;

final readonly class DocumentGenerated
{
    public function __construct(
        public int $orderId,
        public DocumentType $type,
        public ?int $userId = null,
    ) {}
}
