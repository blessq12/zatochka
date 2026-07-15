<?php

namespace App\Application\Equipment\Command;

final readonly class RegisterEquipmentCommand
{
    public function __construct(
        public int $equipmentId,
        public int $clientId,
        public string $title,
        public ?string $notes = null,
    ) {}
}
