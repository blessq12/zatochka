<?php

namespace App\Application\Order\DTO;

use App\Domain\Order\Enum\DocumentType;

final readonly class GeneratedDocument
{
    public function __construct(
        public string $content,
        public string $filename,
        public DocumentType $type,
    ) {}
}
