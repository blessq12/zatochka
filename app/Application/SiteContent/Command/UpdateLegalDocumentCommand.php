<?php

namespace App\Application\SiteContent\Command;

use App\Domain\SiteContent\VO\DocumentType;

final readonly class UpdateLegalDocumentCommand
{
    public function __construct(
        public DocumentType $type,
        public string $title,
        public string $bodyHtml,
    ) {}
}
