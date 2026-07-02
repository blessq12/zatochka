<?php

namespace App\Domain\OrderFulfillment\Event;

use App\Domain\OrderFulfillment\Enum\DocumentType;

final readonly class DocumentTemplateUpdated
{
    public function __construct(
        public DocumentType $type,
        public ?int $userId,
    ) {}
}
