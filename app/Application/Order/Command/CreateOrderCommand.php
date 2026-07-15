<?php

namespace App\Application\Order\Command;

use App\Application\Order\DTO\CreateOrderItemDTO;

final readonly class CreateOrderCommand
{
    /**
     * @param list<CreateOrderItemDTO> $items
     */
    public function __construct(
        public int $orderId,
        public int $clientId,
        public string $estimatedAmount,
        public array $items,
        public string $serviceType,
        public string $billingType,
        public string $urgency,
        public bool $deliveryRequired = false,
        public ?string $defects = null,
        public ?string $internalNotes = null,
        public string $estimatedCurrency = 'RUB',
        public ?string $newClientName = null,
        public ?string $newClientPhone = null,
        public ?string $newClientEmail = null,
        public ?int $warrantySourceOrderId = null,
    ) {}

    public function shouldCreateClient(): bool
    {
        return filled($this->newClientPhone) && filled($this->newClientName);
    }
}
