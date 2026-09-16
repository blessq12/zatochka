<?php

namespace App\Application\Order\Command;

final readonly class UpdateDocumentTemplateCommand
{
    public function __construct(
        public string $templateId,
        public string $name,
        public string $bodyHtml,
        public bool $isActive,
    ) {}
}
