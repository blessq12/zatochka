<?php

namespace App\Application\Equipment\Query;

final readonly class GetEquipmentOrderHistoryQuery
{
    public function __construct(
        public int $equipmentId,
        public int $limit = 20,
    ) {}
}
