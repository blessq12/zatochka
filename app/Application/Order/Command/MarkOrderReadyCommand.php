<?php

namespace App\Application\Order\Command;

final readonly class MarkOrderReadyCommand
{
    public function __construct(
        public string $orderId,
    ) {}
}
