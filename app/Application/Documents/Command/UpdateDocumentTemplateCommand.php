<?php

namespace App\Application\Documents\Command;

final readonly class UpdateDocumentTemplateCommand
{
    public function __construct(
        public string $templateId,
        public string $name,
        public string $bodyHtml,
        public bool $isActive,
    ) {}
}
