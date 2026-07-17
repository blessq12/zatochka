<?php

namespace App\Application\Workshop\ServiceType;

use App\Application\Workshop\DTO\OrderProductionContextDTO;
use App\Domain\Workshop\Entity\ProductionTask;

interface ProductionCompletionPolicy
{
    public function assertReadyToFinish(OrderProductionContextDTO $context, ProductionTask $task): void;
}
