<?php

namespace App\Application\Workshop\Query;

final readonly class GetProductionTaskByIdQuery
{
    public function __construct(
        public int $productionTaskId,
    ) {}
}
