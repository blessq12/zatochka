<?php

namespace App\Application\Workshop\ReadPort;

use App\Application\Workshop\DTO\ProductionTaskDTO;

interface ProductionTaskReadPort
{
    public function findById(int $productionTaskId): ?ProductionTaskDTO;

    /** @return list<ProductionTaskDTO> */
    public function listQueued(): array;
}
