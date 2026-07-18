<?php

namespace App\Application\Equipment\Command;

use App\Application\Equipment\DTO\EquipmentPartDTO;

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
        public ?string $notes = null,
        public array $parts = [],
    ) {}
}
