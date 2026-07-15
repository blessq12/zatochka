<?php

namespace App\Application\Order\Command;

use App\Application\Order\DTO\ReceptionItemDTO;

final readonly class CompleteReceptionCommand
{
    /**
     * @param list<ReceptionItemDTO> $items
     */
    public function __construct(
        public int $orderId,
        public array $items,
    ) {}
}
