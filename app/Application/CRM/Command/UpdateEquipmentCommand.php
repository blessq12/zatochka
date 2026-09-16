<?php

namespace App\Application\CRM\Command;

final readonly class UpdateEquipmentCommand
{
    public function __construct(
        public int $equipmentId,
        public string $title,
        public string $brand,
        public string $modelName,
        public string $equipmentType,
        public ?int $clientId = null,
    ) {}
}
