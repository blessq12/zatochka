<?php

namespace App\Application\Workshop\ServiceType;

use App\Domain\Order\Entity\Order;
use App\Domain\Workshop\Entity\ProductionTask;

interface ProductionCompletionPolicy
{
    public function assertReadyToFinish(Order $order, ProductionTask $task): void;
}
