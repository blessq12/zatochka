<?php

namespace App\Application\Equipment\Command;

final readonly class RegisterSerialNumberCommand
{
    public function __construct(
        public int $equipmentId,
        public int $componentId,
        public string $serialNumber,
    ) {}
}
