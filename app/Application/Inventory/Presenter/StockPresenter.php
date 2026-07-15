<?php

namespace App\Application\Inventory\Presenter;

use App\Application\Inventory\DTO\StockItemDTO;

interface StockPresenter
{
    /** @return array<string, mixed> */
    public function present(StockItemDTO $stockItem): array;
}
