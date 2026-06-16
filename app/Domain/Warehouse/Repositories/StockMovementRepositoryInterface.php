<?php

namespace App\Domain\Warehouse\Repositories;

use App\Domain\Warehouse\Entities\StockMovement;

interface StockMovementRepositoryInterface
{
    public function save(StockMovement $movement): StockMovement;
}
