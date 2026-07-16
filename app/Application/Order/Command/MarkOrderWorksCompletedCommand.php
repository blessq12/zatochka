<?php

namespace App\Application\Order\Command;

final readonly class MarkOrderWorksCompletedCommand
{
    public function __construct(
        public string $orderId,
    ) {}
}
