<?php

namespace App\Application\Equipment\Query;

final readonly class GetEquipmentByIdQuery
{
    public function __construct(
        public int $equipmentId,
    ) {}
}
