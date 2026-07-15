<?php

namespace App\Application\Workshop\Query;

use App\Application\Workshop\DTO\ProductionTaskDTO;
use App\Application\Workshop\ReadPort\ProductionTaskReadPort;

final readonly class GetProductionTaskByIdHandler
{
    public function __construct(
        private ProductionTaskReadPort $readPort,
    ) {}

    public function handle(GetProductionTaskByIdQuery $query): ?ProductionTaskDTO
    {
        return $this->readPort->findById($query->productionTaskId);
    }
}
