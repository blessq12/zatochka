<?php

namespace App\Application\OrderFulfillment\Dto;

use App\Domain\OrderFulfillment\Enum\DocumentType;

final readonly class GeneratedDocument
{
    public function __construct(
        public string $content,
        public string $filename,
        public DocumentType $type,
    ) {}
}
