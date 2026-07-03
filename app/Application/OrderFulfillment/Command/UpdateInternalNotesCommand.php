<?php

namespace App\Application\OrderFulfillment\Command;

final readonly class UpdateInternalNotesCommand
{
    public function __construct(
        public int $orderId,
        public int $masterId,
        public ?string $notes,
    ) {}
}
