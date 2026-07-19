<?php

namespace App\Application\Documents\Command;

use App\Domain\Documents\VO\DocumentType;

final readonly class UpdateLegalDocumentCommand
{
    public function __construct(
        public DocumentType $type,
        public string $title,
        public string $bodyHtml,
    ) {}
}
