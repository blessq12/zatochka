<?php

namespace App\Domain\Catalog\Repositories;

use App\Domain\Catalog\Entities\PriceItem;

interface PriceItemRepositoryInterface
{
    public function save(PriceItem $item): PriceItem;
}
