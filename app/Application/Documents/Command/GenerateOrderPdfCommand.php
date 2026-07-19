<?php

namespace App\Application\Documents\Command;

use App\Domain\Documents\VO\PdfTemplateKind;

final readonly class GenerateOrderPdfCommand
{
    public function __construct(
        public string $orderId,
        public PdfTemplateKind $kind,
    ) {}
}
