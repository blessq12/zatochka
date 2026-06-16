<?php

namespace App\Domain\Equipment\Event;

use App\Domain\Equipment\Entity\Equipment;

final readonly class EquipmentRegistered
{
    public function __construct(
        public Equipment $equipment,
    ) {}
}
