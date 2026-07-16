<?php

namespace App\Application\Order\DTO;

final readonly class CreateOrderItemDTO
{
    /**
     * @param list<array{name: string, serialNumber?: ?string}> $equipmentParts
     */
    public function __construct(
        public int $orderItemId,
        public ?int $clientEquipmentId = null,
        public ?string $toolName = null,
        public ?string $toolType = null,
        public ?int $quantity = null,
        public ?string $equipmentTitle = null,
        public ?string $equipmentBrand = null,
        public ?string $equipmentModelName = null,
        public ?string $equipmentNotes = null,
        public array $equipmentParts = [],
    ) {}

    public function isNewEquipment(): bool
    {
        return $this->clientEquipmentId === null
            && filled($this->equipmentTitle)
            && filled($this->equipmentBrand)
            && filled($this->equipmentModelName);
    }
}
