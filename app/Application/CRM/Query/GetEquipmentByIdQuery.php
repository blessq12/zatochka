<?php

namespace App\Application\CRM\Query;

final readonly class GetEquipmentByIdQuery
{
    public function __construct(
        public int $equipmentId,
    ) {}
}
