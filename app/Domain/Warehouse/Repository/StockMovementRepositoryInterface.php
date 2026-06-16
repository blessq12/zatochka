<?php

namespace App\Domain\Warehouse\Repository;

use App\Domain\Warehouse\Entity\StockMovement;

interface StockMovementRepositoryInterface
{
    public function save(StockMovement $movement): StockMovement;
}
