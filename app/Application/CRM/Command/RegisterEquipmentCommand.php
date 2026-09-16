<?php

namespace App\Application\CRM\Command;

use App\Application\CRM\DTO\EquipmentPartDTO;

final readonly class RegisterEquipmentCommand
{
    /**
     * @param list<EquipmentPartDTO> $parts
     */
    public function __construct(
        public int $equipmentId,
        public string $title,
        public string $brand,
        public string $modelName,
        public string $equipmentType,
        public ?int $clientId = null,
        public array $parts = [],
    ) {}
}
