<?php

namespace App\Application\Order\Command;

use App\Domain\Order\VO\PdfTemplateKind;

final readonly class GenerateOrderPdfCommand
{
    public function __construct(
        public string $orderId,
        public PdfTemplateKind $kind,
    ) {}
}
