<?php

namespace App\Application\Equipment\Command;

final readonly class AddComponentCommand
{
    public function __construct(
        public int $equipmentId,
        public int $componentId,
        public string $name,
    ) {}
}
